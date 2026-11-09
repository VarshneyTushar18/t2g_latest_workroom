#!/usr/bin/env python3
import os, paramiko

HOST = "103.174.102.70"
SCRIPT = r"""
CFG=/var/www/workroom/application/config/app-config.php
DB=$(grep APP_DB_NAME $CFG | sed "s/.*'\([^']*\)'.*/\1/")
USER=$(grep APP_DB_USERNAME $CFG | sed "s/.*'\([^']*\)'.*/\1/")
PASS=$(grep APP_DB_PASSWORD $CFG | sed "s/.*'\([^']*\)'.*/\1/")

mysql -u"$USER" -p"$PASS" -N -e "
SELECT name, value FROM tbloptions WHERE name IN ('migration_version','default_timezone','setup_menu_active');
SELECT COUNT(*) FROM tblstafftaskassignees;
SHOW INDEX FROM tbltasks WHERE Key_name='PRIMARY';
" "$DB" 2>/dev/null

echo '=== DB sizes ==='
mysql -u"$USER" -p"$PASS" -N -e "
SELECT table_name, table_rows FROM information_schema.tables
WHERE table_schema='$DB' AND table_name IN ('tbltasks','tblstafftaskassignees','tbltaskstimers')
ORDER BY table_name;" 2>/dev/null

echo '=== nginx fastcgi timeout ==='
grep -r fastcgi_read_timeout /etc/nginx/nginx.conf /etc/nginx/conf.d/ 2>/dev/null | head -5

echo '=== php-fpm request_terminate_timeout ==='
grep -E 'request_terminate|max_execution' /etc/php-fpm.d/www.conf /etc/php.ini 2>/dev/null | head -8

echo '=== curl tasks/table timing (no auth expect redirect) ==='
curl -sS -o /dev/null -w 'tasks_table=%{http_code} time=%{time_total}\n' --max-time 120 -X POST http://127.0.0.1/admin/tasks/table -d 'draw=1&start=0&length=10' || echo curl_fail

echo '=== workroom_stage task count if exists ==='
mysql -u"$USER" -p"$PASS" -N -e "SELECT COUNT(*) FROM tbltasks;" workroom_stage 2>/dev/null || echo no_workroom_stage_tasks
"""


def main():
    c = paramiko.SSHClient()
    c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    c.connect(HOST, username="root", password=os.environ["STAGING_SSH_PASSWORD"], timeout=25)
    _, o, _ = c.exec_command(SCRIPT, timeout=130)
    print(o.read().decode("utf-8", errors="replace"))
    c.close()


if __name__ == "__main__":
    main()
