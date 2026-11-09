#!/usr/bin/env python3
import os, paramiko
c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect("103.174.102.70", username="root", password=os.environ["STAGING_SSH_PASSWORD"], timeout=25)
_, o, _ = c.exec_command(
    """
ss -tlnp | grep -E ':80 |8088|8081' || echo no_extra_ports
curl -sS -o /dev/null -w 'modules=%{http_code}\n' --max-time 10 http://127.0.0.1/modules/timesheets/assets/js/check_in_out_ts.js
grep docRoot /usr/local/lsws/conf/vhosts/workroom-stage.example.com/vhost.conf | head -1
grep 'root /var/www/workroom' /etc/nginx/nginx.conf | head -1
""",
    timeout=30,
)
print(o.read().decode())
c.close()
