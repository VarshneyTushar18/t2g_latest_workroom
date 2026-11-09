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
$sql = "SELECT s.staffid, s.firstname, s.lastname, s.staff_identifi, b.attendance_date, b.punch_records
FROM tblbiometric_report b
JOIN tblstaff s ON s.staff_identifi = b.employee_code
JOIN tblstaff_departments sd ON sd.staffid = s.staffid
JOIN tbldepartments d ON d.departmentid = sd.departmentid
WHERE d.name LIKE '%Night%'
  AND b.punch_records REGEXP '0[0-6]:[0-9]{2}'
  AND b.attendance_date LIKE '%Nov-2026%'
ORDER BY b.attendance_date DESC
LIMIT 3";
$q = $m->query($sql);
if (!$q) {
    fwrite(STDERR, "query fail\n");
    exit(1);
}
while ($r = $q->fetch_assoc()) {
    echo $r['firstname'] . ' ' . $r['lastname'] . ' #' . $r['staff_identifi']
        . ' staffid ' . $r['staffid'] . ' ' . $r['attendance_date'] . "\n"
        . $r['punch_records'] . "\n---\n";
}
