@echo off
setlocal
set "PHP_EXE=C:\xampp\php\php.exe"
set "SCRIPT_PATH=%~dp0send_park_schedule_reminders.php"
set "LOG_DIR=%~dp0..\storage\logs"
if not exist "%LOG_DIR%" mkdir "%LOG_DIR%"
"%PHP_EXE%" "%SCRIPT_PATH%" >> "%LOG_DIR%\schedule-reminders.log" 2>&1
exit /b %ERRORLEVEL%
