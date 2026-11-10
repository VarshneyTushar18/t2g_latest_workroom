import os
import sys

import paramiko

password = os.environ.get("MILES_SSH_PASSWORD", "")
if not password:
    sys.exit(2)

cmds = [
    "echo '=== root crontab ==='; crontab -l 2>/dev/null | grep -iE 'workroom|cron|103\\.174|t2g|8088|8089' || echo '(no matching lines)'",
    "echo '=== nginx user crontab ==='; crontab -u nginx -l 2>/dev/null | head -20 || echo '(none)'",
    "grep -rE 'workroom|cron' /etc/cron.d/ /var/spool/cron/ 2>/dev/null | grep -v '^Binary' | head -30",
    "echo '=== workroom app-config (base url, db) ==='; grep -E \"APP_BASE_URL|APP_DB_NAME|staging|WORKROOM|app_workroom\" /var/www/workroom/application/config/app-config.php | head -20",
    "test -f /var/www/workroom/application/config/app_workroom_staging_policy.php && echo 'staging_policy=yes' || echo 'staging_policy=no'",
    "echo '=== recent cron log if any ==='; ls -la /var/www/workroom/application/logs/ 2>/dev/null | tail -5",
    "tail -30 /var/www/workroom/application/logs/log-$(date +%Y-%m-%d).php 2>/dev/null | grep -iE 'cron|email|smtp' | tail -15 || echo '(no log hits today)'",
]

php = r"""<?php
define('BASEPATH', '/var/www/workroom/application/');
require '/var/www/workroom/application/config/app-config.php';
require '/var/www/workroom/application/config/app_email_policy.php';
$_SERVER['HTTP_HOST'] = '103.174.102.70';
$_SERVER['SERVER_ADDR'] = '103.174.102.70';
echo "APP_BASE_URL=" . APP_BASE_URL . "\n";
echo "APP_DB_NAME=" . APP_DB_NAME . "\n";
echo "app_is_live_production_workroom=" . (app_is_live_production_workroom() ? 'yes' : 'no') . "\n";
echo "app_should_disable_outbound_email=" . (app_should_disable_outbound_email() ? 'yes' : 'no') . "\n";
echo "app_cron_loopback_would_block_email=" . (app_cron_loopback_would_block_email() ? 'yes' : 'no') . "\n";
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($m->connect_error) { echo "db_fail\n"; exit(0); }
$names = ['smtp_email','smtp_host','smtp_port','companyname','settings_cron_has_run'];
foreach ($names as $n) {
  $q = $m->query("SELECT value FROM tbloptions WHERE name='".$m->real_escape_string($n)."' LIMIT 1");
  if ($q && $row = $q->fetch_assoc()) echo "opt_$n=" . substr($row['value'], 0, 120) . "\n";
}
$q = $m->query("SELECT name, value FROM tbloptions WHERE name LIKE '%cron%' LIMIT 15");
while ($q && $row = $q->fetch_assoc()) echo 'opt_' . $row['name'] . '=' . substr($row['value'], 0, 80) . "\n";
"""

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect("103.174.102.70", username="root", password=password, timeout=25, allow_agent=False, look_for_keys=False)
try:
    for cmd in cmds:
        stdin, stdout, stderr = client.exec_command(cmd, timeout=45)
        out = stdout.read().decode("utf-8", errors="replace").strip()
        if out:
            print(out)
            print()
    sftp = client.open_sftp()
    with sftp.file("/tmp/miles_cron_email.php", "w") as f:
        f.write(php)
    sftp.close()
    stdin, stdout, stderr = client.exec_command("php /tmp/miles_cron_email.php; rm -f /tmp/miles_cron_email.php", timeout=60)
    print("=== email policy simulation ===")
    print(stdout.read().decode("utf-8", errors="replace"))
finally:
    client.close()
