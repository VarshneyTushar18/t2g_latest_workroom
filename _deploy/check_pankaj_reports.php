<?php
$root = '/var/www/html/t2gworkroom';
if (!is_file($root . '/application/config/app-config.php')) {
    $root = dirname(__DIR__);
}
define('BASEPATH', $root . '/application/');
require $root . '/application/config/app-config.php';
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$pfx = defined('APP_DB_PREFIX') ? APP_DB_PREFIX : 'tbl';

echo "=== Options ===\n";
$r = $m->query("SELECT name, value FROM {$pfx}options WHERE name IN (
  'daily_team_break_timing_last_send',
  'last_cron_run',
  'cron_has_run_from_cli'
) OR name LIKE '%cron%' ORDER BY name LIMIT 40");
while ($row = $r->fetch_assoc()) {
    echo $row['name'] . '=' . $row['value'] . "\n";
}

echo "\n=== Pankaj staff 202 ===\n";
$r = $m->query("SELECT staffid, firstname, lastname, email, active, team_manage FROM {$pfx}staff WHERE staffid IN (202) OR email LIKE '%pankaj%'");
while ($row = $r->fetch_assoc()) {
    print_r($row);
}

echo "\n=== Direct reports to Pankaj (team_manage=202) ===\n";
$r = $m->query("SELECT COUNT(*) AS c FROM {$pfx}staff WHERE active=1 AND team_manage=202");
echo 'count=' . $r->fetch_assoc()['c'] . "\n";

echo "\n=== Activity log (Pankaj / e-commerce report) last 14 days ===\n";
$r = $m->query("SELECT date, description FROM {$pfx}activity_log
  WHERE description LIKE '%Pankaj%' OR description LIKE '%E-commerce biometric%' OR description LIKE '%ecommerce%break%'
  ORDER BY date DESC LIMIT 15");
while ($row = $r->fetch_assoc()) {
    echo $row['date'] . ' | ' . $row['description'] . "\n";
}

echo "\n=== Cron_model has send_daily_ecommerce? ===\n";
$path = $root . '/application/models/Cron_model.php';
$src = file_get_contents($path);
echo (strpos($src, 'send_daily_ecommerce_break_timing_to_pankaj') !== false ? 'YES' : 'NO') . "\n";
