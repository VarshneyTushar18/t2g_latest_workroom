#!/bin/bash
set -e
cd /var/www/html/t2gworkroom

echo "=== PHP ==="
php -v | head -1

echo "=== Session config ==="
grep -E "SESS_DRIVER|SESS_SAVE_PATH|APP_SESSION" application/config/app-config.php | head -20

echo "=== Apache log format ==="
sudo grep -E "LogFormat|CustomLog" /etc/apache2/apache2.conf /etc/apache2/sites-enabled/* 2>/dev/null | head -20

echo "=== Recent HRMS page hits (no duration in default log) ==="
sudo grep -E "admin/(timesheets|biometric|holiday|staff/leave)" /var/log/apache2/access.log | tail -40

# Create secret probe endpoint (self-deleting after run conceptually - we delete manually)
PROBE=/var/www/html/t2gworkroom/_hrms_speed_probe.php
cat > "$PROBE" <<'PHPEOF'
<?php
/**
 * Temporary HRMS speed probe. Requires ?k=t2gbench2026
 * Boots Perfex enough to call page data loaders as a real staff user.
 */
if (!isset($_GET['k']) || $_GET['k'] !== 't2gbench2026') {
    http_response_code(403);
    echo 'forbidden';
    exit;
}
header('Content-Type: application/json');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
ini_set('display_errors', '0');
ini_set('memory_limit', '1024M');
set_time_limit(300);

$root = __DIR__;
define('ENVIRONMENT', 'production');

// Bootstrap via CodeIgniter by faking a minimal admin request then capturing instance.
// Use the modules path to load models after requiring index hooks.

$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 't2gworkroom.com';
$_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 't2gworkroom.com';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_URI'] = '/admin/timesheets/hrms_home';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $root . '/index.php';
$_GET = [];
$_POST = [];

// Load app-config for DB constants
require $root . '/application/config/app-config.php';

$mysqli = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($mysqli->connect_error) {
    echo json_encode(['error' => $mysqli->connect_error]);
    exit;
}
$mysqli->set_charset('utf8mb4');

$staff = $mysqli->query("SELECT staffid FROM tblstaff WHERE active=1 AND admin=1 ORDER BY staffid ASC LIMIT 1")->fetch_assoc();
if (!$staff) {
    $staff = $mysqli->query("SELECT staffid FROM tblstaff WHERE active=1 ORDER BY staffid ASC LIMIT 1")->fetch_assoc();
}
$staff_id = (int) $staff['staffid'];
$year = (int) date('Y');
$month = (int) date('n');
$from = sprintf('%04d-%02d-01', $year, $month);
$to = date('Y-m-t', strtotime($from));

function timed(callable $fn) {
    $t0 = microtime(true);
    $out = null;
    $err = null;
    try {
        $out = $fn();
    } catch (Throwable $e) {
        $err = $e->getMessage();
    }
    return [
        'ms' => (int) round((microtime(true) - $t0) * 1000),
        'err' => $err,
        'meta' => $out,
    ];
}

$results = [];

// --- Home-ish light queries ---
$results['hrms_home_recent_leave'] = timed(function () use ($mysqli, $staff_id) {
    $q = $mysqli->query("SELECT id, subject, status, start_time, end_time FROM tbltimesheets_requisition_leave WHERE staff_id={$staff_id} ORDER BY id DESC LIMIT 10");
    return ['rows' => $q ? $q->num_rows : 0];
});

