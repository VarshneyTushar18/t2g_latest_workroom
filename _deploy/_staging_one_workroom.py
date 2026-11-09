#!/usr/bin/env python3
"""
Staging only (103.174.102.70): one Workroom on port 80 + CyberPanel site docroot.
Does not change other nginx vhosts in conf.d/.
"""
import os
import re
import sys

import paramiko

HOST = "103.174.102.70"
NGINX = "/etc/nginx/nginx.conf"
STAGE_VHOST = "/usr/local/lsws/conf/vhosts/workroom-stage.example.com/vhost.conf"

WORKROOM_LOCATIONS = r"""
        root /var/www/workroom;
        index index.php index.html;

        location ~ /\.(?!well-known) {
            deny all;
        }

        location = /token.json {
            deny all;
        }

        location ~* ^/(application|system|vendor|google-client|biometric-bridge)(/|$) {
            deny all;
        }

        location ~* \.(sql|dump|log|ini|env|py|exe)$ {
            deny all;
        }

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location = /index.php {
            include /etc/nginx/fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root/index.php;
            fastcgi_pass unix:/run/php-fpm/www.sock;
        }

        location ~* \.php(?:/|$) {
            deny all;
        }
"""


def main():
    password = os.environ.get("STAGING_SSH_PASSWORD")
    if not password:
        sys.exit(1)

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, username="root", password=password, timeout=25)
    sftp = client.open_sftp()

    with sftp.open(NGINX, "r") as f:
        nginx = f.read().decode("utf-8")

    if "root /var/www/workroom;" in nginx and "127.0.0.1:8088" not in nginx:
        print("nginx default already serves /var/www/workroom")
    else:
        backup = NGINX + ".bak_one_workroom"
        client.exec_command(f"cp -a {NGINX} {backup}")
        new = re.sub(
            r"location / \{[^}]*proxy_pass[^}]*\}",
            "location / {" + WORKROOM_LOCATIONS + "\n        }",
            nginx,
            count=1,
            flags=re.DOTALL,
        )
        if new == nginx:
            print("Could not patch nginx default location /", file=sys.stderr)
            sys.exit(1)
        with sftp.open(NGINX, "w") as f:
            f.write(new.encode("utf-8"))
        print(f"Patched {NGINX} (backup {backup})")

    for path in (STAGE_VHOST, STAGE_VHOST + "0"):
        try:
            with sftp.open(path, "r") as f:
                vh = f.read().decode("utf-8")
        except OSError:
            continue
        if "docRoot                   /var/www/workroom/" in vh:
            print(f"Already set docRoot in {path}")
            continue
        vh2 = re.sub(
            r"docRoot\s+\$VH_ROOT/public_html",
            "docRoot                   /var/www/workroom/",
            vh,
            count=1,
        )
        if vh2 != vh:
            client.exec_command(f"cp -a {path} {path}.bak_one_workroom")
            with sftp.open(path, "w") as f:
                f.write(vh2.encode("utf-8"))
            print(f"Updated CyberPanel vhost docRoot: {path}")

    cmds = """
nginx -t && systemctl reload nginx
# Stop duplicate Workroom stacks (port 8088 nginx + 8081 dev router)
if pgrep -f 'nginx -c /etc/nginx/workroom-8088.conf' >/dev/null; then
  pkill -f 'nginx -c /etc/nginx/workroom-8088.conf' || true
  echo stopped_8088_nginx
fi
if pgrep -f 'workroom-stage-router.php' >/dev/null; then
  pkill -f 'workroom-stage-router.php' || true
  echo stopped_8081_router
fi
/usr/local/lsws/bin/lswsctrl restart >/dev/null 2>&1 || systemctl restart lsws 2>/dev/null || true
curl -sS -o /dev/null -w 'ip80_modules=%{http_code}\n' --max-time 8 http://127.0.0.1/modules/timesheets/assets/js/check_in_out_ts.js
curl -sS -o /dev/null -w 'ip80_admin=%{http_code}\n' --max-time 8 -I http://127.0.0.1/admin/
ss -tlnp | grep -E ':8088|:8081' || echo 'ports_8088_8081_closed'
"""
    _, stdout, stderr = client.exec_command(cmds, timeout=120)
    print(stdout.read().decode("utf-8", errors="replace"))
    err = stderr.read().decode("utf-8", errors="replace")
    if err.strip():
        print(err, file=sys.stderr)

    sftp.close()
    client.close()


if __name__ == "__main__":
    main()
