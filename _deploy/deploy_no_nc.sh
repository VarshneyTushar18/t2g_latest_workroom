#!/bin/bash
set -e
ts=$(date +%Y%m%d_%H%M)
base=/var/www/html/t2gworkroom
sudo cp "$base/modules/timesheets/models/Timesheets_model.php" "$base/modules/timesheets/models/Timesheets_model.php.bak_$ts"
sudo cp "$base/modules/timesheets/views/timekeeping/my_attendance.php" "$base/modules/timesheets/views/timekeeping/my_attendance.php.bak_$ts"
sudo cp "$base/modules/timesheets/views/hrms_home.php" "$base/modules/timesheets/views/hrms_home.php.bak_$ts"
sudo cp "$base/application/views/admin/dashboard/dashboard_js.php" "$base/application/views/admin/dashboard/dashboard_js.php.bak_$ts"
sudo cp /tmp/Timesheets_model.php "$base/modules/timesheets/models/Timesheets_model.php"
sudo cp /tmp/my_attendance.php "$base/modules/timesheets/views/timekeeping/my_attendance.php"
sudo cp /tmp/hrms_home.php "$base/modules/timesheets/views/hrms_home.php"
sudo cp /tmp/dashboard_js.php "$base/application/views/admin/dashboard/dashboard_js.php"
sudo chown www-data:www-data \
  "$base/modules/timesheets/models/Timesheets_model.php" \
  "$base/modules/timesheets/views/timekeeping/my_attendance.php" \
  "$base/modules/timesheets/views/hrms_home.php" \
  "$base/application/views/admin/dashboard/dashboard_js.php"
php -l "$base/modules/timesheets/models/Timesheets_model.php"
echo "BACKUP_$ts"
grep -n "No attendance recorded" "$base/modules/timesheets/models/Timesheets_model.php" | head -3
