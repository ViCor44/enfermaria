@echo off
setlocal
set "LOG_DIR=%~dp0..\storage\logs"
if not exist "%LOG_DIR%" mkdir "%LOG_DIR%"
"C:\xampp\php\php.exe" "%~dp0dispatch_nurse_service_sms.php" >> "%LOG_DIR%\nurse-service-sms.log" 2>&1
exit /b %ERRORLEVEL%
