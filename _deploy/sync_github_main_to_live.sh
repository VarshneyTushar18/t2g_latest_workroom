#!/bin/bash
set -euo pipefail
ROOT=/var/www/html/t2gworkroom
ARCHIVE=/tmp/workroom_github_main.tgz
BACKUP=/tmp/workroom_config_backup_$(date +%Y%m%d_%H%M%S)
mkdir -p "$BACKUP"
for f in application/config/app-config.php application/config/database.php application/config/email.php; do
  if [ -f "$ROOT/$f" ]; then
    cp -a "$ROOT/$f" "$BACKUP/"
  fi
done
echo "Config backup: $BACKUP"
tar xzf "$ARCHIVE" -C "$ROOT"
for f in app-config.php database.php email.php; do
  if [ -f "$BACKUP/$f" ]; then
    cp -a "$BACKUP/$f" "$ROOT/application/config/$f"
  fi
done
chown -R www-data:www-data "$ROOT/application" "$ROOT/modules" 2>/dev/null || true
echo "Deploy extract done. Config restored from $BACKUP"
php -l "$ROOT/application/models/Cron_model.php" >/dev/null && echo "Cron_model.php syntax OK"
curl -sS 'http://127.0.0.1/cron/cron_health' | head -c 400
echo
