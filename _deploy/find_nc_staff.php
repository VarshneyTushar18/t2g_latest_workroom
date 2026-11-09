<?php
define('BASEPATH', true);
require '/var/www/html/t2gworkroom/application/config/app-config.php';
$db = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$db->set_charset('utf8mb4');

$sql = "
SELECT c.staff_id,
       SUM(DATE(c.date) = '2026-10-13') AS d13,
       SUM(DATE(c.date) = '2026-10-15') AS d15,
       SUM(DATE(c.date) = '2026-10-23') AS d23
FROM tblcheck_in_out c
WHERE c.type_check = 2
  AND TIME(c.date) < '12:00:00'
  AND DATE(c.date) IN ('2026-10-13','2026-10-15','2026-10-23')
GROUP BY c.staff_id
HAVING d13 > 0 AND d15 > 0 AND d23 > 0
";
$res = $db->query($sql);
if (!$res) {
    fwrite(STDERR, $db->error . "\n");
    exit(1);
}
while ($row = $res->fetch_assoc()) {
    $id = (int) $row['staff_id'];
    $name = $db->query("SELECT CONCAT(firstname,' ',lastname) AS n FROM tblstaff WHERE staffid = {$id}")->fetch_assoc();
    $ins = $db->query(
        "SELECT DATE(date) d, COUNT(*) n FROM tblcheck_in_out
         WHERE staff_id = {$id} AND type_check = 1
           AND DATE(date) IN ('2026-10-13','2026-10-15','2026-10-23','2026-10-24')
         GROUP BY DATE(date)"
    );
    $inDays = [];
    while ($ins && ($r = $ins->fetch_assoc())) {
        $inDays[] = $r['d'] . ':' . $r['n'];
    }
    echo $id . ' ' . ($name['n'] ?? '') . ' outs=' . $row['d13'] . '/' . $row['d15'] . '/' . $row['d23']
        . ' ins=' . implode(',', $inDays) . "\n";
}
