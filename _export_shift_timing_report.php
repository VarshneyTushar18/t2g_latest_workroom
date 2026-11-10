<?php
/**
 * Export active staff shift timings from live Workroom DB.
 * Usage: php _export_shift_timing_report.php [output.xlsx]
 */
$appRoot = '/var/www/html/t2gworkroom';
if (!is_dir($appRoot)) {
    $appRoot = dirname(__DIR__);
}
chdir($appRoot);

require $appRoot . '/vendor/autoload.php';
define('BASEPATH', 'x');
require $appRoot . '/application/config/app-config.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

$outFile = $argv[1] ?? '/tmp/Workroom_Shift_Timing_Report_' . date('Y-m-d') . '.xlsx';
$today = date('Y-m-d');

$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($m->connect_error) {
    fwrite(STDERR, 'DB error: ' . $m->connect_error . "\n");
    exit(1);
}
$m->set_charset('utf8mb4');

$dayNames = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];

function esc($m, $v)
{
    return $m->real_escape_string((string) $v);
}

function get_staff_departments($m, $staffId)
{
    $ids = [];
    $r = $m->query('SELECT departmentid FROM tblstaff_departments WHERE staffid = ' . (int) $staffId);
    while ($row = $r->fetch_assoc()) {
        $ids[] = (int) $row['departmentid'];
    }
    return $ids;
}

