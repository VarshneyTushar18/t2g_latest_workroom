#!/bin/bash
set -euo pipefail
php /tmp/_tmp_hrms_bench_data.php
echo
COOKIE="$(head -1 /tmp/hrms_live_sid.txt 2>/dev/null || true)"
IP="$(tail -1 /tmp/hrms_live_sid.txt 2>/dev/null || true)"
echo "COOKIE=$COOKIE"
echo "IP=$IP"
if [ -z "$COOKIE" ]; then
  echo "No live session to reuse"
  exit 0
fi

echo
echo "=== AUTH HTTP using reused live session ==="
printf "%-45s %8s %8s %6s %8s %s\n" "Path" "ms" "TTFB" "HTTP" "KB" "Note"
for path in \
  /admin/timesheets/hrms_home \
  /admin/timesheets/my_attendance \
  /admin/timesheets/attendance_regularization \
  /admin/biometric \
  /admin/timesheets/requisition_manage \
  /admin/staff/leave_balance \
  /admin/holiday/calendar \
  /admin/timesheets/table_shiftwork \
  /admin/timesheets/shift_management \
  /admin/timesheets/manage_shift_type \
  /admin/timesheets/reports \
  /admin/timesheets/setting?group=manage_leave \
  /admin/
do
  # warm
  curl -sk -o /tmp/b.html -H "Cookie: ${COOKIE}" -H "X-Forwarded-For: ${IP}" "https://t2gworkroom.com${path}" >/dev/null || true
  out1=$(curl -sk -o /tmp/b.html -w "%{http_code} %{time_total} %{time_starttransfer} %{size_download}" -H "Cookie: ${COOKIE}" -H "X-Forwarded-For: ${IP}" "https://t2gworkroom.com${path}")
  out2=$(curl -sk -o /tmp/b.html -w "%{http_code} %{time_total} %{time_starttransfer} %{size_download}" -H "Cookie: ${COOKIE}" -H "X-Forwarded-For: ${IP}" "https://t2gworkroom.com${path}")
  code=$(echo "$out2" | awk '{print $1}')
  t=$(php -r "echo (int)round((floatval(explode(' ','$out1')[1])+floatval(explode(' ','$out2')[1]))/2*1000);")
  s=$(php -r "echo (int)round((floatval(explode(' ','$out1')[2])+floatval(explode(' ','$out2')[2]))/2*1000);")
  z=$(echo "$out2" | awk '{print $4}')
  kb=$(php -r "echo round($z/1024,1);")
  note="OK"
  if grep -qi 'name="password"' /tmp/b.html; then note="LOGIN"; fi
  if grep -qi 'Access Denied' /tmp/b.html; then note="DENIED"; fi
  if [ "$code" != "200" ]; then note="HTTP$code"; fi
  printf "%-45s %7sms %7sms %6s %8s %s\n" "$path" "$t" "$s" "$code" "$kb" "$note"
done
