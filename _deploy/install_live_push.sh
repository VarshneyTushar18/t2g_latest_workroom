#!/bin/bash
set -e
BASE=/var/www/html/t2gworkroom
SRC=/tmp/live_push
TS=$(date +%Y%m%d%H%M%S)

if [ ! -d "$SRC" ]; then
  echo "Missing $SRC"
  exit 1
fi

cd "$SRC"
mapfile -t FILES < <(find . -type f | sed 's|^\./||')
echo "Files to deploy: ${#FILES[@]}"

if [ ${#FILES[@]} -gt 0 ]; then
  sudo tar czf "/tmp/t2g_backup_${TS}.tgz" -C "$BASE" "${FILES[@]}" || true
  echo "Backup: /tmp/t2g_backup_${TS}.tgz"
fi

for rel in "${FILES[@]}"; do
  dest="$BASE/$rel"
  sudo mkdir -p "$(dirname "$dest")"
  sudo cp -a "$SRC/$rel" "$dest"
  sudo chown www-data:www-data "$dest" 2>/dev/null || true
done

php -l "$BASE/application/config/config.php"
php -l "$BASE/application/controllers/Cron.php"
test -f "$BASE/application/config/app_email_policy.php" && echo OK_email_policy
test -f "$BASE/assets/css/admin-dashboard-nav.css" && echo OK_nav_css
test -f "$BASE/scripts/send_snapshot_not_logged_in_sample.php" && echo OK_snapshot_script
grep -q snapshot_not_logged "$BASE/application/models/Cron_model.php" && echo OK_snapshot_cron || echo WARN_no_snapshot_cron
echo DEPLOY_COMPLETE
