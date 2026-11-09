import os
import sys

import paramiko

password = os.environ.get("MILES_SSH_PASSWORD", "")
if not password:
    sys.exit(2)

cmds = [
    "grep -c 'hooks()->add_action' /var/www/workroom/modules/timesheets/timesheets.php",
    "grep -n 'timesheets_module_init_menu_items' /var/www/workroom/modules/timesheets/timesheets.php",
    "grep -rn \"name.*Home\" /var/www/workroom/modules/timesheets/timesheets.php | head -15",
    "grep -rn 'timesheets_hrms_home' /var/www/workroom/modules /var/www/workroom/application 2>/dev/null | head -25",
    "ls -la /var/www/workroom/modules/timesheets/*.php 2>/dev/null | head -20",
]

php = r"""<?php
define('BASEPATH', '/var/www/workroom/application/');
require '/var/www/workroom/application/config/app-config.php';
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$q = $m->query("SELECT module_name, active FROM tblmodules WHERE module_name IN ('timesheets','hr_profile','menu_setup')");
while ($r = $q->fetch_assoc()) echo $r['module_name'].' active='.$r['active']."\n";
$q2 = $m->query("SELECT name, LEFT(value,200) v FROM tbloptions WHERE name LIKE '%menu%' ORDER BY name LIMIT 25");
while ($r = $q2->fetch_assoc()) echo 'opt_'.$r['name'].'='.$r['v']."\n";
"""

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect("103.174.102.70", username="root", password=password, timeout=25, allow_agent=False, look_for_keys=False)
try:
    for cmd in cmds:
        print("===", cmd[:70])
        stdin, stdout, stderr = client.exec_command(cmd, timeout=45)
        print(stdout.read().decode("utf-8", errors="replace").strip() or stderr.read().decode()[:300])
        print()
    sftp = client.open_sftp()
    with sftp.file("/tmp/miles_menu.php", "w") as f:
        f.write(php)
    sftp.close()
    stdin, stdout, stderr = client.exec_command("php /tmp/miles_menu.php; rm -f /tmp/miles_menu.php", timeout=45)
    print("=== db ===")
    print(stdout.read().decode())
finally:
    client.close()
