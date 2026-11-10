#!/bin/bash
set -euo pipefail
BASE=/var/www/html/t2gworkroom
STAMP=$(date -u +%Y%m%d_%H%M%S)
BK="$BASE/_backups/reg_bulk_${STAMP}"
mkdir -p "$BK"
cp -a "$BASE/modules/timesheets/assets/js/requisition_manage_js.php" "$BK/" 2>/dev/null || true
cp -a "$BASE/modules/timesheets/controllers/Timesheets.php" "$BK/" 2>/dev/null || true
cp -a "$BASE/modules/timesheets/models/Timesheets_model.php" "$BK/" 2>/dev/null || true
cp -a /tmp/requisition_manage_js.php "$BASE/modules/timesheets/assets/js/requisition_manage_js.php"
cp -a /tmp/Timesheets_reg_bulk.php "$BASE/modules/timesheets/controllers/Timesheets.php"
cp -a /tmp/Timesheets_model_reg_bulk.php "$BASE/modules/timesheets/models/Timesheets_model.php"
php -l "$BASE/modules/timesheets/controllers/Timesheets.php"
php -l "$BASE/modules/timesheets/models/Timesheets_model.php"
chown www-data:www-data "$BASE/modules/timesheets/assets/js/requisition_manage_js.php" \
  "$BASE/modules/timesheets/controllers/Timesheets.php" \
  "$BASE/modules/timesheets/models/Timesheets_model.php"
echo "backup: $BK"
