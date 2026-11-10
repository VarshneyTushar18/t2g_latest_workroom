<?php
/**
 * Import annual leave balances from Tushar Leave Data Excel.
 * Overwrites tbltimesheets_day_off (year 2026, type_of_leave 8).
 *
 * Usage: php _import_tushar_leave_balance.php /path/to/file.xlsx [--dry-run]
 */
$appRoot = '/var/www/html/t2gworkroom';
if (!is_dir($appRoot)) {
    $appRoot = __DIR__;
}
chdir($appRoot);

require $appRoot . '/vendor/autoload.php';
define('BASEPATH', 'x');
require $appRoot . '/application/config/app-config.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$dryRun = in_array('--dry-run', $argv, true);
$file = null;
foreach (array_slice($argv, 1) as $arg) {
    if ($arg !== '--dry-run') {
        $file = $arg;
    }
}
if (!$file || !is_file($file)) {
    fwrite(STDERR, "Usage: php _import_tushar_leave_balance.php <excel.xlsx> [--dry-run]\n");
    exit(1);
}

function norm_emp($value): string
{
    if ($value === null || $value === '') {
        return '';
    }
    if (is_float($value) && abs($value - (int) $value) < 0.00001) {
        $value = (int) $value;
    }
    $s = trim((string) $value);
    if (preg_match('/^\d+\.0+$/', $s)) {
        $s = (string) (int) (float) $s;
    }

    return $s;
}

function to_float($value): float
{
    if ($value === null || $value === '') {
        return 0.0;
    }

    return (float) $value;
}

$spreadsheet = IOFactory::load($file);
$ws = $spreadsheet->getActiveSheet();
$excelRows = [];
$maxRow = $ws->getHighestDataRow();
for ($r = 2; $r <= $maxRow; $r++) {
    $emp = norm_emp($ws->getCell('A' . $r)->getCalculatedValue());
    if ($emp === '') {
        continue;
    }
    $excelRows[] = [
        'emp_id'    => $emp,
        'name'      => trim((string) $ws->getCell('B' . $r)->getCalculatedValue()),
        'carry_fwd' => to_float($ws->getCell('E' . $r)->getCalculatedValue()),
        'taken'     => to_float($ws->getCell('F' . $r)->getCalculatedValue()),
        'earned'    => to_float($ws->getCell('G' . $r)->getCalculatedValue()),
        'balance'   => to_float($ws->getCell('H' . $r)->getCalculatedValue()),
    ];
}

echo 'Excel rows: ' . count($excelRows) . "\n";

$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($m->connect_error) {
    fwrite(STDERR, 'DB error: ' . $m->connect_error . "\n");
    exit(1);
}
$m->set_charset('utf8mb4');

$staffMap = [];
$res = $m->query(
    "SELECT s.staffid,
            TRIM(COALESCE(s.staff_identifi, '')) AS staff_identifi,
            TRIM(COALESCE(i.empid, '')) AS empid,
            CONCAT(s.firstname, ' ', s.lastname) AS fullname
     FROM tblstaff s
     LEFT JOIN tblstaff_info i ON i.staffid = s.staffid"
);
$nameMap = [];
while ($row = $res->fetch_assoc()) {
    foreach (['staff_identifi', 'empid'] as $field) {
        $code = norm_emp($row[$field] ?? '');
        if ($code !== '' && !isset($staffMap[$code])) {
            $staffMap[$code] = $row;
        }
    }
    $nameKey = strtolower(preg_replace('/\s+/', ' ', trim($row['fullname'] ?? '')));
    if ($nameKey !== '' && !isset($nameMap[$nameKey])) {
        $nameMap[$nameKey] = $row;
    }
}
echo 'Active staff codes in DB: ' . count($staffMap) . "\n";

$year = '2026';
$leaveType = '8';
$updated = 0;
$inserted = 0;
$missing = [];

if (!$dryRun) {
    $m->begin_transaction();
}

foreach ($excelRows as $row) {
    $staff = $staffMap[$row['emp_id']] ?? null;
    if (!$staff) {
        $nameKey = strtolower(preg_replace('/\s+/', ' ', trim($row['name'] ?? '')));
        if ($nameKey !== '' && isset($nameMap[$nameKey])) {
            $staff = $nameMap[$nameKey];
            echo "MATCH by name emp={$row['emp_id']} -> staffid={$staff['staffid']} ({$staff['fullname']})\n";
        }
    }
    if (!$staff) {
        $missing[] = $row;
        continue;
    }
    $staffid = (int) $staff['staffid'];
    $total = round($row['balance'] + $row['taken'], 2);
    $remain = round($row['balance'], 2);
    $daysOff = round($row['taken'], 2);
    $accumulated = round($row['carry_fwd'], 2);

    $stmt = $m->prepare(
        'SELECT id, total, remain, days_off FROM tbltimesheets_day_off
         WHERE staffid = ? AND year = ? AND type_of_leave = ? LIMIT 1'
    );
    $stmt->bind_param('iss', $staffid, $year, $leaveType);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existing) {
        echo sprintf(
            "UPDATE staffid=%d emp=%s %s: total %s->%s, remain %s->%s, days_off %s->%s\n",
            $staffid,
            $row['emp_id'],
            $row['name'],
            $existing['total'],
            $total,
            $existing['remain'],
            $remain,
            $existing['days_off'],
            $daysOff
        );
        if (!$dryRun) {
            $stmt = $m->prepare(
                'UPDATE tbltimesheets_day_off SET total = ?, remain = ?, accumulated = ?, days_off = ? WHERE id = ?'
            );
            $totalStr = (string) $total;
            $remainStr = (string) $remain;
            $accumStr = (string) $accumulated;
            $id = (int) $existing['id'];
            $stmt->bind_param('sssdi', $totalStr, $remainStr, $accumStr, $daysOff, $id);
            $stmt->execute();
            $stmt->close();
        }
        $updated++;
    } else {
        echo sprintf(
            "INSERT staffid=%d emp=%s %s: total=%s, remain=%s, days_off=%s\n",
            $staffid,
            $row['emp_id'],
            $row['name'],
            $total,
            $remain,
            $daysOff
        );
        if (!$dryRun) {
            $stmt = $m->prepare(
                'INSERT INTO tbltimesheets_day_off
                 (staffid, year, total, remain, accumulated, days_off, type_of_leave)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $totalStr = (string) $total;
            $remainStr = (string) $remain;
            $accumStr = (string) $accumulated;
            $stmt->bind_param('issssds', $staffid, $year, $totalStr, $remainStr, $accumStr, $daysOff, $leaveType);
            $stmt->execute();
            $stmt->close();
        }
        $inserted++;
    }
}

if (!$dryRun) {
    $m->commit();
}
$m->close();

echo "\nDone (" . ($dryRun ? 'DRY RUN' : 'LIVE') . "): updated={$updated}, inserted={$inserted}, missing=" . count($missing) . "\n";
if ($missing) {
    echo "Unmatched Excel Emp IDs:\n";
    foreach ($missing as $mrow) {
        echo '  ' . $mrow['emp_id'] . ' ' . $mrow['name'] . "\n";
    }
    exit(2);
}

exit(0);
