#!/bin/bash
grep -i recruitment /var/log/nginx/error.log | tail -8 | cut -c1-600
grep -i 'view_interview\|recruitment\|Fatal\|Memory' /var/log/php-fpm/www-error.log 2>/dev/null | tail -10 | cut -c1-600
# table counts for recruitment load
PASS=$(grep APP_DB_PASSWORD /var/www/workroom/application/config/app-config.php | sed "s/.*'\([^']*\)'.*/\1/")
mysql -uroot -p"$PASS" production_crm -N -e "
SELECT 'candidates' k, COUNT(*) FROM tblrec_candidate;
SELECT 'interviews' k, COUNT(*) FROM tblrec_interview;
SELECT 'staff' k, COUNT(*) FROM tblstaff WHERE active=1;
" 2>/dev/null
