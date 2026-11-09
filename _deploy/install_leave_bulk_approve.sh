#!/bin/bash
set -euo pipefail
BASE=/var/www/html/t2gworkroom
STAMP=$(date -u +%Y%m%d_%H%M%S)
BK="$BASE/_backups/leave_bulk_${STAMP}"
mkdir -p "$BK"
for f in \
  modules/timesheets/helpers/timesheets_helper.php \
  modules/timesheets/models/Timesheets_model.php \
  modules/timesheets/controllers/Timesheets.php \
  modules/timesheets/assets/js/requisition_manage_js.php \
  modules/timesheets/views/table_registration_leave.php \
  modules/timesheets/views/timekeeping/manage_requisition_hrm.php
do
  cp -a "$BASE/$f" "$BK/$(basename "$f").bak" 2>/dev/null || true
done
cp /tmp/timesheets_helper.php "$BASE/modules/timesheets/helpers/timesheets_helper.php"
cp /tmp/Timesheets.php "$BASE/modules/timesheets/controllers/Timesheets.php"
cp /tmp/requisition_manage_js.php "$BASE/modules/timesheets/assets/js/requisition_manage_js.php"
cp /tmp/table_registration_leave.php "$BASE/modules/timesheets/views/table_registration_leave.php"
cp /tmp/manage_requisition_hrm.php "$BASE/modules/timesheets/views/timekeeping/manage_requisition_hrm.php"
if [[ -f /tmp/patch_approve_pending_leave_method.py ]]; then
  python3 /tmp/patch_approve_pending_leave_method.py "$BASE/modules/timesheets/models/Timesheets_model.php"
fi
chown -R www-data:www-data "$BASE/modules/timesheets"
php -l "$BASE/modules/timesheets/helpers/timesheets_helper.php"
php -l "$BASE/modules/timesheets/controllers/Timesheets.php"
php -l "$BASE/modules/timesheets/models/Timesheets_model.php"
php -l "$BASE/modules/timesheets/assets/js/requisition_manage_js.php"
php -l "$BASE/modules/timesheets/views/table_registration_leave.php"
php -l "$BASE/modules/timesheets/views/timekeeping/manage_requisition_hrm.php"
grep -q approve_pending_leave_request "$BASE/modules/timesheets/models/Timesheets_model.php"
grep -q bulk_approve_leave "$BASE/modules/timesheets/controllers/Timesheets.php"
grep -q t2g_bulk_approve_leave_btn "$BASE/modules/timesheets/assets/js/requisition_manage_js.php"
echo "leave_bulk_ok $STAMP"
