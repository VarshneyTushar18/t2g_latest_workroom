<?php
define('BASEPATH', '/var/www/html/t2gworkroom/application/');
require '/var/www/html/t2gworkroom/application/config/app-config.php';

$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$sid = (int) ($argv[1] ?? 1275);

echo "Staff $sid — pending earned leave (status=0 + approval chain):\n\n";

$types = ['earned-leave', '8', 'Leave', 'annual_leave', 'planned_leaves'];
$tr = $m->query("SELECT id, slug FROM tbltimesheets_type_of_leave WHERE slug='earned-leave'");
if ($tr && ($t = $tr->fetch_assoc())) {
    $types[] = (string) $t['id'];
}
$in = "'" . implode("','", array_map([$m, 'real_escape_string'], array_unique($types))) . "'";

$q = $m->query("SELECT id, type_of_leave, number_of_leaving_day, start_time, end_time, status, datecreated, subject
  FROM tbltimesheets_requisition_leave
  WHERE staff_id=$sid AND status=0 AND type_of_leave IN ($in)
  ORDER BY id DESC");
while ($q && ($row = $q->fetch_assoc())) {
    $id = (int) $row['id'];
    $ad = $m->query("SELECT id, staffid, approve, rel_type FROM tbltimesheets_approval_details WHERE rel_id=$id");
    $ac = $ad ? $ad->num_rows : 0;
    echo "Leave #$id days={$row['number_of_leaving_day']} {$row['start_time']}..{$row['end_time']} type={$row['type_of_leave']} approver_rows=$ac\n";
    echo "  subject: " . substr($row['subject'] ?? '', 0, 80) . "\n";
    if ($ad) {
        while ($a = $ad->fetch_assoc()) {
            echo "    approval id={$a['id']} staff={$a['staffid']} approve={$a['approve']} rel_type={$a['rel_type']}\n";
        }
    }
    $rt = $m->real_escape_string($row['type_of_leave']);
    $ex = $m->query("SELECT COUNT(*) c FROM tbltimesheets_approval_details WHERE rel_id=$id AND rel_type='$rt'");
    $inChain = $ex && (int) $ex->fetch_assoc()['c'] > 0;
    echo "  in_chain=" . ($inChain ? 'YES' : 'no') . "\n\n";
}

echo "Sum in chain:\n";
$pq = $m->query("SELECT COALESCE(SUM(l.number_of_leaving_day),0) t FROM tbltimesheets_requisition_leave l
  WHERE l.staff_id=$sid AND l.status=0 AND l.type_of_leave IN ($in)
  AND EXISTS (SELECT 1 FROM tbltimesheets_approval_details ad WHERE ad.rel_id=l.id AND ad.rel_type=l.type_of_leave)");
echo ($pq ? $pq->fetch_assoc()['t'] : 0) . "\n";

echo "\nOverlap with 2026-10-19:\n";
$q2 = $m->query("SELECT id, status, type_of_leave, number_of_leaving_day, start_time, end_time FROM tbltimesheets_requisition_leave
  WHERE staff_id=$sid AND start_time <= '2026-10-19 23:59:59' AND end_time >= '2026-10-19 00:00:00'
  ORDER BY id DESC LIMIT 15");
while ($q2 && ($r = $q2->fetch_assoc())) {
    echo "  id={$r['id']} st={$r['status']} type={$r['type_of_leave']} days={$r['number_of_leaving_day']} {$r['start_time']}..{$r['end_time']}\n";
}
