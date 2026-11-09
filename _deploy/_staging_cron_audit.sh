#!/bin/bash
echo '=== HOST ==='
hostname; date
echo '=== root crontab ==='
crontab -l 2>/dev/null || echo '(no root crontab)'
echo '=== /etc/cron.d ==='
grep -r -i 'workroom\|t2g\|cron/index\|timesheet\|biometric' /etc/cron.d/ 2>/dev/null | head -30
echo '=== /etc/crontab ==='
grep -v '^#' /etc/crontab | grep -v '^$' | head -20
echo '=== other user crontabs ==='
for u in nginx apache workr1633; do
  echo "-- $u --"
  crontab -u "$u" -l 2>/dev/null || echo none
done
echo '=== APP_CRON_KEY set? ==='
grep APP_CRON_KEY /var/www/workroom/application/config/app-config.php 2>/dev/null | sed 's/=.*/=***/'
echo '=== recent cron in syslog (24h) ==='
grep -i cron /var/log/cron 2>/dev/null | tail -15 || journalctl -u crond --since '24 hours ago' 2>/dev/null | tail -15
echo '=== workroom cron log files ==='
ls -la /var/www/workroom/application/logs/ 2>/dev/null | tail -8
echo '=== test local cron URL (5s) ==='
KEY=$(grep APP_CRON_KEY /var/www/workroom/application/config/app-config.php | sed "s/.*'\([^']*\)'.*/\1/")
curl -sS -o /dev/null -w 'cron_http=%{http_code} time=%{time_total}s\n' --max-time 15 "http://127.0.0.1/cron/index/${KEY}" 2>/dev/null || echo curl_fail
