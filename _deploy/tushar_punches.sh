#!/bin/bash
php -r '
define("BASEPATH","1");
require "/var/www/html/t2gworkroom/application/config/app-config.php";
$m=new mysqli(APP_DB_HOSTNAME,APP_DB_USERNAME,APP_DB_PASSWORD,APP_DB_NAME);
$m->set_charset("utf8mb4");
echo "=== 2144 RECENT ===\n";
$r=$m->query("SELECT attendance_date,a_in_time,a_out_time,punch_records,created_at FROM tblbiometric_report WHERE employee_code=\"2144\" AND attendance_date IN (\"27-Oct-2026\",\"28-Oct-2026\")");
while($row=$r->fetch_assoc()){
  echo $row["attendance_date"]," in=",$row["a_in_time"]," created=",$row["created_at"],"\n",$row["punch_records"],"\n";
}
'
echo "=== PHP ERRORS ==="
sudo tail -n 30 /var/log/apache2/error.log | sed -n "1,30p"
