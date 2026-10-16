<?php
/**
 * Merge tblstaff_drive_data from production_crm into new_workroom (local dev).
 * Usage: php scripts/sync_snapshot_drive_data.php [--days=120] [--full]
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$target = 'new_workroom';
$source = 'production_crm';

$days = 120;
$full = false;
foreach ($argv as $arg) {
	if (preg_match('/^--days=(\d+)$/', $arg, $m)) {
		$days = (int) $m[1];
	}
	if ($arg === '--full') {
		$full = true;
	}
}

$m = new mysqli($host, $user, $pass);
if ($m->connect_error) {
	fwrite(STDERR, "MySQL connect failed: {$m->connect_error}\n");
	exit(1);
}

foreach ([$target, $source] as $db) {
	$r = $m->query("SHOW DATABASES LIKE '" . $m->real_escape_string($db) . "'");
	if (!$r || $r->num_rows === 0) {
		fwrite(STDERR, "Database missing: {$db}\n");
		exit(1);
	}
}

function stats(mysqli $m, string $db): array {
	$m->select_db($db);
	$r = $m->query('SELECT COUNT(*) AS c, MAX(date_time) AS last_dt FROM tblstaff_drive_data');
	$row = $r->fetch_assoc();
	$r2 = $m->query('SELECT COUNT(*) AS c FROM tblstaff_drive_data WHERE date_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
	$w = $r2->fetch_assoc();
	return [
		'total' => (int) $row['c'],
		'last_dt' => $row['last_dt'],
		'last_7d' => (int) $w['c'],
	];
}

echo "Before sync:\n";
echo '  ' . $target . ': ' . json_encode(stats($m, $target)) . "\n";
echo '  ' . $source . ': ' . json_encode(stats($m, $source)) . "\n";

$since = $full ? null : date('Y-m-d H:i:s', strtotime("-{$days} days"));
$where = $since ? "WHERE date_time >= '" . $m->real_escape_string($since) . "'" : '';

$m->select_db($target);
$m->query('SET SESSION sql_mode = CONCAT(@@sql_mode, ",NO_AUTO_VALUE_ON_ZERO")');

$batch = 5000;
$offset = 0;
$inserted = 0;
$skipped = 0;

while (true) {
	$sql = "SELECT staff_id, file_id, directory_id, parent_directory_id, status, date_time
		FROM {$source}.tblstaff_drive_data
		{$where}
		ORDER BY date_time DESC
		LIMIT {$offset}, {$batch}";
	$r = $m->query($sql);
	if (!$r || $r->num_rows === 0) {
		break;
	}

	$stmt = $m->prepare(
		'INSERT INTO tblstaff_drive_data (staff_id, file_id, directory_id, parent_directory_id, status, date_time)
		 SELECT ?, ?, ?, ?, ?, ?
		 FROM DUAL
		 WHERE NOT EXISTS (
		   SELECT 1 FROM tblstaff_drive_data n
		   WHERE n.staff_id = ? AND n.file_id = ? AND n.date_time = ?
		 )'
	);

	while ($row = $r->fetch_assoc()) {
		$staffId = (int) $row['staff_id'];
		$fileId = (string) $row['file_id'];
		$dirId = (string) $row['directory_id'];
		$parentId = (string) $row['parent_directory_id'];
		$status = (int) $row['status'];
		$dt = (string) $row['date_time'];

		$stmt->bind_param(
			'isssisiss',
			$staffId,
			$fileId,
			$dirId,
			$parentId,
			$status,
			$dt,
			$staffId,
			$fileId,
			$dt
		);
		$stmt->execute();
		if ($stmt->affected_rows > 0) {
			$inserted++;
		} else {
			$skipped++;
		}
	}
	$stmt->close();

	$offset += $batch;
	if (!$full && $offset > 200000) {
		echo "Stopped at offset {$offset} (safety cap for --days mode).\n";
		break;
	}
	echo "Processed offset {$offset}...\n";
}

echo "Inserted: {$inserted}, skipped (duplicate): {$skipped}\n";
echo "After sync:\n";
echo '  ' . $target . ': ' . json_encode(stats($m, $target)) . "\n";

$r = $m->query(
	"SELECT s.email, COUNT(d.id) AS cnt, MAX(d.date_time) AS last_dt
	 FROM {$target}.tblstaff_drive_data d
	 INNER JOIN {$target}.tblstaff s ON s.staffid = d.staff_id
	 WHERE LOWER(SUBSTRING_INDEX(s.email, '@', 1)) IN ('tarun.sharma', 'karan.singh')
	 GROUP BY s.email"
);
echo "Sample staff after sync:\n";
while ($row = $r->fetch_assoc()) {
	echo '  ' . $row['email'] . ' rows=' . $row['cnt'] . ' last=' . $row['last_dt'] . "\n";
}
