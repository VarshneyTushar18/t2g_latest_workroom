<?php
$cfg = file_get_contents('/var/www/html/t2gworkroom/application/config/app-config.php');
preg_match("/define\('APP_DB_HOSTNAME',\s*'([^']*)'\)/", $cfg, $h);
preg_match("/define\('APP_DB_USERNAME',\s*'([^']*)'\)/", $cfg, $u);
preg_match("/define\('APP_DB_PASSWORD',\s*'([^']*)'\)/", $cfg, $p);
preg_match("/define\('APP_DB_NAME',\s*'([^']*)'\)/", $cfg, $n);
$m = new mysqli($h[1], $u[1], $p[1], $n[1]);
$m->set_charset('utf8mb4');

echo "=== TABLES ===\n";
$q = $m->query("SHOW TABLES LIKE '%bio%'");
while ($r = $q->fetch_row()) {
    echo $r[0] . "\n";
}
$q = $m->query("SHOW TABLES LIKE '%punch%'");
while ($r = $q->fetch_row()) {
    echo $r[0] . "\n";
}

echo "=== NOV3 ROWS CONTAINING 18:30 OR 18:07 ===\n";
$q = $m->query("SELECT employee_code, employee_name, a_in_time, a_out_time, work_duration, punch_records
 FROM tblbiometric_report
 WHERE attendance_date='03-Nov-2026'
   AND (punch_records LIKE '%18:30%' OR punch_records LIKE '%18:07%' OR punch_records LIKE '%18:0%')
 ORDER BY employee_code");
while ($r = $q->fetch_assoc()) {
    echo $r['employee_code'] . ' | ' . $r['employee_name'] . ' | in ' . $r['a_in_time'] . ' out ' . $r['a_out_time'] . ' dur ' . $r['work_duration'] . "\n";
    echo '  ' . $r['punch_records'] . "\n";
}
