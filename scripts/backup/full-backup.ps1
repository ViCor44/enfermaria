Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

param(
    [string]$ConfigPath = (Join-Path $PSScriptRoot 'backup-config.psd1')
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

$config = Import-PowerShellDataFile -Path $ConfigPath
$projectRoot = $config.ProjectRoot
$backupRoot = $config.BackupRoot
$googleDriveBackupRoot = $config.GoogleDriveBackupRoot
$mySqlDumpPath = $config.MySqlDumpPath
$fullRetentionDays = [int]$config.FullRetentionDays

if (-not (Test-Path -Path $projectRoot)) {
    throw "ProjectRoot not found: $projectRoot"
}

if (-not (Test-Path -Path $mySqlDumpPath)) {
    $resolved = Get-Command mysqldump.exe -ErrorAction SilentlyContinue
    if ($resolved) {
        $mySqlDumpPath = $resolved.Source
    } else {
        throw "mysqldump not found. Update MySqlDumpPath in $ConfigPath"
    }
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
$targetRoot = Join-Path $backupRoot (Join-Path 'full' $timestamp)
$dbTarget = Join-Path $targetRoot 'db'
$filesTarget = Join-Path $targetRoot 'files'

New-Item -Path $dbTarget -ItemType Directory -Force | Out-Null
New-Item -Path $filesTarget -ItemType Directory -Force | Out-Null

$dbDumpFile = Join-Path $dbTarget "$dbName`_full.sql"
$dumpArgs = @(
    "--host=$dbHost",
    "--port=$dbPort",
    "--user=$dbUser",
    '--single-transaction',
    '--quick',
    '--routines',
    '--triggers',
    '--events',
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

foreach ($relativePath in $copyTargets) {
    $sourcePath = Join-Path $projectRoot $relativePath
    if (-not (Test-Path -Path $sourcePath)) {
        continue
    }

    $destinationPath = Join-Path $filesTarget $relativePath
    New-Item -Path $destinationPath -ItemType Directory -Force | Out-Null

    & robocopy $sourcePath $destinationPath /E /R:2 /W:2 /NFL /NDL /NJH /NJS /NP | Out-Null
    if ($LASTEXITCODE -ge 8) {
        throw "robocopy failed for $relativePath with exit code $LASTEXITCODE"
    }
}

$metadata = [ordered]@{
    type = 'full'
    createdAt = (Get-Date).ToString('s')
    projectRoot = $projectRoot
    database = $dbName
}
$metadata | ConvertTo-Json | Set-Content -Path (Join-Path $targetRoot 'backup.json') -Encoding utf8

if (-not [string]::IsNullOrWhiteSpace($googleDriveBackupRoot)) {
    $gdriveTarget = Join-Path $googleDriveBackupRoot (Join-Path 'enfermaria\full' $timestamp)
    New-Item -Path $gdriveTarget -ItemType Directory -Force | Out-Null
    & robocopy $targetRoot $gdriveTarget /E /R:2 /W:2 /NFL /NDL /NJH /NJS /NP | Out-Null
    if ($LASTEXITCODE -ge 8) {
        throw "robocopy to Google Drive failed with exit code $LASTEXITCODE"
    }
}

Remove-OldDirectories -Path (Join-Path $backupRoot 'full') -RetentionDays $fullRetentionDays
if (-not [string]::IsNullOrWhiteSpace($googleDriveBackupRoot)) {
    Remove-OldDirectories -Path (Join-Path $googleDriveBackupRoot 'enfermaria\full') -RetentionDays $fullRetentionDays
}

Write-Host "Full backup completed at: $targetRoot"
