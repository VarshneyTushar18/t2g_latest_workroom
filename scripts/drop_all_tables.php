<?php
$m = new mysqli('127.0.0.1', 'root', '', 'new_workroom', 3300);
$m->query('SET FOREIGN_KEY_CHECKS=0');
$r = $m->query('SHOW TABLES');
$tables = [];
while ($row = $r->fetch_array()) {
	$tables[] = $row[0];
}
foreach ($tables as $t) {
	$m->query('DROP TABLE IF EXISTS `' . $m->real_escape_string($t) . '`');
	echo "dropped {$t}\n";
}
$m->query('SET FOREIGN_KEY_CHECKS=1');
echo 'done ' . count($tables) . " tables\n";
