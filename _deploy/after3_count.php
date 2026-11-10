<?php
$cfg = file_get_contents('/var/www/html/t2gworkroom/application/config/app-config.php');
preg_match("/define\('APP_DB_HOSTNAME',\s*'([^']*)'\)/", $cfg, $h);
preg_match("/define\('APP_DB_USERNAME',\s*'([^']*)'\)/", $cfg, $u);
preg_match("/define\('APP_DB_PASSWORD',\s*'([^']*)'\)/", $cfg, $p);
preg_match("/define\('APP_DB_NAME',\s*'([^']*)'\)/", $cfg, $n);
$m = new mysqli($h[1], $u[1], $p[1], $n[1]);
$m->set_charset('utf8mb4');
$q = $m->query("SELECT attendance_date, employee_code, employee_name, punch_records
 FROM tblbiometric_report
 WHERE attendance_date IN ('02-Nov-2026','03-Nov-2026')
   AND punch_records IS NOT NULL AND punch_records <> ''");
echo "date | code | name | first | last | punches_at_or_after_03\n";
$after = 0; $before = 0;
while ($r = $q->fetch_assoc()) {
    if (!preg_match_all('/(\d{1,2}):(\d{2})(?::\d{2})?\s*\(\s*(in|out)\s*\)/i', $r['punch_records'], $mm, PREG_SET_ORDER)) continue;
    $times = [];
    foreach ($mm as $hit) $times[] = [(int)$hit[1], (int)$hit[2], strtolower($hit[3])];
    $first = $times[0];
    if ($first[0] < 15) continue; // evening starters only
    $late = [];
    foreach ($times as $t) {
        $mins = $t[0]*60+$t[1];
        if ($mins >= 180 && $mins < 480) $late[] = sprintf('%02d:%02d %s', $t[0], $t[1], $t[2]);
    }
    $last = $times[count($times)-1];
    $lastMins = $last[0]*60+$last[1];
    // only rows that reached midnight (some punch before 08:00)
    $hitMidnight = false;
    foreach ($times as $t) if ($t[0] < 8) $hitMidnight = true;
    if (!$hitMidnight && !$late) continue;
    if ($late) $after++; else $before++;
    echo $r['attendance_date'].' #'.$r['employee_code'].' '.$r['employee_name']
        .' first='.sprintf('%02d:%02d',$first[0],$first[1])
        .' last='.sprintf('%02d:%02d %s',$last[0],$last[1],$last[2])
        .' after3='.( $late ? implode(',', $late) : 'NONE' )."\n";
}
echo "evening_rows_with_after3=$after evening_rows_stop_before3=$before\n";
