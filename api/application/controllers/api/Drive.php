<?php

require APPPATH . 'libraries/REST_Controller.php';

class Drive extends REST_Controller
{

  public function __construct()
  {

    parent::__construct();
    $this->load->database();
    $this->load->model(array("api/drive_model"));
    $this->load->library(array("form_validation"));
    $this->load->helper("security");
  }

  /**
   * Accept both form-urlencoded and JSON bodies from Snapshot clients.
   */
  private function request_payload()
  {
    $data = [
      'staff_id' => $this->post('staff_id'),
      'file_id' => $this->post('file_id'),
      'directory_id' => $this->post('directory_id'),
      'parent_directory_id' => $this->post('parent_directory_id'),
      'status' => $this->post('status'),
    ];

    // Fallback to CI input (form posts)
    foreach ($data as $k => $v) {
      if ($v === null || $v === '') {
        $data[$k] = $this->input->post($k);
      }
    }

    // Fallback to JSON body (Snapshot agents often send application/json)
    $raw = $this->input->raw_input_stream;
    if (!empty($raw)) {
      $json = json_decode($raw, true);
      if (is_array($json)) {
        foreach ($data as $k => $v) {
          if (($v === null || $v === '') && array_key_exists($k, $json)) {
            $data[$k] = $json[$k];
          }
        }
      }
    }

    return $data;
  }

  public function index_post()
  {
    $data = $this->request_payload();

    if ($data['staff_id'] === null || $data['staff_id'] === ''
      || $data['file_id'] === null || $data['file_id'] === ''
      || $data['directory_id'] === null || $data['directory_id'] === '') {
      return $this->response([
        'status' => 'error',
        'message' => 'Missing required fields: staff_id, file_id, directory_id',
      ], REST_Controller::HTTP_BAD_REQUEST);
    }

    $ins = $this->drive_model->insert($data);
    if ($ins === true || $ins === 'Data is inserted successfully') {
      return $this->response([
        'status' => 'success',
        'message' => 'Data is inserted successfully',
      ], REST_Controller::HTTP_OK);
    }

    return $this->response([
      'status' => 'error',
      'message' => is_string($ins) ? $ins : 'Insert failed',
    ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
  }

  public function cleanup_old_folders_get()
  {
    require_once '/var/www/html/t2gworkroom/google-client/vendor/autoload.php';

    // ---- 1️⃣ Authenticate Google Drive Service ----
    $client = new Google\Client();
    $client->setAuthConfig('/var/www/html/t2gworkroom/google-client/t2g-monitoring-37bb12065f7f.json');
    $client->addScope(Google\Service\Drive::DRIVE);
    $service = new Google\Service\Drive($client);

    // ---- 2️⃣ Get all usernames from DB ----
    // $query = $this->db->query("
    //     SELECT email 
    //     FROM tblstaff 
    //     WHERE staffid IN (SELECT staffid FROM tbltimeinterval)
    // ");
    // $users = $query->result_array();
    $users = [
        ['email' => 'navneet.baid@tech2globe.in']  
    ];
    if (empty($users)) {
      return $this->response(['status' => 'error', 'message' => 'No users found'], REST_Controller::HTTP_NOT_FOUND);
    }
    
    $deleted_summary = [];
    $thirty_days_ago = strtotime('-2 days');

    // ---- 3️⃣ Loop through each user ----
    foreach ($users as $user) {
      $email = $user['email'];
      $username = preg_replace('/@.*/', '', $email); 

      // Find the user folder on Drive
      $userFolderId = $this->getFolderIdByName($service, $username);
      if (!$userFolderId) continue;

      // List all subfolders (these are date folders)
      $query = sprintf(
        "'%s' in parents and mimeType='application/vnd.google-apps.folder'",
        $userFolderId
      );
      $response = $service->files->listFiles([
        'q' => $query,
        'fields' => 'files(id, name, createdTime)'
      ]);

      foreach ($response->files as $folder) {
        $folderDate = strtotime($folder->name); // folder name = YYYY-MM-DD
        if (!$folderDate) continue;

        if ($folderDate < $thirty_days_ago) {
          try {
            $service->files->delete($folder->id);
            $deleted_summary[] = [
              'username' => $username,
              'folder' => $folder->name,
              'status' => 'deleted'
            ];
          } catch (Exception $e) {
            $deleted_summary[] = [
              'username' => $username,
              'folder' => $folder->name,
              'status' => 'error',
              'error' => $e->getMessage()
            ];
          }
        }
      }
    }

    // ---- 4️⃣ Return cleanup summary ----
    return $this->response([
      'status' => 'success',
      'message' => 'Cleanup completed',
      'deleted' => $deleted_summary
    ], REST_Controller::HTTP_OK);
  }
  private function getFolderIdByName($service, $folderName, $parentId = null)
  {
    try {
      $query = "name = '{$folderName}' and mimeType = 'application/vnd.google-apps.folder'";
      if ($parentId) {
        $query .= " and '{$parentId}' in parents";
      }
      $response = $service->files->listFiles([
        'q' => $query,
        'spaces' => 'drive',
        'fields' => 'files(id, name)'
      ]);

      if (count($response->files) > 0) {
        return $response->files[0]->id;
      }
      return null;
    } catch (Exception $e) {
      return null;
    }
  }
}
