<?php
/**
 * Read-only: identify suspicious early-morning Workroom IN/OUT pairs in Oct 2026.
 */
define('BASEPATH', true);
require '/var/www/html/t2gworkroom/application/config/app-config.php';

$db = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$db->set_charset('utf8mb4');

$sql = <<<'SQL'
SELECT
    p.staff_id,
    CONCAT(s.firstname, ' ', s.lastname) AS staff_name,
    DATE(p.date) AS punch_date,
    MIN(p.date) AS first_punch,
    MAX(p.date) AS last_punch,
    TIMESTAMPDIFF(SECOND, MIN(p.date), MAX(p.date)) AS span_seconds,
    SUM(p.type_check = 1) AS in_count,
    SUM(p.type_check = 2) AS out_count,
    GROUP_CONCAT(CONCAT(TIME(p.date), IF(p.type_check = 1, ' IN', ' OUT')) ORDER BY p.date SEPARATOR ' | ') AS punches,
    GROUP_CONCAT(DISTINCT CONCAT(t.type, ':', t.value) ORDER BY t.id SEPARATOR ' | ') AS timesheets
FROM tblcheck_in_out p
JOIN tblstaff s ON s.staffid = p.staff_id
LEFT JOIN tbltimesheets_timesheet t
    ON t.staff_id = p.staff_id AND t.date_work = DATE(p.date)
WHERE DATE(p.date) BETWEEN '2026-10-01' AND '2026-10-23'
  AND TIME(p.date) < '09:00:00'
GROUP BY p.staff_id, s.firstname, s.lastname, DATE(p.date)
HAVING in_count > 0
   AND out_count > 0
   AND span_seconds BETWEEN 0 AND 600
ORDER BY punch_date, staff_name
SQL;

$query = $db->query($sql);
if (!$query) {
    fwrite(STDERR, $db->error . PHP_EOL);
    exit(1);
}

$rows = $query->fetch_all(MYSQLI_ASSOC);
foreach ($rows as &$row) {
    $staffId = (int) $row['staff_id'];
    $date = $db->real_escape_string($row['punch_date']);
    $row['explicit_wfh_day'] = (bool) $db->query(
        "SELECT 1 FROM tblstaff_wfh_day
         WHERE staffid = {$staffId} AND wfh_date = '{$date}' LIMIT 1"
    )->num_rows;
    $row['approved_wfh_leave'] = (bool) $db->query(
        "SELECT 1 FROM tbltimesheets_requisition_leave
         WHERE staff_id = {$staffId}
           AND status = 1
           AND type_of_leave IN ('work-from-home', 'WFH', 'wfh')
           AND '{$date}' BETWEEN DATE(start_time) AND DATE(end_time)
         LIMIT 1"
    )->num_rows;
}
unset($row);

echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
