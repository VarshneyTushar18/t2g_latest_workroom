#!/bin/bash
# Find lost nginx / OpenLiteSpeed configs mentioning services4amazon (MilesWeb 103.174.102.70)
# Run as root: bash find_lost_nginx_s4a.sh | tee /root/find_nginx_s4a_$(date +%Y%m%d).log

set -u

echo "========== $(date) =========="
echo "=== Current live nginx (amazon / default / workroom) ==="
grep -n 'default_server\|/var/www/workroom\|services4amazon\|server_name' /etc/nginx/nginx.conf 2>/dev/null | head -30
grep -ri 'services4amazon\|s4a\.tech\|services4amazon' /etc/nginx/conf.d/ 2>/dev/null || echo "(none in conf.d now)"

echo
echo "=== nginx backup files on disk ==="
find /etc/nginx /root /var/backups /backup -maxdepth 4 \( \
  -name 'nginx.conf*' -o -name '*.conf.bak*' -o -name '*nginx*.bak*' -o -name '*before-workroom*' -o -name '*pre-workroom*' \
\) 2>/dev/null | sort -u

echo
echo "=== Grep ALL nginx backups for services4amazon / amazon vhost ==="
while IFS= read -r f; do
  [[ -f "$f" ]] || continue
  if grep -qi 'services4amazon\|services4amazon' "$f" 2>/dev/null; then
    echo "--- HIT: $f ---"
    grep -ni 'services4amazon\|server_name.*amazon\|root.*amazon' "$f" | head -20
  fi
done < <(find /etc/nginx /root -type f \( -name '*.conf*' -o -name 'nginx.conf*' \) 2>/dev/null)

echo
echo "=== OpenLiteSpeed / CyberPanel (site may live here, not nginx) ==="
find /usr/local/lsws/conf -type f \( -name '*.conf*' -o -name 'httpd_config*' \) 2>/dev/null | sort -u | head -40
grep -ri 'services4amazon' /usr/local/lsws/conf/ 2>/dev/null || echo "(none in lsws now)"
for f in /root/httpd_config.conf* /usr/local/lsws/conf/httpd_config.conf*; do
  [[ -f "$f" ]] || continue
  if grep -qi services4amazon "$f" 2>/dev/null; then
    echo "--- HIT OLS: $f ---"
    grep -ni services4amazon "$f" | head -15
  fi
done

echo
echo "=== CyberPanel website DB ==="
mysql cyberpanel -N -e "SELECT domainName FROM websiteFunctions_websites WHERE domainName LIKE '%amazon%' OR domainName LIKE '%s4a%';" 2>/dev/null || echo "(mysql cyberpanel failed or no rows)"

echo
echo "=== WordPress docroots (real site files) ==="
find /home /var/www -name wp-config.php 2>/dev/null

echo
echo "=== Done — search HIT lines above for old root / proxy_pass ==="
