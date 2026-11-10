<?php
$cfg = file_get_contents('/var/www/html/t2gworkroom/application/config/app-config.php');
preg_match("/define\('APP_DB_HOSTNAME',\s*'([^']*)'\)/", $cfg, $h);
preg_match("/define\('APP_DB_USERNAME',\s*'([^']*)'\)/", $cfg, $u);
preg_match("/define\('APP_DB_PASSWORD',\s*'([^']*)'\)/", $cfg, $p);
preg_match("/define\('APP_DB_NAME',\s*'([^']*)'\)/", $cfg, $n);
$m = new mysqli($h[1], $u[1], $p[1], $n[1]);
$m->set_charset('utf8mb4');
$q = $m->query("SELECT attendance_date, punch_records
 FROM tblbiometric_report
 WHERE TRIM(employee_code)='2170' AND attendance_date='04-Nov-2026'");
while ($r = $q->fetch_assoc()) {
    echo $r['attendance_date'] . "\n" . $r['punch_records'] . "\n";
}
