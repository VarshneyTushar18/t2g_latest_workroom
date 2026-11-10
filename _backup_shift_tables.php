<?php
define('BASEPATH', 'x');
$appRoot = '/var/www/html/t2gworkroom';
require $appRoot . '/application/config/app-config.php';

$out = '/tmp/shift_tables_backup_' . date('Ymd_His') . '.sql';
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($m->connect_error) {
    die('DB error: ' . $m->connect_error);
}

$tables = ['tblshift_type', 'tblwork_shift', 'tblwork_shift_detail_number_day', 'tblwork_shift_detail'];
$fh = fopen($out, 'w');
foreach ($tables as $table) {
    fwrite($fh, "-- backup {$table}\n");
    $r = $m->query("SELECT * FROM {$table}");
    while ($row = $r->fetch_assoc()) {
        $cols = array_map(function ($c) use ($m) {
            return '`' . str_replace('`', '``', $c) . '`';
        }, array_keys($row));
        $vals = array_map(function ($v) use ($m) {
            if ($v === null) {
                return 'NULL';
            }
            return "'" . $m->real_escape_string((string) $v) . "'";
        }, array_values($row));
        fwrite($fh, 'INSERT INTO ' . $table . ' (' . implode(',', $cols) . ') VALUES (' . implode(',', $vals) . ");\n");
    }
}
fclose($fh);
echo "BACKUP={$out}\n";
