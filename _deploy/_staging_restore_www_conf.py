#!/usr/bin/env python3
import os, paramiko

WWW_CONF = """[www]
user = nginx
group = nginx
listen = /run/php-fpm/www.sock
listen.acl_users = apache,nginx
listen.owner = root
listen.group = nginx
listen.mode = 0660

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

php_admin_value[error_log] = /var/log/php-fpm/www-error.log
php_admin_flag[log_errors] = on
"""

HOST = "103.174.102.70"


def main():
    c = paramiko.SSHClient()
    c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    c.connect(HOST, username="root", password=os.environ["STAGING_SSH_PASSWORD"], timeout=25)

    _, o, _ = c.exec_command(
        "find /etc -name 'www.conf*' 2>/dev/null | head -20; "
        "rpm -ql php-fpm 2>/dev/null | grep www.conf | head -5",
        timeout=25,
    )
    print(o.read().decode())

    sftp = c.open_sftp()
    c.exec_command("cp -a /etc/php-fpm.d/www.conf /etc/php-fpm.d/www.conf.empty.bak 2>/dev/null || true")
    with sftp.open("/etc/php-fpm.d/www.conf", "w") as f:
        f.write(WWW_CONF.encode("utf-8"))
    sftp.close()

    _, o, _ = c.exec_command(
        "mkdir -p /run/php-fpm && "
        "systemctl start php-fpm && systemctl is-active php-fpm && "
        "ls -la /run/php-fpm/www.sock && "
        "curl -sS -o /dev/null -w 'admin=%{http_code}\\n' --max-time 20 -I http://127.0.0.1/admin/",
        timeout=40,
    )
    print(o.read().decode())
    c.close()


if __name__ == "__main__":
    main()
