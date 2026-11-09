<?php
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = '127.0.0.1:8000';
}
$root = dirname(__DIR__);
define('BASEPATH', $root . '/application/');
require $root . '/application/config/app-config.php';
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($m->connect_error) {
    fwrite(STDERR, $m->connect_error . PHP_EOL);
    exit(1);
}
$pfx = defined('APP_DB_PREFIX') ? APP_DB_PREFIX : 'tbl';
$r = $m->query("SELECT version FROM {$pfx}migrations LIMIT 1");
$row = $r ? $r->fetch_assoc() : null;
echo (int) ($row['version'] ?? 0) . PHP_EOL;
