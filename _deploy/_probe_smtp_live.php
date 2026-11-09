<?php
define('BASEPATH', 'x');
require '/var/www/html/t2gworkroom/application/config/app-config.php';
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($m->connect_error) {
    fwrite(STDERR, 'connect: ' . $m->connect_error . "\n");
    exit(1);
}
$r = $m->query("SELECT name, value FROM tbloptions WHERE name LIKE 'smtp%' OR name IN ('email_protocol','mail_engine') ORDER BY name");
while ($row = $r->fetch_assoc()) {
    $v = $row['value'];
    if (stripos($row['name'], 'password') !== false && $v !== '') {
        $v = '[CONFIGURED encrypted len=' . strlen($v) . ']';
    }
    echo $row['name'] . '=' . $v . "\n";
}
