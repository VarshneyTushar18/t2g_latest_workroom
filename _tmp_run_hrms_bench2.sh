#!/bin/bash
set -euo pipefail
cd /var/www/html/t2gworkroom

php <<'PHP'
<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
ini_set('display_errors', '1');
ini_set('memory_limit', '1024M');
set_time_limit(300);

define('BASEPATH', '/var/www/html/t2gworkroom/system/');
require '/var/www/html/t2gworkroom/application/config/app-config.php';

$mysqli = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($mysqli->connect_error) { fwrite(STDERR, $mysqli->connect_error."\n"); exit(1); }
$mysqli->set_charset('utf8mb4');

$staff = $mysqli->query("SELECT staffid, email, firstname, lastname FROM tblstaff WHERE active=1 AND admin=1 ORDER BY staffid ASC LIMIT 1")->fetch_assoc();
if (!$staff) {
  $staff = $mysqli->query("SELECT staffid, email, firstname, lastname FROM tblstaff WHERE active=1 ORDER BY staffid ASC LIMIT 1")->fetch_assoc();
}
$staff_id = (int)$staff['staffid'];
echo "STAFF={$staff_id} {$staff['firstname']} {$staff['lastname']}\n";

$year = (int)date('Y');
$month = (int)date('n');
$from = sprintf('%04d-%02d-01', $year, $month);
$to = date('Y-m-t', strtotime($from));
$suf = date('M-Y', strtotime($from));

function ms($t0) { return (int)round((microtime(true) - $t0) * 1000); }

echo "\n=== TABLE SIZES ===\n";
$tables = ['tblcheck_in_out','tblbiometric_report','tbltimesheets_timesheet','tbltimesheets_requisition_leave','tbltimesheets_additional_timesheet','tblstaff','tblday_off','tblsessions'];
foreach ($tables as $t) {
  $t0 = microtime(true);
  $c = $mysqli->query("SELECT COUNT(*) c FROM `{$t}`");
  $count = $c ? (int)$c->fetch_assoc()['c'] : -1;
  $cms = ms($t0);
  $r = $mysqli->query("SELECT ROUND((DATA_LENGTH+INDEX_LENGTH)/1024/1024,1) mb FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{$t}'");
  $mb = $r ? $r->fetch_assoc()['mb'] : '?';
  echo sprintf("%-42s rows=%-10s size=%sMB count_ms=%d\n", $t, $count, $mb, $cms);
}

echo "\n=== PAGE DATA COST (admin #{$staff_id}, {$from}..{$to}) ===\n";

$t0 = microtime(true);
$mysqli->query("SELECT 1");
echo "db_ping_ms=".ms($t0)."\n";

$t0 = microtime(true);
$mysqli->query("SELECT id, subject, status, start_time FROM tbltimesheets_requisition_leave WHERE staff_id={$staff_id} ORDER BY id DESC LIMIT 15");
echo "hrms_home_recent_leave_ms=".ms($t0)."\n";

$t0 = microtime(true);
$regs = $mysqli->query("SELECT id, additional_day, time_in, time_out, timekeeping_value, status, reason, rejection_comment FROM tbltimesheets_additional_timesheet WHERE creator={$staff_id} AND additional_day BETWEEN '{$from}' AND '{$to}' ORDER BY id DESC");
$ts = $mysqli->query("SELECT date_work, value, type FROM tbltimesheets_timesheet WHERE staff_id={$staff_id} AND date_work BETWEEN '{$from}' AND '{$to}'");
$cio = $mysqli->query("SELECT DATE(date) AS d, type_check, TIME(date) AS t, date FROM tblcheck_in_out WHERE staff_id={$staff_id} AND DATE(date) BETWEEN '{$from}' AND '{$to}' ORDER BY date ASC");
$codes = [];
$sr = $mysqli->query("SELECT s.staff_identifi, i.empid FROM tblstaff s LEFT JOIN tblstaff_info i ON i.staffid=s.staffid WHERE s.staffid={$staff_id}")->fetch_assoc();
foreach (['staff_identifi','empid'] as $f) {
  $c = trim((string)($sr[$f] ?? ''));
  if ($c !== '') $codes[] = "'".$mysqli->real_escape_string($c)."'";
}
$bio_n=0; $bio_bytes=0; $swipes=0;
if ($codes) {
  $in = implode(',', $codes);
  $bio = $mysqli->query("SELECT attendance_date, a_in_time, a_out_time, punch_records FROM tblbiometric_report WHERE TRIM(employee_code) IN ({$in}) AND attendance_date LIKE '%-{$suf}'");
  while ($row = $bio->fetch_assoc()) {
    $bio_n++;
    $pr = (string)$row['punch_records'];
    $bio_bytes += strlen($pr);
    if (preg_match_all('/\d{1,2}:\d{2}/', $pr, $m)) $swipes += count($m[0]);
  }
}
echo "attendance_calendar_fetch_ms=".ms($t0)." regs=".$regs->num_rows." ts=".$ts->num_rows." cio=".$cio->num_rows." bio_days={$bio_n} punch_bytes={$bio_bytes} swipe_tokens={$swipes}\n";

