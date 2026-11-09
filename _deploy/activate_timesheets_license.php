<?php
/**
 * Official vendor activation helper. Credentials are supplied via environment
 * variables and are never written by this script.
 */
$_SERVER['SERVER_NAME'] = 't2gworkroom.com';
$_SERVER['HTTP_HOST'] = 't2gworkroom.com';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = '/admin/modules';
$_SERVER['SERVER_ADDR'] = '127.0.0.1';

$purchaseCode = trim((string) getenv('TIMESHEETS_PURCHASE_CODE'));
$clientName = trim((string) getenv('TIMESHEETS_CLIENT_NAME'));
if ($purchaseCode === '' || $clientName === '') {
    fwrite(STDERR, "Activation credentials are required.\n");
    exit(1);
}

require '/var/www/html/t2gworkroom/modules/timesheets/libraries/gtsslib.php';

$api = new TimesheetLic();
$result = $api->activate_license($purchaseCode, $clientName);
echo json_encode([
    'status' => (bool) ($result['status'] ?? false),
    'message' => (string) ($result['message'] ?? 'No response'),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
