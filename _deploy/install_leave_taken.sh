#!/bin/bash
set -e
STAMP=$(date +%Y%m%d_%H%M%S)
BASE=/var/www/html/t2gworkroom
BK=$BASE/_backups/leave_taken_$STAMP
mkdir -p "$BK/application/controllers/admin" "$BK/modules/timesheets/views/partials"
cp -a "$BASE/application/controllers/admin/Staff.php" "$BK/application/controllers/admin/"
cp -a "$BASE/modules/timesheets/views/partials/leave_balance_cards.php" "$BK/modules/timesheets/views/partials/"
cp -a /tmp/Staff.php "$BASE/application/controllers/admin/Staff.php"
cp -a /tmp/leave_balance_cards.php "$BASE/modules/timesheets/views/partials/leave_balance_cards.php"
chown www-data:www-data "$BASE/application/controllers/admin/Staff.php" "$BASE/modules/timesheets/views/partials/leave_balance_cards.php"
php -l "$BASE/application/controllers/admin/Staff.php"
php -l "$BASE/modules/timesheets/views/partials/leave_balance_cards.php"
echo "BACKUP $BK"
