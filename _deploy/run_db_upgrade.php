<?php
/**
 * Run core DB upgrade via local admin POST (Perfex migrations).
 * Usage: php _deploy/run_db_upgrade.php [base_url]
 * Default base_url: http://127.0.0.1:8000
 */
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}

$base = $argv[1] ?? 'http://127.0.0.1:8000';
$base = rtrim($base, '/');
$adminUrl = $base . '/admin';

$root = dirname(__DIR__);
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = '127.0.0.1:8000';
}
define('BASEPATH', $root . '/application/');
require $root . '/application/config/app-config.php';

$m = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($m->connect_error) {
    fwrite(STDERR, 'DB connect failed: ' . $m->connect_error . PHP_EOL);
    exit(1);
}
$pfx = defined('APP_DB_PREFIX') ? APP_DB_PREFIX : 'tbl';
$r = $m->query("SELECT version FROM {$pfx}migrations LIMIT 1");
$before = ($r && ($row = $r->fetch_assoc())) ? (int) $row['version'] : 0;
$target = 311;
echo "Target migration version: {$target}\n";
echo "DB version before: {$before}\n";

if ($before >= $target) {
    echo "Database already at or above target ({$before}). Running migration->current() via HTTP anyway in case migrations were skipped.\n";
}

$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 't2gworkroom_upgrade_cookies.txt';
@unlink($cookieFile);

$ch = curl_init($adminUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_TIMEOUT => 120,
]);
$html = curl_exec($ch);
$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($code !== 200 || !is_string($html)) {
    fwrite(STDERR, "Failed to load {$adminUrl} (HTTP {$code}). Is php -S 127.0.0.1:8000 router.php running?\n");
    exit(1);
}

if (stripos($html, 'Database upgrade is required') === false && stripos($html, 'upgrade_database') === false) {
    $r2 = $m->query("SELECT version FROM {$pfx}migrations LIMIT 1");
    $after = ($r2 && ($row2 = $r2->fetch_assoc())) ? (int) $row2['version'] : $before;
    echo "No upgrade screen (already on latest). DB version: {$after}\n";
    exit(0);
}

$csrf = '';
if (preg_match('/name="csrf_token_name"\s+value="([^"]+)"/', $html, $m1)) {
    $csrf = $m1[1];
} elseif (preg_match('/name="([^"]*csrf[^"]*)"\s+value="([^"]+)"/i', $html, $m2)) {
    $csrf = $m2[2];
}

$post = ['upgrade_database' => 'true'];
if ($csrf !== '') {
    $post['csrf_token_name'] = $csrf;
}

$ch = curl_init($adminUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($post),
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_TIMEOUT => 300,
]);
$response = curl_exec($ch);
$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
@unlink($cookieFile);

$r3 = $m->query("SELECT version FROM {$pfx}migrations LIMIT 1");
$after = ($r3 && ($row3 = $r3->fetch_assoc())) ? (int) $row3['version'] : $before;
echo "DB version after: {$after}\n";
echo "HTTP after POST: {$code}\n";

if ($after >= $target) {
    echo "OK: database upgraded to {$after}.\n";
    exit(0);
}

if (stripos((string) $response, 'error') !== false || stripos((string) $response, 'A Database Error') !== false) {
    fwrite(STDERR, "Upgrade may have failed. Check application/logs and re-run Upgrade now in browser.\n");
    exit(1);
}

echo "Upgrade POST sent. If version is still behind, open {$adminUrl} and click Upgrade now once.\n";
exit($after >= $target ? 0 : 1);
