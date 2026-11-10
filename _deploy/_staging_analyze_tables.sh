#!/bin/bash
PASS=$(grep APP_DB_PASSWORD /var/www/workroom/application/config/app-config.php | sed "s/.*'\([^']*\)'.*/\1/")
mysql -uroot -p"$PASS" production_crm -e "ANALYZE TABLE tbltasks, tbltask_assigned, tblrec_candidate;" 2>/dev/null
echo ANALYZE_DONE
