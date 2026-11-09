#!/bin/bash
set -euo pipefail
BASE=/var/www/html/t2gworkroom
BK=$(ls -td "$BASE"/_backups/reg_bulk_* 2>/dev/null | head -1)
if [[ -z "$BK" || ! -f "$BK/Timesheets_model.php" ]]; then
  echo "no backup found" >&2
  exit 1
fi
cp -a "$BK/Timesheets_model.php" "$BASE/modules/timesheets/models/Timesheets_model.php"
php -l "$BASE/modules/timesheets/models/Timesheets_model.php"
# One-line fix: bulk approve uses employee id for timesheet edit
sed -i "s/\$this->edit_timesheets(\$row);/\$this->edit_timesheets(\$row, (int) \$row->creator);/" "$BASE/modules/timesheets/models/Timesheets_model.php"
php -l "$BASE/modules/timesheets/models/Timesheets_model.php"
grep -n "approve_pending_additional_timesheet" -A8 "$BASE/modules/timesheets/models/Timesheets_model.php" | head -12
chown www-data:www-data "$BASE/modules/timesheets/models/Timesheets_model.php"
echo "restored from $BK"
