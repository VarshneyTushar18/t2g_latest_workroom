#!/usr/bin/env python3
import os, paramiko

HOST = "103.174.102.70"
COMMANDS = """
echo '=== services ==='
systemctl is-active nginx php-fpm 2>/dev/null || true
echo '=== nginx -t ==='
nginx -t 2>&1
echo '=== tail nginx error ==='
tail -25 /var/log/nginx/error.log 2>/dev/null
echo '=== tail php-fpm ==='
tail -15 /var/log/php-fpm/error.log 2>/dev/null || tail -15 /var/log/php8*/php-fpm.log 2>/dev/null
echo '=== curl local admin ==='
curl -sS -o /dev/null -w 'admin=%{http_code}\n' --max-time 15 -I http://127.0.0.1/admin/ || echo curl_fail
echo '=== default server snippet ==='
sed -n '/listen.*80 default_server/,/error_page 500/p' /etc/nginx/nginx.conf | head -55
"""


def main():
    c = paramiko.SSHClient()
    c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    c.connect(HOST, username="root", password=os.environ["STAGING_SSH_PASSWORD"], timeout=25)
    _, o, e = c.exec_command(COMMANDS, timeout=45)
    print(o.read().decode("utf-8", errors="replace"))
    err = e.read().decode("utf-8", errors="replace")
    if err.strip():
        print("STDERR:", err[:800])
    c.close()


if __name__ == "__main__":
    main()