function fetch_work_shifts($m, $staffId, $roleId, array $deptIds, $date)
{
    $staffId = (int) $staffId;
    $roleId = (int) $roleId;
    $date = esc($m, $date);
    $found = [];

    $r = $m->query("SELECT id, shift_code, from_date, to_date, type_shiftwork
        FROM tblwork_shift
        WHERE FIND_IN_SET('{$staffId}', staff)
          AND from_date <= '{$date}' AND to_date >= '{$date}'");
    while ($row = $r->fetch_assoc()) {
        $found[(int) $row['id']] = $row;
    }
    if ($found) {
        return array_values($found);
    }

    $queries = [];
    if ($deptIds && $roleId > 0) {
        $dpm = implode(' OR ', array_map(function ($d) {
            return 'FIND_IN_SET(' . (int) $d . ', department)';
        }, $deptIds));
        $queries[] = "FIND_IN_SET({$roleId}, position) AND ({$dpm}) AND (staff = '' OR staff IS NULL)";
        $queries[] = "(position = '' OR position IS NULL) AND ({$dpm}) AND (staff = '' OR staff IS NULL)";
        $queries[] = "FIND_IN_SET({$roleId}, position) AND (department = '' OR department IS NULL) AND (staff = '' OR staff IS NULL)";
    } elseif ($roleId > 0) {
        $queries[] = "FIND_IN_SET({$roleId}, position) AND (department = '' OR department IS NULL) AND (staff = '' OR staff IS NULL)";
    } elseif ($deptIds) {
        $dpm = implode(' OR ', array_map(function ($d) {
            return 'FIND_IN_SET(' . (int) $d . ', department)';
        }, $deptIds));
        $queries[] = "(position = '' OR position IS NULL) AND ({$dpm}) AND (staff = '' OR staff IS NULL)";
    }
    $queries[] = "(position = '' OR position IS NULL) AND (department = '' OR department IS NULL) AND (staff = '' OR staff IS NULL)";

    foreach ($queries as $where) {
        $r = $m->query("SELECT id, shift_code, from_date, to_date, type_shiftwork
            FROM tblwork_shift
            WHERE {$where} AND from_date <= '{$date}' AND to_date >= '{$date}'
            LIMIT 5");
        while ($row = $r->fetch_assoc()) {
            $found[(int) $row['id']] = $row;
        }
        if ($found) {
            break;
        }
    }

    return array_values($found);
}

function get_shift_for_day($m, $workShiftId, $staffId, $dayNum)
{
    $workShiftId = (int) $workShiftId;
    $staffId = (int) $staffId;
    $dayNum = (int) $dayNum;
    $r = $m->query("
        SELECT st.id, st.shift_type_name, st.time_start_work, st.time_end_work,
               st.start_lunch_break_time, st.end_lunch_break_time, st.color
        FROM tblwork_shift_detail_number_day wsd
        JOIN tblshift_type st ON st.id = wsd.shift_id
        WHERE wsd.work_shift_id = {$workShiftId}
          AND wsd.number = {$dayNum}
          AND (wsd.staff_id = {$staffId} OR wsd.staff_id = 0)
        ORDER BY wsd.staff_id DESC
        LIMIT 1
    ");
    return $r ? $r->fetch_assoc() : null;
}

function format_shift_cell($shift)
{
    if (!$shift) {
        return '—';
    }
    $name = trim((string) ($shift['shift_type_name'] ?? ''));
    $start = trim((string) ($shift['time_start_work'] ?? ''));
    $end = trim((string) ($shift['time_end_work'] ?? ''));
    if ($start === '' && $end === '') {
        return $name !== '' ? $name : '—';
    }
    return trim($name . ' (' . $start . ' - ' . $end . ')');
}

function calc_work_hours($start, $end)
{
    $start = trim((string) $start);
    $end = trim((string) $end);
    if ($start === '' || $end === '') {
        return '';
    }
    $in = strtotime('1970-01-01 ' . $start);
    $out = strtotime('1970-01-01 ' . $end);
    if ($in === false || $out === false) {
        return '';
    }
    if ($out <= $in) {
        $out += 86400; // night shift crosses midnight
    }
    return round(($out - $in) / 3600, 2);
}

// Active staff
$staffRes = $m->query("
    SELECT s.staffid, s.firstname, s.lastname, s.email,
           TRIM(COALESCE(s.staff_identifi, '')) AS staff_identifi,
           TRIM(COALESCE(i.empid, '')) AS empid,
           COALESCE(r.name, '') AS role_name,
           s.role AS role_id,
           GROUP_CONCAT(DISTINCT d.name ORDER BY d.name SEPARATOR ', ') AS departments
    FROM tblstaff s
    LEFT JOIN tblstaff_info i ON i.staffid = s.staffid
    LEFT JOIN tblroles r ON r.roleid = s.role
    LEFT JOIN tblstaff_departments sd ON sd.staffid = s.staffid
    LEFT JOIN tbldepartments d ON d.departmentid = sd.departmentid
    WHERE s.active = 1
    GROUP BY s.staffid, s.firstname, s.lastname, s.email, s.staff_identifi, i.empid, r.name, s.role
    ORDER BY s.firstname, s.lastname
");

$spreadsheet = new Spreadsheet();

// --- Sheet 1: Staff shift summary ---
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Staff Shift Timings');

$headers = [
    'S.No', 'Staff ID', 'Emp ID', 'Name', 'Email', 'Role', 'Department(s)',
    'Work Shift ID', 'Shift Valid From', 'Shift Valid To',
    'Shift Name', 'Start Time', 'End Time', 'Span (hrs)', 'Lunch Start', 'Lunch End',
    'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday',
    'Notes',
];
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col . '1', $h);
    $col++;
}
$lastCol = chr(ord('A') + count($headers) - 1);
$sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
]);

$rowNum = 2;
$sn = 1;
$noShift = 0;

