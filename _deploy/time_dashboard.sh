#!/bin/bash
for i in 1 2 3; do
  curl -sk -o /tmp/dash_timing.html -w "try ${i} ttfb:%{time_starttransfer} total:%{time_total} code:%{http_code} size:%{size_download}\n" \
    -H 'Host: t2gworkroom.com' \
    -H 'Cookie: sp_session=sksr6uio2fmop7qea1gtq4ubikanun0h' \
    https://127.0.0.1/admin/
done
grep -o 'Dashboard\|login' /tmp/dash_timing.html | head -5
