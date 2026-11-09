#!/usr/bin/env python3
import os, paramiko

SCRIPT = """
PASS=$(grep APP_DB_PASSWORD /var/www/workroom/application/config/app-config.php | sed "s/.*'\\([^']*\\)'.*/\\1/")
for D in production_crm workroom_stage production_crm_test_20261028; do
  C=$(mysql -uroot -p"$PASS" -N -e "SELECT COUNT(*) FROM tbltasks;" "$D" 2>/dev/null || echo ERR)
  P=$(mysql -uroot -p"$PASS" -N -e "SELECT COUNT(*) FROM tblprojects;" "$D" 2>/dev/null || echo ERR)
  echo "$D tasks=$C projects=$P"
done
grep APP_DB_NAME /var/www/workroom/application/config/app-config.php
"""

c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect("103.174.102.70", username="root", password=os.environ["STAGING_SSH_PASSWORD"], timeout=25)
_, o, _ = c.exec_command(SCRIPT, timeout=45)
print(o.read().decode())
c.close()
