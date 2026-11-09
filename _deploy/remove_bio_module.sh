#!/bin/bash
set -e
base=/var/www/html/t2gworkroom
sudo rm -rf "$base/modules/bio_punch_merge"
php -r '
define("BASEPATH", "1");
require "/var/www/html/t2gworkroom/application/config/app-config.php";
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($m->connect_error) { fwrite(STDERR, "db_fail\n"); exit(1); }
$m->query("DELETE FROM tblmodules WHERE module_name=\"bio_punch_merge\"");
echo "module_rows_deleted=", $m->affected_rows, "\n";
'
if [ -d "$base/modules/bio_punch_merge" ]; then echo "MODULE_STILL_THERE"; else echo "MODULE_REMOVED"; fi
grep -n "function merge_punch_record_strings" "$base/application/models/Biometric_model.php" | head -1
sudo systemctl reload apache2
echo "APACHE_RELOADED"
