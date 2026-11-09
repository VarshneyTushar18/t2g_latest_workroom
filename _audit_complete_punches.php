<?php
define('BASEPATH', 'x');
require '/var/www/html/t2gworkroom/application/config/app-config.php';
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$from = $argv[1] ?? '2026-10-01';
$to = $argv[2] ?? date('Y-m-d');
$min = 535;

function span_day($m, $sid, $date) {
    $q = $m->query("SELECT date,type_check FROM tblcheck_in_out WHERE staff_id=$sid AND DATE(date)='$date' ORDER BY date");
    $ins=[];$outs=[];
    while($r=$q->fetch_assoc()){ $t=strtotime($r['date']); if((int)$r['type_check']===2)$outs[]=$t; else $ins[]=$t; }
    if(!$ins) return null;
    $in=min($ins); $vo=array_filter($outs,fn($t)=>$t>$in);
    if(!$vo) return null;
    return ['span'=>intdiv(max($vo)-$in,60),'in'=>date('H:i',$in),'out'=>date('H:i',max($vo))];
}

echo "=== DB WFH sources ===\n";
foreach ([
    "SELECT COUNT(*) c FROM tblstaff_wfh_day" => 'wfh_allotment_rows',
    "SELECT COUNT(DISTINCT staffid) c FROM tblstaff_wfh_day" => 'wfh_allotment_staff',
    "SELECT COUNT(*) c FROM tblstaff_info WHERE LOWER(TRIM(employment_category))='wfh'" => 'employment_wfh',
    "SELECT COUNT(*) c FROM tbltimesheets_timesheet WHERE UPPER(TRIM(type))='WFH' AND date_work BETWEEN '$from' AND '$to'" => 'timesheet_wfh_oct',
] as $sql => $label) {
    $r = $m->query($sql);
    echo "$label: ".($r?$r->fetch_assoc()['c']:'err')."\n";
}

echo "\n=== Staff with COMPLETE check-in+out (>= threshold) Oct ===\n";
$q = $m->query(
    "SELECT DISTINCT c.staff_id, DATE(c.date) d FROM tblcheck_in_out c
     WHERE DATE(c.date) BETWEEN '$from' AND '$to' ORDER BY d, c.staff_id"
);
$ok=0; $wrong=0; $total=0;
while ($row = $q->fetch_assoc()) {
    $p = span_day($m, (int)$row['staff_id'], $row['d']);
    if (!$p) continue;
    $total++;
    $exp = $p['span'] >= $min ? 'P' : ($p['span']>=300?'HD':'AB');
    $ts = $m->query("SELECT type,value FROM tbltimesheets_timesheet WHERE staff_id={$row['staff_id']} AND date_work='{$row['d']}' LIMIT 1")->fetch_assoc();
    $tt = strtoupper(trim($ts['type']??''));
    $match = ($tt===$exp) || ($exp==='P' && in_array($tt,['P','W','WFH','SI'],true));
    if ($match) { $ok++; continue; }
    $wrong++;
    if ($wrong <= 25) {
        $st = $m->query("SELECT s.firstname,s.lastname,i.empid FROM tblstaff s LEFT JOIN tblstaff_info i ON i.staffid=s.staffid WHERE s.staffid={$row['staff_id']}")->fetch_assoc();
        printf("WRONG %s Emp %s %s %s | %dm | expect %s | got %s (%s)\n", $row['d'], $st['empid']??$row['staff_id'], $st['firstname']??'', $st['lastname']??'', $p['span'], $exp, $tt?:'—', $ts['value']??'—');
    }
}
echo "Complete punch days: $total | OK: $ok | WRONG: $wrong\n";

echo "\n=== Check-in ONLY (no checkout) — blocks Present ===\n";
$q = $m->query(
    "SELECT c.staff_id, DATE(c.date) d, COUNT(*) swipes,
            SUM(CASE WHEN c.type_check=1 THEN 1 ELSE 0 END) ins,
            SUM(CASE WHEN c.type_check=2 THEN 1 ELSE 0 END) outs
     FROM tblcheck_in_out c
     WHERE DATE(c.date) BETWEEN '$from' AND '$to'
     GROUP BY c.staff_id, DATE(c.date)
     HAVING ins >= 1 AND outs = 0
     ORDER BY d DESC"
);
$inonly = 0;
while ($row = $q->fetch_assoc()) {
    $inonly++;
    if ($inonly <= 20) {
        $st = $m->query("SELECT s.firstname,s.lastname,i.empid FROM tblstaff s LEFT JOIN tblstaff_info i ON i.staffid=s.staffid WHERE s.staffid={$row['staff_id']}")->fetch_assoc();
        $ts = $m->query("SELECT type FROM tbltimesheets_timesheet WHERE staff_id={$row['staff_id']} AND date_work='{$row['d']}' LIMIT 1")->fetch_assoc();
        printf("IN_ONLY %s Emp %s %s %s | timesheet %s\n", $row['d'], $st['empid']??$row['staff_id'], $st['firstname']??'', $st['lastname']??'', strtoupper(trim($ts['type']??'—')));
    }
}
echo "Total check-in-only days: $inonly\n";
