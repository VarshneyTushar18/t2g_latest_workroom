<?php
/**
 * Apply staff shift timings from CSV to Workroom DB.
 *
 * Usage:
 *   php _apply_staff_shift_timing.php [--dry-run] [csv_path]
 *
 * Default CSV: _data/staff_shift_timing_update.csv (relative to repo root)
 */
$appRoot = '/var/www/html/t2gworkroom';
if (!is_dir($appRoot)) {
    $appRoot = __DIR__;
}
chdir($appRoot);

define('BASEPATH', 'x');
require $appRoot . '/application/config/app-config.php';

$dryRun = in_array('--dry-run', $argv, true);
$csvPath = null;
foreach ($argv as $arg) {
    if ($arg !== '--dry-run' && $arg !== $argv[0]) {
        $csvPath = $arg;
        break;
    }
}
if ($csvPath === null) {
    $csvPath = $appRoot . '/_data/staff_shift_timing_update.csv';
}
if (!is_file($csvPath)) {
    fwrite(STDERR, "CSV not found: {$csvPath}\n");
    exit(1);
}

$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($m->connect_error) {
    fwrite(STDERR, 'DB error: ' . $m->connect_error . "\n");
    exit(1);
}
$m->set_charset('utf8mb4');

$today = date('Y-m-d');
$fromDate = '2023-01-01';
$toDate = '2030-12-31';

/** Map user-facing labels to canonical start/end (H:i). */
$timingMap = [
    '3pm-12am' => ['15:00', '00:00', 'shift 15:00 - 00:00'],
    '6.30pm-3.30am' => ['18:30', '03:30', 'night shift 18:30 - 3:30'],
    '9.30am-6.30pm' => ['09:30', '18:30', 'shift 09:30 - 18:30'],
    '12pm-9pm' => ['12:00', '21:00', 'afternoon shift 12 - 21'],
    '11am-8pm' => ['11:00', '20:00', 'morning 11-8'],
    '10am-7pm' => ['10:00', '19:00', 'morning 10-7'],
    '1pm-10pm' => ['13:00', '22:00', 'shift 13:00 - 22:00'],
    '10.30am-7.30pm' => ['10:30', '19:30', 'shift 10:30 - 19:30'],
    '4pm-1am' => ['16:00', '01:00', 'shift 16:00 - 01:00'],
];

function norm_time($t)
{
    $t = trim((string) $t);
    if ($t === '') {
        return '';
    }
    if (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $t)) {
        return substr($t, 0, 5);
    }
    if (preg_match('/^\d{1,2}:\d{2}$/', $t)) {
        return $t;
    }
    return $t;
}

