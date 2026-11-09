<?php
define('BASEPATH', '/var/www/html/t2gworkroom/application/');
require '/var/www/html/t2gworkroom/application/config/app-config.php';
$sid = (int) ($argv[1] ?? 1275);
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$r = $m->query("SELECT id, status, type_of_leave, number_of_leaving_day, start_time, end_time, datecreated FROM tbltimesheets_requisition_leave WHERE staff_id=$sid ORDER BY id DESC LIMIT 25");
while ($row = $r->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
