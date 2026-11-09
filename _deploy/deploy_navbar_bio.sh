#!/bin/bash
set -e
base=/var/www/html/t2gworkroom
ts=$(date +%Y%m%d_%H%M)
sudo cp "$base/modules/timesheets/helpers/timesheets_helper.php" "$base/modules/timesheets/helpers/timesheets_helper.php.bak_$ts"
sudo cp "$base/application/views/admin/includes/header.php" "$base/application/views/admin/includes/header.php.bak_$ts"
sudo cp /tmp/timesheets_helper.php "$base/modules/timesheets/helpers/timesheets_helper.php"
sudo cp /tmp/header.php "$base/application/views/admin/includes/header.php"
sudo chown www-data:www-data "$base/modules/timesheets/helpers/timesheets_helper.php" "$base/application/views/admin/includes/header.php"
php -l "$base/modules/timesheets/helpers/timesheets_helper.php"
php -l "$base/application/views/admin/includes/header.php"
grep -n "Biometric punches replace an open Workroom check-in" "$base/modules/timesheets/helpers/timesheets_helper.php"
grep -n "Biometric punches replace Workroom check-in/out" "$base/application/views/admin/includes/header.php"
echo "BACKUP_$ts"
sudo systemctl reload apache2
echo "APACHE_RELOADED"
