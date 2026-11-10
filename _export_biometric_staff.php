<?php
/**
 * Export biometric attendance for selected staff to Excel.
 * Usage: php _export_biometric_staff.php from_Y-m-d to_Y-m-d output.xlsx "name1" "name2"
 */
$appRoot = '/var/www/html/t2gworkroom';
if (!is_dir($appRoot)) {
    $appRoot = __DIR__;
}
chdir($appRoot);

require $appRoot . '/vendor/autoload.php';
define('BASEPATH', 'x');
require $appRoot . '/application/config/app-config.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

$fromYmd = $argv[1] ?? '2026-09-01';
$toYmd = $argv[2] ?? date('Y-m-d');
$outFile = $argv[3] ?? '/tmp/biometric_staff_export.xlsx';
$nameFilters = array_slice($argv, 4);
if (empty($nameFilters)) {
    $nameFilters = ['shika', 'priyanshu', 'manisha'];
}

$fromTs = strtotime($fromYmd);
$toTs = strtotime($toYmd);
if ($fromTs === false || $toTs === false || $fromTs > $toTs) {
    fwrite(STDERR, "Invalid date range.\n");
    exit(1);
}

$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($m->connect_error) {
    fwrite(STDERR, 'DB error: ' . $m->connect_error . "\n");
    exit(1);
}

function parse_punch_summary($row)
{
    $date_str = trim((string) ($row['attendance_date'] ?? ''));
    $first_time = '';
    $last_time = '';
    $last_type = '';
    $records = trim((string) ($row['punch_records'] ?? ''));
    if ($records !== '' && preg_match_all('/(\d{1,2}:\d{2}(?::\d{2})?)\s*\(?\s*(in|out)\s*\)?/i', $records, $matches, PREG_SET_ORDER)) {
        $first_time = $matches[0][1];
        $last_match = end($matches);
        $last_time = $last_match[1];
        $last_type = strtolower($last_match[2]);
    }
    if ($first_time === '' && !empty($row['a_in_time'])) {
        $first_time = trim((string) $row['a_in_time']);
    }
    if ($last_time === '' && !empty($row['a_out_time'])) {
        $last_time = trim((string) $row['a_out_time']);
        $last_type = 'out';
    }
    $span_hrs = '';
    if ($first_time !== '' && $last_time !== '' && $last_type === 'out' && $date_str !== '') {
        $in_ts = strtotime($date_str . ' ' . preg_replace('/\s*\(.*$/', '', $first_time));
        $out_ts = strtotime($date_str . ' ' . preg_replace('/\s*\(.*$/', '', $last_time));
        if ($in_ts && $out_ts && $out_ts > $in_ts) {
            $span_hrs = round(($out_ts - $in_ts) / 3600, 2);
        }
    }

    return [
        'first_in' => $first_time,
        'last_out' => ($last_type === 'out') ? $last_time : '',
        'punch_records' => $records,
        'span_hrs' => $span_hrs,
        'a_in_time' => trim((string) ($row['a_in_time'] ?? '')),
        'a_out_time' => trim((string) ($row['a_out_time'] ?? '')),
        't_duration' => trim((string) ($row['t_duration'] ?? '')),
    ];
}

function parse_all_punches($row)
{
    $events = [];
    $records = trim((string) ($row['punch_records'] ?? ''));
    if ($records === '') {
        return $events;
    }
    if (preg_match_all('/(\d{1,2}:\d{2}(?::\d{2})?)\s*\(?\s*(in|out)\s*\)?/i', $records, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $time = $match[1];
            if (strlen($time) === 5) {
                $time .= ':00';
            }
            $events[] = [
                'time' => $time,
                'type' => strtoupper($match[2]),
            ];
        }
    }

    return $events;
}

function staff_matches_filter($firstname, $lastname, array $filters)
{
    $full = preg_replace('/\s+/', ' ', strtolower(trim($firstname . ' ' . $lastname)));
    foreach ($filters as $f) {
        $f = preg_replace('/\s+/', ' ', strtolower(trim($f)));
        if ($f === '') {
            continue;
        }
        if ($full === $f || strpos($full, $f) !== false) {
            return true;
        }
        $parts = array_filter(explode(' ', $f));
        if (count($parts) >= 2) {
            $ok = true;
            foreach ($parts as $part) {
                if (strpos($full, $part) === false) {
                    $ok = false;
                    break;
                }
            }
            if ($ok) {
                return true;
            }
        }
    }

    return false;
}

$dateKeys = [];
$dateYmd = [];
for ($t = $fromTs; $t <= $toTs; $t = strtotime('+1 day', $t)) {
    $dateKeys[] = date('d-M-Y', $t);
    $dateYmd[] = date('Y-m-d', $t);
}

