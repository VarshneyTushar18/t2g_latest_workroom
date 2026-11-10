<?php
define('BASEPATH', true);
require '/var/www/html/t2gworkroom/application/config/app-config.php';
$db = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$db->set_charset('utf8mb4');
$row = $db->query(
    "SELECT module_name, installed_version, active FROM tblmodules WHERE module_name = 'hr_profile'"
)->fetch_assoc();
echo json_encode($row, JSON_PRETTY_PRINT), PHP_EOL;
