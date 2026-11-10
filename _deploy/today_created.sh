#!/bin/bash
php -r '
define("BASEPATH","1");
require "/var/www/html/t2gworkroom/application/config/app-config.php";
$m=new mysqli(APP_DB_HOSTNAME,APP_DB_USERNAME,APP_DB_PASSWORD,APP_DB_NAME);
$m->set_charset("utf8mb4");
echo "PHP_TZ ",date_default_timezone_get()," NOW ",date("Y-m-d H:i:s"),"\n";
$r=$m->query("SELECT employee_code,employee_name,a_in_time,created_at FROM tblbiometric_report WHERE attendance_date=\"28-Oct-2026\" ORDER BY created_at");
while($row=$r->fetch_assoc()){
  echo $row["created_at"]," code=",$row["employee_code"]," ",$row["employee_name"]," in=",$row["a_in_time"],"\n";
}
echo "=== OPEN WORKROOM TODAY ===\n";
$r=$m->query("SELECT c.staff_id,s.firstname,s.lastname,s.staff_identifi,c.type_check,c.date FROM tblcheck_in_out c JOIN tblstaff s ON s.staffid=c.staff_id WHERE c.date>=\"2026-10-28 00:00:00\" AND c.type_check=1 ORDER BY c.date");
while($row=$r->fetch_assoc()){
  $code=$m->real_escape_string($row["staff_identifi"]);
  $b=$m->query("SELECT 1 FROM tblbiometric_report WHERE employee_code=\"$code\" AND attendance_date=\"28-Oct-2026\" LIMIT 1");
  $has=$b && $b->num_rows>0 ? "HAS_BIO" : "NO_BIO";
  echo $row["date"]," ",$row["staff_id"]," ",$row["firstname"]," ",$row["lastname"]," code=",$row["staff_identifi"]," ",$has,"\n";
}
'
