#!/usr/bin/env python3
"""One-off staging probe via SSH. Password from STAGING_SSH_PASSWORD env."""
import os
import sys

import paramiko

HOST = "103.174.102.70"
USER = "root"
COMMANDS = """
echo '=== HOST ===' && hostname
echo '=== APP_BASE_URL ===' && grep -E "APP_BASE_URL|define\('APP_" /var/www/workroom/application/config/app-config.php 2>/dev/null | head -5
echo '=== DOCROOT ===' && ls -la /var/www/workroom/index.php 2>/dev/null | head -1
echo '=== LISTEN ===' && ss -tlnp | grep -E ':80|:8088|:8081' || netstat -tlnp 2>/dev/null | grep -E ':80|:8088|:8081'
echo '=== CURL 80 admin (5s) ===' && curl -sS -o /dev/null -w '%{http_code} time=%{time_total}\n' --max-time 5 -I http://127.0.0.1/admin/ || echo curl80_fail
echo '=== CURL 8088 admin ===' && curl -sS -o /dev/null -w '%{http_code} time=%{time_total}\n' --max-time 5 -I http://127.0.0.1:8088/admin/ || echo curl8088_fail
echo '=== NGINX/OLS vhost hints ===' && ls /usr/local/lsws/conf/vhosts/ 2>/dev/null | head -10
grep -r "103.174.102.70\\|/var/www/workroom" /usr/local/lsws/conf/vhosts/ 2>/dev/null | head -15
grep -r "103.174.102.70\\|workroom" /etc/nginx/sites-enabled/ /etc/nginx/conf.d/ 2>/dev/null | head -15
"""


def main():
    password = os.environ.get("STAGING_SSH_PASSWORD")
    if not password:
        print("Set STAGING_SSH_PASSWORD", file=sys.stderr)
        sys.exit(1)
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, username=USER, password=password, timeout=25, banner_timeout=25)
    stdin, stdout, stderr = client.exec_command(COMMANDS, timeout=120)
    out = stdout.read().decode("utf-8", errors="replace")
    err = stderr.read().decode("utf-8", errors="replace")
    print(out)
    if err.strip():
        print("STDERR:", err, file=sys.stderr)
    client.close()


if __name__ == "__main__":
    main()
