#!/bin/bash
set -e
base=/var/www/html/t2gworkroom
ts=$(date +%Y%m%d_%H%M)
sudo cp "$base/modules/timesheets/models/Timesheets_model.php" "$base/modules/timesheets/models/Timesheets_model.php.bak_$ts"
sudo cp /tmp/Timesheets_model.php "$base/modules/timesheets/models/Timesheets_model.php"
sudo chown www-data:www-data "$base/modules/timesheets/models/Timesheets_model.php"
php -l "$base/modules/timesheets/models/Timesheets_model.php"
grep -n "Biometric attendance replaces a Workroom check-in" "$base/modules/timesheets/models/Timesheets_model.php"
echo "BACKUP_$ts"
sudo systemctl reload apache2
echo "APACHE_RELOADED"
