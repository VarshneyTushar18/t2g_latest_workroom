import os
import sys

import paramiko

host = "103.174.102.70"
user = os.environ.get("MILES_SSH_USER", "root")
password = os.environ.get("MILES_SSH_PASSWORD", "")
if not password:
    sys.exit(2)

cmds = [
    "ls -la /var/www 2>/dev/null | head -30",
    "ls -la /home 2>/dev/null | head -20",
    "find /var/www /home -maxdepth 4 -type d -iname '*workroom*' 2>/dev/null | head -25",
    "grep -rli 'workroom\\|t2gworkroom' /etc/nginx /etc/httpd 2>/dev/null | head -15",
    "nginx -T 2>/dev/null | grep -E 'server_name|root ' | head -40",
    "httpd -S 2>/dev/null | head -25",
    "ls -la /var/www/html 2>/dev/null | head -20",
]

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(host, username=user, password=password, timeout=25, allow_agent=False, look_for_keys=False)
try:
    for cmd in cmds:
        print("=== " + cmd.split("|")[0].strip()[:70] + " ===")
        stdin, stdout, stderr = client.exec_command(cmd, timeout=60)
        out = stdout.read().decode("utf-8", errors="replace")
        err = stderr.read().decode("utf-8", errors="replace")
        if out.strip():
            print(out.rstrip())
        if err.strip() and "Permission denied" not in err:
            print(err.rstrip(), file=sys.stderr)
        print()
finally:
    client.close()
