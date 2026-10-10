<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Recruitment Controller
 */

require_once 'google-client/vendor/autoload.php';


class Emt extends AdminController
{
	private $client;
	private $service;
	public function __construct()
	{
		parent::__construct();
		// $this->load->library('GoogleDriveHelper');
		$this->load->model('Emt_model');
		// $this->load->model('recruitment_model');
		$this->load->config('google_drive');
		$snapshot_key = $this->config->item('snapshot_monitoring_credentials_json')
			?: 'google-client/t2g-monitoring-37bb12065f7f.json';
		$this->client = new Google_Client();
		$this->client->setAuthConfig(FCPATH . $snapshot_key);
		$this->client->addScope(Google_Service_Drive::DRIVE);
		$this->client->setScopes(Google_Service_Drive::DRIVE);
		$this->service = new Google_Service_Drive($this->client);
	}

	
	
	public function index()
	{
		echo 'testdd';
	}
	/**
	 * file
	 * @param  int $id
	 * @param  int $rel_id
	 * @return view
	 */
	
	//public function getFolderId($folderName)
	public function getEmployeeId()
	{
		try {
			$response_parent['result'] = $this->service->files->listFiles([
				'q' => "mimeType='application/vnd.google-apps.folder' and trashed=false and not name contains 'png' and not name contains '202' and not name contains 'New Folder'",
				'spaces' => 'drive',
				'fields' => 'files(id, name)',
				'pageSize' => 200,
				'orderBy' => 'name',
				'supportsAllDrives' => true,
				'includeItemsFromAllDrives' => true,
			]);
		} catch (Exception $e) {
			log_message('error', 'Snapshot dashboard Google Drive error: ' . $e->getMessage());
			set_alert('danger', 'Snapshot dashboard cannot connect to Google Drive. The monitoring service account key may be expired. Ask IT to renew google-client/t2g-monitoring-37bb12065f7f.json.');
			$response_parent['result'] = (object) ['files' => []];
		}

		$this->load->view('admin/emt/emt_dashboard', $response_parent);
	}


	
	public function getDownload()
	{
		$img_id = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($this->input->post('date') ?: ''));
		$text = (string) ($this->input->post('time') ?: '');
		if ($img_id === '') {
			show_404();
			return;
		}

		try {
			$content = $this->service->files->get($img_id, [
				'alt' => 'media',
				'supportsAllDrives' => true,
			]);
			$image_data = $content->getBody()->getContents();
		} catch (Exception $e) {
			log_message('error', 'Snapshot getDownload: ' . $e->getMessage());
			show_404();
			return;
		}

		$image = @imagecreatefromstring($image_data);
		if ($image === false) {
			header('Content-Type: image/png');
			header('Content-Disposition: attachment; filename="snapshot.png"');
			echo $image_data;
			return;
		}

		$image_width = imagesx($image);
		$image_height = imagesy($image);
		$strip_height = 50;
		$new_height = $image_height + $strip_height;
		$new_image = imagecreatetruecolor($image_width, $new_height);
		$white = imagecolorallocate($new_image, 255, 255, 255);
		imagefilledrectangle($new_image, 0, 0, $image_width, $strip_height, $white);
		imagecopy($new_image, $image, 0, $strip_height, 0, 0, $image_width, $image_height);
		$font_size = 5;
		$text_color = imagecolorallocate($new_image, 0, 0, 0);
		$text_width = imagefontwidth($font_size) * strlen($text);
		$text_x = max(0, ($image_width - $text_width) / 2);
		$text_y = max(0, ($strip_height - imagefontheight($font_size)) / 2);
		imagestring($new_image, $font_size, (int) $text_x, (int) $text_y, $text, $text_color);

		header('Content-Type: image/png');
		header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-zA-Z0-9:_-]/', '_', $text) . '.png"');
		imagepng($new_image);
		imagedestroy($new_image);
		imagedestroy($image);
	}
