<?php

use app\services\utilities\Date;

defined('BASEPATH') or exit('No direct script access allowed');

require_once 'google-client/vendor/autoload.php';

class Document_management extends AdminController
{
    public function index()
    {
        if(is_admin() || is_in_managers_list() || get_staff_emp_id(get_staff_user_id()) == 1033 || get_staff_emp_id(get_staff_user_id()) == 1611 || get_staff_emp_id(get_staff_user_id()) == 2047){

            $this->load->model('departments_model');
            $data['departments'] = $this->departments_model->get_staff_departments();

            if(is_admin()){
                $this->load->model('document_upload_model');
                $data['documents'] = $this->document_upload_model->get_documents_of_all_department();
            }else{
                $this->load->model('document_upload_model');
                $data['documents'] = $this->document_upload_model->get_documents_based_on_department();
            }

            $this->load->view('admin/document_management/dashboard', $data);
        }else{
            set_alert('warning', 'Access Denied.');
            redirect(admin_url('/'));
        }
    }

    public function uploadFileToGoogleDrive($filePath, $fileName) {
        // Initialize the Google Client
        try {
            // Initialize the Google Client
            $client = new Google_Client();
            $client->setAuthConfig('google-client/it-asset-document-manag-7fa025a99fbe.json');
            $client->addScope(Google_Service_Drive::DRIVE_FILE);
    
            // Create the Google Drive service
            $service = new Google_Service_Drive($client);
    
            // Prepare file metadata for the upload
            $fileMetadata = new Google_Service_Drive_DriveFile([
                'name' => $fileName,
                'parents' => ['1Xz0E-H2-sRYScpb5MS_xiUcKMiisQ3VW'] // Replace with your folder ID
            ]);
    
            // Read file content
            $content = file_get_contents($filePath);
    
            // Upload the file to Google Drive
            $file = $service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => mime_content_type($filePath),
                'uploadType' => 'multipart',
                'fields' => 'id'
            ]);
    
