<?php
/**
 * Backfill tblstaff_drive_data in new_workroom from Google Drive (monitoring SA).
 * Use when agents have uploaded to Drive but local DB is stale.
 *
 * Usage:
 *   php scripts/backfill_snapshot_from_drive.php [--days=90] [--since=2026-09-29] [--folder=tarun.sharma] [--dry-run]
 */

$root = dirname(__DIR__);
$autoload = $root . '/google-client/vendor/autoload.php';
if (!is_file($autoload)) {
	fwrite(STDERR, "Run composer install in google-client/\n");
	exit(1);
}
require_once $autoload;

$days = 90;
$since = null;
$folderFilter = null;
$dryRun = false;
foreach ($argv as $arg) {
	if (preg_match('/^--days=(\d+)$/', $arg, $m)) {
		$days = (int) $m[1];
	}
	if (preg_match('/^--since=(\d{4}-\d{2}-\d{2})$/', $arg, $m)) {
		$since = $m[1];
	}
	if (preg_match('/^--folder=(.+)$/', $arg, $m)) {
		$folderFilter = strtolower(trim($m[1]));
	}
	if ($arg === '--dry-run') {
		$dryRun = true;
	}
}

if ($since === null) {
	$since = date('Y-m-d', strtotime("-{$days} days"));
}

$db = new mysqli('localhost', 'root', '', 'new_workroom');
if ($db->connect_error) {
	fwrite(STDERR, $db->connect_error . "\n");
	exit(1);
}

$keyCandidates = [
	$root . '/google-client/t2g-monitoring-37bb12065f7f.json',
	$root . '/google-client/t2g-monitoring-e410bbe96b94.json',
	$root . '/google-client/t2gemt-monitoring-c3fa0b6cc3dc.json',
];
$keyPath = null;
foreach ($keyCandidates as $p) {
	if (is_readable($p)) {
		$keyPath = $p;
		break;
	}
}
if (!$keyPath) {
	fwrite(STDERR, "No monitoring service account JSON found.\n");
	exit(1);
}

$client = new Google_Client();
$client->setAuthConfig($keyPath);
$client->setScopes([Google_Service_Drive::DRIVE]);
$service = new Google_Service_Drive($client);

function driveListParams(array $overrides = []): array
{
	return array_merge([
		'corpora'                   => 'allDrives',
		'supportsAllDrives'         => true,
		'includeItemsFromAllDrives' => true,
		'spaces'                    => 'drive',
	], $overrides);
}

function escapeDriveQuery($value): string
{
	return str_replace("'", "\\'", (string) $value);
}

function listAllPages(Google_Service_Drive $service, string $query, string $fields): array
{
	$merged = [];
	$pageToken = null;
	do {
		$result = $service->files->listFiles(driveListParams([
			'q'         => $query,
			'fields'    => 'nextPageToken, ' . $fields,
			'pageSize'  => 1000,
			'pageToken' => $pageToken,
		]));
		foreach ($result->getFiles() as $file) {
			$merged[] = $file;
		}
		$pageToken = $result->getNextPageToken();
	} while ($pageToken);

	return $merged;
}

function isEmployeeFolderName(string $name): bool
{
	$name = trim($name);
	if ($name === '' || strcasecmp($name, 'employees') === 0) {
		return false;
	}
	if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $name)) {
		return false;
	}

	return true;
}

function staffIdForFolder(mysqli $db, string $folderName): int
{
	$key = strtolower(trim($folderName));
	static $cache = [];
	if (isset($cache[$key])) {
		return $cache[$key];
	}
	$stmt = $db->prepare(
		'SELECT staffid FROM tblstaff WHERE LOWER(SUBSTRING_INDEX(email, \'@\', 1)) = ? AND active = 1 LIMIT 1'
	);
	$stmt->bind_param('s', $key);
	$stmt->execute();
	$r = $stmt->get_result()->fetch_assoc();
	$stmt->close();
	$cache[$key] = $r ? (int) $r['staffid'] : 0;

	return $cache[$key];
}

function driveFileUrl(string $fileId): string
{
	return 'https://drive.google.com/file/d/' . $fileId . '/view?usp=drivesdk';
}

function isImageFile($file): bool
{
	$name = $file->getName() ?: '';
	$mime = method_exists($file, 'getMimeType') ? ($file->getMimeType() ?: '') : '';
	if (preg_match('/\.(png|jpe?g|webp|gif|bmp)$/i', $name)) {
		return true;
	}

	return stripos($mime, 'image/') === 0;
}

