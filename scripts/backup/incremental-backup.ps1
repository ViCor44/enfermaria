Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

param(
    [string]$ConfigPath = (Join-Path $PSScriptRoot 'backup-config.psd1'),
    [switch]$IgnoreWindow
)

function Get-DotEnvValue {
    param(
        [string]$FilePath,
        [string]$Key,
        [string]$Default = ''
    )

    if (-not (Test-Path -Path $FilePath)) {
        return $Default
    }

    $line = Get-Content -Path $FilePath | Where-Object {
        $_ -match "^\s*$([Regex]::Escape($Key))\s*="
    } | Select-Object -First 1

    if (-not $line) {
        return $Default
    }

    return ($line -split '=', 2)[1].Trim()
}

function Remove-OldDirectories {
    param(
        [string]$Path,
        [int]$RetentionDays
    )

    if (-not (Test-Path -Path $Path)) {
        return
    }

    $cutoff = (Get-Date).AddDays(-$RetentionDays)
    Get-ChildItem -Path $Path -Directory | Where-Object {
        $_.LastWriteTime -lt $cutoff
    } | ForEach-Object {
        Remove-Item -Path $_.FullName -Recurse -Force
    }
}

function Copy-ChangedFiles {
    param(
        [string]$SourceRoot,
        [string]$DestinationRoot,
        [datetime]$Since
    )

    if (-not (Test-Path -Path $SourceRoot)) {
        return 0
    }

    $resolvedSource = (Resolve-Path -Path $SourceRoot).Path
    $count = 0

    Get-ChildItem -Path $resolvedSource -File -Recurse | Where-Object {
        $_.LastWriteTime -gt $Since
    } | ForEach-Object {
        $relative = $_.FullName.Substring($resolvedSource.Length).TrimStart('\\')
        $destFile = Join-Path $DestinationRoot $relative
        $destDir = Split-Path -Path $destFile -Parent

        New-Item -Path $destDir -ItemType Directory -Force | Out-Null
        Copy-Item -Path $_.FullName -Destination $destFile -Force
        $count++
    }

    return $count
}

$config = Import-PowerShellDataFile -Path $ConfigPath
$projectRoot = $config.ProjectRoot
$backupRoot = $config.BackupRoot
$googleDriveBackupRoot = $config.GoogleDriveBackupRoot
$mySqlDumpPath = $config.MySqlDumpPath
$incrementalRetentionDays = [int]$config.IncrementalRetentionDays
$firstIncrementalHour = [int]$config.FirstIncrementalHour
$lastIncrementalHour = [int]$config.LastIncrementalHour

if (-not (Test-Path -Path $projectRoot)) {
    throw "ProjectRoot not found: $projectRoot"
}

$now = Get-Date
if (-not $IgnoreWindow) {
    if ($now.Hour -lt $firstIncrementalHour -or $now.Hour -gt $lastIncrementalHour) {
        Write-Host "Outside incremental window ($firstIncrementalHour:00-$lastIncrementalHour:00). Backup skipped."
        exit 0
    }
}

if (-not (Test-Path -Path $mySqlDumpPath)) {
    $resolved = Get-Command mysqldump.exe -ErrorAction SilentlyContinue
    if ($resolved) {
        $mySqlDumpPath = $resolved.Source
    } else {
        throw "mysqldump not found. Update MySqlDumpPath in $ConfigPath"
    }
}

$stateDir = Join-Path $backupRoot 'incremental\_state'
$lastRunFile = Join-Path $stateDir 'last_success.txt'
New-Item -Path $stateDir -ItemType Directory -Force | Out-Null

if (Test-Path -Path $lastRunFile) {
    $lastRun = [datetime](Get-Content -Path $lastRunFile -Raw)
} else {
    $lastRun = (Get-Date).AddHours(-1)
}

$envFile = Join-Path $projectRoot '.env'
$dbHost = Get-DotEnvValue -FilePath $envFile -Key 'DB_HOST' -Default '127.0.0.1'
$dbPort = Get-DotEnvValue -FilePath $envFile -Key 'DB_PORT' -Default '3306'
$dbName = Get-DotEnvValue -FilePath $envFile -Key 'DB_DATABASE'
$dbUser = Get-DotEnvValue -FilePath $envFile -Key 'DB_USERNAME'
$dbPassword = Get-DotEnvValue -FilePath $envFile -Key 'DB_PASSWORD'

if ([string]::IsNullOrWhiteSpace($dbName) -or [string]::IsNullOrWhiteSpace($dbUser)) {
    throw "DB_DATABASE or DB_USERNAME missing in .env"
}

$timestamp = Get-Date -Format 'yyyyMMdd_HHmmss'
$targetRoot = Join-Path $backupRoot (Join-Path 'incremental' $timestamp)
$dbTarget = Join-Path $targetRoot 'db'
$filesTarget = Join-Path $targetRoot 'files'

New-Item -Path $dbTarget -ItemType Directory -Force | Out-Null
New-Item -Path $filesTarget -ItemType Directory -Force | Out-Null

$dbDumpFile = Join-Path $dbTarget "$dbName`_incremental.sql"
$dumpArgs = @(
    "--host=$dbHost",
    "--port=$dbPort",
    "--user=$dbUser",
    '--single-transaction',
    '--quick',
    '--skip-lock-tables',
    '--databases',
    $dbName
)

if (-not [string]::IsNullOrWhiteSpace($dbPassword)) {
    $dumpArgs += "--password=$dbPassword"
}

& $mySqlDumpPath @dumpArgs | Out-File -FilePath $dbDumpFile -Encoding utf8
if ($LASTEXITCODE -ne 0) {
    throw "mysqldump failed with exit code $LASTEXITCODE"
}

$copyTargets = @(
    'public\uploads',
    'public\pdfs',
    'storage'
)

$changedFiles = 0
foreach ($relativePath in $copyTargets) {
    $sourcePath = Join-Path $projectRoot $relativePath
    $destinationPath = Join-Path $filesTarget $relativePath
    $changedFiles += Copy-ChangedFiles -SourceRoot $sourcePath -DestinationRoot $destinationPath -Since $lastRun
}

$metadata = [ordered]@{
    type = 'incremental'
    createdAt = (Get-Date).ToString('s')
    since = $lastRun.ToString('s')
    projectRoot = $projectRoot
    database = $dbName
    changedFiles = $changedFiles
}
$metadata | ConvertTo-Json | Set-Content -Path (Join-Path $targetRoot 'backup.json') -Encoding utf8

if (-not [string]::IsNullOrWhiteSpace($googleDriveBackupRoot)) {
    $gdriveTarget = Join-Path $googleDriveBackupRoot (Join-Path 'enfermaria\incremental' $timestamp)
    New-Item -Path $gdriveTarget -ItemType Directory -Force | Out-Null
    & robocopy $targetRoot $gdriveTarget /E /R:2 /W:2 /NFL /NDL /NJH /NJS /NP | Out-Null
    if ($LASTEXITCODE -ge 8) {
        throw "robocopy to Google Drive failed with exit code $LASTEXITCODE"
    }
}

(Get-Date).ToString('o') | Set-Content -Path $lastRunFile -Encoding ascii

Remove-OldDirectories -Path (Join-Path $backupRoot 'incremental') -RetentionDays $incrementalRetentionDays
if (-not [string]::IsNullOrWhiteSpace($googleDriveBackupRoot)) {
    Remove-OldDirectories -Path (Join-Path $googleDriveBackupRoot 'enfermaria\incremental') -RetentionDays $incrementalRetentionDays
}

Write-Host "Incremental backup completed at: $targetRoot"
