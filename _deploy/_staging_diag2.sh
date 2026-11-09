#!/bin/bash
date
systemctl is-active php-fpm
ls -la /run/php-fpm/www.sock 2>/dev/null
ls -la /var/www/workroom/application/config/app-config.php
curl -sS -o /tmp/out.html -w 'admin=%{http_code} t=%{time_total}\n' --max-time 60 http://127.0.0.1/admin/authentication
head -5 /tmp/out.html
echo '--- errors since 07:18 ---'
awk '$0 >= "2026/10/29 07:18"' /var/log/nginx/error.log 2>/dev/null | tail -20 | cut -c1-500
PASS=$(grep APP_DB_PASSWORD /var/www/workroom/application/config/app-config.php | sed "s/.*'\([^']*\)'.*/\1/")
mysql -uroot -p"$PASS" production_crm -N -e "SELECT COUNT(*) FROM tblstaff WHERE active=1;" 2>/dev/null