public function listInsideFolderByName($folderName = 'ishita.rathi')
{
    try {
        // STEP 1: Find the folder by name
        $searchResponse = $this->service->files->listFiles([
            'q' => "mimeType = 'application/vnd.google-apps.folder' 
                    and name = '{$folderName}' 
                    and trashed = false",
            'spaces' => 'drive',
            'fields' => 'files(id, name, parents)',
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
        ]);

        $folders = $searchResponse->getFiles();

        if (empty($folders)) {
            echo "No folder found with name: {$folderName}";
            return;
        }

        // take first match (if multiple with same name, adjust as needed)
        $folderId = $folders[0]->getId();

        // STEP 2: List all files/folders inside that folder
        $childResponse = $this->service->files->listFiles([
            'q' => "'{$folderId}' in parents and trashed = false",
            'spaces' => 'drive',
            'fields' => 'files(id, name, mimeType)',
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
        ]);

        $children = $childResponse->getFiles();

        echo "<h3>Contents inside '{$folderName}' (ID: {$folderId}):</h3><pre>";
        print_r($children);
        echo "</pre>";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}


	public function getEmployeeIds()
	    {
		//$parentFolderId = '1WM2uG5w-DBbyMViK5st8bpbSPG8S653m';
		$childFolderId = '180rTlMbvUBgcD1ET-OmlXl87qNPtLCeS';
		$response_parent['results'] = $this->service->files->listFiles([
			'q' => "not name contains 'png' and not name contains '202' and not name contains 'New Folder'",
			'mimeType' => 'application/vnd.google-apps.folder',
			//'q' => "mimeType='application/vnd.google-apps.folder' and 'root' in parents and trashed=false" ,
			'spaces' => 'drive',
			 'fields' => 'files(id, name)'
		]);
			 $this->load->view('admin/emt/emt_dashboard', $response_parent);
		
	}
	/**
	 * List Drive children of a folder. Drive query must be: 'folderId' in parents
	 * (not "parents in folderId", which returns empty for most folders).
	 *
	 * @param string $folder_id
	 * @param int $page_size
	 * @param string|null $extra_q e.g. "mimeType contains 'image/'"
	 */
	private function list_drive_children($folder_id, $page_size = 200, $extra_q = null)
	{
		$folder_id = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $folder_id);
		if ($folder_id === '') {
			return [];
		}

		$q = "'" . $folder_id . "' in parents and trashed=false";
		if ($extra_q) {
			$q .= ' and (' . $extra_q . ')';
		}

		$response = $this->service->files->listFiles([
			'q' => $q,
			'fields' => 'files(id, name, mimeType)',
			'pageSize' => (int) $page_size,
			'orderBy' => 'name desc',
			'supportsAllDrives' => true,
			'includeItemsFromAllDrives' => true,
		]);

		$out = [];
		foreach ($response->getFiles() as $file) {
			$out[] = [
				'id' => $file->getId(),
				'name' => $file->getName(),
				'mimeType' => $file->getMimeType(),
			];
		}

		return $out;
	}

	/**
	 * Stream a Drive file through Workroom (auth required) so thumbnails work
	 * even when public lh3 hotlinks fail.
	 */
	public function viewImg($file_id = '')
	{
		$file_id = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($file_id ?: $this->input->get('id')));
		if ($file_id === '') {
			show_404();
			return;
		}

		try {
			$meta = $this->service->files->get($file_id, [
				'fields' => 'id,name,mimeType',
				'supportsAllDrives' => true,
			]);
			$content = $this->service->files->get($file_id, [
				'alt' => 'media',
				'supportsAllDrives' => true,
			]);
			$mime = $meta->getMimeType() ?: 'image/png';
			$body = $content->getBody()->getContents();

			$this->output
				->set_content_type($mime)
				->set_header('Cache-Control: private, max-age=300')
				->set_output($body);
		} catch (Exception $e) {
			log_message('error', 'Snapshot viewImg: ' . $e->getMessage());
			show_404();
		}
	}

	public function getEmployeeDate()
	{
		try {
			$parentFolderId = $this->input->get('date');
			echo json_encode($this->list_drive_children(
				$parentFolderId,
				200,
				"mimeType='application/vnd.google-apps.folder'"
			));
		} catch (Exception $e) {
			log_message('error', 'Snapshot getEmployeeDate: ' . $e->getMessage());
			echo json_encode([]);
		}
	}
	
	public function getImg()
	{
		try {
			$childFolderId = $this->input->get('date');
			echo json_encode($this->list_drive_children(
				$childFolderId,
				500,
				"mimeType contains 'image/'"
			));
		} catch (Exception $e) {
			log_message('error', 'Snapshot getImg: ' . $e->getMessage());
			echo json_encode([]);
		}
	}
