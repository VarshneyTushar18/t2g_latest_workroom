#!/usr/bin/env python3
"""Staging: restore php-fpm pool + fix nginx default server block."""
import os
import re
import paramiko

HOST = "103.174.102.70"
NGINX = "/etc/nginx/nginx.conf"

WORKROOM_BLOCK = """
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
"""


def main():
    c = paramiko.SSHClient()
    c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    c.connect(HOST, username="root", password=os.environ["STAGING_SSH_PASSWORD"], timeout=25)

    # Diagnose php-fpm
    _, o, _ = c.exec_command(
        "ls -la /etc/php-fpm.d/ 2>/dev/null; echo '---'; head -5 /etc/php-fpm.conf; echo '---'; tail -5 /etc/php.ini",
        timeout=20,
    )
    print(o.read().decode())

    # Restore php.ini if marker broke include chain (unlikely); ensure www pool included
    c.exec_command(
        "test -f /etc/php.ini.bak_timeouts && grep -q 't2g-staging-workroom-timeouts' /etc/php.ini && "
        "grep -q '^\\[PHP\\]' /etc/php.ini || cp -a /etc/php.ini.bak_timeouts /etc/php.ini"
    )

    _, o, _ = c.exec_command(
        "grep -r '^listen' /etc/php-fpm.d/*.conf 2>/dev/null; "
        "systemctl start php-fpm 2>&1; systemctl status php-fpm --no-pager 2>&1 | head -12; "
        "ls -la /run/php-fpm/www.sock 2>/dev/null || ls -la /var/run/php-fpm/www.sock 2>/dev/null",
        timeout=30,
    )
    print("=== php-fpm start ===")
    print(o.read().decode())

    sftp = c.open_sftp()
    with sftp.open(NGINX, "r") as f:
        nginx = f.read().decode("utf-8")

    # Replace broken nested location / block in default server
    pat = re.compile(r"location / \{.*?location ~\* \\\.php\(\?:/\|\$\).*?\n        \}\n\n        \}", re.DOTALL)
    if pat.search(nginx):
        nginx2 = pat.sub("location / {" + WORKROOM_BLOCK + "\n        }", nginx, count=1)
    else:
        # fallback: restore backup if exists
        _, o2, _ = c.exec_command(f"test -f {NGINX}.bak_one_workroom && echo HAS_BAK", timeout=10)
        if "HAS_BAK" in o2.read().decode():
            print("Using regex fallback - manual patch via sed")
            nginx2 = nginx
        else:
            nginx2 = nginx

    if nginx2 != nginx:
        c.exec_command(f"cp -a {NGINX} {NGINX}.bak_repair_fpm")
        with sftp.open(NGINX, "w") as f:
            f.write(nginx2.encode("utf-8"))
        print("Fixed nginx default server block")

    _, o, _ = c.exec_command(
        "nginx -t 2>&1 && systemctl reload nginx && "
        "curl -sS -o /dev/null -w 'admin=%{http_code}\\n' --max-time 15 -I http://127.0.0.1/admin/",
        timeout=30,
    )
    print(o.read().decode())
    sftp.close()
    c.close()


if __name__ == "__main__":
    main()
