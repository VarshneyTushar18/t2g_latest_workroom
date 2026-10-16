<?php
define('BASEPATH', true);
require '/var/www/html/t2gworkroom/application/config/app-config.php';
$mysqli = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$res = $mysqli->query("SELECT name, value FROM tbloptions WHERE name IN ('biometric_last_sync_at','biometric_last_sync_meta')");
while ($row = $res->fetch_assoc()) {
    echo $row['name'] . '=' . $row['value'] . PHP_EOL;
}
$res = $mysqli->query('SELECT COUNT(*) AS c FROM tblbiometric_report WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)');
$row = $res->fetch_assoc();
echo 'rows_last_30min=' . $row['c'] . PHP_EOL;
$res = $mysqli->query('SELECT created_at, employee_code, attendance_date FROM tblbiometric_report ORDER BY id DESC LIMIT 3');
while ($row = $res->fetch_assoc()) {
    echo 'latest=' . json_encode($row) . PHP_EOL;
}
