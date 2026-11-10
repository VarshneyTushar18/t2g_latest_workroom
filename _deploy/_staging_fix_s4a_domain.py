#!/usr/bin/env python3
"""MilesWeb: restore services4amazon.com vhost; stop Workroom default_server catch-all."""
import os
import re
import sys

import paramiko

HOST = "103.174.102.70"
NGINX = "/etc/nginx/nginx.conf"
WORKROOM = "/var/www/workroom"
REMOTE_SH = "/tmp/fix_services4amazon_milesweb.sh"


def main() -> None:
    password = os.environ.get("STAGING_SSH_PASSWORD")
    if not password:
        print("Set STAGING_SSH_PASSWORD then re-run, or run fix_services4amazon_milesweb.sh on the server.", file=sys.stderr)
        sys.exit(1)

    local_sh = os.path.join(os.path.dirname(__file__), "fix_services4amazon_milesweb.sh")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, username="root", password=password, timeout=25)
    sftp = client.open_sftp()
    sftp.put(local_sh, REMOTE_SH)
    sftp.close()
    _, stdout, stderr = client.exec_command(f"bash {REMOTE_SH}", timeout=180)
    print(stdout.read().decode("utf-8", errors="replace"))
    err = stderr.read().decode("utf-8", errors="replace")
    if err.strip():
        print(err, file=sys.stderr)
    client.close()


if __name__ == "__main__":
    main()