function find_shift_type_id(mysqli $m, $start, $end, $name)
{
    $start = norm_time($start);
    $end = norm_time($end);
    $escStart = $m->real_escape_string($start);
    $escEnd = $m->real_escape_string($end);
    $r = $m->query("SELECT id FROM tblshift_type
        WHERE time_start_work = '{$escStart}' AND time_end_work = '{$escEnd}'
        ORDER BY id ASC LIMIT 1");
    if ($r && ($row = $r->fetch_assoc())) {
        return (int) $row['id'];
    }
    return 0;
}

function ensure_shift_type(mysqli $m, $start, $end, $name, $dryRun)
{
    $id = find_shift_type_id($m, $start, $end, $name);
    if ($id > 0) {
        return $id;
    }
    $escName = $m->real_escape_string($name);
    $escStart = $m->real_escape_string(norm_time($start));
    $escEnd = $m->real_escape_string(norm_time($end));
    $now = date('Y-m-d H:i:s');
    echo "CREATE shift_type: {$name} ({$escStart}-{$escEnd})\n";
    if ($dryRun) {
        return -1;
    }
    $sql = "INSERT INTO tblshift_type
        (shift_type_name, time_start_work, time_end_work, start_lunch_break_time, end_lunch_break_time, color, description, datecreated, add_from)
        VALUES ('{$escName}', '{$escStart}', '{$escEnd}', '00:00', '00:00', '#28B8DA', '', '{$now}', 1)";
    if (!$m->query($sql)) {
        throw new RuntimeException('shift_type insert failed: ' . $m->error);
    }
    return (int) $m->insert_id;
}

function remove_staff_from_all_work_shifts(mysqli $m, $staffId, $dryRun)
{
    $staffId = (int) $staffId;
    $r = $m->query("SELECT id, staff FROM tblwork_shift WHERE FIND_IN_SET('{$staffId}', staff)");
    $updated = 0;
    while ($row = $r->fetch_assoc()) {
        $ids = array_filter(array_map('trim', explode(',', (string) $row['staff'])), 'strlen');
        $ids = array_values(array_filter($ids, function ($id) use ($staffId) {
            return (int) $id !== $staffId;
        }));
        $newStaff = implode(',', $ids);
        echo "  remove staff {$staffId} from work_shift id={$row['id']}\n";
        if (!$dryRun) {
            $esc = $m->real_escape_string($newStaff);
            $m->query("UPDATE tblwork_shift SET staff = '{$esc}' WHERE id = " . (int) $row['id']);
        }
        $updated++;
    }
    return $updated;
}

function delete_staff_shift_details(mysqli $m, $staffId, $dryRun)
{
    $staffId = (int) $staffId;
    $r = $m->query("SELECT COUNT(*) c FROM tblwork_shift_detail_number_day WHERE staff_id = {$staffId}");
    $count = (int) $r->fetch_assoc()['c'];
    if ($count > 0) {
        echo "  delete {$count} work_shift_detail_number_day rows for staff {$staffId}\n";
        if (!$dryRun) {
            $m->query("DELETE FROM tblwork_shift_detail_number_day WHERE staff_id = {$staffId}");
        }
    }
    $r2 = $m->query("SELECT COUNT(*) c FROM tblwork_shift_detail WHERE staff_id = {$staffId}");
    $count2 = (int) $r2->fetch_assoc()['c'];
    if ($count2 > 0) {
        echo "  delete {$count2} work_shift_detail rows for staff {$staffId}\n";
        if (!$dryRun) {
            $m->query("DELETE FROM tblwork_shift_detail WHERE staff_id = {$staffId}");
        }
    }
}

function create_work_shift_for_group(mysqli $m, array $staffIds, $shiftId, $label, $fromDate, $toDate, $dryRun)
{
    $staffIds = array_values(array_unique(array_map('intval', $staffIds)));
    sort($staffIds, SORT_NUMERIC);
    $staffCsv = implode(',', $staffIds);
    $shiftCode = time();
    $shiftName = 'Bulk update 2026-09-17: ' . $label;
    $escStaff = $m->real_escape_string($staffCsv);
    $escName = $m->real_escape_string($shiftName);
    $escFrom = $m->real_escape_string($fromDate);
    $escTo = $m->real_escape_string($toDate);

    echo "CREATE work_shift for [{$label}] staff=" . count($staffIds) . " shift_id={$shiftId}\n";
    if ($dryRun) {
        return 0;
    }

    $sql = "INSERT INTO tblwork_shift
        (shift_code, shift_name, shift_type, from_date, to_date, department, position, staff, type_shiftwork, date_create, add_from, shifts_detail)
        VALUES ({$shiftCode}, '{$escName}', '', '{$escFrom}', '{$escTo}', '', '', '{$escStaff}', 'repeat_periodically', '" . date('Y-m-d') . "', 1, '')";
    if (!$m->query($sql)) {
        throw new RuntimeException('work_shift insert failed: ' . $m->error);
    }
    $workShiftId = (int) $m->insert_id;

    foreach ($staffIds as $staffId) {
        for ($day = 1; $day <= 7; $day++) {
            $sqlDetail = "INSERT INTO tblwork_shift_detail_number_day
                (staff_id, number, shift_id, work_shift_id)
                VALUES ({$staffId}, {$day}, {$shiftId}, {$workShiftId})";
            if (!$m->query($sqlDetail)) {
                throw new RuntimeException('detail insert failed: ' . $m->error);
            }
        }
    }
    return $workShiftId;
}

// Load CSV
$rows = [];
$fh = fopen($csvPath, 'r');
$header = fgetcsv($fh);
while (($data = fgetcsv($fh)) !== false) {
    if (count($data) < 3) {
        continue;
    }
    $staffId = (int) $data[0];
    $label = trim((string) $data[2]);
    if ($staffId <= 0 || $label === '' || $label === '-') {
        continue;
    }
    if (!isset($timingMap[$label])) {
        fwrite(STDERR, "Unknown shift timing label: {$label} (staff {$staffId})\n");
        exit(1);
    }
    $rows[] = ['staff_id' => $staffId, 'label' => $label];
}
fclose($fh);

echo ($dryRun ? '[DRY RUN] ' : '') . 'Loaded ' . count($rows) . " staff rows from CSV\n";

// Verify staff exist and are active
$missing = [];
foreach ($rows as $row) {
    $sid = (int) $row['staff_id'];
    $r = $m->query("SELECT staffid, active, firstname, lastname FROM tblstaff WHERE staffid = {$sid} LIMIT 1");
    if (!$r || !($s = $r->fetch_assoc())) {
        $missing[] = $sid;
        continue;
    }
    if ((int) $s['active'] !== 1) {
        echo "WARN: staff {$sid} ({$s['firstname']} {$s['lastname']}) is not active\n";
    }
}
if ($missing) {
    fwrite(STDERR, 'Missing staff IDs: ' . implode(', ', $missing) . "\n");
    exit(1);
}

// Resolve shift_type ids per label
$labelToShiftId = [];
foreach ($timingMap as $label => $def) {
    [$start, $end, $name] = $def;
    $labelToShiftId[$label] = ensure_shift_type($m, $start, $end, $name, $dryRun);
    if (!$dryRun && $labelToShiftId[$label] > 0) {
        echo "shift_type for {$label}: id={$labelToShiftId[$label]}\n";
    }
}

// Clean old assignments
echo "\n--- Cleaning old shift assignments ---\n";
foreach ($rows as $row) {
    echo "Staff {$row['staff_id']} ({$row['label']})\n";
    remove_staff_from_all_work_shifts($m, $row['staff_id'], $dryRun);
    delete_staff_shift_details($m, $row['staff_id'], $dryRun);
}

// Group by label
$groups = [];
foreach ($rows as $row) {
    $groups[$row['label']][] = $row['staff_id'];
}

echo "\n--- Creating grouped work shifts ---\n";
$created = [];
foreach ($groups as $label => $staffIds) {
    $shiftId = $labelToShiftId[$label];
    if ($dryRun && $shiftId < 0) {
        $shiftId = find_shift_type_id($m, $timingMap[$label][0], $timingMap[$label][1], $timingMap[$label][2]);
        if ($shiftId <= 0) {
            $shiftId = 999;
        }
    }
    $wsId = create_work_shift_for_group($m, $staffIds, $shiftId, $label, $fromDate, $toDate, $dryRun);
    $created[$label] = ['work_shift_id' => $wsId, 'count' => count($staffIds), 'shift_id' => $shiftId];
}

echo "\n=== Summary ===\n";
foreach ($created as $label => $info) {
    echo "{$label}: {$info['count']} staff, shift_type_id={$info['shift_id']}, work_shift_id={$info['work_shift_id']}\n";
}

// Spot-check a few staff
echo "\n--- Spot check ---\n";
$checkIds = [1286, 1310, 1268, 6, 255];
foreach ($checkIds as $sid) {
    $r = $m->query("
        SELECT st.shift_type_name, st.time_start_work, st.time_end_work
        FROM tblwork_shift ws
        JOIN tblwork_shift_detail_number_day wsd ON wsd.work_shift_id = ws.id
        JOIN tblshift_type st ON st.id = wsd.shift_id
        WHERE FIND_IN_SET('{$sid}', ws.staff)
          AND ws.from_date <= '{$today}' AND ws.to_date >= '{$today}'
          AND wsd.staff_id = {$sid}
          AND wsd.number = " . (int) date('N') . "
        LIMIT 1
    ");
    $row = $r ? $r->fetch_assoc() : null;
    $txt = $row ? ($row['shift_type_name'] . ' ' . $row['time_start_work'] . '-' . $row['time_end_work']) : 'NO SHIFT';
    echo "staff {$sid}: {$txt}\n";
}

echo ($dryRun ? "\nDRY RUN complete — no DB changes made.\n" : "\nDone.\n");
