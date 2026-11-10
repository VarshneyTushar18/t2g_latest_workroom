#!/bin/bash
set -euo pipefail
BASE=/var/www/html/t2gworkroom
STAMP=$(date -u +%Y%m%d_%H%M%S)
mkdir -p "$BASE/_backups"
for f in Snapshot.zip Snapshot-x86.zip; do
  if [[ -f "$BASE/$f" ]]; then
    cp -a "$BASE/$f" "$BASE/_backups/${f%.zip}_${STAMP}.zip"
  fi
done
cp -a /tmp/Snapshot.zip "$BASE/Snapshot.zip"
cp -a /tmp/Snapshot-x86.zip "$BASE/Snapshot-x86.zip"
chown www-data:www-data "$BASE/Snapshot.zip" "$BASE/Snapshot-x86.zip"
ls -la "$BASE/Snapshot.zip" "$BASE/Snapshot-x86.zip"
