#!/bin/bash
tail -20 /var/log/php-fpm/www-error.log 2>/dev/null
echo '---'
grep -n require /var/www/workroom/system/core/Common.php | sed -n '248,265p'
