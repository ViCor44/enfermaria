@echo off
setlocal EnableExtensions EnableDelayedExpansion

REM ==========================
REM CONFIGURACAO
REM ==========================
set "MYSQL_USER=root"
set "MYSQL_PASSWORD="
set "MYSQL_PATH=C:\xampp\mysql\bin"
set "MYSQL_DATA_PATH=C:\xampp\mysql\data"
set "MYSQL_HOST=127.0.0.1"
set "MYSQL_PORT=3306"

set "BACKUP_PATH=C:\BackupMySQL"
set "LOG_FILE=%BACKUP_PATH%\incremental_log.txt"
set "STATE_FILE=%BACKUP_PATH%\incremental\last_success.txt"

set "WINDOW_START_HOUR=11"
set "WINDOW_END_HOUR=19"
set "RETENTION_DAYS=7"

set "DBS=radios sistema_cacifos gestao_stock econo_app super_login enfermaria pintura cmms parque_repositorio"

REM ==========================
REM PREPARACAO
REM ==========================
if not exist "%BACKUP_PATH%" mkdir "%BACKUP_PATH%"
if not exist "%BACKUP_PATH%\incremental" mkdir "%BACKUP_PATH%\incremental"

for /f %%I in ('powershell -NoProfile -Command "Get-Date -UFormat %%Y-%%m-%%d_%%H-%%M-%%S"') do set "NOW_STAMP=%%I"
for /f %%I in ('powershell -NoProfile -Command "Get-Date -UFormat %%Y-%%m-%%d %%H:%%M:%%S"') do set "NOW_SQL=%%I"
for /f %%I in ('powershell -NoProfile -Command "Get-Date -UFormat %%H"') do set "CURRENT_HOUR=%%I"
set /a CURRENT_HOUR=1%CURRENT_HOUR%-100

echo ============================== >> "%LOG_FILE%"
echo Incremental iniciado em %DATE% %TIME% >> "%LOG_FILE%"

if %CURRENT_HOUR% LSS %WINDOW_START_HOUR% (
    echo Fora da janela %WINDOW_START_HOUR%h-%WINDOW_END_HOUR%h. Ciclo ignorado. >> "%LOG_FILE%"
    echo Incremental ignorado por horario.
    goto :EOF
)

if %CURRENT_HOUR% GTR %WINDOW_END_HOUR% (
    echo Fora da janela %WINDOW_START_HOUR%h-%WINDOW_END_HOUR%h. Ciclo ignorado. >> "%LOG_FILE%"
    echo Incremental ignorado por horario.
    goto :EOF
)

if not exist "%MYSQL_PATH%\mysqlbinlog.exe" (
    echo ERRO: mysqlbinlog.exe nao encontrado em %MYSQL_PATH% >> "%LOG_FILE%"
    echo ERRO: mysqlbinlog.exe nao encontrado.
    exit /b 1
)

if not exist "%MYSQL_DATA_PATH%\mysql-bin.index" (
    echo ERRO: mysql-bin.index nao encontrado em %MYSQL_DATA_PATH% >> "%LOG_FILE%"
    echo Ative o binary log no MySQL com log_bin=mysql-bin e reinicie o servico. >> "%LOG_FILE%"
    echo ERRO: binary log nao esta ativo.
    exit /b 1
)

if exist "%STATE_FILE%" (
    set /p START_SQL=<"%STATE_FILE%"
) else (
    set "START_SQL=%NOW_SQL%"
)

set "RUN_DIR=%BACKUP_PATH%\incremental\%NOW_STAMP%"
mkdir "%RUN_DIR%" >nul 2>&1

set "BINLOG_LIST="
for /f "usebackq delims=" %%F in ("%MYSQL_DATA_PATH%\mysql-bin.index") do (
    set "BINLOG_LIST=!BINLOG_LIST! ^"%%~F^""
)

if "%BINLOG_LIST%"=="" (
    echo ERRO: lista de binlogs vazia. >> "%LOG_FILE%"
    echo ERRO: sem binlogs para processar.
    exit /b 1
)

set "PASS_ARG="
if not "%MYSQL_PASSWORD%"=="" set "PASS_ARG=--password=%MYSQL_PASSWORD%"

set "HAS_ERROR=0"
for %%D in (%DBS%) do (
    set "OUT_FILE=%RUN_DIR%\inc_%%D_%NOW_STAMP%.sql"
    echo A processar incremental da base %%D...

    "%MYSQL_PATH%\mysqlbinlog.exe" ^
      --start-datetime="%START_SQL%" ^
      --stop-datetime="%NOW_SQL%" ^
      --database=%%D ^
      %BINLOG_LIST% > "!OUT_FILE!" 2>> "%LOG_FILE%"

    if errorlevel 1 (
        echo ERRO no incremental da base %%D >> "%LOG_FILE%"
        set "HAS_ERROR=1"
    ) else (
        echo Incremental da base %%D concluido de %START_SQL% ate %NOW_SQL% >> "%LOG_FILE%"
    )
)

if "%HAS_ERROR%"=="1" (
    echo Incremental terminou com erros em %DATE% %TIME% >> "%LOG_FILE%"
    echo Incremental terminou com erros. Verifique: %LOG_FILE%
    exit /b 1
)

echo %NOW_SQL%>"%STATE_FILE%"

forfiles /p "%BACKUP_PATH%\incremental" /s /m *.sql /d -%RETENTION_DAYS% /c "cmd /c del @path" >nul 2>&1
forfiles /p "%BACKUP_PATH%\incremental" /d -%RETENTION_DAYS% /c "cmd /c if @isdir==TRUE rd /s /q @path" >nul 2>&1

echo Incremental terminado em %DATE% %TIME% >> "%LOG_FILE%"
echo ============================== >> "%LOG_FILE%"
echo Incremental concluido.
exit /b 0
