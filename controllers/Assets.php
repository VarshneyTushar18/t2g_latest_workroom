<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once 'google-client/vendor/autoload.php';

class Assets extends AdminController
{
    private $client;
    private $service;
    public function __construct()
    {

        parent::__construct();
        $this->load->model('assets_model');

        $this->client = new Google_Client();
        $this->client->setAuthConfig('google-client/hr-recruit-cv-429106-f3626e643c56.json');
        $this->client->addScope(Google_Service_Drive::DRIVE);
        $this->service = new Google_Service_Drive($this->client);

    }

    public function setting()
    {
        /*if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('hrm', 'table'));
        }*/
        if (!has_permission('assets', '', 'edit') && !is_admin()) {
            access_denied('assets');
        }
        $data['group'] = $this->input->get('group');

        $data['title'] = _l('setting');
        $data['tab'][] = 'asset_group';
        $data['tab'][] = 'asset_unit';
        $data['tab'][] = 'asset_location';
        if ('' == $data['group']) {
            $data['group'] = 'asset_group';
        }
        $data['tabs']['view'] = 'includes/' . $data['group'];
        $data['asset_group'] = $this->assets_model->get_asset_group();
        $data['asset_unit'] = $this->assets_model->get_asset_unit();
        $data['asset_location'] = $this->assets_model->get_asset_location();
        $this->load->view('manage_setting', $data);
        \modules\assets\core\Apiinit::ease_of_mind('assets');
        \modules\assets\core\Apiinit::the_da_vinci_code('assets');
    }

