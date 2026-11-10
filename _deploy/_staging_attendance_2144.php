<?php
/**
 * Run on staging: php /tmp/staging_attendance_2144.php
 */
define('BASEPATH', 'x');
require '/var/www/workroom/application/config/app-config.php';
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($m->connect_error) {
    exit(1);
}

$code = '2144';
$r = $m->query("SELECT staffid, email, firstname, lastname, staff_identifi FROM tblstaff WHERE TRIM(staff_identifi)='$code' OR staffid='$code' LIMIT 3");
echo "=== staff ===\n";
while ($row = $r->fetch_assoc()) {
    print_r($row);
    $sid = (int) $row['staffid'];
}

if (empty($sid)) {
    echo "staff not found\n";
    exit(0);
}

echo "\n=== biometric Oct 2026 (sample) ===\n";
$r = $m->query("SELECT attendance_date, a_in_time, a_out_time, LEFT(punch_records,60) pr
  FROM tblbiometric_report
  WHERE TRIM(employee_code)='$code' AND attendance_date LIKE '%Oct-2026%'
  ORDER BY attendance_date DESC LIMIT 15");
while ($row = $r->fetch_assoc()) {
    echo implode(' | ', $row) . "\n";
}

$r = $m->query("SELECT COUNT(*) c FROM tblbiometric_report WHERE TRIM(employee_code)='$code' AND attendance_date LIKE '%Oct-2026%'");
echo 'bio_oct_rows=' . $r->fetch_row()[0] . "\n";

echo "\n=== workroom check_in_out Oct 2026 ===\n";
$r = $m->query("SELECT DATE(date) d, type_check, type, LEFT(date,19) dt FROM tblcheck_in_out
  WHERE staff_id=$sid AND date >= '2026-10-01' AND date < '2026-11-01' ORDER BY date DESC LIMIT 10");
while ($row = $r->fetch_assoc()) {
    echo implode(' | ', $row) . "\n";
}
$r = $m->query("SELECT COUNT(*) FROM tblcheck_in_out WHERE staff_id=$sid AND date >= '2026-10-01' AND date < '2026-11-01'");
echo 'workroom_oct_rows=' . $r->fetch_row()[0] . "\n";

echo "\n=== Oct 29 biometric ===\n";
$r = $m->query("SELECT * FROM tblbiometric_report WHERE TRIM(employee_code)='$code' AND attendance_date LIKE '%29-Oct-2026%' LIMIT 1");
$row = $r ? $r->fetch_assoc() : null;
echo $row ? json_encode($row) : "none\n";
