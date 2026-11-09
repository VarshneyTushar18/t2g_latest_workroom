#!/bin/bash
F=/var/www/workroom/system/core/Common.php
cp -a "$F" "$F.bak_require_once"
sed -i '253s/require(/require_once(/;259s/require(/require_once(/' "$F"
grep -n "require" "$F" | sed -n '250,265p'
curl -sS -o /dev/null -w 'admin=%{http_code}\n' --max-time 30 -I http://127.0.0.1/admin/
