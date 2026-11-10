<?php

/**
 * Send a sample Snapshot not logged-in report (CLI).
 * Usage: php scripts/send_snapshot_not_logged_in_sample.php [email] [Y-m-d] [cron_key]
 *
 * Requires local MySQL (app-config uses port 3300 when HTTP_HOST is localhost).
 * Sets APP_FORCE_ENABLE_EMAIL so SMTP is not blocked on local Windows.
 */
if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}

$recipient  = $argv[1] ?? 'karan.singh@tech2globe.in';
$reportDate = isset($argv[2]) ? trim($argv[2]) : '';
$cronKey    = isset($argv[3]) ? trim($argv[3]) : '';
// CI drops empty URI segments; keep a placeholder so date + recipient stay aligned.
if ($cronKey === '') {
    $cronKey = 'local';
}

if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Invalid recipient: {$recipient}\n");
    exit(1);
}

if ($reportDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $reportDate)) {
    fwrite(STDERR, "Invalid date (use Y-m-d)\n");
    exit(1);
}

define('APP_FORCE_ENABLE_EMAIL', true);

$_SERVER['HTTP_HOST']      = 'localhost';
$_SERVER['HTTPS']          = 'off';
$_SERVER['REQUEST_URI']    = '/';
$_SERVER['SCRIPT_NAME']   = '/index.php';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR']    = '127.0.0.1';

$ciArgv = [
    'index.php',
    'cron',
    'send_daily_snapshot_not_logged_in_report',
    $cronKey,
];
if ($reportDate !== '') {
    $ciArgv[] = $reportDate;
}
$ciArgv[] = $recipient;

$_SERVER['argv'] = $ciArgv;
$_SERVER['argc'] = count($ciArgv);

chdir(dirname(__DIR__));

ob_start();
require dirname(__DIR__) . '/index.php';
$output = trim(ob_get_clean());

if ($output !== '') {
    echo $output . PHP_EOL;
}

$decoded = json_decode($output, true);
if (is_array($decoded) && !empty($decoded['sent'])) {
    exit(0);
}

exit(1);
