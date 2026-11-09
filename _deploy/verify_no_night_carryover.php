<?php
$root = getenv('T2G_ROOT') ?: '/var/www/html/t2gworkroom';
chdir($root);
define('ENVIRONMENT', 'production');
$_SERVER['CI_ENV'] = 'production';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_HOST'] = 't2gworkroom.com';
require 'index.php';

$CI = &get_instance();
$CI->load->model('timesheets/timesheets_model');

$staffId = (int) ($argv[1] ?? 0);
$ids = $staffId > 0 ? [$staffId] : [];
if (!$ids) {
    $rows = $CI->db->query(
        "SELECT DISTINCT c1.staff_id
         FROM tblcheck_in_out c1
         JOIN tblcheck_in_out c2
           ON c2.staff_id = c1.staff_id
          AND c2.type_check = 2
          AND DATE(c2.date) = DATE(c1.date) + INTERVAL 1 DAY
          AND TIME(c2.date) < '12:00:00'
         WHERE c1.type_check = 1
           AND c1.date >= '2026-10-01' AND c1.date < '2026-10-24'
           AND NOT EXISTS (
             SELECT 1 FROM tblcheck_in_out c3
             WHERE c3.staff_id = c1.staff_id
               AND c3.type_check = 1
               AND DATE(c3.date) = DATE(c1.date) + INTERVAL 1 DAY
           )
         LIMIT 8"
    )->result_array();
    foreach ($rows as $row) {
        $ids[] = (int) $row['staff_id'];
    }
}

$out = [];
foreach ($ids as $id) {
    $cal = $CI->timesheets_model->get_attendance_regularisation_calendar($id, 2026, 10);
    $days = [];
    foreach ($cal['days'] ?? [] as $day) {
        if (in_array($day['code'] ?? '', ['NC', 'AB', 'P', 'INC'], true) || ($day['status'] ?? '') === 'night_carryover') {
            $days[] = [
                'date' => $day['date'],
                'code' => $day['code'],
                'status' => $day['status'],
                'in' => $day['check_in'],
                'out' => $day['check_out'],
                'issues' => $day['issues'],
            ];
        }
    }
    $out[] = ['staff_id' => $id, 'days' => $days];
}
echo json_encode($out, JSON_PRETTY_PRINT) . "\n";
