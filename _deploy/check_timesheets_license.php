<?php
/**
 * Read-only official vendor verification using the installed encrypted .lic file.
 */
$_SERVER['SERVER_NAME'] = 't2gworkroom.com';
$_SERVER['HTTP_HOST'] = 't2gworkroom.com';
$_SERVER['HTTPS'] = 'on';

require '/var/www/html/t2gworkroom/modules/timesheets/libraries/gtsslib.php';

$api = new TimesheetLic();
$result = $api->verify_license(false);

echo json_encode([
    'local_license_exists' => $api->check_local_license_exist(),
    'verification' => $result,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
