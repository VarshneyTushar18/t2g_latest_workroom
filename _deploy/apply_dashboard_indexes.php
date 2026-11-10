<?php
define('BASEPATH', true);
require '/var/www/html/t2gworkroom/application/config/app-config.php';

$db = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$db->set_charset('utf8mb4');
$db->query('SET SESSION lock_wait_timeout = 120');

$indexes = [
    ['tblbiometric_report', 'idx_bio_employee_date', '`employee_code`, `attendance_date`'],
    ['tblnotifications', 'idx_notifications_user_date', '`touserid`, `date`'],
    ['tblnotifications', 'idx_notifications_user_read', '`touserid`, `isread`'],
    ['tblnotifications', 'idx_notifications_user_inline', '`touserid`, `isread_inline`'],
    ['tbltask_followers', 'idx_task_followers_task_staff', '`taskid`, `staffid`'],
    ['tbldismissed_announcements', 'idx_dismissed_announcements_user', '`staff`, `userid`, `announcementid`'],
];

foreach ($indexes as [$table, $index, $columns]) {
    $tableSql = $db->real_escape_string($table);
    $indexSql = $db->real_escape_string($index);
    $exists = $db->query(
        "SELECT 1 FROM information_schema.statistics
         WHERE table_schema = DATABASE() AND table_name = '{$tableSql}' AND index_name = '{$indexSql}'
         LIMIT 1"
    );
    if ($exists && $exists->num_rows > 0) {
        echo "EXISTS {$table}.{$index}\n";
        continue;
    }
    $started = microtime(true);
    $sql = "ALTER TABLE `{$table}` ADD INDEX `{$index}` ({$columns}), ALGORITHM=INPLACE, LOCK=NONE";
    if (!$db->query($sql)) {
        fwrite(STDERR, "FAILED {$table}.{$index}: {$db->error}\n");
        exit(1);
    }
    echo 'ADDED ' . $table . '.' . $index . ' in ' . round(microtime(true) - $started, 2) . "s\n";
}

$version = $db->query('SELECT version FROM tblmigrations LIMIT 1')->fetch_assoc();
if ((int) ($version['version'] ?? 0) === 312) {
    $db->query('UPDATE tblmigrations SET version = 313');
    echo "MIGRATION=313\n";
} else {
    echo 'MIGRATION_UNCHANGED=' . ($version['version'] ?? 'none') . "\n";
}
