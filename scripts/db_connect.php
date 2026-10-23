<?php

mysqli_report(MYSQLI_REPORT_OFF);

function script_db_mysqli(?string $database = null): mysqli
{
	if (!defined('BASEPATH')) {
		define('BASEPATH', dirname(__DIR__) . '/system/');
	}
	require dirname(__DIR__) . '/application/config/app-config.php';

	$port = defined('APP_DB_PORT') ? (int) APP_DB_PORT : 3306;
	$db   = $database ?? APP_DB_NAME;
	$m    = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, $db, $port);
	if ($m->connect_error) {
		fwrite(STDERR, "MySQL connect failed ({$db}@{$port}): {$m->connect_error}\n");
		exit(1);
	}

	return $m;
}
