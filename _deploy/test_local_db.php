<?php
mysqli_report(MYSQLI_REPORT_OFF);
$m = @new mysqli('localhost', 'root', '', 'production_crm');
if ($m->connect_error) {
    echo 'FAIL: ' . $m->connect_error . PHP_EOL;
    exit(1);
}
echo 'OK: production_crm' . PHP_EOL;
