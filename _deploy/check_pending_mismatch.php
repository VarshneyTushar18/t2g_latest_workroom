<?php
define('BASEPATH', '/var/www/html/t2gworkroom/application/');
require '/var/www/html/t2gworkroom/application/config/app-config.php';
$sid = (int) ($argv[1] ?? 1275);
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);

echo "All status=0 for staff $sid:\n";
$q = $m->query("SELECT id, type_of_leave, number_of_leaving_day, start_time, end_time FROM tbltimesheets_requisition_leave WHERE staff_id=$sid AND status=0 ORDER BY id DESC");
while ($q && ($r = $q->fetch_assoc())) {
    $id = (int) $r['id'];
    $tol = $r['type_of_leave'];
    $ad = $m->query("SELECT rel_type, COUNT(*) c FROM tbltimesheets_approval_details WHERE rel_id=$id GROUP BY rel_type");
    echo "#$id type_of_leave=[$tol] days={$r['number_of_leaving_day']} {$r['start_time']}\n";
    if ($ad && $ad->num_rows) {
        while ($a = $ad->fetch_assoc()) {
            $match = ($a['rel_type'] === $tol) ? 'MATCH' : 'MISMATCH';
            echo "  rel_type=[{$a['rel_type']}] count={$a['c']} $match\n";
        }
    } else {
        echo "  (no approval_details)\n";
    }
}

echo "\nPending sum with rel_type mismatch fix (any approval on rel_id):\n";
$pq = $m->query("SELECT l.id, l.type_of_leave, l.number_of_leaving_day FROM tbltimesheets_requisition_leave l
  WHERE l.staff_id=$sid AND l.status=0
  AND EXISTS (SELECT 1 FROM tbltimesheets_approval_details ad WHERE ad.rel_id=l.id)");
$sum = 0;
while ($pq && ($p = $pq->fetch_assoc())) {
    $keys = ['8','Leave','earned-leave','annual_leave'];
    if (in_array($p['type_of_leave'], $keys, true) || $p['type_of_leave'] === 'earned-leave') {
        echo "  EL-ish #{$p['id']} type={$p['type_of_leave']} days={$p['number_of_leaving_day']}\n";
        $sum += (float) $p['number_of_leaving_day'];
    }
}
echo "Sum: $sum\n";

echo "\nEarned leave type row:\n";
$tr = $m->query("SELECT id, slug FROM tbltimesheets_type_of_leave");
while ($tr && ($t = $tr->fetch_assoc())) {
    echo "  id={$t['id']} slug={$t['slug']}\n";
}
