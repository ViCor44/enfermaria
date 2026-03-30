# Incremental horario para varias bases (BAT)

Este setup cria incrementais reais por hora via binary logs do MySQL para as bases:
- radios
- sistema_cacifos
- gestao_stock
- econo_app
- super_login
- enfermaria
- pintura
- cmms
- parque_repositorio

## Importante
Sem binary log ativo, nao existe incremental real no MySQL.

No `my.ini` (MySQL), confirme:

```ini
[mysqld]
server-id=1
log_bin=mysql-bin
binlog_format=ROW
binlog_expire_logs_seconds=604800
```

Depois reinicie o MySQL.

## Scripts
- `mysql_incremental_all_dbs.bat`: gera incrementais por hora dentro da janela 11h-19h
- `agendar_incremental_11h_19h.bat`: cria a tarefa no Task Scheduler

## Como usar
1. Ajuste caminhos e credenciais no topo de `mysql_incremental_all_dbs.bat`.
2. Teste manual:

```bat
C:\xampp\htdocs\enfermaria\scripts\backup\mysql_incremental_all_dbs.bat
```

3. Agende:

```bat
C:\xampp\htdocs\enfermaria\scripts\backup\agendar_incremental_11h_19h.bat
```

## Restauracao
1. Restaure primeiro o full diario.
2. Aplique os incrementais por ordem de hora para cada base.

Exemplo:

```bat
C:\xampp\mysql\bin\mysql.exe -u root nome_da_base < inc_nomebase_YYYY-MM-DD_HH-mm-ss.sql
```
