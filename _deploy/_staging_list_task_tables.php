<?php
define('BASEPATH', 'x');
require '/var/www/workroom/application/config/app-config.php';
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$r = $m->query("SHOW TABLES LIKE '%task%'");
while ($row = $r->fetch_row()) {
    echo $row[0] . "\n";
}
$r = $m->query("SELECT COUNT(*) c FROM tbloptions");
echo "tbloptions_rows=" . ($r ? $r->fetch_row()[0] : 0) . "\n";
