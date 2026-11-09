<?php
define('BASEPATH', true);
require '/var/www/html/t2gworkroom/application/config/app-config.php';
$db = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$db->set_charset('utf8mb4');

$result = [];
foreach (['tblbiometric_report', 'tblnotifications', 'tblannouncements', 'tbltask_assigned', 'tbltask_followers'] as $table) {
    $count = $db->query("SELECT COUNT(*) AS c FROM {$table}")->fetch_assoc();
    $indexes = $db->query("SHOW INDEX FROM {$table}")->fetch_all(MYSQLI_ASSOC);
    $result[$table] = [
        'rows' => (int) ($count['c'] ?? 0),
        'indexes' => array_map(static function ($row) {
            return [
                'name' => $row['Key_name'],
                'column' => $row['Column_name'],
                'seq' => (int) $row['Seq_in_index'],
                'unique' => !(bool) $row['Non_unique'],
            ];
        }, $indexes),
    ];
}
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
