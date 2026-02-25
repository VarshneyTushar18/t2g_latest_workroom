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
		$this->client = new Google_Client();
		$this->client->setAuthConfig('google-client/t2gemt-monitoring-c3fa0b6cc3dc.json');
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
		//$parentFolderId = '1WM2uG5w-DBbyMViK5st8bpbSPG8S653m';
		$childFolderId = '180rTlMbvUBgcD1ET-OmlXl87qNPtLCeS';
		$response_parent['result'] = $this->service->files->listFiles([
			'q' => "not name contains 'png' and not name contains '202' and not name contains 'New Folder'",
			'mimeType' => 'application/vnd.google-apps.folder',
			'spaces' => 'drive',
			 'fields' => 'files(id, name)'
		]);
		//print_r($response_parent);
			 $this->load->view('admin/emt/emt_dashboard', $response_parent);
		
	}


	
	public function getDownload()
	{
		$img_id = $_POST['date'];
		$text = $_POST['time'];
		//print_r($_POST);die;
		$url = "https://drive.usercontent.google.com/uc?id=$img_id&authuser=0&export=download";
		$image_data = file_get_contents($url);
		$image = imagecreatefromstring($image_data);
		$image_width = imagesx($image);
		$image_height = imagesy($image);
		 $strip_height = 50;
		$new_height = $image_height + $strip_height;
		$new_image = imagecreatetruecolor($image_width, $new_height);
		$white = imagecolorallocate($new_image, 255, 255, 255);
		imagecopy($new_image, $image, 0, $strip_height, 0, 0, $image_width, $image_height);
		$font_size = 100; // Built-in font size
		$text_color = imagecolorallocate($new_image, 255, 255, 255); // Black color
		$text_width = imagefontwidth($font_size) * strlen($text);
		$text_x = ($image_width - $text_width) / 2; // Center horizontally
		$text_y = ($strip_height - imagefontheight($font_size)) / 2; // Center vertically in the strip
		// Add text to the white strip
		imagestring($new_image, $font_size, $text_x, $text_y, $text, $text_color);
		
		header('Content-Type: image/png');
		header('Content-Disposition: attachment; filename=$text');
		    // Output the image as PNG
		// Output the image as PNG
		imagepng($new_image);

		imagedestroy($new_image);
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
	public function getEmployeeDate()
	{

		$parentFolderId = $_GET['date'];
		
		$response_child['res_child'] = $this->service->files->listFiles([
			'q' => 'parents in "' . $parentFolderId . '"',
			'fields' => 'files(id, name)'
		]);
			 echo json_encode($response_child['res_child']['files']);		
	}
	
	public function getImg()
	{
		//print_r("hello");die;
		//print_r($_POST);die;
		$childFolderId = $_GET['date'];
		$pageToken = null;
		$response['res'] = $this->service->files->listFiles([
			'q' => 'parents in "' . $childFolderId . '"',
			//'pageSize'=> 10,
			'pageToken' => $pageToken,
			'fields'=> 'nextPageToken, files(id, name)'
			//'fields' => 'files(id, name)'
		]);
		    //$token = $response['res']['nextPageToken'];
			//print_r($token);die;
			echo json_encode($response['res']['files']);
			// $this->load->view('admin/emt/emtimg_dashboard', $response);
		
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
		//print_r("hello");die;
		//print_r($_POST);die;
		$parentFolderId = $_GET['date'];
		
		$response_child['res_child'] = $this->service->files->listFiles([
			'q' => 'parents in "' . $parentFolderId . '"',
			'fields' => 'files(id, name)'
		]);
			// echo json_encode($response_child['res_child']['files']);
			 $this->load->view('admin/emt/emt_delete_day', $response_child);
		
	}
	public function EmtDeleteDateDay()
	{
		//print_r("hello");die;
		//print_r($_POST);die;
		$parentFolderId = $_GET['date'];
		
		$response_child['res_child'] = $this->service->files->listFiles([
			'q' => 'parents in "' . $parentFolderId . '"',
			'fields' => 'files(id, name)'
		]);
			 //echo json_encode($response_child['res_child']['files']);
			 $this->load->view('admin/emt/emt_delete_day', $response_child);
		
	}
	
	public function EmtDeleteDateDayName()
	{
		//print_r("hello");die;
		//print_r($_POST);die;
		$parentFolderId = $_POST['date'];
		
		$response_child['res_child'] = $this->service->files->listFiles([
			'q' => 'parents in "' . $parentFolderId . '"',
			'mimeType' => 'application/vnd.google-apps.folder',
			'fields' => 'files(id, name)'
		]);
		$file_id = $response_child['res_child']['files'][0]['id'];
		  try {
		 $this->service->files->delete($file_id,array('supportsAllDrives' => true));
		  echo "Folder deleted successfully!";
		 } catch (Exception $e) {

        echo "Error deleting folder: " . $e->getMessage();

    }
		
			 //echo json_encode($response_child['res_child']['files']);
		//	 $this->load->view('admin/emt/emt_delete_day', $delete);
		
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
		$sql="SELECT staff_id,file_id,directory_id,parent_directory_id,status,date_time from tblstaff_drive_data where staff_id='$staffid' GROUP BY DATE_FORMAT(date_time, '%Y-%m-%d') ORDER by date_time desc";    
		$query = $this->db->query($sql);
		$result = $query->result();
		//print_r($result);die;
		echo json_encode($result);
	}
		public function getImgDepartment()
	{
		$childFolderId = $_GET['date'];
	//		$sql="SELECT staff_id,file_id,directory_id,parent_directory_id,status,date_time from tblstaff_drive_data where staff_id='$staffid' GROUP BY DATE_FORMAT(date_time, '%Y-%m-%d') ORDER by DATE_FORMAT(date_time, '%Y-%m-%d') desc";    
	//	$query = $this->db->query($sql);
	//	$results = $query->result();
		$pageToken = null;
		
	//	foreach($results as $res){
			
			//$childFolderId = $res->directory_id;
			//echo $childFolderId;
			$response['res'] = $this->service->files->listFiles([
			'q' => 'parents in "' . $childFolderId . '"',
			//'pageSize'=> 10,
			'pageToken' => $pageToken,
			'fields'=> 'nextPageToken, files(id, name)'
			//'fields' => 'files(id, name)'
		]);
		//}
		echo json_encode($response['res']['files']);
	//	print_r($response['res']['files']);die;
		
		    //$token = $response['res']['nextPageToken'];
			//print_r($token);die;
			//echo json_encode($response['res']['files']);
			// $this->load->view('admin/emt/emtimg_dashboard', $response);
		
	}
}
