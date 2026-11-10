<?php
/**
 * Export all active Workroom staff to Excel.
 * Usage: php _export_active_staff.php [output.xlsx]
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
use PhpOffice\PhpSpreadsheet\Style\Border;

$outFile = $argv[1] ?? '/tmp/Workroom_Active_Staff_' . date('Y-m-d') . '.xlsx';

$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($m->connect_error) {
    fwrite(STDERR, 'DB error: ' . $m->connect_error . "\n");
    exit(1);
}
$m->set_charset('utf8mb4');

$hasEmpCat = false;
$cols = $m->query("SHOW COLUMNS FROM tblstaff_info LIKE 'employment_category'");
if ($cols && $cols->num_rows > 0) {
    $hasEmpCat = true;
}

$sql = "
SELECT
    s.staffid,
    TRIM(COALESCE(s.staff_identifi, '')) AS staff_identifi,
    TRIM(COALESCE(i.empid, '')) AS empid,
    s.firstname,
    s.lastname,
    s.email,
    TRIM(COALESCE(s.phonenumber, '')) AS phonenumber,
    COALESCE(r.name, '') AS role_name,
    GROUP_CONCAT(DISTINCT d.name ORDER BY d.name SEPARATOR ', ') AS departments,
    TRIM(COALESCE(i.doj, '')) AS doj,
    " . ($hasEmpCat ? "TRIM(COALESCE(i.employment_category, 'fte'))" : "'fte'") . " AS employment_category,
    s.datecreated
FROM tblstaff s
LEFT JOIN tblstaff_info i ON i.staffid = s.staffid
LEFT JOIN tblroles r ON r.roleid = s.role
LEFT JOIN tblstaff_departments sd ON sd.staffid = s.staffid
LEFT JOIN tbldepartments d ON d.departmentid = sd.departmentid
WHERE s.active = 1
GROUP BY s.staffid, s.staff_identifi, i.empid, s.firstname, s.lastname, s.email,
         s.phonenumber, r.name, i.doj, employment_category, s.datecreated
ORDER BY s.firstname ASC, s.lastname ASC
";

$res = $m->query($sql);
if (!$res) {
    fwrite(STDERR, 'Query failed: ' . $m->error . "\n");
    exit(1);
}

$headers = [
    'S.No',
    'Staff ID',
    'Emp ID (staff_identifi)',
    'Emp ID (staff_info)',
    'First Name',
    'Last Name',
    'Full Name',
    'Email',
    'Phone',
    'Role',
    'Department(s)',
    'Employment Category',
    'Date of Joining',
    'Workroom Created',
    'Status',
];

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Active Staff');

$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col . '1', $h);
    $col++;
}
$lastCol = chr(ord('A') + count($headers) - 1);
$sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

$rowNum = 2;
$sn = 1;
while ($row = $res->fetch_assoc()) {
    $fullName = trim($row['firstname'] . ' ' . $row['lastname']);
    $empCode = trim($row['staff_identifi'] ?: $row['empid']);
    $values = [
        $sn++,
        (int) $row['staffid'],
        $row['staff_identifi'],
        $row['empid'],
        $row['firstname'],
        $row['lastname'],
        $fullName,
        $row['email'],
        $row['phonenumber'],
        $row['role_name'],
        $row['departments'],
        strtoupper($row['employment_category'] ?: 'FTE'),
        $row['doj'],
        $row['datecreated'],
        'Active',
    ];
    $col = 'A';
    foreach ($values as $v) {
        $sheet->setCellValue($col . $rowNum, $v);
        $col++;
    }
    $rowNum++;
}

$total = $rowNum - 2;
$sheet->setCellValue('A' . $rowNum, 'Total active staff: ' . $total);
$sheet->getStyle('A' . $rowNum)->getFont()->setBold(true);

foreach (range('A', $lastCol) as $c) {
    $sheet->getColumnDimension($c)->setAutoSize(true);
}

$sheet->freezePane('A2');

$writer = new Xlsx($spreadsheet);
$writer->save($outFile);

echo "Exported {$total} active staff to {$outFile}\n";
