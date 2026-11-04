<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class GoogleDriveHelper
{
    private $driveService;

    public function __construct()
    {
        $this->driveService = recruitment_init_google_drive_service();
    }

    public function uploadFile($filePath, $fileName)
    {
        if (!$this->driveService) {
            log_message('error', 'GoogleDriveHelper: Drive service not configured.');

            return false;
        }

        $folderName = date('Y-m-d');
        $folderId = $this->getOrCreateFolder($folderName);

        if (!$folderId) {
            log_message('error', 'GoogleDriveHelper: Failed to get or create folder: ' . $folderName);

            return false;
        }

        $file = new Google_Service_Drive_DriveFile();
        $file->setName($fileName);
        $file->setParents([$folderId]);

        $data = file_get_contents($filePath);

        try {
            return $this->driveService->files->create($file, [
                'data' => $data,
                'mimeType' => mime_content_type($filePath),
                'uploadType' => 'multipart',
            ]);
        } catch (Exception $e) {
            log_message('error', 'Failed to upload file: ' . $e->getMessage());

            return false;
        }
    }

    private function getOrCreateFolder($folderName)
    {
        if (!$this->driveService) {
            return false;
        }

        $parentId = '1X8igwsOqOPkPP9fgORKicpCIhYqmBbUZ';
        $query = sprintf(
            "mimeType='application/vnd.google-apps.folder' and name='%s' and '%s' in parents and trashed=false",
            $folderName,
            $parentId
        );
        $response = $this->driveService->files->listFiles(['q' => $query]);

        if (count($response->files) > 0) {
            return $response->files[0]->id;
        }

        $folder = new Google_Service_Drive_DriveFile();
        $folder->setName($folderName);
        $folder->setMimeType('application/vnd.google-apps.folder');
        $folder->setParents([$parentId]);
        $createdFolder = $this->driveService->files->create($folder);

        return $createdFolder->id;
    }

    public function createFolder($folderName, $parentId = null)
    {
        if (!$this->driveService) {
            return false;
        }

        $fileMetadata = new DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder',
        ]);

        if ($parentId) {
            $fileMetadata->setParents([$parentId]);
        }

        $folder = $this->driveService->files->create($fileMetadata, [
            'fields' => 'id',
        ]);

        return $folder->id;
    }
}
