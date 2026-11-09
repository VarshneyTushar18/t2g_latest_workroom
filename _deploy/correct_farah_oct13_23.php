<?php
/**
 * One-time management-approved correction for Farah Naaz (#191), 13 and 23 Oct 2026.
 * Raw Workroom punches are preserved.
 */
define('BASEPATH', true);
require '/var/www/html/t2gworkroom/application/config/app-config.php';

$db = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$db->set_charset('utf8mb4');

$staffId = 191;
$approverId = 23;
$dates = ['2026-10-13', '2026-10-23'];
$reason = 'Manual attendance correction: management confirmed 9 hours worked; Workroom navbar/check-in unavailable.';
$apply = in_array('--apply', $argv, true);

$existing = [];
foreach ($dates as $date) {
    $stmt = $db->prepare(
        'SELECT id, additional_day, status FROM tbltimesheets_additional_timesheet
         WHERE creator = ? AND additional_day = ? ORDER BY id DESC'
    );
    $stmt->bind_param('is', $staffId, $date);
    $stmt->execute();
    $existing[$date] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

if (!$apply) {
    echo json_encode([
        'mode' => 'dry-run',
        'staff_id' => $staffId,
        'dates' => $dates,
        'existing_regularisations' => $existing,
    ], JSON_PRETTY_PRINT), PHP_EOL;
    exit(0);
}

foreach ($existing as $rows) {
    if (!empty($rows)) {
        fwrite(STDERR, "Stopped: a target date already has a regularisation.\n");
        exit(1);
    }
}

$db->begin_transaction();
try {
    $delete = $db->prepare(
        'DELETE FROM tbltimesheets_timesheet WHERE staff_id = ? AND date_work = ?'
    );
    $insertTimesheet = $db->prepare(
        'INSERT INTO tbltimesheets_timesheet
            (staff_id, date_work, value, type, add_from, latch)
         VALUES (?, ?, 9.00, "P", ?, 0)'
    );
    $insertRegularisation = $db->prepare(
        'INSERT INTO tbltimesheets_additional_timesheet
            (additional_day, time_in, time_out, timekeeping_value, timekeeping_type,
             reason, creator, status, approver)
         VALUES (?, "18:30", "03:30", 9.00, "p", ?, ?, 1, ?)'
    );

    foreach ($dates as $date) {
        $delete->bind_param('is', $staffId, $date);
        $delete->execute();

        $insertTimesheet->bind_param('isi', $staffId, $date, $approverId);
        $insertTimesheet->execute();

        $insertRegularisation->bind_param('ssii', $date, $reason, $staffId, $approverId);
        $insertRegularisation->execute();
    }
    $db->commit();
} catch (Throwable $e) {
    $db->rollback();
    fwrite(STDERR, 'Rolled back: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

echo json_encode([
    'mode' => 'applied',
    'staff_id' => $staffId,
    'dates' => $dates,
    'status' => 'P',
    'hours' => 9,
    'raw_punches_changed' => false,
], JSON_PRETTY_PRINT), PHP_EOL;