    public function asset_group()
    {
        if ($this->input->post()) {
            $message = '';
            $data = $this->input->post();
            $data = $this->input->post();

            if (!$this->input->post('id')) {
                $id = $this->assets_model->add_asset_group($data);
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('asset_group'));
                    set_alert('success', $message);
                    redirect(admin_url('assets/setting?group=asset_group'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->assets_model->update_asset_group($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('asset_group'));
                    set_alert('success', $message);
                    redirect(admin_url('assets/setting?group=asset_group'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
            \modules\assets\core\Apiinit::ease_of_mind('assets');
            \modules\assets\core\Apiinit::the_da_vinci_code('assets');
            die;
        }
    }

    public function delete_assets_group($id)
    {
        if (!$id) {
            redirect(admin_url('assets/setting?group=asset_group'));
        }
        $response = $this->assets_model->delete_asset_group($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('asset_group')));
        } elseif (true == $response) {
            set_alert('success', _l('deleted', _l('asset_group')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('asset_group')));
        }
        redirect(admin_url('assets/setting?group=asset_group'));
    }

    public function asset_unit()
    {
        \modules\assets\core\Apiinit::ease_of_mind('assets');
        \modules\assets\core\Apiinit::the_da_vinci_code('assets');
        if ($this->input->post()) {
            $message = '';
            $data = $this->input->post();
            $data = $this->input->post();

            if (!$this->input->post('id')) {
                $id = $this->assets_model->add_asset_unit($data);
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('asset_unit'));
                    set_alert('success', $message);
                    redirect(admin_url('assets/setting?group=asset_unit'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->assets_model->update_asset_unit($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('asset_unit'));
                    set_alert('success', $message);
                    redirect(admin_url('assets/setting?group=asset_unit'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
            die;
        }
    }

    public function delete_asset_unit($id)
    {
        if (!$id) {
            redirect(admin_url('assets/setting?group=asset_unit'));
        }
        $response = $this->assets_model->delete_asset_unit($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('asset_unit')));
        } elseif (true == $response) {
            set_alert('success', _l('deleted', _l('asset_unit')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('asset_unit')));
        }
        redirect(admin_url('assets/setting?group=asset_unit'));
    }

    public function asset_location()
    {
        if ($this->input->post()) {
            $message = '';
            $data = $this->input->post();
            $data = $this->input->post();

            if (!$this->input->post('id')) {
                $id = $this->assets_model->add_asset_location($data);
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('asset_location'));
                    set_alert('success', $message);
                    redirect(admin_url('assets/setting?group=asset_location'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->assets_model->update_asset_location($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('asset_location'));
                    set_alert('success', $message);
                    redirect(admin_url('assets/setting?group=asset_location'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
            die;
        }
    }

    public function delete_asset_location($id)
    {
        if (!$id) {
            redirect(admin_url('assets/setting?group=asset_location'));
        }
        $response = $this->assets_model->delete_asset_location($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('asset_location')));
        } elseif (true == $response) {
            set_alert('success', _l('deleted', _l('asset_location')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('asset_location')));
        }
        redirect(admin_url('assets/setting?group=asset_location'));
    }

    public function manage_assets($asset_id = '')
    {
        if (!has_permission('assets', '', 'view') && !is_admin()) {
            access_denied('requests');
        }
        $this->load->model('departments_model');
        $data['title'] = _l('assets');
        $data['unit'] = $this->assets_model->get_asset_unit();
        $data['group'] = $this->assets_model->get_asset_group();
        $data['location'] = $this->assets_model->get_asset_location();
        $data['departments'] = $this->departments_model->get();
        $data['asset_id'] = $asset_id;
        $this->load->view('manage_assets', $data);
    }

    public function table_assets($status = '')
    {
        if ('' == $status || 'all_asset' == $status) {
            $this->app->get_table_data(module_views_path('assets', 'table_assets'));
        } elseif ('not_pending_yet' == $status) {
            $this->app->get_table_data(module_views_path('assets', 'table_assets'), ['status' => 1]);
        } elseif ('using' == $status) {
            $this->app->get_table_data(module_views_path('assets', 'table_assets'), ['status' => 2]);
        } elseif ('liquidation' == $status) {
            $this->app->get_table_data(module_views_path('assets', 'table_assets'), ['status' => 3]);
        } elseif ('warranty_repair' == $status) {
            $this->app->get_table_data(module_views_path('assets', 'table_assets'), ['status' => 4]);
        } elseif ('lost' == $status) {
            $this->app->get_table_data(module_views_path('assets', 'table_assets'), ['status' => 5]);
        } elseif ('broken' == $status) {
            $this->app->get_table_data(module_views_path('assets', 'table_assets'), ['status' => 6]);
        }
    }

    public function asset()
    {
        if ($this->input->post()) {
            $message = '';
            $data = $this->input->post();

            if (!$this->input->post('id')) {

                // print_r($this->input->post());die;
                $uploaded_data = $this->upload_file_to_gdrive();

                // echo '<pre>';
                // print_r($uploaded_data); die;
				$data = array_merge($data, $uploaded_data);

                                // echo '<pre>';
                                // print_r($data);die;
                $id = $this->assets_model->add_asset($data);
                if ($id) {
                    // handle_asset_file($id);
                    // handle_asset_image_upload($id);
                    $success = true;
                    $message = _l('added_successfully', _l('assets'));
                    set_alert('success', $message);
                    redirect(admin_url('assets/manage_assets'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            } else {
                $id = $data['id'];
                unset($data['id']);
                $uploaded_data = $this->upload_file_to_gdrive();
				$data = array_merge($data, $uploaded_data);

                $success = $this->assets_model->update_asset($data, $id);
                // handle_asset_file($id);
                if ($success) {
                    $message = _l('updated_successfully', _l('assets'));
                    set_alert('success', $message);
                    redirect(admin_url('assets/manage_assets'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
            die;
        }
    }

    public function delete_assets($id)
    {
        if (!$id) {
            redirect(admin_url('assets/manage_assets'));
        }
        $response = $this->assets_model->delete_assets($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('asset_location')));
        } elseif (true == $response) {
            set_alert('success', _l('deleted', _l('asset_location')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('asset_location')));
        }
        redirect(admin_url('assets/manage_assets'));
    }

    public function assets_code_exists()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                // First we need to check if the email is the same
                $id = $this->input->post('id');
                if ('' != $id) {
                    $this->db->where('id', $id);
                    $assets = $this->db->get('tblassets')->row();
                    if ($assets->assets_code == $this->input->post('assets_code')) {
                        echo json_encode(true);
                        die();
                    }
                }
                $this->db->where('assets_code', $this->input->post('assets_code'));
                $total_rows = $this->db->count_all_results('tblassets    ');
                if ($total_rows > 0) {
                    echo json_encode(false);
                } else {
                    echo json_encode(true);
                }
                die();
            }
        }
    }

    public function get_asset_data_ajax($asset_id)
    {
        $this->load->model('staff_model');
        $data['staffs'] = $this->staff_model->get();
        $data['asset_file'] = $this->assets_model->get_asset_file($asset_id);
        $data['assets'] = $this->assets_model->get($asset_id);

        $broken = $this->assets_model->get_amount_asset_broken($asset_id);
        $warr = $this->assets_model->get_amount_asset_warranty($asset_id);
        $brokens = 0;
        $warrs = 0;
        foreach ($broken as $a) {
            $brokens += $a['amount'];
        }
        foreach ($warr as $a) {
            $warrs += $a['amount'];
        }

        $data['total_broken'] = $brokens - $warrs;
        $this->load->view('asset_preview', $data);
    }

    public function file($id, $rel_id)
    {
        $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
        $data['current_user_is_admin'] = is_admin();
        $data['file'] = $this->assets_model->get_file($id, $rel_id);
        if (!$data['file']) {
            header('HTTP/1.0 404 Not Found');
            die;
        }
        $this->load->view('_file', $data);
    }

    public function delete_asset_attachment($id)
    {
        $this->load->model('misc_model');
        $file = $this->misc_model->get_file($id);
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            echo $this->assets_model->delete_assets_attachment($id);
        } else {
            header('HTTP/1.0 400 Bad error');
            echo _l('access_denied');
            die;
        }
    }

    public function allocation_asset()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $allocation_data = $this->handle_allocation_image_upload();
            $data['images'] = $allocation_data['images'];
            // echo '<pre>';
            // print_r($allocation_data); die;
            $id = $this->assets_model->allocation_asset($data);
            if ($id) {
                $message = _l('allocation_asset') . ' ' . _l('successfully');
                set_alert('success', $message);
                redirect(admin_url('assets/manage_assets#' . $data['assets']));
            }
        }
    }

    public function acction_code_exists()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $this->db->where('acction_code', $this->input->post('acction_code'));
                $total_rows = $this->db->count_all_results('tblassets_acction_1');
                if ($total_rows > 0) {
                    echo json_encode(false);
                } else {
                    echo json_encode(true);
                }
                die();
            }
        }
    }

    public function acction2_code_exists()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $this->db->where('acction_code', $this->input->post('acction_code'));
                $total_rows = $this->db->count_all_results('tblassets_acction_2');
                if ($total_rows > 0) {
                    echo json_encode(false);
                } else {
                    echo json_encode(true);
                }
                die();
            }
        }
    }

    public function get_asset_allocation_by_staff($staff, $assets)
    {
        $allocation = $this->assets_model->get_asset_allocation_by_staff($staff, $assets);
        $revoke = $this->assets_model->get_asset_revoke_by_staff($staff, $assets);
        $total_allocate = 0;
        $total_revoke = 0;
        foreach ($allocation as $a) {
            $total_allocate += $a['amount'];
        }
        foreach ($revoke as $a) {
            $total_revoke += $a['amount'];
        }
        echo json_encode([
            'total' => $total_allocate - $total_revoke,
        ]);
    }

    public function revoke_asset()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $id = $this->assets_model->revoke_asset($data);
            if ($id) {
                $message = _l('recalled_asset') . ' ' . _l('successfully');
                set_alert('success', $message);
                redirect(admin_url('assets/manage_assets#' . $data['assets']));
            }
        }
    }

    public function additional_asset()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $id = $this->assets_model->additional_asset($data);
            if ($id) {
                $message = _l('additional_asset') . ' ' . _l('successfully');
                set_alert('success', $message);
                redirect(admin_url('assets/manage_assets#' . $data['assets']));
            }
        }
    }

    public function lost_asset()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $id = $this->assets_model->lost_asset($data);
            if ($id) {
                $message = _l('report_lost') . ' ' . _l('successfully');
                set_alert('success', $message);
                redirect(admin_url('assets/manage_assets#' . $data['assets']));
            }
        }
    }

    public function broken_asset()
    {
        if ($this->input->post()) {
            $data = $this->input->post();

            $allocation_data = $this->handle_allocation_image_upload();
            $data['images'] = $allocation_data['images'];

            $id = $this->assets_model->broken_asset($data);
            if ($id) {
                $message = _l('report_broken') . ' ' . _l('successfully');
                set_alert('success', $message);
                redirect(admin_url('assets/manage_assets#' . $data['assets']));
            }
        }
    }

    public function liquidation_asset()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $id = $this->assets_model->liquidation_asset($data);
            if ($id) {
                $message = _l('liquidation_asset') . ' ' . _l('successfully');
                set_alert('success', $message);
                redirect(admin_url('assets/manage_assets#' . $data['assets']));
            }
        }
    }

    public function warranty_asset()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $id = $this->assets_model->warranty_asset($data);
            if ($id) {
                $message = _l('warranty_asset') . ' ' . _l('successfully');
                set_alert('success', $message);
                redirect(admin_url('assets/manage_assets#' . $data['assets']));
            }
        }
    }

    public function table_inventory_history($asset_id)
    {
        $this->app->get_table_data(module_views_path('assets', 'includes/table_inventory_history'), ['asset_id' => $asset_id]);
    }

    public function table_action($asset_id)
    {
        $this->app->get_table_data(module_views_path('assets', 'includes/table_action'), ['asset_id' => $asset_id]);
    }

    public function table_action_allocate($type)
    {
        $this->app->get_table_data(module_views_path('assets', 'includes/table_action'), ['type' => $type]);
    }

    public function allocation()
    {
        $data['title'] = _l('allocation');
        $this->load->view('allocation', $data);
    }

    public function eviction()
    {
        $data['title'] = _l('eviction');
        $this->load->view('eviction', $data);
    }

    public function depreciation()
    {
        $data['group'] = $this->assets_model->get_asset_group();
        $data['assets'] = $this->assets_model->get_assets();
        $data['title'] = _l('depreciation');
        $this->load->view('depreciation', $data);
    }

    public function table_depreciation()
    {
        $this->app->get_table_data(module_views_path('assets', 'includes/table_depreciation'));
    }


    public function handle_allocation_image_upload()
    {
        $this->load->helper('form');
        $this->load->library('upload');
        $config['upload_path'] = get_upload_path_by_type('assets');  // Specify the upload folder
        $config['allowed_types'] = 'gif|jpg|png|pdf'; // Specify allowed file types
        $config['max_size'] = 2048; // Set max file size in kilobytes

        $this->upload->initialize($config);

        $file_data = [];

        foreach ($_FILES['images']['name'] as $key => $value) {
            $file = array(
                'name' => $_FILES['images']['name'][$key],
                'type' => $_FILES['images']['type'][$key],
                'tmp_name' => $_FILES['images']['tmp_name'][$key],
                'error' => $_FILES['images']['error'][$key],
                'size' => $_FILES['images']['size'][$key]
            );

            $_FILES['userfile_tmp'] = $file;

            // echo '<pre>';
            // print_r($_FILES);die;
            if (!$this->upload->do_upload('userfile_tmp')) {
                $error = array('error' => $this->upload->display_errors());
                // print_r($error);
            } else {
                $data = array('upload_data' => $this->upload->data());
                // print_r($data);

                $file_data[] = array(
                    'file_name' => $data['upload_data']['file_name'],
                    // 'file_path' => $data['upload_data']['full_path'],
                    // 'upload_date' => date('Y-m-d H:i:s')
                );
            }
        }
        if (!empty($file_data)) {
            $allocation_data = array(
                'images' => json_encode($file_data)
            );
        }
        return $allocation_data;
    }

    public function documents()
    {
        $data['images'] = $this->input->post();
        // print_r($data); die;
        $this->load->view('documents', $data);
        // print_r(); die;
    }

    public function broken()
    {
        $data['title'] = 'Broken assets';
        $this->load->view('broken', $data);
    }

    public function table_action2_allocate($type)
    {
        $this->app->get_table_data(module_views_path('assets', 'includes/table_action2'), ['type' => $type]);
    }


    public function upload_file_to_gdrive()
    {
        if (!empty($_FILES['asset_image']['name'])) {
            // Load the Google Drive service
            // $service = new Google_Service_Drive($this->client);

            $folderId = '1VUrGQn8e9kBpoMpYmcLTUwztfAGLwnjo';
            // Upload the file
            $file = new Google_Service_Drive_DriveFile();
            $file->setName($_FILES['asset_image']['name']);
            $file->setParents([$folderId]);

            $result = $this->service->files->create($file, [
                'data' => file_get_contents($_FILES['asset_image']['tmp_name']),
                'mimeType' => $_FILES['asset_image']['type'],
                'uploadType' => 'multipart',
                'fields' => 'id,webViewLink,webContentLink'
            ]);

            $permission = new Google_Service_Drive_Permission([
                'type' => 'anyone',
                'role' => 'reader',
            ]);

            $this->service->permissions->create($result->id, $permission);

            // echo '<pre>';
            // print_r($result);
            // die;
            $data = [
                'file_id' => $result->id,
                'file_name' => $file->name
            ];


            if ($result) {
                return $data;
            } else {
                return [];
            }
        } else {
            return [];
        }
    }

    public function getFolderId($folderName)
    {
        $parentFolderId = '1X8igwsOqOPkPP9fgORKicpCIhYqmBbUZ';
        // Query to check if the folder exists
        $query = sprintf(
            "name = '%s' and mimeType = 'application/vnd.google-apps.folder' and '%s' in parents and trashed = false",
            $folderName,
            $parentFolderId
        );
        $response = $this->service->files->listFiles([
            'q' => $query,
            'spaces' => 'drive',
            'fields' => 'files(id, name)'
        ]);

        // If the folder exists, return its ID
        if (count($response->files) > 0) {
            return $response->files[0]->id;
        }

        // If the folder doesn't exist, create it and return its ID
        $folderMetadata = new Google_Service_Drive_DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$parentFolderId]
        ]);
        $folder = $this->service->files->create($folderMetadata, [
            'fields' => 'id'
        ]);

        return $folder->id;
    }
	public function assets_dashboard()
	{
		$data['result'] = $this->assets_model->get_staff_departments();
		//echo"<pre>"; print_r($data);die;
		 $this->load->view('assets_dashboard', $data);
	}
	
	public function getDepartmentStaff()
	{
		$deptid = $_GET['date'];
		$data['emp'] = $this->assets_model->getDepartmentemp($deptid);
		//echo"<pre>"; print_r($data);die;
		echo json_encode($data['emp']);
	}
	
	public function getFilterData()
	{
		$dept = $_POST['department'];
		$empid = $_POST['employee'];
		$data['emp'] = $this->assets_model->getDataFilter($dept,$empid);
		//echo"<pre>"; print_r($data);die;
		echo json_encode($data['emp']);
	}
}
