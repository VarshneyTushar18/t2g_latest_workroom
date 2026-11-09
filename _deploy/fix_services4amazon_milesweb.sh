#!/bin/bash
# MilesWeb / CyberPanel (103.174.102.70) — stop services4amazon.com serving Workroom.
# Run as root on the server: bash fix_services4amazon_milesweb.sh
set -euo pipefail

NGINX=/etc/nginx/nginx.conf
STAMP=$(date +%Y%m%d%H%M)
WORKROOM_ROOT=/var/www/workroom

echo "=== 1) Diagnose ==="
echo "DNS should point services4amazon.com -> this host."
grep -r "services4amazon" /etc/nginx/ /usr/local/lsws/conf/vhosts/ 2>/dev/null | head -30 || true
echo "--- default_server / workroom in nginx.conf ---"
grep -n "default_server\|/var/www/workroom\|server_name" "$NGINX" | head -40

S4A_HOME=""
for d in /home/services4amazon.com /home/www.services4amazon.com /home/services4amazon; do
  if [[ -d "$d/public_html" ]]; then
    S4A_HOME="$d/public_html"
    break
  fi
done
if [[ -z "$S4A_HOME" ]]; then
  S4A_HOME=$(find /home -maxdepth 3 -type d -name public_html 2>/dev/null | grep -i amazon | head -1 || true)
fi
if [[ -z "$S4A_HOME" ]]; then
  echo "ERROR: Could not find services4amazon public_html under /home. Create the site in CyberPanel first."
  exit 1
fi
echo "Using services4amazon docroot: $S4A_HOME"

CONF=/etc/nginx/conf.d/services4amazon.com.conf
if [[ ! -f "$CONF" ]]; then
  cp -a "$NGINX" "${NGINX}.bak_${STAMP}"
  cat >"$CONF" <<EOF
# Restored dedicated vhost — do not serve Workroom on this domain.
server {
    listen 80;
    listen [::]:80;
    server_name services4amazon.com www.services4amazon.com;

    root ${S4A_HOME};
    index index.php index.html index.htm;

    location ~ /\\. {
        deny all;
    }

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \\.php\$ {
        include /etc/nginx/fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_pass unix:/run/php-fpm/www.sock;
    }
}
EOF
  echo "Wrote $CONF"
else
  echo "Found existing $CONF — ensure root is NOT $WORKROOM_ROOT"
  grep -E "root|server_name" "$CONF" || true
fi

# Remove Workroom from catch-all default_server (server_name _)
if grep -q "root ${WORKROOM_ROOT}" "$NGINX" && grep -q "default_server" "$NGINX"; then
  cp -a "$NGINX" "${NGINX}.bak_s4a_${STAMP}"
  python3 <<'PY'
import re
from pathlib import Path

nginx = Path("/etc/nginx/nginx.conf")
text = nginx.read_text()
workroom = "/var/www/workroom"

# Drop default_server from listen lines inside the block that uses workroom root
pattern = re.compile(
    r"(server\s*\{[^}]*?root\s+" + re.escape(workroom) + r"[^}]*?\})",
    re.DOTALL,
)
m = pattern.search(text)
if not m:
    print("No workroom server block found in nginx.conf — manual check needed")
else:
    block = m.group(1)
    new_block = block.replace(" default_server", "")
    new_block = re.sub(
        r"server_name\s+[^;]+;",
        "server_name  103.174.102.70 workroom-stage.example.com;",
        new_block,
        count=1,
    )
    text = text[: m.start(1)] + new_block + text[m.end(1) :]
    nginx.write_text(text)
    print("Patched nginx.conf: Workroom no longer default_server; server_name limited to staging hosts")
PY
fi

echo "=== 2) Test & reload ==="
nginx -t
systemctl reload nginx

echo "=== 3) Smoke test (on server) ==="
curl -sS -o /dev/null -w "Host services4amazon.com -> HTTP %{http_code} Location: %{redirect_url}\n" --max-time 10 -I -H "Host: services4amazon.com" http://127.0.0.1/
curl -sS -o /dev/null -w "Host 103.174.102.70 -> HTTP %{http_code}\n" --max-time 10 -I -H "Host: 103.174.102.70" http://127.0.0.1/admin/ || true

echo "Done. From your PC: curl -I http://services4amazon.com/ should NOT redirect to Workroom login."
