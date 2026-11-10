#!/usr/bin/env python3
import os, paramiko
root = os.path.dirname(__file__)
c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect("103.174.102.70", username="root", password=os.environ["STAGING_SSH_PASSWORD"], timeout=25)
sftp = c.open_sftp()
sftp.put(os.path.join(root, "_staging_cron_audit.sh"), "/tmp/cron_audit.sh")
sftp.close()
_, o, _ = c.exec_command("sed -i 's/\\r$//' /tmp/cron_audit.sh && bash /tmp/cron_audit.sh", timeout=45)
print(o.read().decode())
c.close()
