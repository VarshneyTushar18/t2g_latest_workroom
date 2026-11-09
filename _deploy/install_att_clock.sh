#!/bin/bash
set -e
STAMP=$(date +%Y%m%d_%H%M%S)
BASE=/var/www/html/t2gworkroom
BK=$BASE/_backups/att_clock_$STAMP
mkdir -p "$BK/modules/timesheets/models" "$BK/modules/timesheets/views/timekeeping"
cp -a "$BASE/modules/timesheets/models/Timesheets_model.php" "$BK/modules/timesheets/models/"
cp -a "$BASE/modules/timesheets/views/timekeeping/my_attendance.php" "$BK/modules/timesheets/views/timekeeping/"
cp -a /tmp/Timesheets_model.php "$BASE/modules/timesheets/models/Timesheets_model.php"
cp -a /tmp/my_attendance.php "$BASE/modules/timesheets/views/timekeeping/my_attendance.php"
chown www-data:www-data \
  "$BASE/modules/timesheets/models/Timesheets_model.php" \
  "$BASE/modules/timesheets/views/timekeeping/my_attendance.php"
php -l "$BASE/modules/timesheets/models/Timesheets_model.php"
php -l "$BASE/modules/timesheets/views/timekeeping/my_attendance.php"
echo "BACKUP $BK"
