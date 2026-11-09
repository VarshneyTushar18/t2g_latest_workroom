<?php
/**
 * Read-only attendance export for Farah Naaz (#191).
 * Writes CSV to stdout; does not modify database data.
 */
define('BASEPATH', true);
require '/var/www/html/t2gworkroom/application/config/app-config.php';

$db = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$db->set_charset('utf8mb4');
$staffId = 191;
$employeeCode = '1922';
$from = '2023-12-01';
$to = date('Y-m-d');

$timesheets = [];
$q = $db->query(
    "SELECT date_work, type, value
     FROM tbltimesheets_timesheet
     WHERE staff_id = {$staffId} AND date_work BETWEEN '{$from}' AND '{$to}'
     ORDER BY date_work, id"
);
while ($row = $q->fetch_assoc()) {
    $date = $row['date_work'];
    $timesheets[$date][] = trim((string) $row['type']) . ':' . trim((string) $row['value']);
}

$workroom = [];
$q = $db->query(
    "SELECT date, type_check
     FROM tblcheck_in_out
     WHERE staff_id = {$staffId} AND DATE(date) BETWEEN '{$from}' AND '{$to}'
     ORDER BY date, id"
);
while ($row = $q->fetch_assoc()) {
    $date = substr($row['date'], 0, 10);
    $workroom[$date][] = substr($row['date'], 11, 8) . ((int) $row['type_check'] === 1 ? ' IN' : ' OUT');
}

$biometric = [];
$q = $db->query(
    "SELECT attendance_date, a_in_time, a_out_time, status, work_duration, t_duration, punch_records
     FROM tblbiometric_report
     WHERE TRIM(employee_code) = '{$employeeCode}'"
);
while ($row = $q->fetch_assoc()) {
    $dt = DateTime::createFromFormat('d-M-Y', trim((string) $row['attendance_date']));
    if (!$dt) {
        continue;
    }
    $date = $dt->format('Y-m-d');
    if ($date < $from || $date > $to) {
        continue;
    }
    $biometric[$date][] = $row;
}

$leaves = [];
$q = $db->query(
    "SELECT id, subject, start_time, end_time, status, type_of_leave, number_of_leaving_day
     FROM tbltimesheets_requisition_leave
     WHERE staff_id = {$staffId}
       AND DATE(end_time) >= '{$from}'
       AND DATE(start_time) <= '{$to}'
     ORDER BY start_time, id"
);
while ($row = $q->fetch_assoc()) {
    $start = max($from, substr($row['start_time'], 0, 10));
    $end = min($to, substr($row['end_time'], 0, 10));
    for ($date = $start; $date <= $end; $date = date('Y-m-d', strtotime($date . ' +1 day'))) {
        $leaves[$date][] = sprintf(
            '#%s %s | %s | status=%s | days=%s',
            $row['id'],
            trim((string) $row['type_of_leave']),
            trim((string) $row['subject']),
            $row['status'],
            $row['number_of_leaving_day']
        );
    }
}

$wfh = [];
if ($db->query("SHOW TABLES LIKE 'tblstaff_wfh_day'")->num_rows > 0) {
    $q = $db->query(
        "SELECT wfh_date, status
         FROM tblstaff_wfh_day
         WHERE staffid = {$staffId} AND wfh_date BETWEEN '{$from}' AND '{$to}'"
    );
    while ($row = $q->fetch_assoc()) {
        $wfh[$row['wfh_date']][] = (string) $row['status'];
    }
}

$out = fopen('php://output', 'w');
fputcsv($out, [
    'date',
    'weekday',
    'timesheet_entries',
    'workroom_punches',
    'biometric_in',
    'biometric_out',
    'biometric_status',
    'biometric_work_duration',
    'biometric_total_duration',
    'biometric_punch_records',
    'leave_entries',
    'wfh_allotted_status',
]);

for ($date = $from; $date <= $to; $date = date('Y-m-d', strtotime($date . ' +1 day'))) {
    $bioRows = $biometric[$date] ?? [];
    $bioIn = $bioOut = $bioStatus = $bioWork = $bioTotal = $bioPunches = [];
    foreach ($bioRows as $row) {
        $bioIn[] = trim((string) $row['a_in_time']);
        $bioOut[] = trim((string) $row['a_out_time']);
        $bioStatus[] = trim((string) $row['status']);
        $bioWork[] = trim((string) $row['work_duration']);
        $bioTotal[] = trim((string) $row['t_duration']);
        $bioPunches[] = trim((string) $row['punch_records']);
    }

    fputcsv($out, [
        $date,
        date('l', strtotime($date)),
        implode(' | ', $timesheets[$date] ?? []),
        implode(' | ', $workroom[$date] ?? []),
        implode(' | ', $bioIn),
        implode(' | ', $bioOut),
        implode(' | ', $bioStatus),
        implode(' | ', $bioWork),
        implode(' | ', $bioTotal),
        implode(' | ', $bioPunches),
        implode(' || ', $leaves[$date] ?? []),
        implode(' | ', $wfh[$date] ?? []),
    ]);
}

fclose($out);