$staff = [];
$res = $m->query("
    SELECT s.staffid, s.firstname, s.lastname, TRIM(COALESCE(s.staff_identifi, '')) AS staff_identifi,
           TRIM(COALESCE(i.empid, '')) AS empid, s.active
    FROM tblstaff s
    LEFT JOIN tblstaff_info i ON i.staffid = s.staffid
    ORDER BY s.active DESC, s.firstname, s.lastname
");
while ($r = $res->fetch_assoc()) {
    if (!staff_matches_filter($r['firstname'], $r['lastname'], $nameFilters)) {
        continue;
    }
    $codes = [];
    foreach (['staff_identifi', 'empid'] as $k) {
        $c = trim((string) ($r[$k] ?? ''));
        if ($c !== '' && !in_array($c, $codes, true)) {
            $codes[] = $c;
        }
    }
    if (empty($codes)) {
        continue;
    }
    $staff[] = [
        'staffid' => (int) $r['staffid'],
        'name' => trim($r['firstname'] . ' ' . $r['lastname']),
        'codes' => $codes,
        'primary_code' => $codes[0],
        'active' => (int) $r['active'],
    ];
}

if (empty($staff)) {
    fwrite(STDERR, "No staff matched filters: " . implode(', ', $nameFilters) . "\n");
    exit(1);
}

echo "Matched staff:\n";
foreach ($staff as $s) {
    echo "  - {$s['name']} (code {$s['primary_code']}, staffid {$s['staffid']}, active={$s['active']})\n";
}

$ph = implode(',', array_map(fn($d) => "'" . $m->real_escape_string($d) . "'", $dateKeys));
$bioByCodeDate = [];
$res = $m->query("
    SELECT *
    FROM tblbiometric_report
    WHERE attendance_date IN ($ph)
    ORDER BY attendance_date ASC, employee_code ASC
");
while ($row = $res->fetch_assoc()) {
    $code = trim((string) $row['employee_code']);
    $d = trim((string) $row['attendance_date']);
    if ($code === '' || $d === '') {
        continue;
    }
    if (!isset($bioByCodeDate[$code][$d])) {
        $bioByCodeDate[$code][$d] = $row;
        continue;
    }
    $existing = $bioByCodeDate[$code][$d];
    if (strlen(trim((string) ($row['punch_records'] ?? ''))) > strlen(trim((string) ($existing['punch_records'] ?? '')))) {
        $bioByCodeDate[$code][$d] = $row;
    }
}

function find_bio_row(array $staffRow, array $bioByCodeDate, string $dateKey)
{
    foreach ($staffRow['codes'] as $code) {
        if (isset($bioByCodeDate[$code][$dateKey])) {
            return $bioByCodeDate[$code][$dateKey];
        }
    }

    return null;
}

$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()
    ->setCreator('T2G Workroom')
    ->setTitle('Biometric Attendance ' . $fromYmd . ' to ' . $toYmd);

$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
];

$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('All Days');
$headers = ['Date', 'Emp Code', 'Employee Name', 'First IN', 'Last OUT', 'Work Hrs (span)', 'Device Duration', 'Punch Records'];
$sheet->fromArray($headers, null, 'A1');
$sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

$rowNum = 2;
foreach ($dateKeys as $idx => $dateKey) {
    foreach ($staff as $s) {
        $bio = find_bio_row($s, $bioByCodeDate, $dateKey);
        $sum = $bio ? parse_punch_summary($bio) : null;
        $has = $sum && ($sum['punch_records'] !== '' || $sum['a_in_time'] !== '' || $sum['a_out_time'] !== '');
        $sheet->fromArray([
            $dateYmd[$idx],
            $s['primary_code'],
            $s['name'],
            $has ? $sum['first_in'] : '—',
            $has ? ($sum['last_out'] !== '' ? $sum['last_out'] : '—') : '—',
            $has ? $sum['span_hrs'] : '—',
            $has ? $sum['t_duration'] : '—',
            $has ? $sum['punch_records'] : 'No punch',
        ], null, 'A' . $rowNum);
        $rowNum++;
    }
}
foreach (range('A', 'H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}
$sheet->freezePane('A2');

$swipeSheet = $spreadsheet->createSheet();
$swipeSheet->setTitle('All Swipes');
$swipeHeaders = ['Date', 'Emp Code', 'Employee Name', 'Punch Time', 'IN/OUT', 'Source'];
$swipeSheet->fromArray($swipeHeaders, null, 'A1');
$swipeSheet->getStyle('A1:F1')->applyFromArray($headerStyle);
$swipeRow = 2;
foreach ($dateKeys as $idx => $dateKey) {
    foreach ($staff as $s) {
        $bio = find_bio_row($s, $bioByCodeDate, $dateKey);
        if (!$bio) {
            continue;
        }
        foreach (parse_all_punches($bio) as $p) {
            $swipeSheet->fromArray([
                $dateYmd[$idx],
                $s['primary_code'],
                $s['name'],
                $p['time'],
                $p['type'],
                'Biometric API',
            ], null, 'A' . $swipeRow);
            $swipeRow++;
        }
    }
}
foreach (range('A', 'F') as $col) {
    $swipeSheet->getColumnDimension($col)->setAutoSize(true);
}
$swipeSheet->freezePane('A2');

$writer = new Xlsx($spreadsheet);
$writer->save($outFile);

echo "Exported: $outFile\n";
echo 'Rows (All Days): ' . ($rowNum - 2) . "\n";
echo 'Rows (All Swipes): ' . ($swipeRow - 2) . "\n";
echo 'Days: ' . count($dateKeys) . "\n";
