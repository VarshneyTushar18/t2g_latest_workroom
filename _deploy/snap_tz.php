<?php
$cfg = file_get_contents('/var/www/html/t2gworkroom/application/config/app-config.php');
preg_match("/define\('APP_DB_HOSTNAME',\s*'([^']*)'\)/", $cfg, $h);
preg_match("/define\('APP_DB_USERNAME',\s*'([^']*)'\)/", $cfg, $u);
preg_match("/define\('APP_DB_PASSWORD',\s*'([^']*)'\)/", $cfg, $p);
preg_match("/define\('APP_DB_NAME',\s*'([^']*)'\)/", $cfg, $n);
$m = new mysqli($h[1], $u[1], $p[1], $n[1]);
$m->set_charset('utf8mb4');
echo "php tz " . date_default_timezone_get() . " now " . date('Y-m-d H:i:s') . "\n";
$q = $m->query("SELECT name, value FROM tbloptions WHERE name IN ('default_timezone','time_format','date_format')");
while ($r = $q->fetch_assoc()) echo $r['name'] . '=' . $r['value'] . "\n";
echo "=== SAMPLE TODAY ===\n";
$q = $m->query("SELECT s.firstname, s.lastname, MIN(d.date_time) AS first_dt, MAX(d.date_time) AS last_dt
 FROM tblstaff_drive_data d
 JOIN tblstaff s ON s.staffid = d.staff_id
 WHERE d.date_time >= '2026-11-04 00:00:00'
 GROUP BY d.staff_id, s.firstname, s.lastname
 ORDER BY first_dt
 LIMIT 8");
while ($r = $q->fetch_assoc()) echo json_encode($r) . "\n";
echo "=== RAW SAMPLE ===\n";
$q = $m->query("SELECT date_time FROM tblstaff_drive_data ORDER BY id DESC LIMIT 5");
while ($r = $q->fetch_assoc()) echo $r['date_time'] . "\n";
