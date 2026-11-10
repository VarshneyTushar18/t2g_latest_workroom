import os
import sys

import paramiko

password = os.environ.get("MILES_SSH_PASSWORD", "")
if not password:
    sys.exit(2)

cmds = [
    "nginx -T 2>/dev/null | grep -E 'listen |server_name |root ' | head -80",
    "grep -h APP_DB_NAME /var/www/workroom/application/config/app-config.php /var/www/workroom-stage/application/config/app-config.php 2>/dev/null",
    "grep -h APP_BASE_URL /var/www/workroom/application/config/app-config.php /var/www/workroom-stage/application/config/app-config.php 2>/dev/null",
]

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect("103.174.102.70", username="root", password=password, timeout=25, allow_agent=False, look_for_keys=False)
try:
    for cmd in cmds:
        print("===", cmd[:70], "===")
        stdin, stdout, stderr = client.exec_command(cmd, timeout=60)
        print(stdout.read().decode("utf-8", errors="replace"))
finally:
    client.close()
