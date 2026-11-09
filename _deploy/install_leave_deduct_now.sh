#!/bin/bash
set -e
STAMP=$(date +%Y%m%d_%H%M%S)
BASE=/var/www/html/t2gworkroom
BK=$BASE/_backups/leave_deduct_$STAMP
mkdir -p "$BK/modules/timesheets/models" "$BK/modules/timesheets/views/partials" "$BK/application/models"
cp -a "$BASE/modules/timesheets/models/Timesheets_model.php" "$BK/modules/timesheets/models/"
cp -a "$BASE/modules/timesheets/views/partials/leave_balance_cards.php" "$BK/modules/timesheets/views/partials/"
cp -a "$BASE/application/models/Staff_model.php" "$BK/application/models/"
cp -a /tmp/Timesheets_model.php "$BASE/modules/timesheets/models/Timesheets_model.php"
cp -a /tmp/leave_balance_cards.php "$BASE/modules/timesheets/views/partials/leave_balance_cards.php"
cp -a /tmp/Staff_model.php "$BASE/application/models/Staff_model.php"
chown www-data:www-data \
  "$BASE/modules/timesheets/models/Timesheets_model.php" \
  "$BASE/modules/timesheets/views/partials/leave_balance_cards.php" \
  "$BASE/application/models/Staff_model.php"
php -l "$BASE/modules/timesheets/models/Timesheets_model.php"
php -l "$BASE/application/models/Staff_model.php"
php -l "$BASE/modules/timesheets/views/partials/leave_balance_cards.php"
echo "BACKUP $BK"
