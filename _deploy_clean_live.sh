#!/bin/bash
# Remove known webshell payload (1099089 bytes) and restore clean index.php. Do not touch app-config or vendor legit files.
set -e
ROOT="/var/www/html/t2gworkroom"
QUAR="$ROOT/_quarantine_2026-10-10"
mkdir -p "$QUAR"

echo "=== Quarantine 1099089-byte webshell copies ==="
count=0
while IFS= read -r f; do
  base=$(basename "$f")
  dest="$QUAR/vendor_$(echo "$f" | tr '/' '_')"
  sudo mv "$f" "$dest" 2>/dev/null || mv "$f" "$dest"
  count=$((count + 1))
done < <(find "$ROOT" -type f -size 1099089c ! -path "$QUAR/*" 2>/dev/null)
echo "Moved $count payload files to quarantine"

echo "=== Restore clean index.php ==="
if [ -f /tmp/index.php.clean ]; then
  sudo cp /tmp/index.php.clean "$ROOT/index.php"
  sudo chown www-data:www-data "$ROOT/index.php"
  php -l "$ROOT/index.php"
  echo "index.php restored"
else
  echo "ERROR: /tmp/index.php.clean missing"
  exit 1
fi

echo "=== Verify site responds ==="
code=$(curl -s -o /dev/null -w '%{http_code}' http://127.0.0.1/)
echo "HTTP $code for /"
code2=$(curl -s -o /dev/null -w '%{http_code}' http://127.0.0.1/cron/index)
echo "HTTP $code2 for /cron/index"
echo "DONE"