            return $file->id; // Return the file ID on success
    
        } catch (Exception $e) {
            // Handle error
            echo 'Google Drive upload error: ' . $e->getMessage();
            return false;
        }
    }

    public function deleteFileFromGoogleDrive($fileId) {
        try {
            // Initialize the Google Client
            $client = new Google_Client();
            $client->setAuthConfig('google-client/it-asset-document-manag-7fa025a99fbe.json');
            $client->addScope(Google_Service_Drive::DRIVE_FILE);
    
            // Create the Google Drive service
            $service = new Google_Service_Drive($client);
    
            // Delete the file by ID
            $service->files->delete($fileId);
    
            return true; // Return true on successful deletion
    
        } catch (Exception $e) {
            // Handle error
            echo 'Google Drive deletion error: ' . $e->getMessage();
            return false;
        }
    }    

    public function upload_project_document() {
        $data = $this->input->post();
        
        $data['description'] = json_encode($data['description']);
        $data['emp_id'] = get_staff_user_id();
        $data['file'] = '';
        $data['drive_file_id'] = '';
    
        // Generate a unique filename
        $uniqueId = uniqid();
    
        if (isset($_FILES['attachFile']) && $_FILES['attachFile']['error'] == UPLOAD_ERR_OK) {
            // Get original file extension
            $fileExtension = pathinfo($_FILES['attachFile']['name'], PATHINFO_EXTENSION);
            $data['file'] = $uniqueId . '.' . $fileExtension;
    
            $uploadDir = 'uploads/';
            $filePath = $uploadDir . $data['file'];
    
            // Move the file to the local directory temporarily
            if (move_uploaded_file($_FILES['attachFile']['tmp_name'], $filePath)) {
                // Upload the file to Google Drive
                $fileId = $this->uploadFileToGoogleDrive($filePath, $data['file']);
                if ($fileId) {
                    // Update the data with JSON-encoded values
                    $data['file'] = json_encode([$data['file']]);
                    $data['drive_file_id'] = json_encode([$fileId]);
                } else {
                    set_alert('warning', 'Failed to upload file to Google Drive.');
                    redirect(admin_url('document_management'));
                }
    
                // Delete the local file after upload
                unlink($filePath);
            } else {
                set_alert('warning', 'Failed to upload file locally.');
                redirect(admin_url('document_management'));
            }
        }
    
        // Additional fields
        $data['status'] = json_encode([0]); 
        $data['created_at'] = json_encode([date('Y-m-d')]);
    
        // Insert data into the database
        $this->load->model('document_upload_model');
        $id = $this->document_upload_model->insert_into_tbldocument_upload($data);
    
        if ($id) {
            $this->document_upload_model->send_document_upload_email($data, $id);
            set_alert('success', 'Data added successfully.');
        } else {
            set_alert('warning', 'Failed to add data.');
        }

        redirect(admin_url('document_management'));
    }    

    public function view($id){
        if(is_admin() || is_in_managers_list() || get_staff_emp_id(get_staff_user_id()) == 1033 || get_staff_emp_id(get_staff_user_id()) == 1611 || get_staff_emp_id(get_staff_user_id()) == 2047){

            $this->load->model('departments_model');
            $data['departments'] = $this->departments_model->get_staff_departments();

            $this->load->model('document_upload_model');
            $data['document'] = $this->document_upload_model->get_document_by_id($id);
            

            $this->load->view('admin/document_management/doc_view', $data);
        }else{
            set_alert('warning', 'Access Denied.');
            redirect(admin_url('/'));
        }
    }

    public function upload_next_version_document() {
        $id = $this->input->post('docId');
        $description = $this->input->post('description');
    
        $this->load->model('document_upload_model');
        $previousData = $this->document_upload_model->get_document_by_id($id);
    
        if (empty($previousData)) {
            set_alert('warning', 'Document not found.');
            redirect(admin_url('document_management/view/'.$id));
        }
    
        $previousData = $previousData[0];
    
        // Decode JSON fields into arrays
        $data['description'] = json_decode($previousData->description, true) ?? [];
        $data['file'] = json_decode($previousData->file, true) ?? [];
        $data['drive_file_id'] = json_decode($previousData->drive_file_id, true) ?? [];
        $data['status'] = json_decode($previousData->status, true) ?? [];
        $data['created_at'] = json_decode($previousData->created_at, true) ?? [];
    
        // Generate a unique filename
        $uniqueId = uniqid();
    
        if (isset($_FILES['attachFile']) && $_FILES['attachFile']['error'] == UPLOAD_ERR_OK) {
            // Validate file type and size
            $fileExtension = pathinfo($_FILES['attachFile']['name'], PATHINFO_EXTENSION);
    
            $fileNewName = $uniqueId . '.' . $fileExtension;
            $uploadDir = 'uploads/';
            $filePath = $uploadDir . $fileNewName;
    
            // Move the file to the local directory
            if (move_uploaded_file($_FILES['attachFile']['tmp_name'], $filePath)) {
                // Upload the file to Google Drive
                $fileId = $this->uploadFileToGoogleDrive($filePath, $fileNewName);
                if ($fileId) {
                    array_push($data['file'], $fileNewName);
                    array_push($data['drive_file_id'], $fileId);
                } else {
                    set_alert('warning', 'Failed to upload file to Google Drive.');
                    unlink($filePath); // Remove the local file
                    redirect(admin_url('document_management/view/'.$id));
                }
    
                // Delete the local file after upload
                unlink($filePath);
            } else {
                set_alert('warning', 'Failed to upload file locally.');
                redirect(admin_url('document_management/view/'.$id));
            }
        }

        $version = count($data['file']);
    
        // Add new status and created_at values
        array_push($data['description'], $description);
        array_push($data['status'], 0);
        array_push($data['created_at'], date('Y-m-d'));
    
        // Convert arrays back to JSON
        $data['description'] = json_encode($data['description']);
        $data['file'] = json_encode($data['file']);
        $data['drive_file_id'] = json_encode($data['drive_file_id']);
        $data['status'] = json_encode($data['status']);
        $data['created_at'] = json_encode($data['created_at']);
    
        // Update data in the database
        $updateSuccess = $this->document_upload_model->update_into_tbldocument_upload($data, $id);
    
        if ($updateSuccess > 0) {
            $this->document_upload_model->send_new_version_upload_email($previousData, $id, $version);
            set_alert('success', 'Data updated successfully.');
        } else {
            set_alert('warning', 'Failed to update data.');
        }
    
        redirect(admin_url('document_management/view/'.$id));
    }
    
    public function edit_version_document() {
        $id = $this->input->post('docId');
        $description = $this->input->post('description');
        $dFileId = $this->input->post('dFileId');
        $version = $this->input->post('version');
        $status = $this->input->post('status');
    
        $this->load->model('document_upload_model');
        $previousData = $this->document_upload_model->get_document_by_id($id);
    
        if (empty($previousData)) {
            set_alert('warning', 'Document not found.');
            redirect(admin_url('document_management/view/'.$id));
        }
    
        $previousData = $previousData[0];
    
        // Decode JSON fields into arrays
        $data['description'] = json_decode($previousData->description, true) ?? [];
        $data['file'] = json_decode($previousData->file, true) ?? [];
        $data['drive_file_id'] = json_decode($previousData->drive_file_id, true) ?? [];
        $data['status'] = json_decode($previousData->status, true) ?? [];
        $data['created_at'] = json_decode($previousData->created_at, true) ?? [];
    
        // Generate a unique filename
        $uniqueId = uniqid();
    
        if (isset($_FILES['attachFile']) && $_FILES['attachFile']['error'] == UPLOAD_ERR_OK) {
            // Validate file type and size
            $fileExtension = pathinfo($_FILES['attachFile']['name'], PATHINFO_EXTENSION);
    
            $fileNewName = $uniqueId . '.' . $fileExtension;
            $uploadDir = 'uploads/';
            $filePath = $uploadDir . $fileNewName;
    
            // Move the file to the local directory
            if (move_uploaded_file($_FILES['attachFile']['tmp_name'], $filePath)) {
                // Upload the file to Google Drive
                $fileId = $this->uploadFileToGoogleDrive($filePath, $fileNewName);
                if ($fileId) {
                    $this->deleteFileFromGoogleDrive($dFileId);
                    $data['file'][$version] = $fileNewName;
                    $data['drive_file_id'][$version] = $fileId;
                } else {
                    set_alert('warning', 'Failed to upload file to Google Drive.');
                    unlink($filePath); // Remove the local file
                    redirect(admin_url('document_management/view/'.$id));
                }
    
                // Delete the local file after upload
                unlink($filePath);
            } else {
                set_alert('warning', 'Failed to upload file locally.');
                redirect(admin_url('document_management/view/'.$id));
            }
        }
    
        // Add new status and created_at values
        $data['description'][$version] = $description;
        $data['status'][$version] = $status;
        $data['created_at'][$version] = date('Y-m-d');
    
        // Convert arrays back to JSON
        $data['description'] = json_encode($data['description']);
        $data['file'] = json_encode($data['file']);
        $data['drive_file_id'] = json_encode($data['drive_file_id']);
        $data['status'] = json_encode($data['status']);
        $data['created_at'] = json_encode($data['created_at']);
    
        // Update data in the database
        $updateSuccess = $this->document_upload_model->update_into_tbldocument_upload($data, $id);
    
        if ($updateSuccess > 0) {
            $this->document_upload_model->send_document_edit_email($previousData, $id, $version);
            set_alert('success', 'Version Data updated successfully.');
        } else {
            set_alert('warning', 'Failed to update data.');
        }
    
        redirect(admin_url('document_management/view/'.$id));
    }

    public function updateStatus(){
        $id = $this->input->post('docId');
        $status = $this->input->post('status');
        $version = $this->input->post('version');
        $comment = $this->input->post('comment');

        $this->load->model('document_upload_model');
        $previousData = $this->document_upload_model->get_document_by_id($id);
    
        if (empty($previousData)) {
            set_alert('warning', 'Document not found.');
            redirect(admin_url('document_management/view/'.$id));
        }
    
        $previousData = $previousData[0];
    
        $data['status'] = json_decode($previousData->status, true) ?? [];
        $data['manager_comment'] = json_decode($previousData->manager_comment, true) ?? [];

        $data['status'][$version] = $status;
        $data['manager_comment'][$version] = $comment;

        $data['status'] = json_encode($data['status']);
        $data['manager_comment'] = json_encode($data['manager_comment']);
    
        // Update data in the database
        $updateSuccess = $this->document_upload_model->update_into_tbldocument_upload($data, $id);
    
        if ($updateSuccess > 0) {
            $this->document_upload_model->send_status_change_email($previousData, $id, $status, $version);
            set_alert('success', 'Status updated successfully.');
        } else {
            set_alert('warning', 'Failed to update status.');
        }
    
        redirect(admin_url('document_management/view/'.$id));
    

    }

    public function category(){

        if(is_admin() || is_in_managers_list() || get_staff_emp_id(get_staff_user_id()) == 1033 || get_staff_emp_id(get_staff_user_id()) == 1611 || get_staff_emp_id(get_staff_user_id()) == 2047){

            $this->load->model('document_upload_model');

            if(is_admin()){
                $data['category'] = $this->document_upload_model->get_documents_cat_of_all_department();
            }else{
                $data['category'] = $this->document_upload_model->get_documents_cat_based_on_department();
            }

            $this->load->view('admin/document_management/category', $data);
        }else{
            set_alert('warning', 'Access Denied.');
            redirect(admin_url('/'));
        }
    }

    public function add_doc_category(){
        $data['departmentid'] = $this->input->post('departmentId');
        $data['name'] = $this->input->post('newCategoryName');
        $data['staffid'] = get_staff_user_id();

        // Insert data into the database
        $this->load->model('document_upload_model');
        $id = $this->document_upload_model->insert_into_tbldocument_upload_category($data);

        if($id){
            $resp = ['status' => 200,'id' => $id];
            echo json_encode($resp);
        }else{
            $resp = ['status' => 500,'id' => $id];
            echo json_encode($resp);
        }
    }

    public function get_doc_cat_department_json(){
        $departmentId = $this->input->post('department');

        $this->db->select('*');
        $this->db->from('tbldocument_upload_category');
        $this->db->where('departmentid', $departmentId);

        $category = $this->db->get()->result_array();

        echo json_encode($category);
    }

    public function updateCategory() {
        $id = $this->input->post('id');
        $name = $this->input->post('name');
    
        $id = intval($id); 
        $name = strip_tags(trim($name));
    
        // Execute the update query
        $this->db->set('name', $name);
        $this->db->where('id', $id);
        $this->db->update('tbldocument_upload_category');
    
        // Check affected rows for feedback
        if ($this->db->affected_rows() > 0) {
            set_alert('success', 'Data updated successfully.');
        } else {
            set_alert('warning', 'Failed to update data or no changes made.');
        }
    
        redirect(admin_url('document_management/category'));
    }    
    
}