/*	public function nextToken()
	{
		//print_r("hello");die;
		print_r($_POST);die;
		$childFolderId = $_POST['date'];
		$pageToken = null;
		$response['res'] = $this->service->files->listFiles([
			'q' => 'parents in "' . $childFolderId . '"',
			'pageSize'=> 10,
			'pageToken' => $pageToken,
			'fields'=> 'nextPageToken, files(id, name)'
			//'fields' => 'files(id, name)'
		]);
		print_r($response['res']['nextPageToken']);die;
			echo json_encode($response['res']['nextPageToken']);
			// $this->load->view('admin/emt/emtimg_dashboard', $response);
		
	}*/
	public function EmtInterval()
	{
		$emt['result'] = $this->Emt_model->get();
		$this->load->view('admin/emt/emtinterval', $emt);
	}
	
	public function EmtUpdateInterval()
	{
		$id = $_POST['id'];
		$emt['result'] = $this->Emt_model->update();
		$this->load->view('admin/emt/emtinterval', $emt);
	}
	/*public function AddInterval()
	{
		//print_r($_POST);die;
		$staffid = $_POST['staffid'];
		$time = $_POST['time'];
		//$id = $_POST['id'];
		$emt['result'] = $this->Emt_model->addIntervalData();
		$this->load->view('admin/emt/emtinterval', $emt);
	}*/
	
	public function EmtDeleteId()
	{
		//$parentFolderId = '1WM2uG5w-DBbyMViK5st8bpbSPG8S653m';
		$childFolderId = '180rTlMbvUBgcD1ET-OmlXl87qNPtLCeS';
		$response_parents['result'] = $this->service->files->listFiles([
			'q' => "not name contains 'png' and not name contains '202' and not name contains 'New Folder'",
			'mimeType' => 'application/vnd.google-apps.folder',
			//'q' => "mimeType='application/vnd.google-apps.folder' and 'root' in parents and trashed=false" ,
			'spaces' => 'drive',
			 'fields' => 'files(id, name)'
		]);
		//print_r($response_parent);
			 $this->load->view('admin/emt/emt_delete_id', $response_parents);
	}
	public function EmtDeleteDate()
	{
		$parentFolderId = $this->input->get('date');
		$files = $this->list_drive_children($parentFolderId);
		$response_child['res_child'] = (object) ['files' => array_map(function ($f) {
			return (object) $f;
		}, $files)];
		$this->load->view('admin/emt/emt_delete_day', $response_child);
	}
	public function EmtDeleteDateDay()
	{
		$parentFolderId = $this->input->get('date');
		$files = $this->list_drive_children($parentFolderId);
		$response_child['res_child'] = (object) ['files' => array_map(function ($f) {
			return (object) $f;
		}, $files)];
		$this->load->view('admin/emt/emt_delete_day', $response_child);
	}
	
	public function EmtDeleteDateDayName()
	{
		$parentFolderId = $this->input->post('date');
		$files = $this->list_drive_children($parentFolderId, 1);
		if (empty($files[0]['id'])) {
			echo 'No folder found to delete.';
			return;
		}
		$file_id = $files[0]['id'];
		try {
			$this->service->files->delete($file_id, ['supportsAllDrives' => true]);
			echo 'Folder deleted successfully!';
		} catch (Exception $e) {
			echo 'Error deleting folder: ' . $e->getMessage();
		}
	}
	
	public function getDepartment()
	{
		$staff_id = get_staff_user_id();
	$data['result']  = $this->Emt_model->getDepartment();
	$data['dept']  = $this->Emt_model->getEmtDepartmentCount();
	$data['emp_not_loggedin']  = $this->Emt_model->getEmpStaffNotLoggedin();
	$data['emp_yesterday_count']  = $this->Emt_model->getEmpStaffYesterdayLoggedin();
	//echo "<pre>"; print_r($data);die;
	$this->load->view('admin/emt/emt_department', $data);
	}
	public function getEmployeedepartment()
	{
		$staffid = $_GET['staffid'];

		//echo "SELECT staff_id,file_id,directory_id,parent_directory_id,status,date_time from tblstaff_drive_data where staff_id='$staffid' GROUP BY DATE_FORMAT(date_time, '%Y-%m-%d') ";   die;
		$sql = "SELECT sdd.staff_id, sdd.file_id, sdd.directory_id, sdd.parent_directory_id, sdd.status, sdd.date_time
			FROM tblstaff_drive_data sdd
			INNER JOIN (
				SELECT DATE_FORMAT(date_time, '%Y-%m-%d') AS day_key, MAX(date_time) AS max_dt
				FROM tblstaff_drive_data
				WHERE staff_id = ?
				GROUP BY DATE_FORMAT(date_time, '%Y-%m-%d')
			) latest ON latest.max_dt = sdd.date_time
			WHERE sdd.staff_id = ?
			ORDER BY sdd.date_time DESC";
		$query = $this->db->query($sql, [(int) $staffid, (int) $staffid]);
		$result = $query ? $query->result() : [];
		echo json_encode($result);
	}
	public function getImgDepartment()
	{
		try {
			$childFolderId = $this->input->get('date');
			echo json_encode($this->list_drive_children($childFolderId, 500));
		} catch (Exception $e) {
			log_message('error', 'Snapshot getImgDepartment: ' . $e->getMessage());
			echo json_encode([]);
		}
	}
}
