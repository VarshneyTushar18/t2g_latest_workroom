#!/usr/bin/env python3
import os, sys, paramiko

COMMANDS = r"""
echo '=== APP_DB_NAME ==='
grep APP_DB_NAME /var/www/workroom/application/config/app-config.php

echo '=== CyberPanel IP vhost / default 80 ==='
grep -r "103.174.102.70\|default_server\|8081" /etc/nginx/ /usr/local/lsws/conf/ 2>/dev/null | head -40

echo '=== curl Host IP port 80 ==='
curl -sS -o /dev/null -w 'admin80=%{http_code} redirect=%{redirect_url}\n' --max-time 10 -H 'Host: 103.174.102.70' http://127.0.0.1/admin/
curl -sS -o /dev/null -w 'asset80=%{http_code}\n' --max-time 10 -H 'Host: 103.174.102.70' http://127.0.0.1/assets/plugins/jquery/jquery.min.js

echo '=== workroom-8088 active conf tail ==='
tail -80 /etc/nginx/workroom-8088.conf

echo '=== ps nginx workroom ==='
ps aux | grep -E 'nginx|9088|8081' | grep -v grep | head -15

echo '=== dashboard ajax sample (logged out) ==='
curl -sS --max-time 8 'http://127.0.0.1:8088/admin/' | grep -oE 'assets/[^"\']+' | head -8
curl -sS --max-time 8 'http://127.0.0.1/admin/' | grep -oE 'assets/[^"\']+' | head -8
"""


def main():
    password = os.environ.get("STAGING_SSH_PASSWORD")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect("103.174.102.70", username="root", password=password, timeout=25)
    stdin, stdout, stderr = client.exec_command(COMMANDS, timeout=120)
    print(stdout.read().decode("utf-8", errors="replace"))
    client.close()

if __name__ == "__main__":
    main()
