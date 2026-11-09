#!/bin/bash
set -euo pipefail
define_basepath_php() {
php <<'PHP'
<?php
define('BASEPATH', '/var/www/html/t2gworkroom/system/');
require '/var/www/html/t2gworkroom/application/config/app-config.php';
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$row = $m->query("SELECT id, ip_address FROM tblsessions WHERE data LIKE '%staff_logged_in%' AND timestamp > " . (time() - 3600) . " ORDER BY timestamp DESC LIMIT 1")->fetch_assoc();
if (!$row) { echo ""; exit; }
file_put_contents('/tmp/hrms_live_sid.txt', "sp_session={$row['id']}\n{$row['ip_address']}");
echo "ok";
PHP
}
define_basepath_php >/dev/null
COOKIE="$(head -1 /tmp/hrms_live_sid.txt 2>/dev/null || true)"
IP="$(tail -1 /tmp/hrms_live_sid.txt 2>/dev/null || true)"
if [ -z "$COOKIE" ]; then echo "No live session"; exit 1; fi
echo "Using $COOKIE"
echo "=== AFTER OPTIMIZE (HTML open TTFB) ==="
printf "%-45s %8s %8s %6s %8s\n" "Path" "ms" "TTFB" "HTTP" "KB"
for path in \
  /admin/timesheets/hrms_home \
  /admin/timesheets/my_attendance \
  /admin/timesheets/attendance_regularization \
  /admin/biometric \
  /admin/timesheets/requisition_manage \
  /admin/staff/leave_balance \
  /admin/holiday/calendar \
  /admin/timesheets/setting?group=manage_leave \
  /admin/
do
  curl -sk -o /dev/null -H "Cookie: ${COOKIE}" -H "X-Forwarded-For: ${IP}" "https://t2gworkroom.com${path}" >/dev/null || true
  out1=$(curl -sk -o /tmp/b.html -w "%{http_code} %{time_total} %{time_starttransfer} %{size_download}" -H "Cookie: ${COOKIE}" -H "X-Forwarded-For: ${IP}" "https://t2gworkroom.com${path}")
  out2=$(curl -sk -o /tmp/b.html -w "%{http_code} %{time_total} %{time_starttransfer} %{size_download}" -H "Cookie: ${COOKIE}" -H "X-Forwarded-For: ${IP}" "https://t2gworkroom.com${path}")
  code=$(echo "$out2" | awk '{print $1}')
  t=$(php -r "\$a=explode(' ','$out1'); \$b=explode(' ','$out2'); echo (int)round((floatval(\$a[1])+floatval(\$b[1]))/2*1000);")
  s=$(php -r "\$a=explode(' ','$out1'); \$b=explode(' ','$out2'); echo (int)round((floatval(\$a[2])+floatval(\$b[2]))/2*1000);")
  z=$(echo "$out2" | awk '{print $4}')
  kb=$(php -r "echo round($z/1024,1);")
  printf "%-45s %7sms %7sms %6s %8s\n" "$path" "$t" "$s" "$code" "$kb"
done
rm -f /tmp/hrms_live_sid.txt /tmp/b.html
