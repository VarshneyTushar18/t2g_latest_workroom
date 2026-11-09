#!/bin/bash
curl -sk -H 'Host: t2gworkroom.com' -H 'Cookie: sp_session=sksr6uio2fmop7qea1gtq4ubikanun0h' \
  'https://127.0.0.1/admin/timesheets/my_attendance_calendar?month=2026-10' \
  -o /tmp/cal_oct.json -w 'code:%{http_code} size:%{size_download}\n'
python3 - << 'PY'
import json
raw=open('/tmp/cal_oct.json','r',errors='replace').read()
print(raw[:180].replace('\n',' '))
try:
    data=json.loads(raw)
except Exception as e:
    print('NOT_JSON', e)
    raise SystemExit
days=data.get('days') or []
print('days', len(days))
for d in days:
    code=d.get('code')
    status=d.get('status')
    if code in ('NC','AB','INC','P','HO') or status in ('night_carryover','absent','punch_missing'):
        if d.get('date','') >= '2026-10-01':
            print(d.get('date'), code, status, d.get('check_in'), d.get('check_out'), '|'.join(d.get('issues') or []))
PY