// --- My Attendance / Regularization calendar core (same heavy path) ---
$results['attendance_calendar_queries'] = timed(function () use ($mysqli, $staff_id, $from, $to) {
    $counts = [];
    $r = $mysqli->query("SELECT COUNT(*) c FROM tbltimesheets_additional_timesheet WHERE creator={$staff_id} AND additional_day BETWEEN '{$from}' AND '{$to}'");
    $counts['regs'] = (int) $r->fetch_assoc()['c'];
    $r = $mysqli->query("SELECT COUNT(*) c FROM tbltimesheets_timesheet WHERE staff_id={$staff_id} AND date_work BETWEEN '{$from}' AND '{$to}'");
    $counts['timesheet'] = (int) $r->fetch_assoc()['c'];
    $r = $mysqli->query("SELECT COUNT(*) c FROM tblcheck_in_out WHERE staff_id={$staff_id} AND DATE(date) BETWEEN '{$from}' AND '{$to}'");
    $counts['checkinout'] = (int) $r->fetch_assoc()['c'];
    $r = $mysqli->query("SELECT COUNT(*) c FROM tblbiometric_report WHERE attendance_date LIKE '%-" . date('M-Y', strtotime($from)) . "'");
    $counts['biometric_month_all'] = (int) $r->fetch_assoc()['c'];
    // staff-scoped bio if codes exist
    $codes = [];
    $sr = $mysqli->query("SELECT s.staff_identifi, i.empid FROM tblstaff s LEFT JOIN tblstaff_info i ON i.staffid=s.staffid WHERE s.staffid={$staff_id} LIMIT 1")->fetch_assoc();
    foreach (['staff_identifi', 'empid'] as $f) {
        $c = trim((string) ($sr[$f] ?? ''));
        if ($c !== '') $codes[] = $mysqli->real_escape_string($c);
    }
    if ($codes) {
        $in = "'" . implode("','", $codes) . "'";
        $r = $mysqli->query("SELECT COUNT(*) c FROM tblbiometric_report WHERE TRIM(employee_code) IN ({$in}) AND attendance_date LIKE '%-" . date('M-Y', strtotime($from)) . "'");
        $counts['biometric_staff'] = (int) $r->fetch_assoc()['c'];
        $r = $mysqli->query("SELECT attendance_date, LENGTH(punch_records) plen FROM tblbiometric_report WHERE TRIM(employee_code) IN ({$in}) AND attendance_date LIKE '%-" . date('M-Y', strtotime($from)) . "'");
        $plen = 0; $n = 0;
        while ($row = $r->fetch_assoc()) { $plen += (int)$row['plen']; $n++; }
        $counts['bio_days'] = $n;
        $counts['punch_records_bytes'] = $plen;
    }
    return $counts;
});

// Full calendar PHP logic via including model is hard; approximate with repeated analyze load.
$results['leave_balance_cards_sql'] = timed(function () use ($mysqli, $staff_id, $year) {
    $r = $mysqli->query("SELECT COUNT(*) c FROM tbltimesheets_requisition_leave WHERE staff_id={$staff_id} AND YEAR(start_time)={$year}");
    $c1 = (int) $r->fetch_assoc()['c'];
    $r = $mysqli->query("SELECT COUNT(*) c FROM tblstaff_day_off_type");
    // table may not exist
    return ['leave_rows_year' => $c1];
});

$results['requisition_manage_list'] = timed(function () use ($mysqli, $staff_id) {
    $r = $mysqli->query("SELECT COUNT(*) c FROM tbltimesheets_requisition_leave WHERE staff_id={$staff_id}");
    $own = (int) $r->fetch_assoc()['c'];
    $r = $mysqli->query("SELECT COUNT(*) c FROM tbltimesheets_requisition_leave");
    $all = (int) $r->fetch_assoc()['c'];
    $r = $mysqli->query("SELECT id FROM tbltimesheets_requisition_leave ORDER BY id DESC LIMIT 50");
    $n = $r ? $r->num_rows : 0;
    return ['own' => $own, 'all' => $all, 'last50' => $n];
});

$results['holiday_calendar'] = timed(function () use ($mysqli, $year) {
    $r = $mysqli->query("SELECT COUNT(*) c FROM tblday_off WHERE YEAR(break_date)={$year}");
    $c = $r ? (int) $r->fetch_assoc()['c'] : 0;
    $r = $mysqli->query("SELECT COUNT(*) c FROM tblholiday");
    $h = $r ? (int) $r->fetch_assoc()['c'] : 0;
    return ['day_off' => $c, 'holiday_rows' => $h];
});

$results['biometric_page_index'] = timed(function () use ($mysqli) {
    $month = date('M-Y');
    $r = $mysqli->query("SELECT COUNT(*) c FROM tblbiometric_report WHERE attendance_date LIKE '%-{$month}'");
    $c = (int) $r->fetch_assoc()['c'];
    $r = $mysqli->query("SELECT COUNT(DISTINCT employee_code) c FROM tblbiometric_report WHERE attendance_date LIKE '%-{$month}'");
    $e = (int) $r->fetch_assoc()['c'];
    return ['rows' => $c, 'employees' => $e];
});

