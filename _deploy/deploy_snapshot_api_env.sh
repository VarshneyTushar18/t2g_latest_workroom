#!/bin/bash
set -e
API_INDEX="/var/www/html/t2gworkroom/api/index.php"
TS="$(date +%Y%m%d_%H%M)"
sudo cp "$API_INDEX" "${API_INDEX}.bak_${TS}"

# Default to production so PHP 8.x deprecations are not printed before JSON (Snapshot login).
sudo sed -i "s/define('ENVIRONMENT', isset(\$_SERVER\['CI_ENV'\]) ? \$_SERVER\['CI_ENV'\] : 'development');/define('ENVIRONMENT', isset(\$_SERVER['CI_ENV']) ? \$_SERVER['CI_ENV'] : 'production');/" "$API_INDEX"

grep -n "define('ENVIRONMENT'" "$API_INDEX" | head -1
php -l "$API_INDEX"
sudo chown www-data:www-data "$API_INDEX"
sudo systemctl reload apache2
echo "OK snapshot api env deploy"
