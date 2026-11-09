#!/bin/bash
set -e
echo "=== LAST SYNC HITS ==="
logs=$(ls -1t /var/log/apache2/access.log /var/log/nginx/access.log /var/log/httpd/access_log 2>/dev/null || true)
if [ -z "$logs" ]; then
  echo "NO_ACCESS_LOG"
else
  for f in $logs; do
    echo "LOG $f"
    grep "biometric_sync/attendance" "$f" | tail -n 5 || true
  done
fi
echo "=== MODULE ==="
ls -ld /var/www/html/t2gworkroom/modules/bio_punch_merge 2>/dev/null || echo "NO_MODULE_DIR"
echo "=== MODEL MARKER ==="
grep -n "function merge_punch_record_strings" /var/www/html/t2gworkroom/application/models/Biometric_model.php | head -2 || echo "NO_MARKER"
php -r '
define("BASEPATH", "1");
require "/var/www/html/t2gworkroom/application/config/app-config.php";
$m = @new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($m->connect_error) { fwrite(STDERR, "db_fail\n"); exit(1); }
$r = $m->query("SELECT name, value FROM tbloptions WHERE name IN (\"biometric_last_sync_at\",\"biometric_last_sync_meta\")");
while ($row = $r->fetch_assoc()) {
  echo $row["name"], "=", $row["value"], "\n";
}
$r = $m->query("SELECT COUNT(*) c, MAX(created_at) created_max FROM tblbiometric_report WHERE attendance_date=\"26-Oct-2026\"");
$row = $r->fetch_assoc();
echo "today_rows=", $row["c"], " created_max=", $row["created_max"], "\n";
$r = $m->query("SELECT module_name, active, installed_version FROM tblmodules WHERE module_name=\"bio_punch_merge\"");
if ($row = $r->fetch_assoc()) {
  echo "module_db=", $row["module_name"], " active=", $row["active"], " version=", $row["installed_version"], "\n";
} else {
  echo "module_db=none\n";
}
'
