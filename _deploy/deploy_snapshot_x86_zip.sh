#!/bin/bash
set -euo pipefail
BASE=/var/www/html/t2gworkroom
STAMP=$(date -u +%Y%m%d_%H%M%S)
ZIP_SRC="${1:-/tmp/Snapshot-x86.zip}"
if [[ ! -f "$ZIP_SRC" ]]; then
  echo "missing zip: $ZIP_SRC" >&2
  exit 1
fi
mkdir -p "$BASE/_backups"
cp -a "$BASE/Snapshot-x86.zip" "$BASE/_backups/Snapshot-x86_${STAMP}.zip" 2>/dev/null || true
cp -a "$ZIP_SRC" "$BASE/Snapshot-x86.zip"
chown www-data:www-data "$BASE/Snapshot-x86.zip"
ls -la "$BASE/Snapshot-x86.zip"
