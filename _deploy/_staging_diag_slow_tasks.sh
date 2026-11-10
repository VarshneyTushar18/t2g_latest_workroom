#!/bin/bash
echo '=== services ==='
systemctl is-active php-fpm nginx
echo '=== recent errors ==='
grep -E 'recruitment|tasks/table|Fatal|500' /var/log/nginx/error.log 2>/dev/null | tail -15 | cut -c1-450
tail -20 /var/log/php-fpm/www-error.log 2>/dev/null | cut -c1-450
echo '=== timing ==='
for path in /admin/ /admin/tasks/table; do
  curl -sS -o /dev/null -w "$path code=%{http_code} time=%{time_total}s\n" --max-time 120 -X POST "http://127.0.0.1${path}" -d 'draw=1&start=0&length=10' 2>/dev/null || \
  curl -sS -o /dev/null -w "$path code=%{http_code} time=%{time_total}s\n" --max-time 120 -I "http://127.0.0.1${path}"
done
echo '=== recruitment tables ==='
PASS=$(grep APP_DB_PASSWORD /var/www/workroom/application/config/app-config.php | sed "s/.*'\([^']*\)'.*/\1/")
mysql -uroot -p"$PASS" production_crm -N -e "
SHOW TABLES LIKE '%interview%';
SHOW TABLES LIKE '%recruit%';
SELECT COUNT(*) AS tasks FROM tbltasks;
SELECT COUNT(*) AS assigned FROM tbltask_assigned;
" 2>/dev/null
