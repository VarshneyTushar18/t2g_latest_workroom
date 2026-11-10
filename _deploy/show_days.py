import json
data = json.load(open('/tmp/cal_297.json'))
for d in data['days']:
    if d['date'] in ('2026-10-12','2026-10-13','2026-10-14','2026-10-15','2026-10-22','2026-10-23','2026-10-24'):
        print(d['date'], d['code'], d['status'], 'in', d.get('check_in'), 'out', d.get('check_out'), 'hrs', d.get('hours'), 'swipes', len(d.get('swipes') or []), d.get('issues'))
