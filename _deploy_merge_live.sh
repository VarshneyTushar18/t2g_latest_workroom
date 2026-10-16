#!/bin/bash
set -e
APP=/var/www/html/t2gworkroom
TS=$(date +%Y%m%d-%H%M%S)
TMP=/tmp

cp_one() {
  local src="$TMP/$1"
  local dest="$APP/$2"
  if [ ! -f "$src" ]; then
    echo "SKIP missing $src"
    return 0
  fi
  sudo cp "$dest" "${dest}.bak-${TS}" 2>/dev/null || true
  sudo cp "$src" "$dest"
  echo "OK $2"
}

cp_one Cron.php application/controllers/Cron.php
cp_one Biometric.php application/controllers/admin/Biometric.php
cp_one Holiday.php application/controllers/admin/Holiday.php
cp_one Emt.php application/controllers/admin/Emt.php
cp_one Staff.php application/controllers/admin/Staff.php
cp_one custom_helper.php application/helpers/custom_helper.php
cp_one menu_helper.php application/helpers/menu_helper.php
cp_one Cron_model.php application/models/Cron_model.php
cp_one Holiday_model.php application/models/Holiday_model.php
cp_one Emt_model.php application/models/Emt_model.php
cp_one manage_wfh.php application/views/admin/holiday/manage_wfh.php
cp_one header.php application/views/admin/includes/header.php
cp_one helpers_bottom.php application/views/admin/includes/helpers_bottom.php
cp_one scripts.php application/views/admin/includes/scripts.php
cp_one snapshot_login_reminder_popup.php application/views/admin/includes/modals/snapshot_login_reminder_popup.php
cp_one emt_dashboard.php application/views/admin/emt/emt_dashboard.php
cp_one emtinterval.php application/views/admin/emt/emtinterval.php
cp_one monitoring.php application/views/admin/emt/monitoring.php
cp_one snapshot_daily_report.php application/views/admin/emt/snapshot_daily_report.php
cp_one snapshot_staff_list.php application/views/admin/emt/snapshot_staff_list.php
cp_one _snapshot_ui_loader.php application/views/admin/emt/_snapshot_ui_loader.php
cp_one _snapshot_ui_loader_script.php application/views/admin/emt/_snapshot_ui_loader_script.php
cp_one 311_version_311.php application/migrations/311_version_311.php
cp_one config.php application/config/config.php
cp_one migration.php application/config/migration.php
cp_one Timesheets.php modules/timesheets/controllers/Timesheets.php
cp_one Timesheets_model.php modules/timesheets/models/Timesheets_model.php
cp_one timesheets_helper.php modules/timesheets/helpers/timesheets_helper.php
cp_one timesheets.php modules/timesheets/timesheets.php
cp_one my_attendance.php modules/timesheets/views/timekeeping/my_attendance.php
cp_one Interval_model.php api/application/models/api/Interval_model.php
cp_one backfill_snapshot_from_drive.php scripts/backfill_snapshot_from_drive.php
cp_one sync_snapshot_drive_data.php scripts/sync_snapshot_drive_data.php

php -l "$APP/application/models/Cron_model.php"
php -l "$APP/application/controllers/admin/Emt.php"
test -f "$APP/application/views/admin/emt/monitoring.php" && echo "monitoring.php present"
