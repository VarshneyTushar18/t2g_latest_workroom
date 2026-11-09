import os
import sys

import paramiko

host = "103.174.102.70"
password = os.environ.get("MILES_SSH_PASSWORD", "")
if not password:
    sys.exit(2)

roots = ["/var/www/workroom", "/var/www/workroom-stage"]
checks = []
for root in roots:
    checks.extend([
        f"echo '===== {root} ====='",
        f"test -d {root}/modules && ls -la {root}/modules 2>/dev/null | head -25",
        f"test -d {root}/modules/timesheets && echo 'timesheets: YES' || echo 'timesheets: NO'",
        f"test -f {root}/modules/timesheets/timesheets.php && head -40 {root}/modules/timesheets/timesheets.php 2>/dev/null",
        f"grep -r 'hrms\\|HRMS\\|timesheets' {root}/application/config 2>/dev/null | head -15",
        f"test -f {root}/application/config/app-config.php && grep -E 'APP_DB_NAME|APP_BASE_URL' {root}/application/config/app-config.php 2>/dev/null | head -5",
    ])

# DB module flags if mysql creds in app-config (read only db name, query modules table via php one-liner)
php = r"""
<?php
foreach (['/var/www/workroom', '/var/www/workroom-stage'] as $root) {
    $cfg = $root . '/application/config/app-config.php';
    if (!is_file($cfg)) { echo "$root: no app-config\n"; continue; }
    $c = file_get_contents($cfg);
    preg_match("/define\('APP_DB_NAME',\s*'([^']*)'\)/", $c, $n);
    preg_match("/define\('APP_DB_USERNAME',\s*'([^']*)'\)/", $c, $u);
    preg_match("/define\('APP_DB_PASSWORD',\s*'([^']*)'\)/", $c, $p);
    preg_match("/define\('APP_DB_HOSTNAME',\s*'([^']*)'\)/", $c, $h);
    if (empty($n[1])) { echo "$root: no db name\n"; continue; }
    $m = @new mysqli($h[1] ?? 'localhost', $u[1] ?? '', $p[1] ?? '', $n[1]);
    if ($m->connect_error) { echo "$root db fail\n"; continue; }
    echo "=== DB {$n[1]} ($root) ===\n";
    $q = $m->query("SELECT module_name, active FROM tblmodules ORDER BY module_name");
    if ($q) while ($r = $q->fetch_assoc()) echo $r['module_name'].' active='.$r['active']."\n";
    $q2 = $m->query("SELECT name, value FROM tbloptions WHERE name LIKE '%timesheet%' OR name LIKE '%hrms%' OR name='enabled_modules' LIMIT 30");
    if ($q2) while ($r = $q2->fetch_assoc()) echo 'opt '.$r['name'].'='.substr($r['value'],0,80)."\n";
    $m->close();
}
"""

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(host, username="root", password=password, timeout=25, allow_agent=False, look_for_keys=False)
try:
    for cmd in checks:
        stdin, stdout, stderr = client.exec_command(cmd, timeout=45)
        out = stdout.read().decode("utf-8", errors="replace").strip()
        if out:
            print(out)
    sftp = client.open_sftp()
    with sftp.file("/tmp/miles_mod_check.php", "w") as f:
        f.write(php)
    sftp.close()
    stdin, stdout, stderr = client.exec_command("php /tmp/miles_mod_check.php; rm -f /tmp/miles_mod_check.php", timeout=60)
    print(stdout.read().decode("utf-8", errors="replace"))
    err = stderr.read().decode("utf-8", errors="replace")
    if err.strip():
        print(err, file=sys.stderr)
finally:
    client.close()
