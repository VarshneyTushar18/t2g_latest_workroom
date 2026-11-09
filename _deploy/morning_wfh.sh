#!/bin/bash
php -r '
define("BASEPATH","1");
require "/var/www/html/t2gworkroom/application/config/app-config.php";
$m=new mysqli(APP_DB_HOSTNAME,APP_DB_USERNAME,APP_DB_PASSWORD,APP_DB_NAME);
$m->set_charset("utf8mb4");
$ids="1268,99,1295,91,1316,1296,1308,1283,1281,1243,1301,1300,1282,1306,202,203";
echo "=== CATEGORY ===\n";
$r=$m->query("SELECT s.staffid,s.firstname,s.lastname,i.employment_category FROM tblstaff s LEFT JOIN tblstaff_info i ON i.staffid=s.staffid WHERE s.staffid IN ($ids)");
while($row=$r->fetch_assoc()){
  echo $row["staffid"]," ",$row["firstname"]," ",$row["lastname"]," cat=",$row["employment_category"],"\n";
}
echo "=== TUSHAR CIO ===\n";
$r=$m->query("SELECT id,type_check,date FROM tblcheck_in_out WHERE staff_id=1268 ORDER BY date DESC,id DESC LIMIT 4");
while($row=$r->fetch_assoc()){
  echo $row["id"]," type=",$row["type_check"]," ",$row["date"],"\n";
}
echo "=== WFH DAY ===\n";
$r=$m->query("SHOW TABLES LIKE \"%wfh%\"");
while($row=$r->fetch_row()){ echo $row[0],"\n"; }
'