while ($staff = $staffRes->fetch_assoc()) {
    $staffId = (int) $staff['staffid'];
    $deptIds = get_staff_departments($m, $staffId);
    $workShifts = fetch_work_shifts($m, $staffId, (int) $staff['role_id'], $deptIds, $today);

    if (empty($workShifts)) {
        $noShift++;
        $values = [
            $sn++, $staffId,
            trim($staff['staff_identifi'] ?: $staff['empid']),
            trim($staff['firstname'] . ' ' . $staff['lastname']),
            $staff['email'], $staff['role_name'], $staff['departments'],
            '', '', '', '', '', '', '', '', '',
            '—', '—', '—', '—', '—', '—', '—',
            'No work shift assigned',
        ];
        $c = 'A';
        foreach ($values as $v) {
            $sheet->setCellValue($c . $rowNum, $v);
            $c++;
        }
        $rowNum++;
        continue;
    }

    foreach ($workShifts as $ws) {
        $wsId = (int) $ws['id'];
        $weekShifts = [];
        foreach ($dayNames as $num => $label) {
            $weekShifts[$num] = get_shift_for_day($m, $wsId, $staffId, $num);
        }
        // Primary = Monday or first available day
        $primary = $weekShifts[1] ?? null;
        foreach ($weekShifts as $sh) {
            if ($sh) {
                $primary = $sh;
                break;
            }
        }

        $values = [
            $sn++, $staffId,
            trim($staff['staff_identifi'] ?: $staff['empid']),
            trim($staff['firstname'] . ' ' . $staff['lastname']),
            $staff['email'], $staff['role_name'], $staff['departments'],
            $wsId, $ws['from_date'], $ws['to_date'],
            $primary['shift_type_name'] ?? '',
            $primary['time_start_work'] ?? '',
            $primary['time_end_work'] ?? '',
            calc_work_hours($primary['time_start_work'] ?? '', $primary['time_end_work'] ?? ''),
            $primary['start_lunch_break_time'] ?? '',
            $primary['end_lunch_break_time'] ?? '',
        ];
        foreach ($dayNames as $num => $label) {
            $values[] = format_shift_cell($weekShifts[$num] ?? null);
        }
        $unique = array_unique(array_map(function ($sh) {
            return $sh ? format_shift_cell($sh) : '—';
        }, $weekShifts));
        $values[] = count(array_filter($unique, function ($v) {
            return $v !== '—';
        })) === 0 ? 'Shift mapped but no day detail' : (count($unique) === 1 ? 'Same all week' : 'Varies by day');

        $c = 'A';
        foreach ($values as $v) {
            $sheet->setCellValue($c . $rowNum, $v);
            $c++;
        }
        $rowNum++;
    }
}

$sheet->setCellValue('A' . $rowNum, 'Total rows: ' . ($rowNum - 2) . ' | Staff without shift: ' . $noShift . ' | Exported: ' . date('Y-m-d H:i:s') . ' (LIVE)');
$sheet->getStyle('A' . $rowNum)->getFont()->setBold(true);
$sheet->freezePane('A2');
foreach (range('A', $lastCol) as $c) {
    $sheet->getColumnDimension($c)->setAutoSize(true);
}

// --- Sheet 2: Shift type catalog ---
$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle('Shift Types');
$catHeaders = ['Shift ID', 'Shift Name', 'Start', 'End', 'Span (hrs)', 'Lunch Start', 'Lunch End', 'Color'];
$c = 'A';
foreach ($catHeaders as $h) {
    $sheet2->setCellValue($c . '1', $h);
    $c++;
}
$r2 = $m->query('SELECT id, shift_type_name, time_start_work, time_end_work, start_lunch_break_time, end_lunch_break_time, color FROM tblshift_type ORDER BY id');
$rn = 2;
while ($st = $r2->fetch_assoc()) {
    $sheet2->setCellValue('A' . $rn, $st['id']);
    $sheet2->setCellValue('B' . $rn, $st['shift_type_name']);
    $sheet2->setCellValue('C' . $rn, $st['time_start_work']);
    $sheet2->setCellValue('D' . $rn, $st['time_end_work']);
    $sheet2->setCellValue('E' . $rn, calc_work_hours($st['time_start_work'], $st['time_end_work']));
    $sheet2->setCellValue('F' . $rn, $st['start_lunch_break_time']);
    $sheet2->setCellValue('G' . $rn, $st['end_lunch_break_time']);
    $sheet2->setCellValue('H' . $rn, $st['color']);
    $rn++;
}
foreach (range('A', 'H') as $c) {
    $sheet2->getColumnDimension($c)->setAutoSize(true);
}

$writer = new Xlsx($spreadsheet);
$writer->save($outFile);

$totalStaff = $sn - 1;
echo "Exported shift timings for {$totalStaff} active staff ({$noShift} without shift) to {$outFile}\n";
