<?php
define('BASEPATH', '/var/www/html/t2gworkroom/application/');
require '/var/www/html/t2gworkroom/application/config/app-config.php';
$sid = (int) ($argv[1] ?? 1275);
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$keys = ['earned-leave','8','Leave','annual_leave','planned_leaves','16','14'];
$in = "'" . implode("','", $keys) . "'";

foreach ([0, 2] as $st) {
    echo "status=$st EL types:\n";
    $q = $m->query("SELECT id, type_of_leave, number_of_leaving_day, start_time, end_time FROM tbltimesheets_requisition_leave WHERE staff_id=$sid AND status=$st AND type_of_leave IN ($in)");
    while ($q && ($r = $q->fetch_assoc())) {
        $id = (int) $r['id'];
        $ad = $m->query("SELECT rel_type, staffid, approve FROM tbltimesheets_approval_details WHERE rel_id=$id");
        echo json_encode($r) . " approvals=" . ($ad ? $ad->num_rows : 0) . "\n";
        if ($ad) {
            while ($a = $ad->fetch_assoc()) {
                echo "  rel_type={$a['rel_type']} approve={$a['approve']}\n";
            }
        }
    }
}

echo "\nOct 19 any status:\n";
$q = $m->query("SELECT id, status, type_of_leave, number_of_leaving_day FROM tbltimesheets_requisition_leave WHERE staff_id=$sid AND DATE(start_time)='2026-10-19'");
while ($q && ($r = $q->fetch_assoc())) {
    echo json_encode($r) . "\n";
}

echo "\nFlexible pending sum (status 0, EL type, any approval row):\n";
$q = $m->query("SELECT SUM(number_of_leaving_day) t FROM tbltimesheets_requisition_leave l WHERE staff_id=$sid AND status=0 AND type_of_leave IN ($in) AND EXISTS (SELECT 1 FROM tbltimesheets_approval_details ad WHERE ad.rel_id=l.id)");
echo ($q ? $q->fetch_assoc()['t'] : 0) . "\n";
