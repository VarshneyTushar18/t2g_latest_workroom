#!/usr/bin/env python3
import os, paramiko
root = os.path.dirname(__file__)
c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect("103.174.102.70", username="root", password=os.environ["STAGING_SSH_PASSWORD"], timeout=25)
sftp = c.open_sftp()
sftp.put(os.path.join(root, "_staging_diag_slow_tasks.sh"), "/tmp/diag.sh")
sftp.close()
_, o, _ = c.exec_command("sed -i 's/\\r$//' /tmp/diag.sh && bash /tmp/diag.sh", timeout=150)
print(o.read().decode("utf-8", errors="replace"))
c.close()
