#!/usr/bin/env python3
import os, paramiko

COMMANDS = """
ls /usr/local/lsws/conf/vhosts/
grep -E 'docRoot|vhDomain|vhAliases|listeners' /usr/local/lsws/conf/vhosts/Workroom/vhconf.conf 2>/dev/null | head -15
grep -E 'docRoot|vhDomain' /usr/local/lsws/conf/vhosts/workroom-stage.example.com/vhconf.conf 2>/dev/null | head -10
grep -E 'map.*Workroom|103.174|8088' /usr/local/lsws/conf/httpd_config.conf 2>/dev/null | head -20
ps aux | grep -E 'workroom-8088|8081.*workroom' | grep -v grep
ls -la /etc/systemd/system/*workroom* 2>/dev/null
cat /etc/systemd/system/nginx-workroom8088.service 2>/dev/null | head -20
"""


def main():
    c = paramiko.SSHClient()
    c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    c.connect("103.174.102.70", username="root", password=os.environ["STAGING_SSH_PASSWORD"], timeout=25)
    _, o, e = c.exec_command(COMMANDS, timeout=30)
    print(o.read().decode("utf-8", errors="replace"))
    c.close()


if __name__ == "__main__":
    main()
