#!/usr/bin/env php
<?php
/**
 * Import DailyAttendance_DetailedReportForDepartment Excel into tblbiometric_report.
 * Official department report wins over API sync (force_replace).
 *
 * Usage: php _import_department_bio_report.php /path/to/report.xls [--dry-run]
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

$xls_path = $argv[1] ?? '';
$dry_run = in_array('--dry-run', $argv ?? [], true);
if ($xls_path === '' || !is_file($xls_path)) {
    fwrite(STDERR, "Usage: php _import_department_bio_report.php /path/to/report.xls [--dry-run]\n");
    exit(1);
}

$environment = 'production';
$root = '/var/www/html/t2gworkroom';
chdir($root);

define('BASEPATH', str_replace('\\', '/', realpath($root . '/system') . '/'));
define('APPPATH', $root . '/application/');
define('VIEWPATH', APPPATH . 'views/');
define('EXT', '.php');
define('ENVIRONMENT', $environment);
define('FCPATH', $root . '/');

$vendor_autoload = $root . '/vendor/autoload.php';
if (!is_file($vendor_autoload)) {
    $vendor_autoload = $root . '/application/vendor/autoload.php';
}
if (!is_file($vendor_autoload)) {
    fwrite(STDERR, "vendor/autoload.php not found.\n");
    exit(1);
}
require_once $vendor_autoload;

require APPPATH . 'config/constants.php';
require BASEPATH . 'core/Common.php';
if (file_exists(APPPATH . 'vendor/autoload.php')) {
    require_once APPPATH . 'vendor/autoload.php';
}
require_once APPPATH . 'config/hooks.php';
require_once APPPATH . 'hooks/App_Autoloader.php';
(new App_Autoloader)->register();
define('MB_ENABLED', extension_loaded('mbstring'));
define('ICONV_ENABLED', extension_loaded('iconv'));
$GLOBALS['CFG'] = &load_class('Config', 'core');
$GLOBALS['UNI'] = &load_class('Utf8', 'core');
if (file_exists(BASEPATH . 'core/Security.php')) {
    $GLOBALS['SEC'] = &load_class('Security', 'core');
}
load_class('Router', 'core');
load_class('Input', 'core');
load_class('Lang', 'core');
require BASEPATH . 'core/Controller.php';

function &get_instance()
{
    return CI_Controller::get_instance();
}

$CI = new CI_Controller();
require_once APPPATH . 'hooks/InitHook.php';
_app_init_load();
$CI->load->model('biometric_model');

$header_aliases = [
    'attendance_date' => ['AttendanceDate', 'Attendance Date'],
    'company' => ['Company'],
    'location' => ['Location'],
    'employee_code' => ['Employee Code', 'EmployeeCode'],
    'employee_name' => ['EmployeeName', 'Employee Name'],
    'shift' => ['Shift'],
    's_in_time' => ['S. InTime', 'Scheduled In Time', 'S InTime'],
    's_out_time' => ['S. OutTime', 'Scheduled Out Time', 'S OutTime'],
    'a_in_time' => ['A. InTime', 'Actual In Time', 'A InTime'],
    'a_out_time' => ['A.OutTime', 'Actual Out Time', 'A OutTime'],
    'work_duration' => ['Work Duration'],
    't_duration' => ['T Duration', 'Total Duration'],
    'late_by' => ['LateBy', 'Late By'],
    'early_going_by' => ['EarlyGoing By', 'Early Going By'],
    'over_time' => ['OverTime', 'Over Time'],
    'status' => ['Status'],
    'punch_records' => ['Punch Records'],
    'remark' => ['Remarks', 'Remark'],
];

$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($xls_path);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray(null, true, true, false);

$header_index = null;
$column_map = [];
foreach ($data as $i => $row) {
    $headers = array_map(function ($h) {
        return trim((string) $h);
    }, $row);
    $map = [];
    foreach ($header_aliases as $field => $labels) {
        foreach ($labels as $label) {
            $idx = array_search($label, $headers, true);
            if ($idx !== false) {
                $map[$field] = $idx;
                break;
            }
        }
    }
    if (isset($map['attendance_date'], $map['employee_code'])) {
        $header_index = $i;
        $column_map = $map;
        break;
    }
}

if ($header_index === null) {
    fwrite(STDERR, "Could not find AttendanceDate + Employee Code headers.\n");
    exit(1);
}

$staff_codes = [];
$staff_rows = $CI->db->query(
    'SELECT TRIM(staff_identifi) AS code FROM ' . db_prefix() . 'staff WHERE active = 1 AND staff_identifi IS NOT NULL AND TRIM(staff_identifi) <> ""'
)->result_array();
foreach ($staff_rows as $sr) {
    $staff_codes[trim((string) $sr['code'])] = true;
}
$emp_rows = $CI->db->query(
    'SELECT TRIM(empid) AS code FROM ' . db_prefix() . 'staff_info WHERE empid IS NOT NULL AND TRIM(empid) <> ""'
)->result_array();
foreach ($emp_rows as $er) {
    $staff_codes[trim((string) $er['code'])] = true;
}

$records = [];
$skipped_not_staff = 0;
for ($i = $header_index + 1; $i < count($data); $i++) {
    $row = $data[$i];
    $mapped = [];
    foreach ($column_map as $field => $col) {
        $mapped[$field] = isset($row[$col]) ? trim((string) $row[$col]) : '';
    }
    $code = trim((string) ($mapped['employee_code'] ?? ''));
    if ($code === '' && ($mapped['attendance_date'] ?? '') === '') {
        continue;
    }
    if ($code === '') {
        continue;
    }
    // Normalize numeric employee codes (2162 not 2162.0 from Excel).
    if (preg_match('/^\d+(\.0+)?$/', $code)) {
        $code = (string) (int) floatval($code);
    }
    if (!isset($staff_codes[$code])) {
        $skipped_not_staff++;
        continue;
    }

    $punch_raw = trim((string) ($mapped['punch_records'] ?? ''));
    if ($punch_raw === '' || strtolower($punch_raw) === 'nan') {
        $mapped['punch_records'] = '';
        $mapped['force_clear_punches'] = true;
    }

    $mapped['employee_code'] = $code;
    $mapped['force_replace'] = true;
    $mapped['remark'] = 'department_report_import';
    $records[] = $mapped;
}

echo 'Rows in file: ' . (count($data) - $header_index - 1) . "\n";
echo 'Staff rows to import: ' . count($records) . "\n";
echo 'Skipped (not active staff code): ' . $skipped_not_staff . "\n";

if ($dry_run) {
    echo "Dry run — no DB changes.\n";
    exit(0);
}

$batch = 200;
$inserted = 0;
$updated = 0;
$skipped = 0;
for ($offset = 0; $offset < count($records); $offset += $batch) {
    $chunk = array_slice($records, $offset, $batch);
    $result = $CI->biometric_model->insert_attendance_bulk($chunk);
    $inserted += (int) ($result['inserted'] ?? 0);
    $updated += (int) ($result['updated'] ?? 0);
    $skipped += (int) ($result['skipped'] ?? 0);
    echo 'Batch ' . ($offset / $batch + 1) . ': +' . (int) ($result['inserted'] ?? 0) . ' / ~' . (int) ($result['updated'] ?? 0) . "\n";
}

echo "\nDone. Inserted: $inserted | Updated: $updated | Skipped: $skipped\n";
