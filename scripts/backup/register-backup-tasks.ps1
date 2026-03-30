Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

param(
    [string]$ConfigPath = (Join-Path $PSScriptRoot 'backup-config.psd1')
)

$currentPrincipal = New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())
if (-not $currentPrincipal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    throw 'Run this script in an elevated PowerShell (Administrator).'
}

$config = Import-PowerShellDataFile -Path $ConfigPath
$firstIncrementalHour = [int]$config.FirstIncrementalHour
$lastIncrementalHour = [int]$config.LastIncrementalHour

if ($lastIncrementalHour -lt $firstIncrementalHour) {
    throw 'LastIncrementalHour must be >= FirstIncrementalHour'
}

$hours = ($lastIncrementalHour - $firstIncrementalHour) + 1
$duration = '{0:00}:00' -f $hours
$startTime = '{0:00}:00' -f $firstIncrementalHour

$fullScript = Join-Path $PSScriptRoot 'full-backup.ps1'
$incrementalScript = Join-Path $PSScriptRoot 'incremental-backup.ps1'

if (-not (Test-Path -Path $fullScript)) {
    throw "Script not found: $fullScript"
}
if (-not (Test-Path -Path $incrementalScript)) {
    throw "Script not found: $incrementalScript"
}

$psExe = Join-Path $env:SystemRoot 'System32\WindowsPowerShell\v1.0\powershell.exe'
$fullTaskName = 'Enfermaria-Backup-Full-Daily'
$incrementalTaskName = 'Enfermaria-Backup-Incremental-Hourly'

$fullCmd = "`"$psExe`" -NoProfile -ExecutionPolicy Bypass -File `"$fullScript`" -ConfigPath `"$ConfigPath`""
$incrementalCmd = "`"$psExe`" -NoProfile -ExecutionPolicy Bypass -File `"$incrementalScript`" -ConfigPath `"$ConfigPath`""

& schtasks.exe /Create /F /TN $fullTaskName /SC DAILY /ST 02:00 /TR $fullCmd /RU SYSTEM
if ($LASTEXITCODE -ne 0) {
    throw "Failed to register task: $fullTaskName"
}

& schtasks.exe /Create /F /TN $incrementalTaskName /SC DAILY /ST $startTime /RI 60 /DU $duration /TR $incrementalCmd /RU SYSTEM
if ($LASTEXITCODE -ne 0) {
    throw "Failed to register task: $incrementalTaskName"
}

Write-Host 'Tasks registered successfully:'
Write-Host "- $fullTaskName (daily 02:00)"
Write-Host "- $incrementalTaskName (hourly from $startTime for $duration)"
Write-Host ''
Write-Host 'Use this to check:'
Write-Host "schtasks /Query /TN $fullTaskName /V /FO LIST"
Write-Host "schtasks /Query /TN $incrementalTaskName /V /FO LIST"
