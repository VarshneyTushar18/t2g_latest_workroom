<?php
$cfg = file_get_contents('/var/www/html/t2gworkroom/application/config/app-config.php');
preg_match("/define\('APP_DB_HOSTNAME',\s*'([^']*)'\)/", $cfg, $h);
preg_match("/define\('APP_DB_USERNAME',\s*'([^']*)'\)/", $cfg, $u);
preg_match("/define\('APP_DB_PASSWORD',\s*'([^']*)'\)/", $cfg, $p);
preg_match("/define\('APP_DB_NAME',\s*'([^']*)'\)/", $cfg, $n);
$m = new mysqli($h[1], $u[1], $p[1], $n[1]);
$m->set_charset('utf8mb4');

foreach ([1268, 1304] as $sid) {
    echo "=== staff $sid ===\n";
    $q = $m->query("SELECT id, type_of_leave, status, start_time, end_time, number_of_leaving_day, leave_balance
     FROM tbltimesheets_requisition_leave WHERE staff_id = $sid ORDER BY id DESC LIMIT 20");
    $n = 0;
    while ($r = $q->fetch_assoc()) {
        $n++;
        echo json_encode($r) . "\n";
    }
    if (!$n) echo "no rows\n";
}
