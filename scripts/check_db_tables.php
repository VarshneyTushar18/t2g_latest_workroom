<?php
$m = new mysqli('127.0.0.1', 'root', '', 'new_workroom', 3300);
$bad = $ok = 0;
$r = $m->query('SHOW TABLE STATUS');
while ($row = $r->fetch_assoc()) {
	if (!empty($row['Comment']) && stripos($row['Comment'], "doesn't exist in engine") !== false) {
		$bad++;
	} else {
		$ok++;
	}
}
echo "MySQL8:3300 ok={$ok} bad={$bad}\n";
$r = $m->query('SHOW DATABASES');
while ($row = $r->fetch_array()) {
	$db = $row[0];
	if (!preg_match('/^(new_|production|sample|test)/', $db)) {
		continue;
	}
	$m2 = @new mysqli('127.0.0.1', 'root', '', $db, 3300);
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
