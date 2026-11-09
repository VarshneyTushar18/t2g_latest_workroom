#!/usr/bin/env python3
import os
import sys
import paramiko

HOST = "103.174.102.70"
USER = "root"
COMMANDS = r"""
echo '=== nginx 80 default ==='
grep -r "listen.*80" /etc/nginx/conf.d/ /etc/nginx/sites-enabled/ 2>/dev/null | head -20
for f in /etc/nginx/conf.d/*.conf /etc/nginx/sites-enabled/*; do
  [ -f "$f" ] || continue
  echo "--- $f ---"
  cat "$f"
  echo
done 2>/dev/null | head -200

echo '=== nginx 8088 ==='
grep -rl "8088" /etc/nginx/ 2>/dev/null | while read f; do echo "--- $f ---"; cat "$f"; echo; done | head -120

echo '=== curl assets port 80 vs 8088 ==='
for p in "" ":8088"; do
  code=$(curl -sS -o /dev/null -w '%{http_code}' --max-time 8 "http://127.0.0.1${p}/assets/plugins/jquery/jquery.min.js")
  echo "jquery$p -> $code"
  code=$(curl -sS -o /dev/null -w '%{http_code}' --max-time 8 "http://127.0.0.1${p}/admin/dashboard/get_dashboard_widgets")
  echo "widgets$p -> $code"
done

echo '=== grep base_url in options ==='
mysql -u root -p'MySql@Strong123' -N -e "SELECT name,value FROM tbloptions WHERE name IN ('default_timezone','companyname') LIMIT 5;" workroom 2>/dev/null || mysql -u root -p'MySql@Strong123' -N -e "SHOW DATABASES;" 2>/dev/null | head -5
"""


def main():
    password = os.environ.get("STAGING_SSH_PASSWORD")
    if not password:
        sys.exit(1)
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, username=USER, password=password, timeout=25)
    stdin, stdout, stderr = client.exec_command(COMMANDS, timeout=120)
    print(stdout.read().decode("utf-8", errors="replace"))
    err = stderr.read().decode("utf-8", errors="replace")
    if err.strip():
        print("STDERR:", err)
    client.close()


if __name__ == "__main__":
    main()
