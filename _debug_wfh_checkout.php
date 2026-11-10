<?php
define('BASEPATH', 'x');
require '/var/www/html/t2gworkroom/application/config/app-config.php';
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);

echo "=== Attendance settings ===\n";
$opts = ['timekeeping_enable_valid_ip','allow_attendance_by_coordinates','allow_attendance_by_route','allows_updating_check_in_time'];
foreach ($opts as $o) {
    $r = $m->query("SELECT * FROM tbltimesheets_option WHERE option_name='$o' LIMIT 1");
    if ($r && ($row = $r->fetch_assoc())) {
        $v = $row['option_value'] ?? $row['value'] ?? json_encode($row);
    } else {
        $v = 'unset';
    }
    echo "$o = $v\n";
}

echo "\n=== Rehan Khan + recent WFH punch issues ===\n";
$q = $m->query(
    "SELECT s.staffid, i.empid, s.firstname, s.lastname, i.employment_category,
            (SELECT COUNT(*) FROM tblcheck_in_out c WHERE c.staff_id=s.staffid AND DATE(c.date)>=CURDATE()-INTERVAL 7 DAY AND c.type_check=1) ins,
            (SELECT COUNT(*) FROM tblcheck_in_out c WHERE c.staff_id=s.staffid AND DATE(c.date)>=CURDATE()-INTERVAL 7 DAY AND c.type_check=2) outs
     FROM tblstaff s
     LEFT JOIN tblstaff_info i ON i.staffid=s.staffid
     WHERE s.firstname LIKE '%Rehan%' OR s.lastname LIKE '%Khan%' AND s.firstname LIKE '%Rehan%'
     LIMIT 5"
);
while ($st = $q->fetch_assoc()) {
    print_r($st);
    $sid = (int)$st['staffid'];
    echo "  Today punches:\n";
    $p = $m->query("SELECT date,type_check FROM tblcheck_in_out WHERE staff_id=$sid AND DATE(date)=CURDATE() ORDER BY date");
    while ($row = $p->fetch_assoc()) echo "    {$row['date']} type={$row['type_check']}\n";
    echo "  Latest 4 punches:\n";
    $p = $m->query("SELECT date,type_check FROM tblcheck_in_out WHERE staff_id=$sid ORDER BY date DESC LIMIT 4");
    while ($row = $p->fetch_assoc()) echo "    {$row['date']} type={$row['type_check']}\n";
    $bio = $m->query("SHOW TABLES LIKE 'tblbiometric_attendance'");
    if ($bio && $bio->num_rows) {
        $b = $m->query("SELECT punch_date,a_in_time,a_out_time FROM tblbiometric_attendance WHERE staff_id=$sid ORDER BY punch_date DESC LIMIT 3");
        echo "  biometric_rows:\n";
        while ($row = $b->fetch_assoc()) echo "    ".json_encode($row)."\n";
    }
}

echo "\n=== Open check-ins older than 13h (checkout blocked by code) ===\n";
$q = $m->query(
    "SELECT c.staff_id, i.empid, CONCAT(s.firstname,' ',s.lastname) name, c.date last_in,
            TIMESTAMPDIFF(HOUR, c.date, NOW()) hrs
     FROM tblcheck_in_out c
     JOIN (SELECT staff_id, MAX(date) md FROM tblcheck_in_out GROUP BY staff_id) x
       ON x.staff_id=c.staff_id AND x.md=c.date
     JOIN tblstaff s ON s.staffid=c.staff_id
     LEFT JOIN tblstaff_info i ON i.staffid=s.staffid
     WHERE c.type_check=1 AND TIMESTAMPDIFF(HOUR, c.date, NOW()) >= 13
       AND NOT EXISTS (SELECT 1 FROM tblcheck_in_out o WHERE o.staff_id=c.staff_id AND o.type_check=2 AND o.date > c.date)
     ORDER BY c.date DESC LIMIT 15"
);
while ($r = $q->fetch_assoc()) {
    printf("  %s (%s) IN %s (%sh ago) — checkout button hidden\n", $r['name'], $r['empid']??$r['staff_id'], $r['last_in'], $r['hrs']);
}

echo "\n=== Staff checked IN today but no OUT (open sessions) ===\n";
$q = $m->query(
    "SELECT s.staffid, i.empid, CONCAT(s.firstname,' ',s.lastname) name, MAX(c.date) last_in
     FROM tblcheck_in_out c
     JOIN tblstaff s ON s.staffid=c.staff_id
     LEFT JOIN tblstaff_info i ON i.staffid=s.staffid
     WHERE c.type_check=1 AND DATE(c.date)=CURDATE()
     GROUP BY c.staff_id
     HAVING NOT EXISTS (
       SELECT 1 FROM tblcheck_in_out o
       WHERE o.staff_id=c.staff_id AND o.type_check=2 AND DATE(o.date)=CURDATE() AND o.date > last_in
     )
     ORDER BY last_in DESC LIMIT 20"
);
while ($r = $q->fetch_assoc()) {
    printf("  %s (%s) last IN %s\n", $r['name'], $r['empid']??$r['staffid'], $r['last_in']);
}