$t0 = microtime(true);
$mysqli->query("SELECT r.id, r.type_of_leave, r.number_of_leaving_day, r.status, r.start_time, r.end_time FROM tbltimesheets_requisition_leave r WHERE r.staff_id={$staff_id} AND YEAR(r.start_time)={$year}");
echo "leave_balance_year_fetch_ms=".ms($t0)."\n";

$t0 = microtime(true);
$mysqli->query("SELECT id FROM tbltimesheets_requisition_leave ORDER BY id DESC LIMIT 100");
$all = $mysqli->query("SELECT COUNT(*) c FROM tbltimesheets_requisition_leave")->fetch_assoc()['c'];
echo "leave_apply_list_ms=".ms($t0)." total_leave_rows={$all}\n";

$t0 = microtime(true);
$r = $mysqli->query("SELECT COUNT(*) c FROM tblday_off WHERE YEAR(break_date)={$year}");
$dayoff = $r ? (int)$r->fetch_assoc()['c'] : 0;
$r = $mysqli->query("SELECT COUNT(*) c FROM tblholiday");
$hol = $r ? (int)$r->fetch_assoc()['c'] : 0;
echo "holiday_calendar_ms=".ms($t0)." day_off={$dayoff} holiday_rows={$hol}\n";

$t0 = microtime(true);
$r = $mysqli->query("SELECT COUNT(*) c, COUNT(DISTINCT employee_code) e FROM tblbiometric_report WHERE attendance_date LIKE '%-".date('M-Y')."'")->fetch_assoc();
echo "biometric_month_scan_ms=".ms($t0)." rows={$r['c']} employees={$r['e']}\n";

$t0 = microtime(true);
$mysqli->query("SELECT employee_code, attendance_date, a_in_time, a_out_time FROM tblbiometric_report WHERE attendance_date LIKE '%-".date('M-Y')."' LIMIT 500");
echo "biometric_sample500_ms=".ms($t0)."\n";

$t0 = microtime(true);
$ws = $mysqli->query("SELECT COUNT(*) c FROM tblwork_shift");
$st = $mysqli->query("SELECT COUNT(*) c FROM tblshift_type");
$wsc = $ws ? $ws->fetch_assoc()['c'] : '?';
$stc = $st ? $st->fetch_assoc()['c'] : '?';
echo "shift_tables_ms=".ms($t0)." work_shift={$wsc} shift_type={$stc}\n";

// Create DB session for authenticated HTTP timing
$cookie = defined('APP_SESSION_COOKIE_NAME') ? APP_SESSION_COOKIE_NAME : 'sp_session';
$sid = bin2hex(random_bytes(20));
$now = time();
$userData = 'staff_user_id|s:'.strlen((string)$staff_id).':"'.$staff_id.'";staff_logged_in|b:1;';
$sessionData = '__ci_last_regenerate|i:'.$now.';'.$userData;

$cols = [];
$cr = $mysqli->query('SHOW COLUMNS FROM tblsessions');
while ($row = $cr->fetch_assoc()) $cols[] = $row['Field'];
echo "\nsessions_cols=".implode(',', $cols)."\n";

