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
    "config",
    "app_workroom_staging_policy.php",
)
remote = "/var/www/workroom/application/config/app_workroom_staging_policy.php"
stamp = datetime.utcnow().strftime("%Y%m%d_%H%M%S")
backup = f"/var/www/workroom/_backups/staging_email_{stamp}/"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect("103.174.102.70", username="root", password=password, timeout=25, allow_agent=False, look_for_keys=False)
try:
    client.exec_command(f"mkdir -p {backup} && cp -a {remote} {backup}/", timeout=30)
    sftp = client.open_sftp()
    sftp.put(local, remote)
    verify_php = """<?php
define('BASEPATH', '/var/www/workroom/application/');
require '/var/www/workroom/application/config/app-config.php';
require '/var/www/workroom/application/config/app_email_policy.php';
echo 'APP_FORCE_DISABLE_EMAIL=' . ((defined('APP_FORCE_DISABLE_EMAIL') && APP_FORCE_DISABLE_EMAIL) ? 'yes' : 'no') . "\\n";
echo 'APP_DISABLE_EMAIL=' . ((defined('APP_DISABLE_EMAIL') && APP_DISABLE_EMAIL) ? 'yes' : 'no') . "\\n";
"""
    with sftp.file("/tmp/miles_email_chk.php", "w") as f:
        f.write(verify_php)
    sftp.close()
    client.exec_command(f"chown nginx:nginx {remote}", timeout=15)
    stdin, stdout, stderr = client.exec_command(
        "php /tmp/miles_email_chk.php; rm -f /tmp/miles_email_chk.php", timeout=30
    )
    print(stdout.read().decode())
    print(f"backup: {backup}")
finally:
    client.close()
