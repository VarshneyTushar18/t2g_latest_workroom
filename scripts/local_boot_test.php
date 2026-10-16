<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
define('ENVIRONMENT', 'development');
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['HTTPS'] = 'off';
$_SERVER['REQUEST_URI'] = '/myproject/';
$_SERVER['SCRIPT_NAME'] = '/myproject/index.php';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
chdir(dirname(__DIR__));
ob_start();
try {
	require dirname(__DIR__) . '/index.php';
} catch (Throwable $e) {
	ob_end_clean();
	echo 'Throwable: ' . $e->getMessage() . "\n" . $e->getTraceAsString();
	exit(1);
}
$out = ob_get_clean();
echo substr($out, 0, 3000);
