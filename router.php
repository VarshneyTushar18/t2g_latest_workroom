<?php
// Dev router for PHP built-in server to emulate Apache mod_rewrite for Perfex/CodeIgniter.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$full = __DIR__ . urldecode($path);

// Serve existing static files (assets, uploads, etc.) directly.
if ($path !== '/' && is_file($full)) {
    return false;
}

// Route everything else through the front controller.
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/index.php';
require __DIR__ . '/index.php';
