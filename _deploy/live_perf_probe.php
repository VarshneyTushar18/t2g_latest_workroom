<?php
/** One-off live perf probe — run: php /tmp/live_perf_probe.php on server */
define('BASEPATH', '/var/www/html/t2gworkroom/application/');
require '/var/www/html/t2gworkroom/application/config/app-config.php';
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($m->connect_error) {
    fwrite(STDERR, $m->connect_error . PHP_EOL);
    exit(1);
}
$pfx = defined('APP_DB_PREFIX') ? APP_DB_PREFIX : 'tbl';
$opts = ['last_cron_run', 'cron_has_run_from_cli'];
foreach ($opts as $name) {
    $st = $m->prepare("SELECT value FROM {$pfx}options WHERE name = ? LIMIT 1");
    $st->bind_param('s', $name);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    echo $name . '=' . ($r['value'] ?? 'NULL') . PHP_EOL;
    if ($name === 'last_cron_run' && !empty($r['value'])) {
        echo 'last_cron_run_ago_sec=' . (time() - (int) $r['value']) . PHP_EOL;
    }
}
$ver = $m->query("SELECT version FROM {$pfx}migrations LIMIT 1")->fetch_assoc();
echo 'db_migration=' . ($ver['version'] ?? '?') . PHP_EOL;
$proc = $m->query('SHOW FULL PROCESSLIST');
echo "--- mysql processlist (non-sleep) ---" . PHP_EOL;
while ($row = $proc->fetch_assoc()) {
    if (($row['Command'] ?? '') === 'Sleep') {
        continue;
    }
    $info = substr((string) ($row['Info'] ?? ''), 0, 120);
    echo ($row['Id'] ?? '') . ' ' . ($row['Time'] ?? '') . 's ' . ($row['State'] ?? '') . ' ' . $info . PHP_EOL;
}
