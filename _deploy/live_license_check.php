<?php
define('BASEPATH', '/var/www/html/t2gworkroom/application/');
$_SERVER['SERVER_NAME'] = 't2gworkroom.com';
$_SERVER['HTTP_HOST'] = 't2gworkroom.com';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = '/admin/modules';
$_SERVER['SERVER_ADDR'] = '127.0.0.1';

$root = '/var/www/html/t2gworkroom';
require $root . '/application/config/app-config.php';

$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($m->connect_error) {
    fwrite(STDERR, "db fail\n");
    exit(1);
}
echo "=== tblmodules ===\n";
$q = $m->query("SELECT module_name, active FROM tblmodules WHERE module_name IN ('timesheets','hr_profile') ORDER BY module_name");
while ($r = $q->fetch_assoc()) {
    echo $r['module_name'] . ' active=' . $r['active'] . "\n";
}
$m->close();

foreach (['timesheets' => 'TimesheetLic', 'hr_profile' => 'HRProfileLic'] as $mod => $cls) {
    echo "\n=== $mod ===\n";
    $licPath = "$root/modules/$mod/libraries/.lic";
    echo 'local_lic=' . (is_file($licPath) ? 'yes' : 'no') . "\n";
    require_once "$root/modules/$mod/libraries/gtsslib.php";
    $api = new $cls();
    $v = $api->verify_license(false);
    echo 'verify_status=' . (($v['status'] ?? false) ? 'ok' : 'fail') . "\n";
    echo 'verify_message=' . ($v['message'] ?? '') . "\n";
}
