#!/bin/bash
set -e
echo "Before:"
ls -la /var/www/workroom/application/config/app-config.php
# PHP-FPM www pool uses user nginx
chown -R nginx:nginx /var/www/workroom
find /var/www/workroom -type d -exec chmod 755 {} \;
find /var/www/workroom -type f -exec chmod 644 {} \;
chmod 640 /var/www/workroom/application/config/app-config.php 2>/dev/null || true
echo "After:"
ls -la /var/www/workroom/application/config/app-config.php
systemctl is-active php-fpm nginx
curl -sS -o /dev/null -w 'admin=%{http_code}\n' --max-time 30 -L http://127.0.0.1/admin/
curl -sS -o /dev/null -w 'auth=%{http_code}\n' --max-time 30 http://127.0.0.1/admin/authentication
tail -2 /var/log/nginx/error.log | cut -c1-400
