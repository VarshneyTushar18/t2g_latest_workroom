<?php
define('BASEPATH', __DIR__ . '/../application/');
require __DIR__ . '/../application/config/app-config.php';
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$pfx = defined('APP_DB_PREFIX') ? APP_DB_PREFIX : 'tbl';
$target = '311'; // files migration_version 3.1.1
$r = $m->query("SELECT name, value FROM {$pfx}options WHERE name IN ('migration_version','updated_version')");
while ($row = $r->fetch_assoc()) {
    echo "before {$row['name']}={$row['value']}\n";
}
$m->query("UPDATE {$pfx}options SET value='{$target}' WHERE name='migration_version'");
echo "set migration_version={$target}\n";
