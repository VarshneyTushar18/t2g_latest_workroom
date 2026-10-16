<?php
/**
 * Run on live: php /tmp/dump_live_database.php /var/www/html/t2gworkroom
 */
$root = rtrim($argv[1] ?? dirname(__DIR__), '/\\');
define('BASEPATH', $root . '/system/');
define('APPPATH', $root . '/application/');
require APPPATH . 'config/app-config.php';

$host = APP_DB_HOSTNAME;
$user = APP_DB_USERNAME;
$pass = APP_DB_PASSWORD;
$name = APP_DB_NAME;

$out = '/tmp/t2gworkroom_live_' . date('Y-m-d_His') . '.sql.gz';
$cmd = sprintf(
    'mysqldump --single-transaction --routines --triggers -h %s -u %s -p%s %s 2>/dev/null | gzip > %s',
    escapeshellarg($host),
    escapeshellarg($user),
    escapeshellarg($pass),
    escapeshellarg($name),
    escapeshellarg($out)
);

passthru($cmd, $code);
if ($code !== 0 || !is_file($out) || filesize($out) < 1000) {
    fwrite(STDERR, "mysqldump failed code=$code size=" . (is_file($out) ? filesize($out) : 0) . "\n");
    exit(1);
}
echo $out . "\n";
