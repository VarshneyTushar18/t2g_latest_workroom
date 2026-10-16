<?php
/**
 * CLI smoke test for Snapshot / Google Drive (run on server from repo root).
 * Usage: php _deploy/snapshot_smoke_test.php
 */
chdir(dirname(__DIR__));
$root = getcwd();

$checks = [];

$checks['google_autoload'] = is_file($root . '/google-client/vendor/autoload.php');
$checks['service_account'] = is_readable($root . '/google-client/t2g-monitoring-37bb12065f7f.json');
$checks['emt_controller'] = is_file($root . '/application/controllers/admin/Emt.php');
$checks['emt_model'] = is_file($root . '/application/models/Emt_model.php');
$checks['emt_dashboard_view'] = is_file($root . '/application/views/admin/emt/emt_dashboard.php');
$checks['snapshot_zip'] = is_file($root . '/Snapshot.zip');
$checks['migration_311'] = is_file($root . '/application/migrations/311_version_311.php');

$driveOk = false;
$driveFolders = null;
$driveError = null;

if ($checks['google_autoload'] && $checks['service_account']) {
    require_once $root . '/google-client/vendor/autoload.php';
    $checks['guzzle_request_class'] = class_exists('GuzzleHttp\\Psr7\\Request', true);
    try {
        $client = new Google_Client();
        $client->setAuthConfig($root . '/google-client/t2g-monitoring-37bb12065f7f.json');
        $client->setScopes([Google_Service_Drive::DRIVE]);
        $service = new Google_Service_Drive($client);
        $checks['drive_service_constructed'] = true;
        $parentId = '180rTlMbvUBgcD1ET-OmlXl87qNPtLCeS';
        $resp = $service->files->listFiles([
            'q'      => "'" . str_replace("'", "\\'", $parentId) . "' in parents and mimeType='application/vnd.google-apps.folder' and trashed=false",
            'pageSize' => 5,
            'fields'   => 'files(id, name)',
        ]);
        $files = $resp->getFiles();
        $driveOk = true;
        $driveFolders = count($files);
    } catch (Throwable $e) {
        $driveError = $e->getMessage();
    }
}

$checks['drive_api'] = $driveOk;

echo json_encode([
    'checks'        => $checks,
    'drive_folders_sample' => $driveFolders,
    'drive_error'   => $driveError,
], JSON_PRETTY_PRINT) . "\n";

exit($checks['google_autoload'] && $checks['emt_controller'] && $checks['emt_model'] ? 0 : 1);
