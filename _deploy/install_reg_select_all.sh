#!/bin/bash
set -euo pipefail
BASE=/var/www/html/t2gworkroom
STAMP=$(date -u +%Y%m%d_%H%M%S)
BK="$BASE/_backups/reg_select_${STAMP}"
mkdir -p "$BK"
cp -a "$BASE/modules/timesheets/assets/js/requisition_manage_js.php" "$BK/"
cp -a "$BASE/modules/timesheets/views/timekeeping/additional_timesheets.php" "$BK/"
cp -a /tmp/requisition_manage_js.php "$BASE/modules/timesheets/assets/js/requisition_manage_js.php"
cp -a /tmp/additional_timesheets.php "$BASE/modules/timesheets/views/timekeeping/additional_timesheets.php"
php -l "$BASE/modules/timesheets/assets/js/requisition_manage_js.php"
chown www-data:www-data "$BASE/modules/timesheets/assets/js/requisition_manage_js.php" \
  "$BASE/modules/timesheets/views/timekeeping/additional_timesheets.php"
echo "backup: $BK"
