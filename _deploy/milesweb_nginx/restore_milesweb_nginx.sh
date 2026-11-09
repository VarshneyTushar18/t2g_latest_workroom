#!/bin/bash
# Restore MilesWeb nginx layout: default_server = /usr/share/nginx/html (NOT Workroom).
# Workroom + services4amazon as separate files in conf.d/
set -euo pipefail

NGINX=/etc/nginx/nginx.conf
STAMP=$(date +%Y%m%d%H%M)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

if grep -q '/var/www/workroom' "$NGINX"; then
  cp -a "$NGINX" "${NGINX}.bak_before_restore_${STAMP}"
  python3 <<'PY'
import re
from pathlib import Path

p = Path("/etc/nginx/nginx.conf")
text = p.read_text()
# Replace workroom default_server block with stock default (from RHEL/CentOS template)
stock = """    server {
        listen       80 default_server;
        listen       [::]:80 default_server;
        server_name  _;
        root         /usr/share/nginx/html;

        include /etc/nginx/default.d/*.conf;

        location / {
        }

        error_page 404 /404.html;
            location = /40x.html {
        }

        error_page 500 502 503 504 /50x.html;
            location = /50x.html {
        }
    }"""
# Remove any server block that contains workroom root
text2 = re.sub(
    r"    server \{[^}]*root\s+/var/www/workroom[^}]*\}(?:\s*\})?",
    stock,
    text,
    count=1,
    flags=re.DOTALL,
)
if text2 == text:
    # Fallback: strip default_server from workroom listens only
    text2 = text.replace(" default_server", "")
    print("WARN: Could not replace full workroom block; removed default_server flags only")
else:
    print("OK: Restored stock default_server block in nginx.conf")
p.write_text(text2)
PY
else
  echo "nginx.conf has no /var/www/workroom — main file looks OK"
fi

install -m 644 "${SCRIPT_DIR}/conf.d/workroom-staging.conf" /etc/nginx/conf.d/workroom-staging.conf

S4A_ROOT=""
for d in /home/services4amazon.com/public_html /home/www.services4amazon.com/public_html; do
  [[ -d "$d" ]] && S4A_ROOT="$d" && break
done
if [[ -n "$S4A_ROOT" ]]; then
  sed "s|/home/services4amazon.com/public_html|${S4A_ROOT}|" \
    "${SCRIPT_DIR}/conf.d/services4amazon.com.conf" >/etc/nginx/conf.d/services4amazon.com.conf
  echo "Installed services4amazon.com.conf root=$S4A_ROOT"
else
  echo "WARN: No services4amazon public_html found — search:"
  echo "  ls /home | grep -i amazon"
  echo "  grep -ri services4amazon /etc/nginx/conf.d.bak* ~/httpd_config.conf* 2>/dev/null"
fi

nginx -t
systemctl reload nginx

echo "--- smoke ---"
curl -sI -H 'Host: services4amazon.com' http://127.0.0.1/ | head -8
curl -sI -H 'Host: 103.174.102.70' http://127.0.0.1/admin/ | head -8
