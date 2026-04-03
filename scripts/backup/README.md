## Normalizar textos antigos (Ocorrencias/Local/Tratamentos)

Existe um script para normalizar valores antigos para formato com iniciais maiusculas
(ex.: "zona de cimento" -> "Zona de Cimento") e consolidar duplicados quando necessario.

Script:
- `php scripts/normalize_legacy_text.php` (dry-run, nao grava)
- `php scripts/normalize_legacy_text.php --apply` (grava alteracoes)
- `php scripts/normalize_legacy_text.php --apply --skip-internal` (nao altera `internal_records.treatment`)

Tabelas abrangidas:
- `incident_types.name`
- `locations.name`
- `treatment_types.name`
- `internal_records.treatment` (opcional, por omissao ligado)

Recomendacao:
1. Fazer backup da BD.
2. Executar primeiro em dry-run.
3. Validar o resumo.
4. Executar com `--apply`.

# Backup automation (local Windows)

This folder contains a ready setup for:
- 1 full backup every day at 02:00
- hourly incremental backups from 11:00 to 19:00

## Files
- `backup-config.psd1`: central config
- `full-backup.ps1`: full nightly backup
- `incremental-backup.ps1`: hourly incremental backup
- `register-backup-tasks.ps1`: registers Task Scheduler jobs

## 1) Configure
Edit `backup-config.psd1` and adjust:
- `ProjectRoot`
- `BackupRoot`
- `GoogleDriveBackupRoot` (optional, local Google Drive sync path)
- `MySqlDumpPath`
- retention and hours

Example Google Drive path:
- `C:\Users\YOUR_USER\Google Drive\Backups`

## 2) Run once manually (test)
Open PowerShell in this folder and run:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\full-backup.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File .\incremental-backup.ps1 -IgnoreWindow
```

## 3) Register scheduled tasks
Open PowerShell as Administrator and run:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\register-backup-tasks.ps1
```

## 4) Check tasks

```powershell
schtasks /Query /TN "Enfermaria-Backup-Full-Daily" /V /FO LIST
schtasks /Query /TN "Enfermaria-Backup-Incremental-Hourly" /V /FO LIST
```

## Restore quick notes
1. Restore latest full backup first.
2. Restore incremental backups in chronological order after that.
3. For DB restore, use mysql client:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -p < path\to\dump.sql
```

## Retention defaults
- Full backups: 30 days
- Incremental backups: 7 days
