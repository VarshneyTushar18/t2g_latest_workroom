<?php
define('BASEPATH', 'x');
require '/var/www/html/t2gworkroom/application/config/app-config.php';
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$opts = ['email_protocol', 'mail_engine', 'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_email', 'companyname'];
foreach ($opts as $o) {
    $r = $m->query("SELECT value FROM tbloptions WHERE name='" . $m->real_escape_string($o) . "'");
    $v = $r ? ($r->fetch_row()[0] ?? '') : '';
    echo $o . '=' . $v . "\n";
}
