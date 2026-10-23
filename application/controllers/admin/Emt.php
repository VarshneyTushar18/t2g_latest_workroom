<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * EMT / Workroom snapshot (Google Drive) controller
 */
class Emt extends AdminController
{
	private const MONITORING_LEGACY_ROOT_FOLDER_ID = '180rTlMbvUBgcD1ET-OmlXl87qNPtLCeS';

	private $client;
	private $service;
	private $driveReady = false;

	/** @var array<string, true>|null */
	private $snapshotAllowedEmployeeFolderIds = null;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Emt_model');

		$autoload = FCPATH . 'google-client/vendor/autoload.php';
		if (!is_file($autoload)) {
			log_message('error', 'Google client autoload missing: ' . $autoload);

			return;
		}

		require_once $autoload;

		try {
			$this->client = new Google_Client();
			$this->client->setAuthConfig($this->resolveMonitoringServiceAccountPath());
			$this->client->setScopes([Google_Service_Drive::DRIVE]);
			$this->service = new Google_Service_Drive($this->client);
			$this->driveReady = true;
		} catch (Exception $e) {
			log_message('error', 'Emt Google Drive init: ' . $e->getMessage());
		}
	}

	public function index()
	{
		echo 'testdd';
	}

	public function getEmployeeId()
	{
		$this->load->helper('custom');
		$response_parent = [
			'drive_error' => null,
			'drive_hint'  => null,
		];

		if (!$this->driveReady) {
			$response_parent['drive_error'] = 'Google Drive is not configured.';
		}

		$this->load->model('departments_model');
		$response_parent['departments'] = $this->snapshotDepartmentsForViewer();
		$response_parent['snapshot_view_all_departments'] = snapshot_can_view_all_departments();
		$metrics = $this->buildSnapshotDashboardMetrics();
		if ($this->driveReady && empty($response_parent['drive_error']) && (int) ($metrics['employee_folder_count'] ?? 0) === 0) {
			$response_parent['drive_hint'] = $this->snapshotEmptyHint();
		}
		$response_parent['snapshot_summary'] = $metrics['summary'];
		$response_parent['snapshot_by_department'] = $metrics['by_department'];
		$response_parent['employee_folder_count'] = (int) ($metrics['employee_folder_count'] ?? 0);

		$this->load->view('admin/emt/emt_dashboard', $response_parent);
	}

	/**
	 * View employee snapshot screenshots (Drive gallery).
	 */
	public function monitoring()
	{
		$data = $this->loadSnapshotMonitoringViewData();
		$this->load->view('admin/emt/monitoring', $data);
	}

	/**
	 * Previous completed day: check-in vs snapshot login/logout for all active staff.
	 */
	public function report()
	{
		if (!is_staff_logged_in()) {
			redirect(admin_url('authentication'));
		}

		$this->load->model('departments_model');
		$defaultDate = $this->Emt_model->snapshotDefaultReportDateYmd();
		$reportDate = trim((string) ($this->input->get('date', true) ?: $defaultDate));
		if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $reportDate)) {
			$reportDate = $defaultDate;
		}
		if ($reportDate > $defaultDate) {
			$reportDate = $defaultDate;
		}

		$this->load->helper('custom');
		$scopeIds = $this->snapshotScopeDepartmentIds();
		$departmentId = snapshot_resolve_department_id((int) $this->input->get('department_id'));
		$loginFilter = (string) ($this->input->get('snapshot_login', true) ?: 'all');
		if (!in_array($loginFilter, ['all', 'logged_in', 'not_logged_in'], true)) {
			$loginFilter = 'all';
		}

		$staffFolderMap = [];
		$dayFolderByStaffId = [];
		if ($this->driveReady) {
			try {
				$folderFiles = $this->filterEmployeeFolderFilesForViewer($this->fetchEmployeeFolderFiles());
				$staffFolderMap = $this->Emt_model->buildStaffDriveFolderMap($folderFiles);
				$dayFolderByStaffId = $this->resolveSnapshotDayDriveContext($staffFolderMap, $reportDate)['day_folder_by_staff_id'];
			} catch (Exception $e) {
				log_message('error', 'Emt report Drive: ' . $e->getMessage());
			}
		}

		$rows = $this->Emt_model->getSnapshotDailyReportRows(
			$reportDate,
			$departmentId,
			$dayFolderByStaffId,
			$scopeIds
		);
		$driveTimes = $this->buildSnapshotReportDriveTimes($reportDate, $rows, $staffFolderMap, $dayFolderByStaffId);
		$rows = $this->Emt_model->mergeSnapshotDailyReportDriveTimes($rows, $driveTimes);
		$rows = $this->Emt_model->filterSnapshotDailyReportRows($rows, $loginFilter);

		$data = [
			'title'              => 'Snapshot daily report',
			'report_date'        => $reportDate,
			'report_date_label'  => date('l, F j, Y', strtotime($reportDate . ' 12:00:00')),
			'default_report_date'=> $defaultDate,
			'department_id'      => $departmentId,
			'snapshot_login_filter' => $loginFilter,
			'departments'        => $this->snapshotDepartmentsForViewer(),
			'snapshot_view_all_departments' => snapshot_can_view_all_departments(),
			'rows'               => $rows,
			'dashboard_url'      => admin_url('Emt/getEmployeeId'),
			'report_base_url'    => admin_url('Emt/report'),
		];

		$this->load->view('admin/emt/snapshot_daily_report', $data);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function loadSnapshotMonitoringViewData()
	{
		$data = [
			'result'            => null,
			'drive_error'       => null,
			'drive_hint'        => null,
			'staff_folder_map'  => [],
		];

		if ($this->driveReady) {
			try {
				$folders = $this->filterEmployeeFolderFilesForViewer($this->fetchEmployeeFolderFiles());
				$data['result'] = $this->fileListFromFiles($folders);
				$data['staff_folder_map'] = $this->Emt_model->buildStaffDriveFolderMap($folders);
				if (empty($folders)) {
					$data['drive_hint'] = $this->snapshotEmptyHint();
				}
			} catch (Exception $e) {
				$data['drive_error'] = $e->getMessage();
				log_message('error', 'monitoring: ' . $e->getMessage());
			}
		} else {
			$data['drive_error'] = 'Google Drive is not configured.';
		}

		$this->load->helper('custom');
		$data['departments'] = $this->snapshotDepartmentsForViewer();
		$data['snapshot_view_all_departments'] = snapshot_can_view_all_departments();
		$data['folder_departments'] = $this->Emt_model->getFolderDepartmentMap();
		$data['dashboard_url'] = admin_url('Emt/getEmployeeId');

		return $data;
	}

	/**
	 * JSON summary counts for Dashboard stat cards.
	 */
	public function snapshot_dashboard_summary()
	{
		header('Content-Type: application/json');
		if (!is_staff_logged_in()) {
			echo json_encode(['success' => false]);

			return;
		}
		$this->load->helper('custom');
		$departmentId = snapshot_resolve_department_id((int) $this->input->get('department_id'));
		$metrics = $this->buildSnapshotDashboardMetrics($departmentId);
		echo json_encode([
			'success' => true,
			'summary' => $metrics['summary'],
		]);
	}

	/**
	 * JSON employee list for Dashboard stat drill-down.
	 */
	public function snapshot_dashboard_staff()
	{
		header('Content-Type: application/json');
		if (!is_staff_logged_in()) {
			echo json_encode(['success' => false, 'staff' => []]);

			return;
		}
		$this->load->helper('custom');
		$type = $this->input->get('type', true) ?: 'active';
		$departmentId = snapshot_resolve_department_id((int) $this->input->get('department_id'));
		$metrics = $this->buildSnapshotDashboardMetrics($departmentId);
		echo json_encode([
			'success' => true,
			'type'    => $type,
			'staff'   => $this->loadSnapshotStaffList($type, $departmentId ?: null, $metrics),
		]);
	}

	/**
	 * Full-page employee list for snapshot stat cards (replaces modal drill-down).
	 */
	public function snapshot_staff_list()
	{
		$this->renderSnapshotStaffListPage();
	}

	/**
	 * CamelCase alias (matches getEmployeeId routing style).
	 */
	public function getSnapshotStaffList()
	{
		$this->renderSnapshotStaffListPage();
	}

	private function renderSnapshotStaffListPage()
	{
		if (!is_staff_logged_in()) {
			redirect(admin_url('authentication'));
		}

		$type = (string) ($this->input->get('type', true) ?: 'active');
		if (!in_array($type, ['active', 'logged_in', 'not_logged_in'], true)) {
			$type = 'active';
		}
		$this->load->helper('custom');
		$departmentId = snapshot_resolve_department_id((int) $this->input->get('department_id'));

		$titles = [
			'active'        => 'Total active employees',
			'logged_in'     => "Today's snapshot logged-in",
			'not_logged_in' => 'Not logged-in today',
		];

		$metrics = $this->buildSnapshotDashboardMetrics($departmentId);

		$data = [
			'title'             => $titles[$type] ?? 'Snapshot employees',
			'list_type'         => $type,
			'department_id'     => max(0, $departmentId),
			'departments'       => $this->snapshotDepartmentsForViewer(),
			'snapshot_view_all_departments' => snapshot_can_view_all_departments(),
			'staff'             => $this->loadSnapshotStaffList($type, $departmentId, $metrics),
			'staff_folder_map'  => $metrics['staff_folder_map'],
			'dashboard_url'     => admin_url('Emt/getEmployeeId'),
			'monitoring_url'    => admin_url('Emt/monitoring'),
			'list_base_url'     => admin_url('Emt/getSnapshotStaffList'),
		];

		$this->load->view('admin/emt/snapshot_staff_list', $data);
	}

	public function snapshotImage()
	{
		while (ob_get_level() > 0) {
			ob_end_clean();
		}

		if (!$this->driveReady) {
			$this->snapshotImageError(503);

			return;
		}

		$fileId = $this->input->get('id', true);
		if (!$fileId || !preg_match('/^[a-zA-Z0-9_-]{10,}$/', $fileId)) {
			$this->snapshotImageError(404);

			return;
		}

		$this->load->helper('custom');
		if (!snapshot_can_view_all_departments() && !$this->snapshotDriveResourceAccessible($fileId)) {
			$this->snapshotImageError(403);

			return;
		}

		try {
			$meta = $this->service->files->get($fileId, [
				'supportsAllDrives' => true,
				'fields'            => 'mimeType,name',
			]);
			$mime = $meta->getMimeType() ?: 'image/png';
			$name = $meta->getName() ?: '';
			if (!$this->isSnapshotImageMime($mime, $name)) {
				$this->snapshotImageError(404);

				return;
			}

			if (!$this->streamDriveFileContents($fileId, $mime)) {
				$this->snapshotImageError(502);
			}
		} catch (Throwable $e) {
			log_message('error', 'snapshotImage: ' . $e->getMessage());
			$this->snapshotImageError(502);
		}
	}

	public function getDownload()
	{
		$img_id = $_POST['date'];
		$text = $_POST['time'];
		$image_data = $this->downloadDriveFileContents($img_id);
		if ($image_data === false || $image_data === '') {
			show_error('Unable to download image from Drive.');

			return;
		}
		$image = @imagecreatefromstring($image_data);
		if ($image === false) {
			show_error('Invalid image data from Drive.');

			return;
		}
		$image_width = imagesx($image);
		$image_height = imagesy($image);
		$strip_height = 50;
		$new_height = $image_height + $strip_height;
		$new_image = imagecreatetruecolor($image_width, $new_height);
		imagecolorallocate($new_image, 255, 255, 255);
		imagecopy($new_image, $image, 0, $strip_height, 0, 0, $image_width, $image_height);
		$font_size = 100;
		$text_color = imagecolorallocate($new_image, 255, 255, 255);
		$text_width = imagefontwidth($font_size) * strlen($text);
		$text_x = ($image_width - $text_width) / 2;
		$text_y = ($strip_height - imagefontheight($font_size)) / 2;
		imagestring($new_image, $font_size, $text_x, $text_y, $text, $text_color);

		header('Content-Type: image/png');
		header('Content-Disposition: attachment; filename=$text');
		imagepng($new_image);
		imagedestroy($new_image);
	}

	public function listInsideFolderByName($folderName = 'ishita.rathi')
	{
		if (!$this->driveReady) {
			echo 'Google Drive is not configured.';

			return;
		}

		try {
			$folder = $this->findEmployeeFolderByName($folderName);
			if (!$folder) {
				echo "No folder found with name: {$folderName}";

				return;
			}

			$folderId = $folder->getId();
			$childResponse = $this->service->files->listFiles($this->driveListParams([
				'q'      => "'" . $this->escapeDriveQuery($folderId) . "' in parents and trashed=false",
				'fields' => 'files(id, name, mimeType)',
			]));

			echo "<h3>Contents inside '{$folderName}' (ID: {$folderId}):</h3><pre>";
			print_r($childResponse->getFiles());
			echo '</pre>';
		} catch (Exception $e) {
			echo 'Error: ' . $e->getMessage();
		}
	}

	public function getEmployeeIds()
	{
		$this->getEmployeeId();
	}

	public function getEmployeeDate()
	{
		header('Content-Type: application/json');
		if (!$this->driveReady) {
			echo json_encode([]);

			return;
		}

		$parentFolderId = $this->input->get('date', true);
		if (!$parentFolderId) {
			echo json_encode([]);

			return;
		}

		$this->load->helper('custom');
		if (!$this->snapshotDriveResourceAccessible($parentFolderId)) {
			echo json_encode([]);

			return;
		}

		try {
			echo json_encode($this->collectSnapshotDays($parentFolderId));
		} catch (Exception $e) {
			log_message('error', 'getEmployeeDate: ' . $e->getMessage());
			echo json_encode([]);
		}
	}

	public function getImg()
	{
		header('Content-Type: application/json');
		if (!$this->driveReady) {
			echo json_encode([]);

			return;
		}

		$childFolderId = $this->input->get('date', true);
		$dayFilter = $this->input->get('day_filter', true);
		if (!$childFolderId) {
			echo json_encode([]);

			return;
		}

		$this->load->helper('custom');
		if (!$this->snapshotDriveResourceAccessible($childFolderId)) {
			echo json_encode([]);

			return;
		}

		try {
			if ($dayFilter !== null && $dayFilter !== '') {
				echo json_encode($this->listSnapshotImagesFiltered($childFolderId, $dayFilter));

				return;
			}

			$list = $this->service->files->listFiles($this->driveListParams([
				'q'       => "'" . $this->escapeDriveQuery($childFolderId) . "' in parents and trashed=false",
				'fields'  => 'nextPageToken, files(id, name)',
				'orderBy' => 'name',
			]));
			$files = $this->fileListToArray($list);
			if (empty($files)) {
				$files = $this->listSnapshotImagesFiltered($childFolderId, null);
			}
			echo json_encode($files);
		} catch (Exception $e) {
			log_message('error', 'getImg: ' . $e->getMessage());
			echo json_encode([]);
		}
	}

	public function EmtInterval()
	{
		$this->load->helper('custom');
		if (!can_manage_snapshot_interval()) {
			access_denied('Emt');
		}

		$this->Emt_model->ensureStaffIntervalRows();
		$this->load->model('departments_model');
		$emt['result'] = $this->Emt_model->get();
		$emt['snapshot_interval_label'] = function_exists('snapshot_capture_interval_time')
			? snapshot_capture_interval_time()
			: '00:10:00';
		$emt['departments'] = $this->departments_model->get();
		$emt['can_edit_interval'] = true;
		$emt['interval_presets'] = [
			'00:05:00' => '5 minutes',
			'00:10:00' => '10 minutes (default)',
			'00:15:00' => '15 minutes',
			'00:20:00' => '20 minutes',
			'00:30:00' => '30 minutes',
		];
		$this->load->view('admin/emt/emtinterval', $emt);
	}

	public function EmtUpdateInterval()
	{
		$this->load->helper('custom');
		if (!can_manage_snapshot_interval()) {
			ajax_access_denied();
		}

		if (strtolower($this->input->method()) !== 'post') {
			redirect(admin_url('Emt/EmtInterval'));
		}

		$timeRaw = $this->input->post('time');
		$time = snapshot_normalize_interval_time($timeRaw);
		if ($time === false) {
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode(['success' => false, 'message' => 'Invalid interval (use 1–60 minutes).']));

			return;
		}

		$ids = $this->input->post('ids');
		if (is_array($ids) && count($ids) > 0) {
			$updated = $this->Emt_model->updateIntervalsBulk($ids, $time);
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode([
					'success' => $updated > 0,
					'updated' => $updated,
					'time'    => $time,
				]));

			return;
		}

		$ok = $this->Emt_model->updateInterval($this->input->post('date'), $time);
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(['success' => (bool) $ok, 'time' => $time]));
	}

	/**
	 * JSON for Workroom popup when snapshot monitor is not logged in after check-in.
	 */
	public function snapshot_login_reminder()
	{
		redirect(admin_url('staff/snapshot_login_reminder'));
	}

	public function EmtDeleteId()
	{
		$response_parents = ['result' => null, 'drive_error' => null];
		if ($this->driveReady) {
			try {
				$response_parents['result'] = $this->fileListFromFiles($this->fetchEmployeeFolderFiles());
			} catch (Exception $e) {
				$response_parents['drive_error'] = $e->getMessage();
			}
		}
		$this->load->view('admin/emt/emt_delete_id', $response_parents);
	}

	public function EmtDeleteDate()
	{
		$parentFolderId = $_GET['date'];
		$response_child['res_child'] = null;
		if ($this->driveReady && $parentFolderId) {
			try {
				$response_child['res_child'] = $this->service->files->listFiles($this->driveListParams([
					'q'      => "'" . $this->escapeDriveQuery($parentFolderId) . "' in parents and trashed=false",
					'fields' => 'files(id, name)',
				]));
			} catch (Exception $e) {
				log_message('error', 'EmtDeleteDate: ' . $e->getMessage());
			}
		}
		$this->load->view('admin/emt/emt_delete_day', $response_child);
	}

	public function EmtDeleteDateDay()
	{
		$this->EmtDeleteDate();
	}

	public function EmtDeleteDateDayName()
	{
		$parentFolderId = $_POST['date'];
		if (!$this->driveReady || !$parentFolderId) {
			echo 'Google Drive is not configured.';

			return;
		}

		try {
			$response_child = $this->service->files->listFiles($this->driveListParams([
				'q'      => "'" . $this->escapeDriveQuery($parentFolderId) . "' in parents and mimeType='application/vnd.google-apps.folder' and trashed=false",
				'fields' => 'files(id, name)',
			]));
			$files = $response_child->getFiles();
			if (empty($files)) {
				echo 'No folder to delete.';

				return;
			}
			$file_id = $files[0]->getId();
			$this->service->files->delete($file_id, ['supportsAllDrives' => true]);
			echo 'Folder deleted successfully!';
		} catch (Exception $e) {
			echo 'Error deleting folder: ' . $e->getMessage();
		}
	}

	public function getDepartment()
	{
		redirect(admin_url('Emt/getEmployeeId'));
	}

	public function getEmployeedepartment()
	{
		$this->load->helper('custom');
		$staffid = (int) ($_GET['staffid'] ?? 0);
		if ($staffid < 1 || !snapshot_staff_id_in_viewer_scope($staffid)) {
			echo json_encode([]);

			return;
		}
		$sql = "SELECT staff_id,file_id,directory_id,parent_directory_id,status,date_time from tblstaff_drive_data where staff_id='$staffid' GROUP BY DATE_FORMAT(date_time, '%Y-%m-%d') ORDER by date_time desc";
		$query = $this->db->query($sql);
		echo json_encode($query->result());
	}

	public function getImgDepartment()
	{
		header('Content-Type: application/json');
		$childFolderId = $_GET['date'];
		if (!$this->driveReady || !$childFolderId) {
			echo json_encode([]);

			return;
		}

		$this->load->helper('custom');
		if (!snapshot_can_view_all_departments() && !$this->snapshotDriveResourceAccessible($childFolderId)) {
			echo json_encode([]);

			return;
		}

		try {
			$list = $this->service->files->listFiles($this->driveListParams([
				'q'      => "'" . $this->escapeDriveQuery($childFolderId) . "' in parents and trashed=false",
				'fields' => 'nextPageToken, files(id, name)',
			]));
			echo json_encode($this->fileListToArray($list));
		} catch (Exception $e) {
			echo json_encode([]);
		}
	}

	private function snapshotImageError($code)
	{
		http_response_code((int) $code);
		header('Content-Type: text/plain');
		echo 'Image unavailable';
		exit;
	}

	private function isSnapshotImageMime($mime, $name)
	{
		if (preg_match('/\.(png|jpe?g|webp|gif|bmp)$/i', (string) $name)) {
			return true;
		}

		return stripos((string) $mime, 'image/') === 0;
	}

	private function streamDriveFileContents($fileId, $mime)
	{
		try {
			$http = $this->client->authorize();
			$url = 'https://www.googleapis.com/drive/v3/files/' . rawurlencode($fileId)
				. '?alt=media&supportsAllDrives=true';
			$response = $http->request('GET', $url, ['stream' => true, 'http_errors' => false]);
			if ($response->getStatusCode() !== 200) {
				return false;
			}
			header('Content-Type: ' . $mime);
			header('Cache-Control: private, max-age=1800');
			$body = $response->getBody();
			while (!$body->eof()) {
				echo $body->read(8192);
			}
			exit;
		} catch (Throwable $e) {
			log_message('error', 'streamDriveFileContents: ' . $e->getMessage());

			return false;
		}
	}

	private function downloadDriveFileContents($fileId)
	{
		try {
			$http = $this->client->authorize();
			$url = 'https://www.googleapis.com/drive/v3/files/' . rawurlencode($fileId)
				. '?alt=media&supportsAllDrives=true';
			$response = $http->request('GET', $url, ['http_errors' => false]);
			if ($response->getStatusCode() !== 200) {
				return false;
			}

			return (string) $response->getBody();
		} catch (Throwable $e) {
			return false;
		}
	}

	private function resolveMonitoringServiceAccountPath()
	{
		$candidates = [
			FCPATH . 'google-client/t2g-monitoring-37bb12065f7f.json',
			FCPATH . 'google-client/t2g-monitoring-e410bbe96b94.json',
			FCPATH . 'google-client/t2gemt-monitoring-c3fa0b6cc3dc.json',
		];
		foreach ($candidates as $path) {
			if (is_readable($path)) {
				return $path;
			}
		}

		throw new RuntimeException('No Google monitoring service account JSON found.');
	}

	private function driveListParams(array $overrides = [])
	{
		return array_merge([
			'corpora'                   => 'allDrives',
			'supportsAllDrives'         => true,
			'includeItemsFromAllDrives' => true,
			'spaces'                    => 'drive',
		], $overrides);
	}

	private function employeeFolderNameFilters()
	{
		return "and not name contains 'png' and not name contains '202' and not name contains 'New Folder'";
	}

	private function employeeFoldersUnderParentQuery($parentId)
	{
		$parent = $this->escapeDriveQuery($parentId);

		return "mimeType='application/vnd.google-apps.folder' and trashed=false and '{$parent}' in parents "
			. $this->employeeFolderNameFilters();
	}

	private function employeeFoldersLegacyQuery()
	{
		return "mimeType='application/vnd.google-apps.folder' and trashed=false "
			. $this->employeeFolderNameFilters();
	}

	private function listFilesAllPages($query, $fields = 'files(id, name)')
	{
		$merged = [];
		$pageToken = null;
		do {
			$result = $this->service->files->listFiles($this->driveListParams([
				'q'         => $query,
				'fields'    => 'nextPageToken, ' . $fields,
				'orderBy'   => 'name',
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

	private function fetchEmployeeFolderFiles()
	{
		$byName = [];
		$queries = [$this->employeeFoldersLegacyQuery()];
		if (self::MONITORING_LEGACY_ROOT_FOLDER_ID !== '') {
			$queries[] = $this->employeeFoldersUnderParentQuery(self::MONITORING_LEGACY_ROOT_FOLDER_ID);
		}
		foreach ($queries as $query) {
			foreach ($this->listFilesAllPages($query) as $file) {
				if (!$this->isEmployeeSnapshotFolderName($file->getName())) {
					continue;
				}
				$byName[strtolower($file->getName())] = $file;
			}
		}
		$merged = array_values($byName);
		usort($merged, function ($a, $b) {
			return strcasecmp($a->getName(), $b->getName());
		});

		return $merged;
	}

	private function isEmployeeSnapshotFolderName($name)
	{
		$name = trim((string) $name);
		if ($name === '' || strcasecmp($name, 'employees') === 0) {
			return false;
		}
		if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $name)) {
			return false;
		}

		return true;
	}

	private function fileListFromFiles(array $files)
	{
		$list = new \Google\Service\Drive\FileList();
		$list->setFiles($files);

		return $list;
	}

	private function snapshotEmptyHint()
	{
		return 'Drive is connected but no employee folders were returned. Share each employee snapshot folder with t2gworkroom@t2g-monitoring.iam.gserviceaccount.com (Viewer or higher). Folder names should match the email prefix (e.g. firstname.lastname).';
	}

	private function escapeDriveQuery($value)
	{
		return str_replace("'", "\\'", (string) $value);
	}

	/**
	 * Dashboard counts: DB activity + Drive folders dated today (matches gallery).
	 *
	 * @return array{summary:array,by_department:array,staff_folder_map:array,drive_logged_staff_ids:array,today_folder_by_staff_id:array}
	 */
	private function buildSnapshotDashboardMetrics($departmentId = null)
	{
		$this->load->helper('custom');
		if ($departmentId === null) {
			$departmentId = 0;
		}
		$departmentId = (int) $departmentId;
		$scopeIds = $this->snapshotScopeDepartmentIds();

		$folderFiles = [];
		$staffFolderMap = [];
		$driveLoggedStaffIds = [];
		$todayFolderByStaffId = [];
		if ($this->driveReady) {
			try {
				$folderFiles = $this->filterEmployeeFolderFilesForViewer($this->fetchEmployeeFolderFiles());
				$staffFolderMap = $this->Emt_model->buildStaffDriveFolderMap($folderFiles);
				$todayCtx = $this->resolveSnapshotTodayDriveContext($staffFolderMap);
				$driveLoggedStaffIds = $todayCtx['drive_logged_staff_ids'];
				$todayFolderByStaffId = $todayCtx['today_folder_by_staff_id'];
				if ($scopeIds !== null) {
					$allowedStaff = $this->Emt_model->getActiveStaffIdsInDepartmentScope($scopeIds);
					$driveLoggedStaffIds = array_values(array_filter($driveLoggedStaffIds, function ($sid) use ($allowedStaff) {
						return isset($allowedStaff[(int) $sid]);
					}));
					$todayFolderByStaffId = array_filter(
						$todayFolderByStaffId,
						function ($folderId, $sid) use ($allowedStaff) {
							return isset($allowedStaff[(int) $sid]);
						},
						ARRAY_FILTER_USE_BOTH
					);
				}
			} catch (Exception $e) {
				log_message('error', 'buildSnapshotDashboardMetrics: ' . $e->getMessage());
			}
		}

		$deptFilter = $departmentId === 0 ? null : $departmentId;

		return [
			'summary'                    => $this->Emt_model->getSnapshotDashboardSummary($deptFilter, $staffFolderMap, $driveLoggedStaffIds, $scopeIds),
			'by_department'              => $this->Emt_model->getSnapshotDashboardSummaryByDepartment($staffFolderMap, $driveLoggedStaffIds, $scopeIds),
			'staff_folder_map'           => $staffFolderMap,
			'drive_logged_staff_ids'     => $driveLoggedStaffIds,
			'today_folder_by_staff_id'   => $todayFolderByStaffId,
			'employee_folder_count'      => count($folderFiles),
			'scope_department_ids'       => $scopeIds,
			'department_id'              => $departmentId,
		];
	}

	/**
	 * @param array<int, string> $staffIdToFolderId
	 * @return array{drive_logged_staff_ids:array<int,int>,today_folder_by_staff_id:array<int,string>}
	 */
	private function resolveSnapshotTodayDriveContext(array $staffIdToFolderId)
	{
		[$todayStart] = $this->Emt_model->snapshotTodayDatetimeBounds();
		$ctx = $this->resolveSnapshotDayDriveContext($staffIdToFolderId, substr($todayStart, 0, 10));

		return [
			'drive_logged_staff_ids'   => array_keys($ctx['day_folder_by_staff_id']),
			'today_folder_by_staff_id' => $ctx['day_folder_by_staff_id'],
		];
	}

	/**
	 * Employee folders that contain a date-named subfolder (Y-m-d) for snapshot activity.
	 *
	 * @param array<int, string> $staffIdToFolderId
	 * @return array{day_folder_by_staff_id:array<int,string>}
	 */
	private function resolveSnapshotDayDriveContext(array $staffIdToFolderId, $dateYmd)
	{
		$dateYmd = trim((string) $dateYmd);
		if (!$this->driveReady || empty($staffIdToFolderId) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateYmd)) {
			return ['day_folder_by_staff_id' => []];
		}

		$folderIdToStaffId = [];
		foreach ($staffIdToFolderId as $staffId => $folderId) {
			$folderId = trim((string) $folderId);
			if ($folderId !== '') {
				$folderIdToStaffId[$folderId] = (int) $staffId;
			}
		}
		if (empty($folderIdToStaffId)) {
			return ['day_folder_by_staff_id' => []];
		}

		$query = "mimeType='application/vnd.google-apps.folder' and trashed=false and name='"
			. $this->escapeDriveQuery($dateYmd) . "'";
		$dayFolderByStaffId = [];
		foreach ($this->listFilesAllPages($query, 'files(id, parents)') as $file) {
			$parents = $file->getParents();
			if (!is_array($parents)) {
				continue;
			}
			$dayFolderId = $file->getId();
			foreach ($parents as $parentId) {
				if (isset($folderIdToStaffId[$parentId])) {
					$dayFolderByStaffId[$folderIdToStaffId[$parentId]] = $dayFolderId;
				}
			}
		}

		return ['day_folder_by_staff_id' => $dayFolderByStaffId];
	}

	/**
	 * First/last screenshot times from Drive for report rows still missing DB times.
	 *
	 * @param array<int, array<string, mixed>> $rows
	 * @param array<int, string> $staffFolderMap
	 * @param array<int, string> $dayFolderByStaffId
	 * @return array<int, array{login:string,logout:string}>
	 */
	private function buildSnapshotReportDriveTimes($reportDateYmd, array $rows, array $staffFolderMap, array $dayFolderByStaffId)
	{
		if (!$this->driveReady || empty($rows)) {
			return [];
		}

		$dayCompact = preg_replace('/[^0-9]/', '', (string) $reportDateYmd);
		$times = [];

		foreach ($rows as $row) {
			$sid = (int) ($row['staffid'] ?? 0);
			if ($sid < 1) {
				continue;
			}
			$hasLogin = trim((string) ($row['snapshot_login_raw'] ?? '')) !== '';
			$hasLogout = trim((string) ($row['snapshot_logout_raw'] ?? '')) !== '';
			if ($hasLogin && $hasLogout) {
				continue;
			}

			$dayFolderId = trim((string) ($dayFolderByStaffId[$sid] ?? ''));
			$employeeFolderId = trim((string) ($staffFolderMap[$sid] ?? ''));
			if ($dayFolderId === '' && $employeeFolderId === '') {
				continue;
			}

			$images = [];
			if ($dayFolderId !== '') {
				$images = $this->listSnapshotImageFilesFromDrive($dayFolderId, null);
			}
			if (empty($images) && $employeeFolderId !== '') {
				$images = $this->listSnapshotImageFilesFromDrive($employeeFolderId, $dayCompact);
			}
			if (empty($images)) {
				continue;
			}

			usort($images, function ($a, $b) {
				return strcmp($a['created_time'], $b['created_time']);
			});

			$alignMin = 0;
			$checkinRaw = trim((string) ($row['first_checkin_raw'] ?? ''));
			if ($checkinRaw !== '') {
				$checkinTs = is_numeric($checkinRaw) ? (int) $checkinRaw : strtotime($checkinRaw);
				if ($checkinTs) {
					$alignMin = max(0, $checkinTs - (30 * 60));
				}
			}
			if ($alignMin > 0) {
				$images = array_values(array_filter($images, function ($img) use ($alignMin, $reportDateYmd) {
					$ts = strtotime((string) ($img['created_time'] ?? ''));
					if (!$ts) {
						$ts = $this->snapshotTimeFromImageFileName($img['name'] ?? '', $reportDateYmd);
					}

					return $ts && $ts >= $alignMin;
				}));
				if (empty($images)) {
					continue;
				}
			}

			$firstTs = strtotime($images[0]['created_time']);
			$lastTs = strtotime($images[count($images) - 1]['created_time']);
			if (!$firstTs) {
				$firstTs = $this->snapshotTimeFromImageFileName($images[0]['name'], $reportDateYmd);
			}
			if (!$lastTs) {
				$lastTs = $this->snapshotTimeFromImageFileName($images[count($images) - 1]['name'], $reportDateYmd);
			}
			if (!$firstTs && !$lastTs) {
				continue;
			}
			if (!$lastTs) {
				$lastTs = $firstTs;
			}
			if (!$firstTs) {
				$firstTs = $lastTs;
			}

			$times[$sid] = [
				'login'  => date('Y-m-d H:i:s', $firstTs),
				'logout' => date('Y-m-d H:i:s', $lastTs),
			];
		}

		return $times;
	}

	/**
	 * Parse HH:MM from snapshot filename token (e.g. name_20261010_143022.jpg).
	 */
	private function snapshotTimeFromImageFileName($fileName, $reportDateYmd)
	{
		$fileName = (string) $fileName;
		if (!preg_match('/(\d{1,2})[:\-]?(\d{2})[:\-]?(\d{2})/', $fileName, $m)) {
			return 0;
		}
		$hour = (int) $m[1];
		$min = (int) $m[2];
		$sec = isset($m[3]) ? (int) $m[3] : 0;

		return strtotime($reportDateYmd . sprintf(' %02d:%02d:%02d', $hour, $min, $sec));
	}

	/**
	 * @param array<string, mixed> $metrics from buildSnapshotDashboardMetrics()
	 */
	private function loadSnapshotStaffList($type, $departmentId, array $metrics)
	{
		$deptFilter = ($departmentId === null || (int) $departmentId === 0) ? null : (int) $departmentId;
		$scopeIds = $metrics['scope_department_ids'] ?? $this->snapshotScopeDepartmentIds();
		$staff = $this->Emt_model->getSnapshotDashboardStaffList(
			$type,
			$deptFilter,
			$metrics['staff_folder_map'] ?? [],
			$metrics['drive_logged_staff_ids'] ?? [],
			$metrics['today_folder_by_staff_id'] ?? [],
			$scopeIds
		);

		return $this->enrichSnapshotStaffListFromDrive(
			$staff,
			$metrics['staff_folder_map'] ?? [],
			$metrics['today_folder_by_staff_id'] ?? []
		);
	}

	/**
	 * Fill login time and screenshot from Drive when tblstaff_drive_data has no usable row for today.
	 *
	 * @param array<int, array<string, mixed>> $rows
	 * @param array<int, string> $staffFolderMap
	 * @param array<int, string> $todayFolderByStaffId
	 * @return array<int, array<string, mixed>>
	 */
	private function enrichSnapshotStaffListFromDrive(array $rows, array $staffFolderMap, array $todayFolderByStaffId)
	{
		if (!$this->driveReady || empty($rows)) {
			return $rows;
		}

		[$todayStart] = $this->Emt_model->snapshotTodayDatetimeBounds();
		$todayLabel = substr($todayStart, 0, 10);
		$todayCompact = preg_replace('/[^0-9]/', '', $todayLabel);

		foreach ($rows as &$row) {
			$needsLogin = ($row['login_time'] ?? '') === '';
			$needsShot = ($row['screenshot_file_id'] ?? '') === '';
			if (!$needsLogin && !$needsShot) {
				continue;
			}

			$sid = (int) ($row['staffid'] ?? 0);
			$dayFolderId = trim((string) ($todayFolderByStaffId[$sid] ?? ''));
			$employeeFolderId = trim((string) ($staffFolderMap[$sid] ?? ''));

			$images = [];
			if ($dayFolderId !== '') {
				$images = $this->listSnapshotImageFilesFromDrive($dayFolderId, null);
			}
			if (empty($images) && $employeeFolderId !== '') {
				$images = $this->listSnapshotImageFilesFromDrive($employeeFolderId, $todayCompact);
			}
			if (empty($images)) {
				continue;
			}

			usort($images, function ($a, $b) {
				return strcmp($a['created_time'], $b['created_time']);
			});

			if ($needsLogin) {
				$row['login_time_raw'] = $images[0]['created_time'];
				$row['login_time'] = $this->formatSnapshotLoginTime($images[0]['created_time'], $images[0]['name']);
			}
			if ($needsShot) {
				$latest = $images[count($images) - 1];
				$row['screenshot_file_id'] = $latest['id'];
			}
		}
		unset($row);

		return $rows;
	}

	/**
	 * @return array<int, array{id:string,name:string,created_time:string}>
	 */
	private function listSnapshotImageFilesFromDrive($folderId, $dayFilterCompact)
	{
		$folderId = trim((string) $folderId);
		if ($folderId === '') {
			return [];
		}

		$query = "'" . $this->escapeDriveQuery($folderId) . "' in parents and trashed=false";
		$files = [];
		foreach ($this->listFilesAllPages($query, 'files(id, name, mimeType, createdTime)') as $item) {
			$name = $item->getName();
			if (!$this->isSnapshotImageMime($item->getMimeType(), $name)) {
				continue;
			}
			if ($dayFilterCompact !== null && $dayFilterCompact !== '') {
				if (stripos($name, (string) $dayFilterCompact) === false) {
					continue;
				}
			}
			$created = (string) $item->getCreatedTime();
			$files[] = [
				'id'           => $item->getId(),
				'name'         => $name,
				'created_time' => $created,
			];
		}

		return $files;
	}

	private function formatSnapshotLoginTime($isoTime, $fileName)
	{
		$ts = strtotime((string) $isoTime);
		if ($ts) {
			return date('g:i A', $ts);
		}
		if (preg_match('/(\d{1,2})[:\-](\d{2})/', (string) $fileName, $m)) {
			return date('g:i A', strtotime($m[1] . ':' . $m[2]));
		}

		return '';
	}

	private function fileListToArray($fileList)
	{
		$files = [];
		if ($fileList && method_exists($fileList, 'getFiles')) {
			foreach ($fileList->getFiles() as $f) {
				$files[] = [
					'id'   => $f->getId(),
					'name' => $f->getName(),
				];
			}
		}

		return $files;
	}

	private function collectSnapshotDays($employeeFolderId)
	{
		$employeeFolderId = trim((string) $employeeFolderId);
		$days = [];
		$seen = [];

		foreach ($this->listEmployeeDateFoldersFromDrive($employeeFolderId) as $day) {
			if (isset($seen[$day['id']])) {
				continue;
			}
			$seen[$day['id']] = true;
			$days[] = $day;
		}

		foreach ($this->getSnapshotDaysFromDb($employeeFolderId) as $row) {
			if (isset($seen[$row['id']])) {
				continue;
			}
			$seen[$row['id']] = true;
			$days[] = $row;
		}

		if (empty($days)) {
			foreach ($this->snapshotDaysFromImagesInFolder($employeeFolderId) as $row) {
				$key = $row['name'];
				if (isset($seen[$key])) {
					continue;
				}
				$seen[$key] = true;
				$days[] = $row;
			}
		}

		usort($days, function ($a, $b) {
			return strcmp($b['name'], $a['name']);
		});

		return $days;
	}

	private function listEmployeeDateFoldersFromDrive($employeeFolderId)
	{
		$days = [];
		$query = "'" . $this->escapeDriveQuery($employeeFolderId) . "' in parents and trashed=false";
		foreach ($this->listFilesAllPages($query) as $item) {
			$name = trim($item->getName());
			if ($name === '' || preg_match('/\.(png|jpe?g|webp|gif|bmp)$/i', $name)) {
				continue;
			}
			if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $name)) {
				continue;
			}
			$days[] = [
				'id'       => $item->getId(),
				'name'     => $name,
				'date_key' => '',
			];
		}

		return $days;
	}

	private function getSnapshotDaysFromDb($employeeFolderId)
	{
		$out = [];
		if (!$this->db->table_exists('tblstaff_drive_data')) {
			return $out;
		}

		$folderName = $this->getDriveFileName($employeeFolderId);
		$rowsByKey = [];
		$mergeRows = function ($rows) use (&$rowsByKey) {
			foreach ($rows as $row) {
				$dayKey = !empty($row->directory_id)
					? $row->directory_id
					: ('dt-' . date('Y-m-d', strtotime($row->last_dt)));
				if (!isset($rowsByKey[$dayKey])) {
					$rowsByKey[$dayKey] = $row;
				}
			}
		};

		$mergeRows($this->db->query(
			'SELECT directory_id, MAX(date_time) AS last_dt
			 FROM tblstaff_drive_data
			 WHERE parent_directory_id = ?
			 GROUP BY directory_id, DATE(date_time)
			 ORDER BY last_dt DESC',
			[$employeeFolderId]
		)->result());

		$staffId = $this->findStaffIdByDriveFolderName($folderName);
		if ($staffId > 0) {
			$mergeRows($this->db->query(
				'SELECT directory_id, MAX(date_time) AS last_dt
				 FROM tblstaff_drive_data
				 WHERE staff_id = ?
				 GROUP BY directory_id, DATE(date_time)
				 ORDER BY last_dt DESC',
				[$staffId]
			)->result());
		}

		foreach ($rowsByKey as $row) {
			$dirId = trim((string) $row->directory_id);
			$label = $dirId !== '' ? $this->getDriveFileName($dirId) : '';
			if ($label === '') {
				$label = date('Y-m-d', strtotime($row->last_dt));
			}
			$dateKey = '';
			if ($dirId === '' || $dirId === $employeeFolderId) {
				$dirId = $employeeFolderId;
				$dateKey = preg_replace('/[^0-9]/', '', $label);
			}
			$out[] = [
				'id'       => $dirId,
				'name'     => $label,
				'date_key' => $dateKey,
			];
		}

		return $out;
	}

	private function findStaffIdByDriveFolderName($folderName)
	{
		$folderName = strtolower(trim((string) $folderName));
		if ($folderName === '') {
			return 0;
		}
		$row = $this->db->query(
			'SELECT staffid FROM ' . db_prefix() . 'staff
			 WHERE LOWER(SUBSTRING_INDEX(email, \'@\', 1)) = ?
			 LIMIT 1',
			[$folderName]
		)->row();

		return $row ? (int) $row->staffid : 0;
	}

	private function resolveCanonicalEmployeeFolderId($folderId)
	{
		$name = $this->getDriveFileName($folderId);
		if ($name === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $name)) {
			return $folderId;
		}
		$found = $this->findEmployeeFolderByName($name);
		if ($found) {
			return $found->getId();
		}

		return $folderId;
	}

	private function findEmployeeFolderByName($folderName)
	{
		$folderName = trim((string) $folderName);
		if ($folderName === '') {
			return null;
		}
		$escaped = $this->escapeDriveQuery($folderName);
		$query = "mimeType='application/vnd.google-apps.folder' and trashed=false and name='{$escaped}'";
		$matches = $this->listFilesAllPages($query);
		if (empty($matches)) {
			return null;
		}
		if (count($matches) === 1) {
			return $matches[0];
		}
		$best = $matches[0];
		$bestCount = -1;
		foreach ($matches as $candidate) {
			$count = count($this->listEmployeeDateFoldersFromDrive($candidate->getId()));
			if ($count > $bestCount) {
				$bestCount = $count;
				$best = $candidate;
			}
		}

		return $best;
	}

	private function getDriveFileName($fileId)
	{
		if (!$this->driveReady || !$fileId) {
			return '';
		}
		try {
			$f = $this->service->files->get($fileId, [
				'supportsAllDrives' => true,
				'fields'            => 'name',
			]);

			return trim((string) $f->getName());
		} catch (Exception $e) {
			return '';
		}
	}

	private function snapshotDaysFromImagesInFolder($employeeFolderId)
	{
		$days = [];
		$query = "'" . $this->escapeDriveQuery($employeeFolderId) . "' in parents and trashed=false";
		foreach ($this->listFilesAllPages($query, 'files(id, name)') as $item) {
			$name = $item->getName();
			if (!preg_match('/(\d{4}-\d{2}-\d{2})/', $name, $m)) {
				continue;
			}
			$label = $m[1];
			$days[] = [
				'id'       => $employeeFolderId,
				'name'     => $label,
				'date_key' => preg_replace('/[^0-9]/', '', $label),
			];
		}

		return $days;
	}

	private function listSnapshotImagesFiltered($folderId, $dayFilter)
	{
		$folderId = $this->resolveCanonicalEmployeeFolderId($folderId);
		$query = "'" . $this->escapeDriveQuery($folderId) . "' in parents and trashed=false";
		$files = [];
		foreach ($this->listFilesAllPages($query, 'files(id, name, mimeType)') as $item) {
			$name = $item->getName();
			if (!$this->isSnapshotImageMime('', $name)) {
				continue;
			}
			if ($dayFilter !== null && $dayFilter !== '') {
				$compact = preg_replace('/[^0-9]/', '', (string) $dayFilter);
				if ($compact !== '' && stripos($name, $compact) === false && stripos($name, (string) $dayFilter) === false) {
					continue;
				}
			}
			$files[] = [
				'id'   => $item->getId(),
				'name' => $name,
			];
		}

		return $files;
	}

	/**
	 * @return int[]|null
	 */
	private function snapshotScopeDepartmentIds()
	{
		$this->load->helper('custom');

		return snapshot_viewer_department_ids();
	}

	private function snapshotDepartmentsForViewer()
	{
		$this->load->model('departments_model');
		$this->load->helper('custom');

		return filter_snapshot_departments_for_viewer($this->departments_model->get());
	}

	/**
	 * @param array<int, object> $files
	 * @return array<int, object>
	 */
	private function filterEmployeeFolderFilesForViewer(array $files)
	{
		$scopeIds = $this->snapshotScopeDepartmentIds();
		if ($scopeIds === null) {
			return $files;
		}

		$allowedStaff = $this->Emt_model->getActiveStaffIdsInDepartmentScope($scopeIds);
		$filtered = [];
		foreach ($files as $file) {
			if (!is_object($file) || !method_exists($file, 'getName')) {
				continue;
			}
			$staffId = $this->Emt_model->findStaffIdByEmailPrefix($file->getName());
			if ($staffId > 0 && isset($allowedStaff[$staffId])) {
				$filtered[] = $file;
			}
		}

		return $filtered;
	}

	/**
	 * @return array<string, true>
	 */
	private function snapshotAllowedEmployeeFolderIdSet()
	{
		if ($this->snapshotAllowedEmployeeFolderIds !== null) {
			return $this->snapshotAllowedEmployeeFolderIds;
		}

		$set = [];
		if ($this->driveReady) {
			try {
				$folders = $this->filterEmployeeFolderFilesForViewer($this->fetchEmployeeFolderFiles());
				foreach ($this->Emt_model->buildStaffDriveFolderMap($folders) as $folderId) {
					$folderId = trim((string) $folderId);
					if ($folderId !== '') {
						$set[$folderId] = true;
					}
				}
			} catch (Exception $e) {
				log_message('error', 'snapshotAllowedEmployeeFolderIdSet: ' . $e->getMessage());
			}
		}

		$this->snapshotAllowedEmployeeFolderIds = $set;

		return $set;
	}

	private function snapshotStaffIdForEmployeeFolderId($folderId)
	{
		$folderId = trim((string) $folderId);
		if ($folderId === '' || !$this->driveReady) {
			return 0;
		}

		$name = $this->getDriveFileName($folderId);
		if ($name === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $name)) {
			return 0;
		}

		return $this->Emt_model->findStaffIdByEmailPrefix($name);
	}

	private function snapshotDriveResourceAccessible($resourceId)
	{
		$this->load->helper('custom');
		$resourceId = trim((string) $resourceId);
		if ($resourceId === '') {
			return false;
		}

		if (snapshot_can_view_all_departments()) {
			return true;
		}

		$staffId = $this->snapshotStaffIdForEmployeeFolderId($resourceId);
		if ($staffId > 0 && snapshot_staff_id_in_viewer_scope($staffId)) {
			return true;
		}

		$staffId = $this->Emt_model->findStaffIdForDriveFile($resourceId);
		if ($staffId > 0 && snapshot_staff_id_in_viewer_scope($staffId)) {
			return true;
		}

		if (!$this->driveReady) {
			return false;
		}

		try {
			$meta = $this->service->files->get($resourceId, [
				'supportsAllDrives' => true,
				'fields'            => 'parents,name',
			]);
			foreach ((array) $meta->getParents() as $parentId) {
				$parentStaffId = $this->snapshotStaffIdForEmployeeFolderId($parentId);
				if ($parentStaffId > 0 && snapshot_staff_id_in_viewer_scope($parentStaffId)) {
					return true;
				}
			}
		} catch (Exception $e) {
			return false;
		}

		return false;
	}
}
