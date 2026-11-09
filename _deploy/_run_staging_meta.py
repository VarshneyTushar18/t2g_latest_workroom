#!/usr/bin/env python3
import os, paramiko

local = os.path.join(os.path.dirname(__file__), "_staging_db_meta.php")
c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect("103.174.102.70", username="root", password=os.environ["STAGING_SSH_PASSWORD"], timeout=25)
sftp = c.open_sftp()
sftp.put(local, "/tmp/staging_db_meta.php")
sftp.close()
_, o, e = c.exec_command("php /tmp/staging_db_meta.php; rm -f /tmp/staging_db_meta.php", timeout=30)
print(o.read().decode())
print(e.read().decode())
c.close()
