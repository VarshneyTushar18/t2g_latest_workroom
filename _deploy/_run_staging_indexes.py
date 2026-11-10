#!/usr/bin/env python3
import os, paramiko

local = os.path.join(os.path.dirname(__file__), "_staging_add_task_indexes.sh")
c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect("103.174.102.70", username="root", password=os.environ["STAGING_SSH_PASSWORD"], timeout=25)
sftp = c.open_sftp()
sftp.put(local, "/tmp/add_task_indexes.sh")
sftp.close()
_, o, e = c.exec_command("sed -i 's/\\r$//' /tmp/add_task_indexes.sh && bash /tmp/add_task_indexes.sh", timeout=600)
print(o.read().decode())
print(e.read().decode())
c.close()
