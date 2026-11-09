#!/bin/bash
set -e
STAMP=$(date +%Y%m%d_%H%M%S)
BASE=/var/www/html/t2gworkroom
BK=$BASE/_backups/snap_ist_$STAMP
mkdir -p "$BK/application/models" "$BK/application/controllers/admin"
cp -a "$BASE/application/models/Emt_model.php" "$BK/application/models/"
cp -a "$BASE/application/controllers/admin/Emt.php" "$BK/application/controllers/admin/"
cp -a /tmp/Emt_model.php "$BASE/application/models/Emt_model.php"
cp -a /tmp/Emt.php "$BASE/application/controllers/admin/Emt.php"
chown www-data:www-data "$BASE/application/models/Emt_model.php" "$BASE/application/controllers/admin/Emt.php"
php -l "$BASE/application/models/Emt_model.php"
php -l "$BASE/application/controllers/admin/Emt.php"
echo "BACKUP $BK"
