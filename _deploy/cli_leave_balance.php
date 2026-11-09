<?php
/**
 * Run on server: php _deploy/cli_leave_balance.php STAFF_ID
 * Bootstraps CI and prints get_synced_leave_balance + cards.
 */
$staffId = (int) ($argv[1] ?? 0);
if ($staffId <= 0) {
    fwrite(STDERR, "Usage: php cli_leave_balance.php STAFF_ID\n");
    exit(1);
}

$root = getenv('T2G_ROOT') ?: dirname(__DIR__);
chdir($root);
define('ENVIRONMENT', getenv('CI_ENV') ?: 'production');
$_SERVER['CI_ENV'] = ENVIRONMENT;
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_HOST'] = 'localhost';

require 'index.php';

$CI = &get_instance();
$CI->load->model('timesheets/timesheets_model');

$year = (int) date('Y');
$month = (int) date('m');
$synced = $CI->timesheets_model->get_synced_leave_balance($staffId, 'earned-leave', $year, $month);
$cards = $CI->timesheets_model->get_staff_leave_balance_cards($staffId, $year, $month);

echo json_encode([
    'staff_id' => $staffId,
    'month' => $month,
    'year' => $year,
    'synced' => $synced,
    'cards' => $cards,
], JSON_PRETTY_PRINT) . "\n";
