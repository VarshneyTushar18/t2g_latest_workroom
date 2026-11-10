<?php
$cfg = file_get_contents('/var/www/html/t2gworkroom/application/config/app-config.php');
preg_match("/define\('APP_DB_HOSTNAME',\s*'([^']*)'\)/", $cfg, $h);
preg_match("/define\('APP_DB_USERNAME',\s*'([^']*)'\)/", $cfg, $u);
preg_match("/define\('APP_DB_PASSWORD',\s*'([^']*)'\)/", $cfg, $p);
preg_match("/define\('APP_DB_NAME',\s*'([^']*)'\)/", $cfg, $n);
$m = new mysqli($h[1], $u[1], $p[1], $n[1]);
$m->set_charset('utf8mb4');

echo "=== BIO NOV ONLY ===\n";
$q = $m->query("SELECT attendance_date, status, a_in_time, a_out_time, work_duration, punch_records
 FROM tblbiometric_report
 WHERE TRIM(employee_code)='2167' AND attendance_date LIKE '%Nov-2026'
 ORDER BY STR_TO_DATE(attendance_date, '%d-%b-%Y') DESC");
if (!$q || $q->num_rows === 0) {
    echo "no november biometric rows\n";
} else {
    while ($r = $q->fetch_assoc()) {
        echo $r['attendance_date'] . ' | ' . $r['status'] . ' | in ' . $r['a_in_time'] . ' | out ' . $r['a_out_time'] . ' | dur ' . $r['work_duration'] . "\n";
        echo '  ' . $r['punch_records'] . "\n";
    }
}

echo "=== WORKROOM CHECK ===\n";
$q = $m->query("SELECT date, type_check FROM tblcheck_in_out WHERE staff_id=1282 AND date >= '2026-11-01' ORDER BY date");
if (!$q || $q->num_rows === 0) {
    echo "no workroom punches in november\n";
} else {
    while ($r = $q->fetch_assoc()) {
        echo json_encode($r) . "\n";
    }
}

echo "=== LATEST BIO ANY ===\n";
$q = $m->query("SELECT attendance_date, status, a_in_time, a_out_time FROM tblbiometric_report WHERE TRIM(employee_code)='2167' ORDER BY STR_TO_DATE(attendance_date, '%d-%b-%Y') DESC LIMIT 3");
while ($r = $q->fetch_assoc()) {
    echo json_encode($r) . "\n";
}