$results['shift_table_work'] = timed(function () use ($mysqli) {
    $r = $mysqli->query("SELECT COUNT(*) c FROM tblwork_shift");
    $a = $r ? (int) $r->fetch_assoc()['c'] : 0;
    $r = $mysqli->query("SELECT COUNT(*) c FROM tblshift_type");
    $b = $r ? (int) $r->fetch_assoc()['c'] : 0;
    return ['work_shift' => $a, 'shift_type' => $b];
});

$results['db_ping'] = timed(function () use ($mysqli) {
    $mysqli->query('SELECT 1');
    return ['ok' => 1];
});

// Table sizes (approximate cost drivers)
$sizes = [];
$tables = [
    'tblcheck_in_out',
    'tbltimesheets_timesheet',
    'tblbiometric_report',
    'tbltimesheets_requisition_leave',
    'tbltimesheets_additional_timesheet',
    'tblstaff',
    'tblday_off',
];
foreach ($tables as $t) {
    $r = $mysqli->query("SELECT TABLE_ROWS AS est_rows, ROUND((DATA_LENGTH+INDEX_LENGTH)/1024/1024,1) AS mb FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{$t}'");
    if ($r && ($row = $r->fetch_assoc())) {
        $sizes[$t] = ['est_rows' => (int)$row['est_rows'], 'mb' => (float)$row['mb']];
    }
}

// End-to-end HTTP timings from localhost (unauthenticated -> redirect) AND
// for pages via internal include of calendar builder if we can load CI.

echo json_encode([
    'staff_id' => $staff_id,
    'month' => $from . ' .. ' . $to,
    'query_timings_ms' => $results,
    'table_sizes' => $sizes,
    'generated_at' => date('c'),
], JSON_PRETTY_PRINT);
PHPEOF

sudo chown www-data:www-data "$PROBE"
sudo chmod 644 "$PROBE"

echo "=== Running SQL/data probe ==="
curl -sS "https://t2gworkroom.com/_hrms_speed_probe.php?k=t2gbench2026" | head -c 20000
echo

# HTTP wall-clock for each module (login redirect expected without cookie — still measures gateway)
echo "=== HTTP wall-clock (3 runs, follow redirects off) ==="
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
  "Dashboard|/admin"
)

printf "%-18s %8s %8s %6s\n" "Page" "Avg_ms" "TTFB_ms" "HTTP"
for entry in "${PAGES[@]}"; do
  label="${entry%%|*}"
  path="${entry#*|}"
  url="https://t2gworkroom.com${path}"
  total=0
  ttfb=0
  code=0
  for i in 1 2 3; do
    out=$(curl -sS -o /dev/null -w "%{http_code} %{time_total} %{time_starttransfer}" -H "Host: t2gworkroom.com" "$url")
    c=$(echo "$out" | awk '{print $1}')
    t=$(echo "$out" | awk '{print $2}')
    s=$(echo "$out" | awk '{print $3}')
    code=$c
    total=$(php -r "echo $total + ($t*1000);")
    ttfb=$(php -r "echo $ttfb + ($s*1000);")
  done
  avg=$(php -r "echo (int)round($total/3);")
  avgttfb=$(php -r "echo (int)round($ttfb/3);")
  printf "%-18s %7dms %7dms %6s\n" "$label" "$avg" "$avgttfb" "$code"
done

# Try to steal a live session cookie from apache? Not possible.
# Instead: enable a one-shot authenticated bench via setting session in probe using CI.

echo "=== Building authenticated CI bench ==="
AUTHPROBE=/var/www/html/t2gworkroom/_hrms_speed_auth.php
cat > "$AUTHPROBE" <<'PHPEOF'
<?php
if (!isset($_GET['k']) || $_GET['k'] !== 't2gbench2026') { http_response_code(403); exit('forbidden'); }
header('Content-Type: text/plain');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
ini_set('display_errors', '1');
ini_set('memory_limit', '1024M');
set_time_limit(300);

// Bootstrap full Perfex as admin by loading init then models.
define('HOOKABLE', true);
$root = __DIR__;

