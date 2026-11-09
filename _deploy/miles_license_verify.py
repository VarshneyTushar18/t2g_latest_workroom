import os
import sys

import paramiko

password = os.environ.get("MILES_SSH_PASSWORD", "")
if not password:
    sys.exit(2)

php = r"""<?php
$_SERVER['SERVER_NAME'] = '103.174.102.70';
$_SERVER['HTTP_HOST'] = '103.174.102.70';
$_SERVER['HTTPS'] = 'off';
$_SERVER['REQUEST_URI'] = '/admin/modules';
$_SERVER['SERVER_ADDR'] = '103.174.102.70';

$root = '/var/www/workroom';
foreach (['timesheets' => 'TimesheetLic', 'hr_profile' => 'HRProfileLic'] as $mod => $cls) {
    echo "=== $mod ===\n";
    $lib = "$root/modules/$mod/libraries/gtsslib.php";
    if (!is_file($lib)) { echo "no gtsslib\n\n"; continue; }
    require_once $lib;
    $api = new $cls();
    echo 'local_lic=' . ($api->check_local_license_exist() ? 'yes' : 'no') . "\n";
    $v = $api->verify_license(false);
    echo 'verify=' . json_encode($v) . "\n\n";
}
"""

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect("103.174.102.70", username="root", password=password, timeout=25, allow_agent=False, look_for_keys=False)
try:
    sftp = client.open_sftp()
    with sftp.file("/tmp/miles_lic_verify.php", "w") as f:
        f.write(php)
    sftp.close()
    stdin, stdout, stderr = client.exec_command("php /tmp/miles_lic_verify.php; rm -f /tmp/miles_lic_verify.php", timeout=60)
    print(stdout.read().decode("utf-8", errors="replace"))
finally:
    client.close()
