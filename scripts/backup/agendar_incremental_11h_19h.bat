@echo off
setlocal

set "TASK_NAME=MySQL-Incremental-Todas-BDs"
set "SCRIPT_PATH=C:\xampp\htdocs\enfermaria\scripts\backup\mysql_incremental_all_dbs.bat"

schtasks /Create /F /TN "%TASK_NAME%" /SC DAILY /ST 11:00 /RI 60 /DU 09:00 /TR "\"%SCRIPT_PATH%\"" /RU SYSTEM

if errorlevel 1 (
    echo Erro ao criar tarefa agendada.
    exit /b 1
)

echo Tarefa criada com sucesso: %TASK_NAME%
echo Janela: 11:00 ate 19:00 (hora a hora).
exit /b 0
