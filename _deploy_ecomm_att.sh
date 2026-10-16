#!/bin/bash
set -e
ROOT=/var/www/html/t2gworkroom
SRC=/tmp/deploy_ecomm_att_20261015
TAG=bak-ecomm-att-20261015
for f in \
  modules/timesheets/helpers/timesheets_helper.php \
  modules/timesheets/controllers/Timesheets.php \
  modules/timesheets/views/check_employee_attendance.php \
  modules/timesheets/timesheets.php \
  application/helpers/custom_helper.php \
  application/controllers/admin/Biometric.php
do
  if [ -f "$ROOT/$f" ]; then sudo cp -a "$ROOT/$f" "$ROOT/$f.$TAG"; fi
  sudo cp -a "$SRC/$f" "$ROOT/$f"
done
sudo chown www-data:www-data "$ROOT/application/helpers/custom_helper.php" "$ROOT/application/controllers/admin/Biometric.php"
sudo chown -R www-data:www-data "$ROOT/modules/timesheets/helpers" "$ROOT/modules/timesheets/controllers" "$ROOT/modules/timesheets/views" "$ROOT/modules/timesheets/timesheets.php"
grep -n timesheets_can_view_ecommerce_attendance "$ROOT/modules/timesheets/helpers/timesheets_helper.php" | head -1
grep -n "id === 202" "$ROOT/application/helpers/custom_helper.php" | head -1
php -l "$ROOT/modules/timesheets/controllers/Timesheets.php" | tail -1
echo DEPLOY_OK
