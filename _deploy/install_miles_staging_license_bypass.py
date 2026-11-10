"""Deploy staging-only GreenTech license bypass to MilesWeb /var/www/workroom only."""
import os
import sys
from datetime import datetime

import paramiko

HOST = "103.174.102.70"
ROOT = "/var/www/workroom"
LOCAL_ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

FILES = [
    (
        "application/config/app_workroom_staging_policy.php",
        f"{ROOT}/application/config/app_workroom_staging_policy.php",
    ),
    ("modules/timesheets/timesheets.php", f"{ROOT}/modules/timesheets/timesheets.php"),
    ("modules/hr_profile/hr_profile.php", f"{ROOT}/modules/hr_profile/hr_profile.php"),
]

APP_CONFIG_MARKER = "app_workroom_staging_policy.php"
APP_CONFIG_SNIPPET = """
/*
 * MilesWeb staging: skip Envato/GreenTech license checks (do not copy to AWS live).
 */
if (is_file(__DIR__ . '/app_workroom_staging_policy.php')) {
    require_once __DIR__ . '/app_workroom_staging_policy.php';
}
"""

password = os.environ.get("MILES_SSH_PASSWORD", "")
if not password:
    sys.exit("Set MILES_SSH_PASSWORD")

stamp = datetime.utcnow().strftime("%Y%m%d_%H%M%S")
backup_dir = f"{ROOT}/_backups/staging_lic_bypass_{stamp}"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, username="root", password=password, timeout=25, allow_agent=False, look_for_keys=False)

try:
    client.exec_command(f"mkdir -p {backup_dir}", timeout=30)
    sftp = client.open_sftp()

    for rel, remote in FILES:
        local = os.path.join(LOCAL_ROOT, rel.replace("/", os.sep))
        if not os.path.isfile(local):
            raise SystemExit(f"missing local file: {local}")
        client.exec_command(f"cp -a {remote} {backup_dir}/ 2>/dev/null || true", timeout=30)
        sftp.put(local, remote)
        print(f"uploaded {rel}")

    cfg_path = f"{ROOT}/application/config/app-config.php"
    with sftp.open(cfg_path, "r") as f:
        cfg = f.read().decode("utf-8", errors="replace")
    if APP_CONFIG_MARKER not in cfg:
        client.exec_command(f"cp -a {cfg_path} {backup_dir}/app-config.php", timeout=30)
        if not cfg.rstrip().endswith("?>"):
            cfg = cfg.rstrip() + APP_CONFIG_SNIPPET
        else:
            cfg = cfg.rstrip()[:-2].rstrip() + APP_CONFIG_SNIPPET + "\n?>\n"
        with sftp.open(cfg_path, "w") as f:
            f.write(cfg)
        print("patched app-config.php (staging policy include)")
    else:
        print("app-config.php already includes staging policy")

    sftp.close()

    php_enable = f"""<?php
define('BASEPATH', '{ROOT}/application/');
require '{ROOT}/application/config/app-config.php';
$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($m->connect_error) {{ fwrite(STDERR, 'db'); exit(1); }}
$m->query("UPDATE tblmodules SET active=1 WHERE module_name IN ('timesheets','hr_profile')");
$r = $m->query("SELECT module_name, active FROM tblmodules WHERE module_name IN ('timesheets','hr_profile')");
while ($row = $r->fetch_assoc()) echo $row['module_name'].' active='.$row['active']."\\n";
"""
    sftp = client.open_sftp()
    with sftp.file("/tmp/miles_enable_hr.php", "w") as f:
        f.write(php_enable)
    sftp.close()

    for cmd in [
        f"php -l {ROOT}/modules/timesheets/timesheets.php",
        f"php -l {ROOT}/modules/hr_profile/hr_profile.php",
        f"php -l {ROOT}/application/config/app_workroom_staging_policy.php",
        "php /tmp/miles_enable_hr.php; rm -f /tmp/miles_enable_hr.php",
        f"chown -R nginx:nginx {ROOT}/application/config/app_workroom_staging_policy.php "
        f"{ROOT}/modules/timesheets/timesheets.php {ROOT}/modules/hr_profile/hr_profile.php",
    ]:
        stdin, stdout, stderr = client.exec_command(cmd, timeout=60)
        out = stdout.read().decode().strip()
        err = stderr.read().decode().strip()
        if out:
            print(out)
        if err and "No syntax errors" not in err:
            print(err, file=sys.stderr)

    print(f"backup: {backup_dir}")
finally:
    client.close()
