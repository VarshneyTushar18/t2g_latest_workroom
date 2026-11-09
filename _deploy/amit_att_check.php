<?php
$cfg = file_get_contents('/var/www/html/t2gworkroom/application/config/app-config.php');
preg_match("/define\('APP_DB_HOSTNAME',\s*'([^']*)'\)/", $cfg, $h);
preg_match("/define\('APP_DB_USERNAME',\s*'([^']*)'\)/", $cfg, $u);
preg_match("/define\('APP_DB_PASSWORD',\s*'([^']*)'\)/", $cfg, $p);
preg_match("/define\('APP_DB_NAME',\s*'([^']*)'\)/", $cfg, $n);
$m = new mysqli($h[1], $u[1], $p[1], $n[1]);
if ($m->connect_error) {
    fwrite(STDERR, "db fail\n");
    exit(1);
}
$m->set_charset('utf8mb4');

echo "=== STAFF ===\n";
$q = $m->query("SELECT s.staffid, s.firstname, s.lastname, s.staff_identifi, i.empid
 FROM tblstaff s LEFT JOIN tblstaff_info i ON i.staffid=s.staffid
 WHERE s.staff_identifi='2170' OR i.empid='2170' OR s.staffid=2170");
$staff = [];
while ($r = $q->fetch_assoc()) {
    $staff[] = $r;
    echo json_encode($r) . "\n";
}
$ids = [];
foreach ($staff as $r) { $ids[] = (int)$r['staffid']; }
if (!$ids) { echo "no staff\n"; exit; }
$idlist = implode(',', $ids);

echo "=== DEPTS ===\n";
$q = $m->query("SELECT sd.staffid, d.name FROM tblstaff_departments sd JOIN tbldepartments d ON d.departmentid=sd.departmentid WHERE sd.staffid IN ($idlist)");
while ($r = $q->fetch_assoc()) echo json_encode($r) . "\n";

echo "=== BIO ===\n";
$q = $m->query("SELECT employee_code, attendance_date, shift, status, a_in_time, a_out_time, work_duration, t_duration, LEFT(punch_records, 800) AS punches
 FROM tblbiometric_report
 WHERE TRIM(employee_code)='2170'
   AND (attendance_date LIKE '%Nov-2026' OR attendance_date LIKE '2026-11%')
 ORDER BY attendance_date");
while ($r = $q->fetch_assoc()) echo json_encode($r) . "\n";

echo "=== TIMESHEET ===\n";
$q = $m->query("SELECT staff_id, date_work, type, value, check_in_time, check_out_time
 FROM tbltimesheets_timesheet
 WHERE staff_id IN ($idlist) AND date_work BETWEEN '2026-11-01' AND '2026-11-04'
 ORDER BY date_work");
while ($r = $q->fetch_assoc()) echo json_encode($r) . "\n";
