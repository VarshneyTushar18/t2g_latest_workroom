<?php
foreach ([3307 => '', 3300 => ''] as $port => $pass) {
	try {
		$m = new mysqli('127.0.0.1', 'root', $pass, 'new_workroom', $port);
		echo "connect ok {$port}\n";
		$r = $m->query('SELECT COUNT(*) c FROM tbloptions');
		echo 'count ' . $r->fetch_assoc()['c'] . "\n";
	} catch (Throwable $e) {
		echo "port {$port}: " . $e->getMessage() . "\n";
	}
}
