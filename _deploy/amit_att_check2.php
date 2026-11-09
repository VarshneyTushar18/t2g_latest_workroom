<?php
$cfg = file_get_contents('/var/www/html/t2gworkroom/application/config/app-config.php');
preg_match("/define\('APP_DB_HOSTNAME',\s*'([^']*)'\)/", $cfg, $h);
preg_match("/define\('APP_DB_USERNAME',\s*'([^']*)'\)/", $cfg, $u);
preg_match("/define\('APP_DB_PASSWORD',\s*'([^']*)'\)/", $cfg, $p);
preg_match("/define\('APP_DB_NAME',\s*'([^']*)'\)/", $cfg, $n);
$m = new mysqli($h[1], $u[1], $p[1], $n[1]);
$m->set_charset('utf8mb4');
echo "=== TIMESHEET COLS ===\n";
$q = $m->query('SHOW COLUMNS FROM tbltimesheets_timesheet');
$cols = [];
while ($r = $q->fetch_assoc()) { $cols[] = $r['Field']; }
echo implode(',', $cols) . "\n";
echo "=== TIMESHEET ===\n";
$q = $m->query("SELECT * FROM tbltimesheets_timesheet WHERE staff_id=1284 AND date_work BETWEEN '2026-11-01' AND '2026-11-04'");
while ($r = $q->fetch_assoc()) echo json_encode($r) . "\n";
echo "=== ROLLOVER SNIP ===\n";
$src = file_get_contents('/var/www/html/t2gworkroom/application/models/Biometric_model.php');
$pos = strpos($src, 'allow_rollover');
echo substr($src, max(0, $pos - 200), 700) . "\n";
