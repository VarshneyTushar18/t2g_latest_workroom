<?php
define('BASEPATH', 'x');
require '/var/www/workroom/application/config/app-config.php';
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$r = $m->query("SELECT name, LEFT(value,40) v FROM tbloptions WHERE name LIKE '%migration%' OR name LIKE '%version%' OR name LIKE 'setup_%'");
while ($row = $r->fetch_assoc()) {
    echo $row['name'] . '=' . $row['v'] . "\n";
}
$r = $m->query('SELECT COUNT(*) FROM tbltask_assigned');
echo 'task_assigned=' . $r->fetch_row()[0] . "\n";
// Simulate slow path: count my tasks for staff 1
$staffid = 1;
$t0 = microtime(true);
$r = $m->query("SELECT COUNT(*) FROM tbltasks t INNER JOIN tbltask_assigned ta ON ta.taskid=t.id WHERE ta.staffid=$staffid AND t.status != 5");
echo 'staff1_open_tasks=' . ($r ? $r->fetch_row()[0] : '?') . ' ms=' . round((microtime(true)-$t0)*1000) . "\n";
