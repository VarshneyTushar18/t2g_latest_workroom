<?php
define('BASEPATH', 'x');
require '/var/www/workroom/application/config/app-config.php';
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);

foreach (['tbltask_assigned', 'tbltasks'] as $t) {
    echo "== $t indexes ==\n";
    $r = $m->query("SHOW INDEX FROM $t");
    while ($row = $r->fetch_assoc()) {
        echo $row['Key_name'] . ' (' . $row['Column_name'] . ")\n";
    }
}

// Top staff by open assigned tasks
$sql = "SELECT ta.staffid, COUNT(*) c FROM tbltask_assigned ta
        JOIN tbltasks t ON t.id=ta.taskid WHERE t.status != 5
        GROUP BY ta.staffid ORDER BY c DESC LIMIT 5";
$r = $m->query($sql);
echo "== top assignees open tasks ==\n";
while ($row = $r->fetch_assoc()) {
    echo 'staffid=' . $row['staffid'] . ' tasks=' . $row['c'] . "\n";
}
