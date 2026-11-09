<?php
/**
 * Audit leave rows with missing/invalid datecreated (all staff).
 * Usage on live: php /tmp/audit_leave_datecreated.php
 */
define('BASEPATH', '/var/www/html/t2gworkroom/application/');
require '/var/www/html/t2gworkroom/application/config/app-config.php';

$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($m->connect_error) {
    fwrite(STDERR, "DB connect failed\n");
    exit(1);
}

$bad = "datecreated IS NULL OR datecreated < '1971-01-01' OR datecreated LIKE '0000-%'";
$r = $m->query("SELECT COUNT(*) c FROM tbltimesheets_requisition_leave");
$total = (int) $r->fetch_row()[0];
$r = $m->query("SELECT COUNT(*) c FROM tbltimesheets_requisition_leave WHERE $bad");
$badCount = (int) $r->fetch_row()[0];
$r = $m->query("SELECT COUNT(DISTINCT staff_id) c FROM tbltimesheets_requisition_leave WHERE $bad");
$staffBad = (int) $r->fetch_row()[0];

echo "leave_rows_total=$total\n";
echo "leave_rows_bad_datecreated=$badCount\n";
echo "staff_with_bad_rows=$staffBad\n";

$r = $m->query("SELECT staff_id, COUNT(*) n FROM tbltimesheets_requisition_leave WHERE $bad GROUP BY staff_id ORDER BY n DESC LIMIT 15");
echo "top_staff_bad:\n";
while ($row = $r->fetch_assoc()) {
    echo "  staff_id={$row['staff_id']} count={$row['n']}\n";
}
