#!/usr/bin/env python3
import os
import re
import paramiko

NGINX = "/etc/nginx/nginx.conf"

SERVER_BLOCK = """    server {
        listen       80 default_server;
        listen       [::]:80 default_server;
        server_name  _;

        root /var/www/workroom;
        index index.php index.html;

        location ~ /\\. {
            deny all;
        }

        location = /token.json {
            deny all;
        }

        location ~* ^/(application|system|vendor|google-client|biometric-bridge)(/|$) {
            deny all;
        }

        location ~* \\.(sql|dump|log|ini|env|py|exe)$ {
            deny all;
        }

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location = /index.php {
            include /etc/nginx/fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root/index.php;
            fastcgi_read_timeout 300;
            fastcgi_send_timeout 300;
            fastcgi_pass unix:/run/php-fpm/www.sock;
        }

        location ~ \\.php$ {
            deny all;
        }

        error_page 404 /404.html;
        location = /40x.html {
        }

        error_page 500 502 503 504 /50x.html;
        location = /50x.html {
        }
    }
"""


def main():
    c = paramiko.SSHClient()
    c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    c.connect("103.174.102.70", username="root", password=os.environ["STAGING_SSH_PASSWORD"], timeout=25)
    sftp = c.open_sftp()
    with sftp.open(NGINX, "r") as f:
        text = f.read().decode("utf-8")

    new = re.sub(
        r"    server \{\s*listen\s+80 default_server;.*?error_page 500 502 503 504 /50x\.html;\s*location = /50x\.html \{\s*\}\s*\}",
        SERVER_BLOCK.strip(),
        text,
        count=1,
        flags=re.DOTALL,
    )
    if new == text:
        print("Pattern did not match; abort")
        sftp.close()
        c.close()
        return

    c.exec_command(f"cp -a {NGINX} {NGINX}.bak_flat_workroom")
    with sftp.open(NGINX, "w") as f:
        f.write(new.encode("utf-8"))
    sftp.close()

    _, o, _ = c.exec_command(
        "nginx -t 2>&1 && systemctl reload nginx && "
        "systemctl is-active php-fpm && "
        "curl -sS -o /dev/null -w 'admin=%{http_code}\\n' --max-time 25 -I http://127.0.0.1/admin/ && "
        "curl -sS --max-time 25 http://127.0.0.1/admin/ 2>&1 | head -3",
        timeout=40,
    )
    print(o.read().decode())
    c.close()


if __name__ == "__main__":
    main()
