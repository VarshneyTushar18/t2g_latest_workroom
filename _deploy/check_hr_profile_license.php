<?php
/**
 * Read-only official verification for the installed HR Records licence.
 */
$_SERVER['SERVER_NAME'] = 't2gworkroom.com';
$_SERVER['HTTP_HOST'] = 't2gworkroom.com';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = '/admin/modules';
$_SERVER['SERVER_ADDR'] = '127.0.0.1';

require '/var/www/html/t2gworkroom/modules/hr_profile/libraries/gtsslib.php';

$api = new HRProfileLic();
$result = $api->verify_license(in_array('--timed', $argv, true));

echo json_encode([
    'local_license_exists' => $api->check_local_license_exist(),
    'verification' => $result,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
