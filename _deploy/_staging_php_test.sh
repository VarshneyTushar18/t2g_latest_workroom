#!/bin/bash
sed -n '38,95p' /etc/nginx/nginx.conf
echo '=== php cli ==='
php -r 'define("BASEPATH","test/"); require "/var/www/workroom/application/config/config.php"; echo "once ok\n";'
php /var/www/workroom/index.php 2>&1 | head -5
