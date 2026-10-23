<?php
require __DIR__ . '/db_connect.php';
$m = script_db_mysqli();
$port = defined('APP_DB_PORT') ? (int) APP_DB_PORT : 3306;
$bad = $ok = 0;
$r = $m->query('SHOW TABLE STATUS');
while ($row = $r->fetch_assoc()) {
	if (!empty($row['Comment']) && stripos($row['Comment'], "doesn't exist in engine") !== false) {
		$bad++;
	} else {
		$ok++;
	}
}
echo "MySQL:{$port} ok={$ok} bad={$bad}\n";
$r = $m->query('SHOW DATABASES');
while ($row = $r->fetch_array()) {
	$db = $row[0];
	if (!preg_match('/^(new_|production|sample|test)/', $db)) {
		continue;
	}
	$m2 = @new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, $db, $port);
	if ($m2->connect_error) {
		continue;
	}
	$st = @$m2->query("SELECT COUNT(*) c FROM tbloptions");
	if ($st) {
		echo "DB {$db} tbloptions count=" . $st->fetch_assoc()['c'] . "\n";
	} else {
		echo "DB {$db} tbloptions broken\n";
	}
}
