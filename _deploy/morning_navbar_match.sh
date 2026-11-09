#!/bin/bash
php -r '
define("BASEPATH","1");
require "/var/www/html/t2gworkroom/application/config/app-config.php";
$m=new mysqli(APP_DB_HOSTNAME,APP_DB_USERNAME,APP_DB_PASSWORD,APP_DB_NAME);
$m->set_charset("utf8mb4");
echo "=== TUSHAR ===\n";
$r=$m->query("SELECT s.staffid,s.firstname,s.lastname,s.staff_identifi,s.active,i.empid FROM tblstaff s LEFT JOIN tblstaff_info i ON i.staffid=s.staffid WHERE s.firstname LIKE \"%Tushar%\" OR s.lastname LIKE \"%Varshney%\"");
while($row=$r->fetch_assoc()){
  echo $row["staffid"]," | ",$row["firstname"]," ",$row["lastname"]," | identifi=",$row["staff_identifi"]," | empid=",$row["empid"]," | active=",$row["active"],"\n";
}
echo "=== MORNING CODES VS STAFF ===\n";
$codes=["1806","2179","1683","2209","2208","2183","2191","2168","2162","2113","2190","2192","2167","2195","1114","2144"];
foreach($codes as $c){
  $st=$m->real_escape_string($c);
  $q=$m->query("SELECT s.staffid,s.firstname,s.lastname,s.staff_identifi,i.empid,s.active FROM tblstaff s LEFT JOIN tblstaff_info i ON i.staffid=s.staffid WHERE TRIM(s.staff_identifi)=\"$st\" OR TRIM(COALESCE(i.empid,\"\"))=\"$st\" LIMIT 3");
  $n=0;
  while($row=$q->fetch_assoc()){
    $n++;
    echo $c," -> staff ",$row["staffid"]," ",$row["firstname"]," ",$row["lastname"]," identifi=",$row["staff_identifi"]," empid=",$row["empid"]," active=",$row["active"],"\n";
  }
  if($n===0) echo $c," -> NO STAFF MATCH\n";
}
echo "=== OPEN WORKROOM CHECKINS TODAY ===\n";
$r=$m->query("SELECT c.staff_id,s.firstname,s.lastname,s.staff_identifi,c.type_check,c.date FROM tblcheck_in_out c JOIN tblstaff s ON s.staffid=c.staff_id WHERE c.date>=\"2026-10-28 00:00:00\" ORDER BY c.date DESC");
$seen=[];
while($row=$r->fetch_assoc()){
  $id=$row["staff_id"];
  if(isset($seen[$id])) continue;
  $seen[$id]=1;
  echo $row["staff_id"]," ",$row["firstname"]," ",$row["lastname"]," code=",$row["staff_identifi"]," type=",$row["type_check"]," ",$row["date"],"\n";
}
'
