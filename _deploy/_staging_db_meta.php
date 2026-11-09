<?php
define('BASEPATH', 'x');
require '/var/www/workroom/application/config/app-config.php';
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($m->connect_error) {
    echo "DB_FAIL " . $m->connect_error . "\n";
    exit(1);
}
foreach (['migration_version', 'companyname', 'setup_menu_active'] as $n) {
    $r = $m->query("SELECT value FROM tbloptions WHERE name='" . $m->real_escape_string($n) . "'");
    $row = $r ? $r->fetch_row() : null;
    echo $n . '=' . ($row[0] ?? '?') . "\n";
}
$r = $m->query('SELECT COUNT(*) FROM tblstafftaskassignees');
echo 'assignees=' . ($r ? $r->fetch_row()[0] : '?') . "\n";
$r = $m->query('SELECT staffid, email FROM tblstaff WHERE active=1 ORDER BY staffid LIMIT 3');
while ($row = $r->fetch_assoc()) {
    echo 'staff ' . $row['staffid'] . ' ' . $row['email'] . "\n";
}
