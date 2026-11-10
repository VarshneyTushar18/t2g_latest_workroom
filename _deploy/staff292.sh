#!/bin/bash
php -r '
define("BASEPATH","1");
require "/var/www/html/t2gworkroom/application/config/app-config.php";
$m=new mysqli(APP_DB_HOSTNAME,APP_DB_USERNAME,APP_DB_PASSWORD,APP_DB_NAME);
$m->set_charset("utf8mb4");
$r=$m->query("SELECT staffid,firstname,lastname,email,active,staff_identifi FROM tblstaff WHERE staffid=292");
$s=$r->fetch_assoc();
echo "STAFF ",json_encode($s),"\n";
$r=$m->query("SELECT empid,employment_category FROM tblstaff_info WHERE staff_id=292");
echo "INFO ",json_encode($r->fetch_assoc()),"\n";
$code=$m->real_escape_string($s["staff_identifi"]??"");
$r=$m->query("SELECT attendance_date,punch_records FROM tblbiometric_report WHERE employee_code=\"$code\" AND attendance_date IN (\"27-Oct-2026\",\"28-Oct-2026\")");
echo "BIO rows ".$r->num_rows."\n";
while($row=$r->fetch_assoc()){ echo $row["attendance_date"]," ",$row["punch_records"],"\n"; }
$r=$m->query("SELECT id,type_check,date FROM tblcheck_in_out WHERE staff_id=292 ORDER BY id DESC LIMIT 4");
while($row=$r->fetch_assoc()){ echo "CIO ",$row["id"]," type=",$row["type_check"]," ",$row["date"],"\n"; }
'
