<?php
/**
 * Backfill datecreated from start_time where datecreated is missing/invalid.
 * Usage: php backfill_leave_datecreated.php [--dry-run]
 */
define('BASEPATH', '/var/www/html/t2gworkroom/application/');
require '/var/www/html/t2gworkroom/application/config/app-config.php';

$dry = in_array('--dry-run', $argv ?? [], true);
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($m->connect_error) {
    fwrite(STDERR, "DB connect failed\n");
    exit(1);
}

$where = "(datecreated IS NULL OR datecreated < '1971-01-01' OR datecreated LIKE '0000-%')
    AND start_time IS NOT NULL AND start_time >= '1971-01-01'";

$r = $m->query("SELECT COUNT(*) c FROM tbltimesheets_requisition_leave WHERE $where");
$count = (int) $r->fetch_row()[0];
echo ($dry ? 'dry_run ' : '') . "rows_to_backfill=$count\n";

if ($dry || $count === 0) {
    exit(0);
}

$sql = "UPDATE tbltimesheets_requisition_leave
    SET datecreated = start_time
    WHERE $where";
if (!$m->query($sql)) {
    fwrite(STDERR, "update failed: " . $m->error . "\n");
    exit(1);
}
echo "updated=" . $m->affected_rows . "\n";
