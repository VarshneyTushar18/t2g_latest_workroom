#!/bin/bash
for id in 6 17 297 320 191; do
  curl -sk -H 'Host: t2gworkroom.com' -H 'Cookie: sp_session=sksr6uio2fmop7qea1gtq4ubikanun0h' \
    "https://127.0.0.1/admin/timesheets/my_attendance_calendar?month=2026-10&staff_id=${id}" \
    -o /tmp/cal_${id}.json -w "staff ${id} code:%{http_code} size:%{size_download}\n"
  python3 - "$id" << 'PY'
import json,sys
id=sys.argv[1]
raw=open(f'/tmp/cal_{id}.json',errors='replace').read()
try:
    data=json.loads(raw)
except Exception:
    print(raw[:120].replace('\n',' '))
    raise SystemExit
codes=[]
for d in data.get('days') or []:
    if d.get('date','') <= '2026-10-24':
        codes.append(d['date'][8:]+ (d.get('code') or d.get('status') or '-'))
print(' '.join(codes))
nc=[d['date'] for d in (data.get('days') or []) if d.get('code')=='NC' or d.get('status')=='night_carryover']
print('NC', nc or 'none')
PY
done
