#!/usr/bin/env python3
"""Staging: verify DB + task/project counts after fresh import."""
import os
import re
import paramiko

HOST = "103.174.102.70"


def main():
    password = os.environ.get("STAGING_SSH_PASSWORD")
    if not password:
        raise SystemExit("Set STAGING_SSH_PASSWORD")

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, username="root", password=password, timeout=25)

    script = r"""
set -e
CFG=/var/www/workroom/application/config/app-config.php
DB=$(grep APP_DB_NAME $CFG | sed "s/.*'\([^']*\)'.*/\1/")
HOST=$(grep APP_DB_HOSTNAME $CFG | sed "s/.*'\([^']*\)'.*/\1/")
USER=$(grep APP_DB_USERNAME $CFG | sed "s/.*'\([^']*\)'.*/\1/")
PASS=$(grep APP_DB_PASSWORD $CFG | sed "s/.*'\([^']*\)'.*/\1/")
echo "APP_DB=$DB HOST=$HOST USER=$USER"

mysql -u"$USER" -p"$PASS" -N -e "
SELECT 'db_version' AS k, value FROM tbloptions WHERE name='migration_version' LIMIT 1;
SELECT 'company' AS k, value FROM tbloptions WHERE name='companyname' LIMIT 1;
SELECT 'tasks_total' AS k, COUNT(*) FROM tbltasks;
SELECT 'tasks_not_finished' AS k, COUNT(*) FROM tbltasks WHERE status != 5;
SELECT 'projects' AS k, COUNT(*) FROM tblprojects;
SELECT 'staff_active' AS k, COUNT(*) FROM tblstaff WHERE active=1;
SELECT 'staffid_1' AS k, CONCAT(firstname,' ',lastname, ' active=',active) FROM tblstaff WHERE staffid=1;
" "$DB" 2>/dev/null || echo MYSQL_FAIL

echo '=== recent mysql databases (workroom related) ==='
mysql -u"$USER" -p"$PASS" -N -e "SHOW DATABASES LIKE '%crm%'; SHOW DATABASES LIKE '%workroom%';" 2>/dev/null | head -20

echo '=== php errors tail ==='
tail -30 /var/www/workroom/application/logs/log-$(date +%Y-%m-%d).php 2>/dev/null || tail -30 /var/log/nginx/error.log 2>/dev/null | tail -15

echo '=== tbltasks sample ==='
mysql -u"$USER" -p"$PASS" -N -e "SELECT id,name,status,dateadded FROM tbltasks ORDER BY id DESC LIMIT 5;" "$DB" 2>/dev/null || true
"""
    _, stdout, stderr = client.exec_command(script, timeout=60)
    out = stdout.read().decode("utf-8", errors="replace")
    # Redact password lines from accidental echo
    out = re.sub(r"(PASS=).+", r"\1***", out)
    print(out)
    err = stderr.read().decode("utf-8", errors="replace")
    if err.strip():
        print("STDERR:", err[:500])
    client.close()


if __name__ == "__main__":
    main()
