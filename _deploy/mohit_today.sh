#!/bin/bash
php -r '
define("BASEPATH", "1");
require "/var/www/html/t2gworkroom/application/config/app-config.php";
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($m->connect_error) { fwrite(STDERR, "db_fail\n"); exit(1); }
$m->set_charset("utf8mb4");
echo "=== STAFF ===\n";
$sql = "SELECT staffid, firstname, lastname, staff_identifi, active FROM tblstaff WHERE firstname LIKE \"%Mohit%\" OR lastname LIKE \"%Mohit%\" OR staff_identifi LIKE \"%Mohit%\"";
$r = $m->query($sql);
while ($row = $r->fetch_assoc()) {
  echo $row["staffid"], " | ", $row["firstname"], " ", $row["lastname"], " | code=", $row["staff_identifi"], " | active=", $row["active"], "\n";
}
echo "=== BIO TODAY ===\n";
$sql = "SELECT employee_code, employee_name, a_in_time, a_out_time, status, punch_records, created_at FROM tblbiometric_report WHERE attendance_date=\"26-Oct-2026\" AND (employee_name LIKE \"%Mohit%\" OR employee_code IN (SELECT staff_identifi FROM tblstaff WHERE firstname LIKE \"%Mohit%\"))";
$r = $m->query($sql);
if (!$r) { echo $m->error, "\n"; exit; }
while ($row = $r->fetch_assoc()) {
  echo $row["employee_code"], " | ", $row["employee_name"], " | in=", $row["a_in_time"], " out=", $row["a_out_time"], " | ", $row["status"], " | created=", $row["created_at"], "\n";
  echo $row["punch_records"], "\n";
}
'
