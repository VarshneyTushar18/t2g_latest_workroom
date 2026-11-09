#!/bin/bash
set -e
base=/var/www/html/t2gworkroom
ts=$(date +%Y%m%d_%H%M)
sudo cp "$base/application/models/Biometric_model.php" "$base/application/models/Biometric_model.php.bak_$ts"
sudo cp /tmp/Biometric_model.php "$base/application/models/Biometric_model.php"
sudo chown www-data:www-data "$base/application/models/Biometric_model.php"
php -l "$base/application/models/Biometric_model.php"
grep -n "merge_punch_record_strings" "$base/application/models/Biometric_model.php" | head -3
echo "BACKUP_$ts"
