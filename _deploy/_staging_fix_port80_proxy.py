#!/usr/bin/env python3
"""Point staging nginx :80 default_server at Workroom :8088 (not broken :8081 router)."""
import os
import sys
import paramiko

HOST = "103.174.102.70"
USER = "root"
NGINX = "/etc/nginx/nginx.conf"


def main():
    password = os.environ.get("STAGING_SSH_PASSWORD")
    if not password:
        print("Set STAGING_SSH_PASSWORD", file=sys.stderr)
        sys.exit(1)

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, username=USER, password=password, timeout=25)

    sftp = client.open_sftp()
    with sftp.open(NGINX, "r") as f:
        text = f.read().decode("utf-8")

    if "127.0.0.1:8088" in text and "127.0.0.1:8081" not in text.split("default_server")[1].split("server {")[0]:
        print("Already proxied to 8088 in default server block")
    else:
        new = text.replace(
            "proxy_pass http://127.0.0.1:8081;",
            "proxy_pass http://127.0.0.1:8088;",
        ).replace(
            "proxy_redirect http://103.174.102.70:8081/ http://103.174.102.70/;",
            "proxy_redirect http://103.174.102.70:8088/ http://103.174.102.70/;",
        )
        if new == text:
            print("No 8081 proxy lines found; aborting", file=sys.stderr)
            sys.exit(1)

        backup = NGINX + ".bak_workroom8088"
        client.exec_command(f"cp -a {NGINX} {backup}")
        with sftp.open(NGINX, "w") as f:
            f.write(new.encode("utf-8"))
        print(f"Updated {NGINX} (backup {backup})")

    for cmd in ("nginx -t", "systemctl reload nginx"):
        stdin, stdout, stderr = client.exec_command(cmd)
        out = stdout.read().decode()
        err = stderr.read().decode()
        print(out or err)

    test = r"""
curl -sS -o /dev/null -w 'modules_js_80=%{http_code}\n' --max-time 10 \
  http://127.0.0.1/modules/timesheets/assets/js/check_in_out_ts.js
curl -sS -o /dev/null -w 'admin_80=%{http_code}\n' --max-time 10 -I http://127.0.0.1/admin/
"""
    stdin, stdout, stderr = client.exec_command(test)
    print(stdout.read().decode())

    sftp.close()
    client.close()


if __name__ == "__main__":
    main()
