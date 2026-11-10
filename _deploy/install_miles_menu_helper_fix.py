import os
import sys
from datetime import datetime

import paramiko

password = os.environ.get("MILES_SSH_PASSWORD", "")
if not password:
    sys.exit(2)

local = os.path.join(
    os.path.dirname(os.path.dirname(os.path.abspath(__file__))),
    "application",
    "helpers",
    "menu_helper.php",
)
remote = "/var/www/workroom/application/helpers/menu_helper.php"
stamp = datetime.utcnow().strftime("%Y%m%d_%H%M%S")
backup = f"/var/www/workroom/_backups/menu_helper_{stamp}/"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect("103.174.102.70", username="root", password=password, timeout=25, allow_agent=False, look_for_keys=False)
try:
    client.exec_command(f"mkdir -p {backup} && cp -a {remote} {backup}/", timeout=30)
    sftp = client.open_sftp()
    sftp.put(local, remote)
    sftp.close()
    stdin, stdout, stderr = client.exec_command(f"php -l {remote}", timeout=30)
    print(stdout.read().decode())
    client.exec_command(f"chown nginx:nginx {remote}", timeout=15)
    print(f"backup: {backup}")
finally:
    client.close()
