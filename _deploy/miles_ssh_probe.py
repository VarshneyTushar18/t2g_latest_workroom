import os
import sys

import paramiko

host = "103.174.102.70"
user = os.environ.get("MILES_SSH_USER", "root")
password = os.environ.get("MILES_SSH_PASSWORD", "")
if not password:
    print("no password env", file=sys.stderr)
    sys.exit(2)

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    client.connect(host, username=user, password=password, timeout=20, allow_agent=False, look_for_keys=False)
    for cmd in ("whoami", "hostname", "uname -a"):
        stdin, stdout, stderr = client.exec_command(cmd, timeout=15)
        out = stdout.read().decode("utf-8", errors="replace").strip()
        err = stderr.read().decode("utf-8", errors="replace").strip()
        print(f"{cmd}: {out or err}")
finally:
    client.close()
