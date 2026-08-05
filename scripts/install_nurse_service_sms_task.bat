@echo off
setlocal
set "TASK_NAME=SAE - SMS Enfermeiro de Servico"
set "RUNNER=%~dp0dispatch_nurse_service_sms.bat"
schtasks /Create /F /TN "%TASK_NAME%" /SC MINUTE /MO 1 /TR "%RUNNER%" /RU SYSTEM
if errorlevel 1 (
    echo Nao foi possivel criar a tarefa. Execute este ficheiro como Administrador.
    exit /b 1
)
echo Tarefa "%TASK_NAME%" criada para executar a cada minuto.
