#!/usr/bin/env python3
"""Staging-only: guard db_prefix() against double include (502/500 recovery)."""
import os
import paramiko

PATH = "/var/www/workroom/application/config/config.php"
OLD = """/**
 * Database Tables Prefix
 * @return string
 */
function db_prefix()
{
    return defined('APP_DB_PREFIX') ? APP_DB_PREFIX : 'tbl';
}"""

NEW = """/**
 * Database Tables Prefix
 * @return string
 */
if (!function_exists('db_prefix')) {
    function db_prefix()
    {
        return defined('APP_DB_PREFIX') ? APP_DB_PREFIX : 'tbl';
    }
}"""


def main():
    c = paramiko.SSHClient()
    c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    c.connect("103.174.102.70", username="root", password=os.environ["STAGING_SSH_PASSWORD"], timeout=25)
    sftp = c.open_sftp()
    with sftp.open(PATH, "r") as f:
        text = f.read().decode("utf-8")
    if "function_exists('db_prefix')" in text:
        print("Already patched")
    elif OLD not in text:
        print("Unexpected config.php content; not patching")
        sftp.close()
        c.close()
        return
    else:
        c.exec_command(f"cp -a {PATH} {PATH}.bak_db_prefix")
        with sftp.open(PATH, "w") as f:
            f.write(text.replace(OLD, NEW, 1).encode("utf-8"))
        print("Patched db_prefix guard on staging")
    sftp.close()
    _, o, _ = c.exec_command(
        "curl -sS -o /dev/null -w 'admin=%{http_code}\\n' --max-time 30 -I http://127.0.0.1/admin/ && "
        "tail -2 /var/log/nginx/error.log",
        timeout=35,
    )
    print(o.read().decode())
    c.close()


if __name__ == "__main__":
    main()
