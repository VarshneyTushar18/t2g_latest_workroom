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
$q = $m->query("SELECT s.staffid, s.firstname, s.lastname, s.email, s.active, s.staff_identifi, i.empid
 FROM tblstaff s
 LEFT JOIN tblstaff_info i ON i.staffid = s.staffid
 WHERE s.firstname LIKE 'Anshika%' OR s.lastname LIKE 'Anshika%' OR s.email LIKE 'anshika%'");
$ids = [];
while ($r = $q->fetch_assoc()) {
    $ids[] = (int) $r['staffid'];
    echo json_encode($r) . "\n";
}
if (!$ids) {
    echo "none\n";
    exit;
}
$idlist = implode(',', $ids);
$codes = [];
$q = $m->query("SELECT s.staffid, s.staff_identifi, i.empid FROM tblstaff s LEFT JOIN tblstaff_info i ON i.staffid=s.staffid WHERE s.staffid IN ($idlist)");
while ($r = $q->fetch_assoc()) {
    foreach (['staff_identifi', 'empid'] as $f) {
        $c = trim((string) ($r[$f] ?? ''));
        if ($c !== '') {
            $codes[$c] = true;
        }
    }
}
echo "=== DEPTS ===\n";
$q = $m->query("SELECT sd.staffid, d.name FROM tblstaff_departments sd JOIN tbldepartments d ON d.departmentid=sd.departmentid WHERE sd.staffid IN ($idlist)");
while ($r = $q->fetch_assoc()) {
    echo json_encode($r) . "\n";
}

echo "=== BIO NOV ===\n";
if ($codes) {
    $in = "'" . implode("','", array_map([$m, 'real_escape_string'], array_keys($codes))) . "'";
    $q = $m->query("SELECT employee_code, employee_name, attendance_date, status, a_in_time, a_out_time, work_duration, LEFT(punch_records, 500) AS punches
     FROM tblbiometric_report
     WHERE TRIM(employee_code) IN ($in)
       AND (attendance_date LIKE '%Nov-2026' OR attendance_date LIKE '%Oct-2026')
     ORDER BY attendance_date DESC
     LIMIT 12");
    while ($r = $q->fetch_assoc()) {
        echo json_encode($r) . "\n";
    }
}

echo "=== TIMESHEET ===\n";
$q = $m->query("SELECT staff_id, date_work, type, value FROM tbltimesheets_timesheet WHERE staff_id IN ($idlist) AND date_work >= '2026-10-27' ORDER BY date_work DESC LIMIT 15");
while ($r = $q->fetch_assoc()) {
    echo json_encode($r) . "\n";
}

echo "=== LEAVE ===\n";
$q = $m->query("SELECT id, staff_id, type_of_leave, start_time, end_time, number_of_leaving_day, status, datecreated
 FROM tbltimesheets_requisition_leave
 WHERE staff_id IN ($idlist)
 ORDER BY id DESC LIMIT 8");
if ($q) {
    while ($r = $q->fetch_assoc()) {
        echo json_encode($r) . "\n";
    }
}
