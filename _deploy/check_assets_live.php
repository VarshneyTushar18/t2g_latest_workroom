<?php
define('BASEPATH', true);
require '/var/www/html/t2gworkroom/application/config/app-config.php';
$mysqli = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($mysqli->connect_error) {
    fwrite(STDERR, 'DB connect failed: ' . $mysqli->connect_error . PHP_EOL);
    exit(1);
}
$res = $mysqli->query("SELECT module_name, active FROM tblmodules WHERE module_name='assets'");
$row = $res ? $res->fetch_assoc() : null;
echo 'module=' . json_encode($row) . PHP_EOL;
$res = $mysqli->query("SHOW COLUMNS FROM tblassets LIKE 'status_override'");
echo 'status_override=' . ($res && $res->num_rows ? 'yes' : 'no') . PHP_EOL;
$res = $mysqli->query("SHOW TABLES LIKE 'tblasset_sales'");
echo 'asset_sales_table=' . ($res && $res->num_rows ? 'yes' : 'no') . PHP_EOL;
$res = $mysqli->query('SELECT COUNT(*) AS c FROM tblassets');
$row = $res ? $res->fetch_assoc() : ['c' => 0];
echo 'assets_count=' . $row['c'] . PHP_EOL;
