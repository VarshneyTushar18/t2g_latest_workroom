#!/bin/bash
# Auto-repair Workroom if core files go missing or known webshell payload reappears.
# Safe to run from cron every 10 minutes.
set -u
ROOT="/var/www/html/t2gworkroom"
LOG="/var/log/workroom_health_guard.log"
QUAR="$ROOT/_quarantine_$(date +%F)"
LOCK="/tmp/workroom_health_guard.lock"

exec >>"$LOG" 2>&1
echo "=== $(date -Is) health check ==="

if ! mkdir "$LOCK" 2>/dev/null; then
  echo "skip: already running"
  exit 0
fi
trap 'rmdir "$LOCK" 2>/dev/null || true' EXIT

cd "$ROOT" || exit 1

# 1) Restore git-tracked files if deleted (does not revert local edits to modified files).
deleted=$(git diff --name-only --diff-filter=D 2>/dev/null | wc -l)
if [ "$deleted" -gt 0 ]; then
  echo "restoring $deleted deleted git files"
  git diff --name-only --diff-filter=D -z | xargs -0 -r git checkout HEAD --
fi

# 2) Ensure entrypoint exists.
if [ ! -f "$ROOT/index.php" ]; then
  echo "index.php missing — restoring from git"
  git checkout HEAD -- index.php
  chown www-data:www-data "$ROOT/index.php" 2>/dev/null || true
  chmod 644 "$ROOT/index.php" 2>/dev/null || true
fi

# 3) Quarantine known webshell payload (1099089 bytes).
mkdir -p "$QUAR"
payload_count=0
while IFS= read -r f; do
  dest="$QUAR/payload_$(date +%s)_$payload_count"
  mv "$f" "$dest" 2>/dev/null && payload_count=$((payload_count + 1)) || true
done < <(find "$ROOT" -type f -size 1099089c ! -path "*/_quarantine*/*" 2>/dev/null)
if [ "$payload_count" -gt 0 ]; then
  echo "quarantined $payload_count webshell payloads"
fi

# 4) Remove empty suspicious drop dirs from past attacks.
for d in "$ROOT"/616fd0 "$ROOT"/c0cfb; do
  if [ -d "$d" ] && [ -z "$(ls -A "$d" 2>/dev/null)" ]; then
    rmdir "$d" 2>/dev/null && echo "removed empty dir $d" || true
  fi
done

# 5) Quick HTTP sanity (local only).
code=$(curl -s -o /dev/null -w '%{http_code}' -L -k --max-time 20 \
  https://127.0.0.1/admin -H 'Host: t2gworkroom.com' || echo 000)
echo "admin HTTP $code"
if [ "$code" != "200" ] && [ "$code" != "302" ] && [ "$code" != "301" ]; then
  echo "WARN: admin not healthy (HTTP $code)"
fi

echo "done"
