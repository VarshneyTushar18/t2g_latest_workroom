#!/bin/bash
set -e
STAMP=$(date +%Y%m%d_%H%M%S)
BASE=/var/www/html/t2gworkroom
BK=$BASE/_backups/sheet_punches_$STAMP
mkdir -p "$BK/modules/timesheets/models"
cp -a "$BASE/modules/timesheets/models/Timesheets_model.php" "$BK/modules/timesheets/models/"
cp -a /tmp/Timesheets_model.php "$BASE/modules/timesheets/models/Timesheets_model.php"
chown www-data:www-data "$BASE/modules/timesheets/models/Timesheets_model.php"
php -l "$BASE/modules/timesheets/models/Timesheets_model.php"
echo "BACKUP $BK"
