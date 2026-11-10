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

$q = $m->query("SELECT s.staffid, s.firstname, s.lastname, s.staff_identifi, i.empid
 FROM tblstaff s
 LEFT JOIN tblstaff_info i ON i.staffid = s.staffid
 WHERE s.staffid = 1284 OR s.staff_identifi = '2170' OR i.empid = '2170'");
$codes = [];
while ($r = $q->fetch_assoc()) {
    echo json_encode($r) . "\n";
    $sid = (int) $r['staffid'];
    foreach (['staff_identifi', 'empid'] as $f) {
        $c = trim((string) ($r[$f] ?? ''));
        if ($c !== '') {
            $codes[$c] = true;
        }
    }
    $d = $m->query("SELECT d.name FROM tblstaff_departments sd JOIN tbldepartments d ON d.departmentid=sd.departmentid WHERE sd.staffid=$sid");
    while ($row = $d->fetch_assoc()) {
        echo "dept: " . $row['name'] . "\n";
    }
}
if (!$codes) {
    echo "no staff\n";
    exit;
}
$list = "'" . implode("','", array_map([$m, 'real_escape_string'], array_keys($codes))) . "'";
echo "codes $list\n";
$b = $m->query("SELECT attendance_date, status, a_in_time, a_out_time, work_duration, shift, punch_records
 FROM tblbiometric_report
 WHERE TRIM(employee_code) IN ($list)
   AND (attendance_date LIKE '%Nov-2026' OR attendance_date LIKE '%Oct-2026')
 ORDER BY STR_TO_DATE(attendance_date, '%d-%b-%Y')");
if (!$b) {
    fwrite(STDERR, $m->error . "\n");
    exit(1);
}
while ($row = $b->fetch_assoc()) {
    echo $row['attendance_date'] . " | " . $row['status']
        . " | in=" . $row['a_in_time']
        . " | out=" . $row['a_out_time']
        . " | dur=" . $row['work_duration']
        . " | shift=" . $row['shift'] . "\n"
        . $row['punch_records'] . "\n---\n";
}
