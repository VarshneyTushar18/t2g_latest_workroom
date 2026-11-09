#!/bin/bash
php -r '
define("BASEPATH", "1");
require "/var/www/html/t2gworkroom/application/config/app-config.php";
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$m->set_charset("utf8mb4");
$r = $m->query("SELECT id, type_check, date FROM tblcheck_in_out WHERE staff_id=203 ORDER BY date DESC, id DESC LIMIT 3");
echo "=== CHECKIN ===\n";
while ($row = $r->fetch_assoc()) {
  echo $row["id"], " type=", $row["type_check"], " ", $row["date"], "\n";
}
$r = $m->query("SELECT a_in_time, punch_records FROM tblbiometric_report WHERE employee_code=\"1705\" AND attendance_date=\"26-Oct-2026\" LIMIT 1");
echo "=== BIO ===\n";
if ($row = $r->fetch_assoc()) {
  echo "in=", $row["a_in_time"], "\n", $row["punch_records"], "\n";
} else {
  echo "none\n";
}
'
