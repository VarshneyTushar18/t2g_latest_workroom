<?php
/**
 * Read-only live audit for Farah Naaz (#191), 14-17 Oct 2026.
 */
define('BASEPATH', true);
require '/var/www/html/t2gworkroom/application/config/app-config.php';

$db = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$db->set_charset('utf8mb4');

$queries = [
    'timesheets' => "SELECT * FROM tbltimesheets_timesheet
        WHERE staff_id = 191 AND date_work BETWEEN '2026-10-14' AND '2026-10-17'
        ORDER BY date_work, id",
    'punches' => "SELECT * FROM tblcheck_in_out
        WHERE staff_id = 191 AND DATE(date) BETWEEN '2026-10-13' AND '2026-10-18'
        ORDER BY date, id",
    'regularisations' => "SELECT * FROM tbltimesheets_additional_timesheet
        WHERE creator = 191 AND additional_day BETWEEN '2026-10-14' AND '2026-10-17'
        ORDER BY additional_day, id",
    'leaves' => "SELECT * FROM tbltimesheets_requisition_leave
        WHERE staff_id = 191
          AND DATE(end_time) >= '2026-10-14'
          AND DATE(start_time) <= '2026-10-17'
        ORDER BY start_time, id",
    'wfh_days' => "SELECT * FROM tblstaff_wfh_day
        WHERE staffid = 191 AND wfh_date BETWEEN '2026-10-14' AND '2026-10-17'
        ORDER BY wfh_date",
];

$result = [];
foreach ($queries as $key => $sql) {
    $result[$key] = [];
    $query = $db->query($sql);
    if (!$query) {
        $result[$key] = ['error' => $db->error];
        continue;
    }
    while ($row = $query->fetch_assoc()) {
        $result[$key][] = $row;
    }
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