function rowExists(mysqli $db, int $staffId, string $fileId): bool
{
	static $stmt = null;
	if (!$stmt) {
		$stmt = $db->prepare('SELECT id FROM tblstaff_drive_data WHERE staff_id = ? AND file_id = ? LIMIT 1');
	}
	$stmt->bind_param('is', $staffId, $fileId);
	$stmt->execute();
	$exists = (bool) $stmt->get_result()->fetch_assoc();

	return $exists;
}

function insertRow(mysqli $db, int $staffId, string $fileId, string $dirId, string $parentId, string $dateTime): bool
{
	$status = 0;
	$stmt = $db->prepare(
		'INSERT INTO tblstaff_drive_data (staff_id, file_id, directory_id, parent_directory_id, status, date_time)
		 VALUES (?, ?, ?, ?, ?, ?)'
	);
	$stmt->bind_param('isssis', $staffId, $fileId, $dirId, $parentId, $status, $dateTime);
	$ok = $stmt->execute();
	$stmt->close();

	return $ok;
}

function modifiedToMysql($file): string
{
	if (method_exists($file, 'getModifiedTime') && $file->getModifiedTime()) {
		return date('Y-m-d H:i:s', strtotime($file->getModifiedTime()));
	}
	if (method_exists($file, 'getCreatedTime') && $file->getCreatedTime()) {
		return date('Y-m-d H:i:s', strtotime($file->getCreatedTime()));
	}

	return date('Y-m-d H:i:s');
}

echo "Backfill since {$since}" . ($dryRun ? ' (dry-run)' : '') . PHP_EOL;

$empQuery = "mimeType='application/vnd.google-apps.folder' and trashed=false "
	. "and not name contains 'png' and not name contains '202' and not name contains 'New Folder'";
$employees = [];
foreach (listAllPages($service, $empQuery, 'files(id, name)') as $file) {
	if (!isEmployeeFolderName($file->getName())) {
		continue;
	}
	$key = strtolower($file->getName());
	if ($folderFilter !== null && $key !== $folderFilter) {
		continue;
	}
	$employees[$key] = $file;
}
ksort($employees);
echo 'Employee folders: ' . count($employees) . PHP_EOL;

$inserted = 0;
$skipped = 0;
$noStaff = 0;

foreach ($employees as $name => $empFolder) {
	$staffId = staffIdForFolder($db, $name);
	if ($staffId <= 0) {
		$noStaff++;
		echo "  skip {$name}: no active staff match\n";
		continue;
	}
	$parentId = $empFolder->getId();
	$dateFolders = listAllPages(
		$service,
		"'" . escapeDriveQuery($parentId) . "' in parents and trashed=false and mimeType='application/vnd.google-apps.folder'",
		'files(id, name)'
	);
	$folderInserts = 0;
	foreach ($dateFolders as $dayFolder) {
		$dayName = trim($dayFolder->getName());
		if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dayName) || $dayName < $since) {
			continue;
		}
		$dirId = $dayFolder->getId();
		$images = listAllPages(
			$service,
			"'" . escapeDriveQuery($dirId) . "' in parents and trashed=false",
			'files(id, name, mimeType, modifiedTime, createdTime)'
		);
		foreach ($images as $img) {
			if (!isImageFile($img)) {
				continue;
			}
			$fileId = driveFileUrl($img->getId());
			if (rowExists($db, $staffId, $fileId)) {
				$skipped++;
				continue;
			}
			$dt = modifiedToMysql($img);
			if ($dryRun) {
				$inserted++;
				$folderInserts++;
				continue;
			}
			if (insertRow($db, $staffId, $fileId, $dirId, $parentId, $dt)) {
				$inserted++;
				$folderInserts++;
			}
		}
	}
	if ($folderInserts > 0) {
		echo "  {$name} (staff {$staffId}): +{$folderInserts} rows\n";
	}
}

$r = $db->query('SELECT COUNT(*) c, MAX(date_time) mx FROM tblstaff_drive_data');
$row = $r->fetch_assoc();
echo "Done. inserted={$inserted} skipped_existing={$skipped} no_staff_folder={$noStaff}\n";
echo 'DB total=' . $row['c'] . ' last=' . $row['mx'] . PHP_EOL;