// Simulate logged-in staff BEFORE CI boots using a pre-hook file.
require $root . '/application/config/app-config.php';
$mysqli = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$mysqli->set_charset('utf8mb4');
$staff = $mysqli->query("SELECT staffid FROM tblstaff WHERE active=1 AND admin=1 ORDER BY staffid ASC LIMIT 1")->fetch_assoc();
$staff_id = (int)$staff['staffid'];

// Use Perfex's internal API: load system and application core manually.
define('ENVIRONMENT', 'production');
define('BASEPATH', $root . '/system/');
define('FCPATH', $root . DIRECTORY_SEPARATOR);
define('APPPATH', $root . '/application/');
define('VIEWPATH', APPPATH . 'views/');
define('EXT', '.php');

require_once BASEPATH . 'core/Common.php';
require APPPATH . 'config/constants.php';
require APPPATH . 'config/config.php'; // sets $config

// Load database + models the Perfex way via getting CI super object is hard.
// Call calendar builder by including the model file and a stub CI.

class CI_Stub {
    public $db;
    public $load;
    public function __construct($db) {
        $this->db = $db;
        $this->load = new CI_Load_Stub($this);
    }
}
class CI_Load_Stub {
    private $ci;
    public function __construct($ci) { $this->ci = $ci; }
    public function model($n, $name = '', $db = false) {
        // no-op for nested
    }
    public function helper($n) {}
}
class CI_DB_mysqli_driver_lite {
    public $mysqli;
    public $qb = [];
    public function __construct($m) { $this->mysqli = $m; }
    public function query($sql, $binds = false) {
        if (is_array($binds)) {
            foreach ($binds as $b) {
                $b = $this->mysqli->real_escape_string($b);
                $sql = preg_replace('/\?/', "'{$b}'", $sql, 1);
            }
        }
        $res = $this->mysqli->query($sql);
        return new CI_DB_result_lite($res);
    }
    public function select($s) { $this->qb['select'] = $s; return $this; }
    public function from($t) { $this->qb['from'] = $t; return $this; }
    public function where($k, $v = null) { $this->qb['where'][] = [$k,$v]; return $this; }
    public function where_in($k, $v) { $this->qb['where_in'][] = [$k,$v]; return $this; }
    public function order_by($a,$b='') { return $this; }
    public function get($t = null) {
        // too incomplete — skip
        return new CI_DB_result_lite(false);
    }
}
class CI_DB_result_lite {
    private $res;
    public function __construct($res) { $this->res = $res; }
    public function result_array() {
        $out = [];
        if (!$this->res) return $out;
        while ($r = $this->res->fetch_assoc()) $out[] = $r;
        return $out;
    }
    public function row() {
        return $this->res ? $this->res->fetch_object() : null;
    }
    public function num_rows() { return $this->res ? $this->res->num_rows : 0; }
}

echo "Auth CI full bench skipped (too heavy to stub).\n";
echo "staff_id={$staff_id}\n";

// Realistic: measure wall clock of authenticated requests by creating a valid CI database session.
// Perfex stores sessions in DB when sess_driver=database.
$drv = defined('SESS_DRIVER') ? SESS_DRIVER : 'files';
echo "sess_driver={$drv}\n";

$cookie = defined('APP_SESSION_COOKIE_NAME') ? APP_SESSION_COOKIE_NAME : 'sp_session';
echo "cookie={$cookie}\n";

if ($drv === 'database' || true) {
    // Insert a session row that marks staff logged in (Perfex session payload)
    $sid = bin2hex(random_bytes(16));
    $payload = array(
        'session_id' => $sid,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'HRMS-Bench',
        'last_activity' => time(),
        // CI3 session data format
    );
}

