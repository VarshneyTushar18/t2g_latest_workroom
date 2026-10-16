# Restore local Workroom DB on MySQL 8 (port 3300). Run in PowerShell as Admin if DROP fails.
$mysql = "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe"
$dump  = "D:\karan\production_crm_maria.sql"
$db    = "new_workroom"

& $mysql --host=127.0.0.1 --port=3300 -uroot -e "CREATE DATABASE IF NOT EXISTS ${db}_import CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
Write-Host "Importing (10–30 min)..."
cmd /c "`"$mysql`" --host=127.0.0.1 --port=3300 -uroot ${db}_import < `"$dump`""
& $mysql --host=127.0.0.1 --port=3300 -uroot -e "SELECT COUNT(*) AS tbloptions_rows FROM ${db}_import.tbloptions;"
Write-Host "If OK, set APP_DB_NAME to ${db}_import in app-config-local.php or rename DB in app-config.php"
