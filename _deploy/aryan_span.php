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
 FROM tblstaff s
 LEFT JOIN tblstaff_info i ON i.staffid = s.staffid
 WHERE s.firstname LIKE '%Aryan%' OR s.lastname LIKE '%Aryan%' OR s.firstname LIKE '%Aaryan%'");
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
echo "=== ROWS with 18:27:35 ===\n";
$b = $m->query("SELECT employee_code, employee_name, attendance_date, status, a_in_time, a_out_time, t_duration, work_duration, punch_records
 FROM tblbiometric_report
 WHERE TRIM(employee_code) IN ($list)
   AND punch_records LIKE '%18:27:35%'
 ORDER BY STR_TO_DATE(attendance_date, '%d-%b-%Y') DESC
 LIMIT 5");
if (!$b) {
    fwrite(STDERR, $m->error . "\n");
    exit(1);
}
while ($row = $b->fetch_assoc()) {
    echo $row['employee_code'] . ' ' . $row['employee_name'] . ' ' . $row['attendance_date']
        . ' status=' . $row['status']
        . ' in=' . $row['a_in_time']
        . ' out=' . $row['a_out_time']
        . ' t=' . $row['t_duration']
        . ' w=' . $row['work_duration'] . "\n"
        . $row['punch_records'] . "\n---\n";
}