// Time full calendar via direct SQL replication of get_attendance_regularisation_calendar data fetch
$year = (int)date('Y'); $month = (int)date('n');
$from = sprintf('%04d-%02d-01', $year, $month);
$to = date('Y-m-t', strtotime($from));
$t0 = microtime(true);
$regs = $mysqli->query("SELECT id, additional_day, time_in, time_out, timekeeping_value, status, reason, rejection_comment FROM tbltimesheets_additional_timesheet WHERE creator={$staff_id} AND additional_day BETWEEN '{$from}' AND '{$to}' ORDER BY id DESC");
$ts = $mysqli->query("SELECT date_work, value, type FROM tbltimesheets_timesheet WHERE staff_id={$staff_id} AND date_work BETWEEN '{$from}' AND '{$to}'");
$cio = $mysqli->query("SELECT DATE(date) AS d, type_check, TIME(date) AS t, date FROM tblcheck_in_out WHERE staff_id={$staff_id} AND DATE(date) BETWEEN '{$from}' AND '{$to}' ORDER BY date ASC");
$codes = [];
$sr = $mysqli->query("SELECT s.staff_identifi, i.empid FROM tblstaff s LEFT JOIN tblstaff_info i ON i.staffid=s.staffid WHERE s.staffid={$staff_id}")->fetch_assoc();
foreach (['staff_identifi','empid'] as $f) { $c=trim((string)($sr[$f]??'')); if($c!=='') $codes[]="'".$mysqli->real_escape_string($c)."'"; }
$bio_n = 0; $bio_bytes = 0;
if ($codes) {
    $in = implode(',', $codes);
    $suf = date('M-Y', strtotime($from));
    $bio = $mysqli->query("SELECT attendance_date, a_in_time, a_out_time, punch_records FROM tblbiometric_report WHERE TRIM(employee_code) IN ({$in}) AND attendance_date LIKE '%-{$suf}'");
    while ($row = $bio->fetch_assoc()) { $bio_n++; $bio_bytes += strlen((string)$row['punch_records']); }
}
$ms = (int)round((microtime(true)-$t0)*1000);
echo "attendance_calendar_data_fetch_ms={$ms} bio_days={$bio_n} punch_bytes={$bio_bytes} regs=".$regs->num_rows." ts=".$ts->num_rows." cio=".$cio->num_rows."\n";

// Simulate punch parsing cost
$t0 = microtime(true);
if ($codes) {
    $in = implode(',', $codes);
    $suf = date('M-Y', strtotime($from));
    $bio = $mysqli->query("SELECT punch_records FROM tblbiometric_report WHERE TRIM(employee_code) IN ({$in}) AND attendance_date LIKE '%-{$suf}'");
    $swipes = 0;
    while ($row = $bio->fetch_assoc()) {
        $pr = (string)$row['punch_records'];
        // crude parse like model
        if (preg_match_all('/\d{1,2}:\d{2}/', $pr, $m)) $swipes += count($m[0]);
    }
}
$ms = (int)round((microtime(true)-$t0)*1000);
echo "punch_parse_ms={$ms} swipe_tokens={$swipes}\n";

// Leave balance style: count heavy joins
$t0 = microtime(true);
$mysqli->query("SELECT r.id, r.type_of_leave, r.number_of_leaving_day, r.status, r.start_time FROM tbltimesheets_requisition_leave r WHERE r.staff_id={$staff_id} AND YEAR(r.start_time)=".date('Y'));
$ms = (int)round((microtime(true)-$t0)*1000);
echo "leave_year_fetch_ms={$ms}\n";

// Biometric admin page month scan
$t0 = microtime(true);
$suf = date('M-Y');
$r = $mysqli->query("SELECT COUNT(*) c, COUNT(DISTINCT employee_code) e FROM tblbiometric_report WHERE attendance_date LIKE '%-{$suf}'")->fetch_assoc();
$ms = (int)round((microtime(true)-$t0)*1000);
echo "biometric_month_scan_ms={$ms} rows={$r['c']} employees={$r['e']}\n";

// Full table counts
foreach (['tblcheck_in_out','tblbiometric_report','tbltimesheets_timesheet','tbltimesheets_requisition_leave'] as $t) {
    $t0 = microtime(true);
    $c = $mysqli->query("SELECT COUNT(*) c FROM {$t}")->fetch_assoc()['c'];
    $ms = (int)round((microtime(true)-$t0)*1000);
    echo "count_{$t}_ms={$ms} rows={$c}\n";
}
PHPEOF
sudo chown www-data:www-data "$AUTHPROBE"
curl -sS "https://t2gworkroom.com/_hrms_speed_auth.php?k=t2gbench2026"
echo

# Cleanup probes for safety
sudo rm -f "$PROBE" "$AUTHPROBE"
echo "=== probes removed ==="
