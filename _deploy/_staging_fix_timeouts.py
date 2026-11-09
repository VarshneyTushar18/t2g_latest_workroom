#!/usr/bin/env python3
"""Staging: raise PHP/nginx timeouts so dashboard task AJAX can finish."""
import os
import re
import paramiko

NGINX = "/etc/nginx/nginx.conf"
PHP_INI = "/etc/php.ini"
MARK = "# t2g-staging-workroom-timeouts"


def main():
    c = paramiko.SSHClient()
    c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    c.connect("103.174.102.70", username="root", password=os.environ["STAGING_SSH_PASSWORD"], timeout=25)
    sftp = c.open_sftp()

    with sftp.open(NGINX, "r") as f:
        nginx = f.read().decode("utf-8")

    if MARK not in nginx:
        nginx = nginx.replace(
            "fastcgi_pass unix:/run/php-fpm/www.sock;",
            MARK + "\n            fastcgi_read_timeout 300;\n            fastcgi_send_timeout 300;\n            fastcgi_pass unix:/run/php-fpm/www.sock;",
            1,
        )
        c.exec_command(f"cp -a {NGINX} {NGINX}.bak_timeouts")
        with sftp.open(NGINX, "w") as f:
            f.write(nginx.encode("utf-8"))
        print("Patched nginx fastcgi timeouts")

    _, o, _ = c.exec_command(
        f"grep -q '{MARK}' {PHP_INI} 2>/dev/null || "
        f"(cp -a {PHP_INI} {PHP_INI}.bak_timeouts && "
        f"sed -i 's/^max_execution_time = .*/max_execution_time = 300/' {PHP_INI} && "
        f"echo '{MARK}' >> {PHP_INI}) && "
        "grep max_execution_time /etc/php.ini | head -1 && "
        "nginx -t && systemctl reload nginx && systemctl restart php-fpm",
        timeout=60,
    )
    print(o.read().decode())

    # Index check
    idx_script = """
PASS=$(grep APP_DB_PASSWORD /var/www/workroom/application/config/app-config.php | sed "s/.*'\\([^']*\\)'.*/\\1/")
mysql -uroot -p"$PASS" -N -e "
SELECT COUNT(*) FROM tblstafftaskassignees;
SELECT name,value FROM tbloptions WHERE name='migration_version';
" production_crm 2>/dev/null
"""
    _, o2, _ = c.exec_command(idx_script, timeout=30)
    print("=== DB meta ===")
    print(o2.read().decode())

    sftp.close()
    c.close()


if __name__ == "__main__":
    main()