if (in_array('id', $cols, true) && in_array('data', $cols, true)) {
  $ip = '127.0.0.1';
  $dataEsc = $mysqli->real_escape_string($sessionData);
  $mysqli->query("DELETE FROM tblsessions WHERE id='{$sid}'");
  $ok = $mysqli->query("INSERT INTO tblsessions (id, ip_address, timestamp, data) VALUES ('{$sid}', '{$ip}', {$now}, '{$dataEsc}')");
  if (!$ok) {
    echo "session_insert_error=".$mysqli->error."\n";
    file_put_contents('/tmp/hrms_bench_sid.txt', '');
  } else {
    file_put_contents('/tmp/hrms_bench_sid.txt', $cookie.'='.$sid);
    echo "session_created cookie={$cookie} sid={$sid}\n";
  }
} else {
  echo "unexpected sessions schema\n";
  file_put_contents('/tmp/hrms_bench_sid.txt', '');
}
PHP

COOKIE="$(cat /tmp/hrms_bench_sid.txt 2>/dev/null || true)"
echo ""
echo "=== AUTHENTICATED HTTP PAGE OPEN (avg of 2 after 1 warm) ==="
if [ -z "$COOKIE" ]; then
  echo "No session cookie; skipping authed HTTP"
  exit 0
fi

PAGES=(
  "Home|/admin/timesheets/hrms_home"
  "Attendance|/admin/timesheets/my_attendance"
  "Regularization|/admin/timesheets/attendance_regularization"
  "Biometric|/admin/biometric"
  "LeaveApply|/admin/timesheets/requisition_manage"
  "LeaveBalance|/admin/staff/leave_balance"
  "Holiday|/admin/holiday/calendar"
  "ShiftTable|/admin/timesheets/table_shiftwork"
  "ShiftMgmt|/admin/timesheets/shift_management"
  "ShiftType|/admin/timesheets/manage_shift_type"
  "Reports|/admin/timesheets/reports"
  "Settings|/admin/timesheets/setting?group=manage_leave"
  "Dashboard|/admin/"
)

printf "%-18s %8s %8s %6s %8s %s\n" "Page" "Avg_ms" "TTFB_ms" "HTTP" "SizeKB" "Note"
for entry in "${PAGES[@]}"; do
  label="${entry%%|*}"
  path="${entry#*|}"
  total=0; ttfb=0; code=0; size=0
  curl -sk -o /tmp/hrms_bench_body.html -w "" -H "Cookie: ${COOKIE}" "https://t2gworkroom.com${path}" >/dev/null || true
  for i in 1 2; do
    out=$(curl -sk -o /tmp/hrms_bench_body.html -w "%{http_code} %{time_total} %{time_starttransfer} %{size_download}" -H "Cookie: ${COOKIE}" "https://t2gworkroom.com${path}")
    c=$(echo "$out" | awk '{print $1}')
    t=$(echo "$out" | awk '{print $2}')
    s=$(echo "$out" | awk '{print $3}')
    z=$(echo "$out" | awk '{print $4}')
    code=$c; size=$z
    total=$(php -r "echo $total + ($t*1000);")
    ttfb=$(php -r "echo $ttfb + ($s*1000);")
  done
  avg=$(php -r "echo (int)round($total/2);")
  avgttfb=$(php -r "echo (int)round($ttfb/2);")
  sizekb=$(php -r "echo round($size/1024,1);")
  note=""
  if grep -qi 'name="password"' /tmp/hrms_bench_body.html 2>/dev/null; then note="LOGIN"; fi
  if grep -qi 'Access Denied' /tmp/hrms_bench_body.html 2>/dev/null; then note="DENIED"; fi
  if [ "$code" = "303" ] || [ "$code" = "302" ] || [ "$code" = "307" ]; then note="REDIRECT"; fi
  if [ -z "$note" ] && [ "$code" = "200" ]; then note="OK"; fi
  printf "%-18s %7dms %7dms %6s %8s %s\n" "$label" "$avg" "$avgttfb" "$code" "$sizekb" "$note"
done

php -r 'define("BASEPATH","/var/www/html/t2gworkroom/system/"); require "/var/www/html/t2gworkroom/application/config/app-config.php"; $m=new mysqli(APP_DB_HOSTNAME,APP_DB_USERNAME,APP_DB_PASSWORD,APP_DB_NAME); $c=@file_get_contents("/tmp/hrms_bench_sid.txt"); if(preg_match("/=([a-f0-9]+)/",$c,$mm)){$m->query("DELETE FROM tblsessions WHERE id='\''".$m->real_escape_string($mm[1])."'\''");} echo "session_cleaned\n";'
rm -f /tmp/hrms_bench_sid.txt /tmp/hrms_bench_body.html
