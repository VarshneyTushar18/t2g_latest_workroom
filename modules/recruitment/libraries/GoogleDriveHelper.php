<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once 'google-client/vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class GoogleDriveHelper
{
    private $client;
    private $driveService;

    public function __construct()
    {

        $this->CI = &get_instance();
        $this->CI->load->config('google_drive');
        $credentialsPath = $this->CI->config->item('service_account_credentials_json');

        $this->client = new Client();
        $this->client->setAuthConfig($credentialsPath);
        $this->client->addScope(Drive::DRIVE);

        $this->driveService = new Google_Service_Drive($this->client);
    }

    public function uploadFile($filePath, $fileName)
    {
        // Check if the folder with the current date exists, if not create it
        $folderName = date('Y-m-d');
        $folderId = $this->getOrCreateFolder($folderName);

        // Upload the file to the folder
        if (!$folderId) {
            echo ('Failed to get or create folder: ' . $folderName);
            return false;
        }

        $file = new Google_Service_Drive_DriveFile();

        $file->setName($fileName);
        $file->setParents(array($folderId));

        echo '<pre>';
        print_r($file);

        echo 'this is parent id : ';
        print_r($file->getParents());

        $data = file_get_contents($filePath);

        try {
            $createdFile = $this->service->files->create($file, [
                'data' => $data,
                'mimeType' => mime_content_type($filePath),
                'uploadType' => 'multipart'
            ]);
        } catch (Exception $e) {
            log_message('error', 'Failed to upload file: ' . $e->getMessage());
            return false;
        }

        echo '<pre>';
        print_r($createdFile);
        echo ('this is parent id : ' . $createdFile->getParents());
        die;

        return $createdFile;
    }

    private function getOrCreateFolder($folderName)
    {
        $folderName = 'test';
        // Check if the folder exists
        $query = sprintf("mimeType='application/vnd.google-apps.folder' and name='%s'", $folderName);
        $response = $this->service->files->listFiles(['q' => $query]);

        if (count($response->files) == 0) {
            // Folder does not exist, create it
            $folder = new Google_Service_Drive_DriveFile();
            $folder->setName($folderName);
            $folder->setMimeType('application/vnd.google-apps.folder');
            $folder->setParents(array('1X8igwsOqOPkPP9fgORKicpCIhYqmBbUZ'));

            // echo '<pre>';
            // print_r($folder);
            // die;
            $createdFolder = $this->service->files->create($folder);
            return $createdFolder->id;

        } else {

            // echo '<pre>';
            // print_r($response);
            // die;
            // Folder exists, return its ID
            return $response->files[0]->id;
        }
    }

    public function createFolder($folderName, $parentId = null)
    {
        $fileMetadata = new DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder'
        ]);

        if ($parentId) {
            $fileMetadata->setParents([$parentId]);
        }

        $folder = $this->driveService->files->create($fileMetadata, [
            'fields' => 'id'
        ]);

        return $folder->id;
    }
}
