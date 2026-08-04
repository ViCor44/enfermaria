@echo off
setlocal
set "TASK_NAME=SAE - Lembretes da Escala"
set "RUNNER=%~dp0send_park_schedule_reminders.bat"
schtasks /Create /F /TN "%TASK_NAME%" /SC DAILY /ST 19:30 /TR "%RUNNER%" /RU SYSTEM
if errorlevel 1 (
    echo Nao foi possivel criar a tarefa. Execute este ficheiro como Administrador.
    exit /b 1
)
echo Tarefa "%TASK_NAME%" criada para as 19:30.
