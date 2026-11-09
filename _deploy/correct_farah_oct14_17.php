<?php
/**
 * One-time manual correction approved by management for Farah Naaz (#191).
 *
 * Usage:
 *   php correct_farah_oct14_17.php          # dry run
 *   php correct_farah_oct14_17.php --apply  # transactional apply
 *
 * Raw Workroom punches and leave audit rows are intentionally preserved.
 */
define('BASEPATH', true);
require '/var/www/html/t2gworkroom/application/config/app-config.php';

$db = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$db->set_charset('utf8mb4');

$staffId = 191;
$approverId = 23;
$dates = ['2026-10-14', '2026-10-15', '2026-10-16', '2026-10-17'];
$apply = in_array('--apply', $argv, true);
$reason = 'Manual attendance correction: management confirmed 9 hours worked; Workroom check-in issue.';

$placeholders = implode(',', array_fill(0, count($dates), '?'));
$types = 'i' . str_repeat('s', count($dates));
$params = array_merge([$staffId], $dates);

$stmt = $db->prepare(
    "SELECT id, date_work, value, type FROM tbltimesheets_timesheet
     WHERE staff_id = ? AND date_work IN ({$placeholders})
     ORDER BY date_work, id"
);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$before = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $db->prepare(
    "SELECT id, additional_day, time_in, time_out, timekeeping_value, status
     FROM tbltimesheets_additional_timesheet
     WHERE creator = ? AND additional_day IN ({$placeholders})
     ORDER BY additional_day, id"
);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$existingRegularisations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (!$apply) {
    echo json_encode([
        'mode' => 'dry-run',
        'staff_id' => $staffId,
        'dates' => $dates,
        'timesheets_before' => $before,
        'existing_regularisations' => $existingRegularisations,
        'planned' => 'Replace timesheet rows with one P:9.00 row and add an approved 18:30-03:30 regularisation per date.',
    ], JSON_PRETTY_PRINT), PHP_EOL;
    exit(0);
}

if (!empty($existingRegularisations)) {
    fwrite(STDERR, "Stopped: a regularisation already exists for at least one target date.\n");
    exit(1);
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
    'timesheet_value' => 9.00,
    'status' => 'P',
    'raw_punches_changed' => false,
], JSON_PRETTY_PRINT), PHP_EOL;
