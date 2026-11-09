#!/usr/bin/env python3
import os, paramiko

PATH = "/var/www/workroom/system/core/Common.php"


def main():
    c = paramiko.SSHClient()
    c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    c.connect("103.174.102.70", username="root", password=os.environ["STAGING_SSH_PASSWORD"], timeout=25)
    sftp = c.open_sftp()
    with sftp.open(PATH, "r") as f:
        text = f.read().decode("utf-8")
    if "require_once($file_path)" in text and "get_config" in text:
        print("Already require_once")
    else:
        new = text.replace(
            "\t\t\t\trequire($file_path);\n\t\t\t}\n\n\t\t\t// Is the config file in the environment folder?",
            "\t\t\t\trequire_once($file_path);\n\t\t\t}\n\n\t\t\t// Is the config file in the environment folder?",
            1,
        ).replace(
            "\t\t\t{\n\t\t\t\trequire($file_path);\n\t\t\t}\n\t\t\telseif ( ! $found)",
            "\t\t\t{\n\t\t\t\trequire_once($file_path);\n\t\t\t}\n\t\t\telseif ( ! $found)",
            1,
        )
        if new == text:
            print("Pattern not found")
        else:
            c.exec_command(f"cp -a {PATH} {PATH}.bak_require_once")
            with sftp.open(PATH, "w") as f:
                f.write(new.encode("utf-8"))
            print("Patched get_config to require_once")
    sftp.close()
    _, o, _ = c.exec_command(
        "curl -sS -o /dev/null -w 'admin=%{http_code}\\n' --max-time 30 -I http://127.0.0.1/admin/ && "
        "curl -sS -o /dev/null -w 'jquery=%{http_code}\\n' --max-time 10 http://127.0.0.1/assets/plugins/jquery/jquery.min.js",
        timeout=40,
    )
    print(o.read().decode())
    _, o2, _ = c.exec_command("tail -3 /var/log/nginx/error.log", timeout=10)
    print(o2.read().decode())
    c.close()


if __name__ == "__main__":
    main()
