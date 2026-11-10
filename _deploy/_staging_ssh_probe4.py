#!/usr/bin/env python3
import os, sys, paramiko

COMMANDS = r"""
sed -n '1,120p' /etc/nginx/nginx.conf
echo '=== router ==='
cat /root/workroom-stage-router.php 2>/dev/null | head -40
echo '=== 8081 docroot vs workroom ==='
ls -la /root/t2g_latest_workroom/index.php /var/www/workroom/index.php 2>/dev/null
echo '=== systemd/cron for 8081 ==='
grep -r "8081\|workroom-stage-router" /etc/systemd /root /etc/crontab 2>/dev/null | head -15
"""


def main():
    password = os.environ.get("STAGING_SSH_PASSWORD")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect("103.174.102.70", username="root", password=password, timeout=25)
    stdin, stdout, stderr = client.exec_command(COMMANDS, timeout=60)
    print(stdout.read().decode("utf-8", errors="replace"))
    client.close()

if __name__ == "__main__":
    main()
