<?php
define('BASEPATH', '/var/www/html/t2gworkroom/application/');
require '/var/www/html/t2gworkroom/application/config/app-config.php';
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$sid = 1275;
$m->query("UPDATE tbltimesheets_day_off SET remain='1.5', total='1.5' WHERE staffid=$sid AND year='2026' AND type_of_leave='8'");
echo "day_off updated: " . $m->affected_rows . "\n";
$r = $m->query("SELECT remain, total FROM tbltimesheets_day_off WHERE staffid=$sid AND year='2026'");
echo json_encode($r->fetch_assoc()) . "\n";
