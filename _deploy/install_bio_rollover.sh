#!/bin/bash
set -e
STAMP=$(date +%Y%m%d_%H%M%S)
BASE=/var/www/html/t2gworkroom
BK=$BASE/_backups/bio_rollover_$STAMP
mkdir -p "$BK/application/models"
cp -a "$BASE/application/models/Biometric_model.php" "$BK/application/models/"
cp -a /tmp/Biometric_model.php "$BASE/application/models/Biometric_model.php"
chown www-data:www-data "$BASE/application/models/Biometric_model.php"
php -l "$BASE/application/models/Biometric_model.php"
echo "BACKUP $BK"
