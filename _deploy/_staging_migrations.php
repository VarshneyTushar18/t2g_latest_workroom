<?php
define('BASEPATH', 'x');
require '/var/www/workroom/application/config/app-config.php';
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
foreach (['tblmigrations', 'tblmigration'] as $t) {
    $r = $m->query("SHOW TABLES LIKE '$t'");
    if ($r && $r->num_rows) {
        $r2 = $m->query("SELECT * FROM $t LIMIT 3");
        echo "== $t ==\n";
        while ($row = $r2->fetch_assoc()) {
            print_r($row);
        }
    }
}
