<?php
define('BASEPATH', true);
require '/var/www/html/t2gworkroom/application/config/app-config.php';

$db = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$db->set_charset('utf8mb4');

if (($argv[1] ?? '') === '--activate') {
    $db->query("UPDATE tblmodules SET active = 1 WHERE module_name = 'timesheets'");
    echo 'ACTIVATED_ROWS=' . $db->affected_rows . PHP_EOL;
}

$module = $db->query(
    "SELECT module_name, installed_version, active
     FROM tblmodules
     WHERE module_name = 'timesheets'"
)->fetch_assoc();
echo 'MODULE=' . json_encode($module) . PHP_EOL;

$columns = $db->query("SHOW COLUMNS FROM tbloptions")->fetch_all(MYSQLI_ASSOC);
echo 'OPTIONS_COLUMNS=' . json_encode(array_column($columns, 'Field')) . PHP_EOL;
