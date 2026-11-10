<?php
define('BASEPATH', '/var/www/html/t2gworkroom/application/');
require '/var/www/html/t2gworkroom/application/config/app-config.php';

$year = (int) ($argv[1] ?? 2026);
$month = (int) ($argv[2] ?? 10);

$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);

$r = $m->query("SELECT staffid, firstname, lastname, email, staff_identifi FROM tblstaff WHERE firstname LIKE '%Himanshu%' AND lastname LIKE '%Gupta%' LIMIT 5");
if (!$r || !$r->num_rows) {
    $r = $m->query("SELECT staffid, firstname, lastname, email, staff_identifi FROM tblstaff WHERE CONCAT(firstname,' ',lastname) LIKE '%Himanshu%Gupta%' LIMIT 5");
}
if (!$r || !$r->num_rows) {
    echo "No staff Himanshu Gupta\n";
    $r2 = $m->query("SELECT staffid, firstname, lastname FROM tblstaff WHERE firstname LIKE '%Himanshu%' LIMIT 10");
    while ($r2 && ($x = $r2->fetch_assoc())) {
        echo "  #{$x['staffid']} {$x['firstname']} {$x['lastname']}\n";
    }
    exit(1);
}

$row = $r->fetch_assoc();
$sid = (int) $row['staffid'];
$empCode = trim($row['staff_identifi'] ?? '');
echo "Staff #{$sid}: {$row['firstname']} {$row['lastname']} emp_id=[$empCode] {$row['email']}\n\n";

$info = $m->query("SELECT empid FROM tblstaff_info WHERE staffid=$sid");
if ($info && $info->num_rows) {
    $empid = trim($info->fetch_assoc()['empid'] ?? '');
    if ($empid !== '') {
        echo "staff_info empid: $empid\n";
        if ($empCode === '') {
            $empCode = $empid;
        }
    }
}

$monthLabel = date('M', mktime(0, 0, 0, $month, 1, $year));
$like = '%-' . $monthLabel . '-' . $year;

$conds = ["attendance_date LIKE '" . $m->real_escape_string($like) . "'"];
if ($empCode !== '') {
    $ec = $m->real_escape_string($empCode);
    $conds[] = "TRIM(employee_code) = '$ec'";
}
$conds[] = "employee_name LIKE '%Himanshu%Gupta%'";

$sql = "SELECT id, employee_name, employee_code, attendance_date, shift, a_in_time, a_out_time, t_duration, work_duration, punch_records, remark, created_at
  FROM tblbiometric_report
  WHERE (" . implode(' OR ', $conds) . ")
  ORDER BY STR_TO_DATE(attendance_date, '%e-%b-%Y'), id";

echo "Query: October $year (pattern $like)\n\n";
$q = $m->query($sql);
if (!$q) {
    echo "Error: " . $m->error . "\n";
    exit(1);
}
$n = 0;
while ($row = $q->fetch_assoc()) {
    $n++;
    echo "=== {$row['attendance_date']} | {$row['shift']} | remark={$row['remark']} ===\n";
    echo "employee: {$row['employee_name']} code={$row['employee_code']}\n";
    echo "IN: {$row['a_in_time']}  OUT: {$row['a_out_time']}  t_duration: {$row['t_duration']}  work_duration: {$row['work_duration']}\n";
    echo "punch_records:\n{$row['punch_records']}\n\n";
}
echo "Total days: $n\n";
