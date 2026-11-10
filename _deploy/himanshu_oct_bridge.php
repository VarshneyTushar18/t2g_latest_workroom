<?php
define('BASEPATH', '/var/www/html/t2gworkroom/application/');
require '/var/www/html/t2gworkroom/application/config/app-config.php';
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$q = $m->query("SELECT attendance_date, a_in_time, a_out_time, t_duration, work_duration, punch_records, remark
  FROM tblbiometric_report
  WHERE TRIM(employee_code)='2133'
    AND attendance_date LIKE '%-Oct-2026'
    AND remark='biometric-bridge'
  ORDER BY STR_TO_DATE(attendance_date, '%e-%b-%Y')");
echo "Himanshu Kumar Gupta (Emp 2133, staff #1261) — October 2026 biometric-bridge raw\n";
echo str_repeat('=', 72) . "\n";
$n = 0;
while ($r = $q->fetch_assoc()) {
    $n++;
    echo "\n{$r['attendance_date']}\n";
    echo "  First IN: {$r['a_in_time']}  Last OUT: {$r['a_out_time']}  Duration: {$r['t_duration']} (work: {$r['work_duration']})\n";
    echo "  punch_records: {$r['punch_records']}\n";
}
echo "\nTotal days with bridge data: $n\n";
