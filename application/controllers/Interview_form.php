<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Interview_form extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->library('upload');
        $this->load->model('interview_form_model');
        $this->load->model('recruitment/recruitment_model');

    }
    public function index($token = '')
    {
		$data['job_positions'] = $this->recruitment_model->get_job_position();
        $this->load->view('interview_form',$data);
    }

    public function uploadFileToGoogleDrive($filePath, $fileName) {
        require_once 'google-client/vendor/autoload.php';

        // Initialize the Google Client
        try {
            // Initialize the Google Client
            $client = new Google_Client();
            $client->setAuthConfig('google-client/hr-recruit-cv-429106-f3626e643c56.json');
            $client->addScope(Google_Service_Drive::DRIVE_FILE);
    
            // Create the Google Drive service
            $service = new Google_Service_Drive($client);
    
            // Prepare file metadata for the upload
            $fileMetadata = new Google_Service_Drive_DriveFile([
                'name' => $fileName,
                'parents' => ['1rI_ki97AVcT0Y9wTU2xB5AZXKi5fPJA0'] // Replace with your folder ID
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

    public function submit_interview_details()
    {
        $data = $this->input->post();
        // echo '<pre>';
        // print_r($data);die;
        $data_add = [];

        $name = explode(' ',$data['name'],2);
        $data_add['candidate_name'] = $name[0];
        $data_add['last_name'] = $name[1];
        $data_add['email'] = $data['email'];
        $data_add['date_add'] = $data['date'];
        $data_add['refer_by'] = $data['refer_by'];
        $data_add['birthday'] = $data['dob'];
        $data_add['travel_mode'] = $data['travel_mode'];
        $data_add['home_distance'] = $data['home_distance'];
        $data_add['travelling_hour'] = $data['travelling_hour'];
        $data_add['city'] = $data['city'];
        $data_add['pin'] = $data['pin'];
        $data_add['phonenumber'] = $data['mobile_no_1'];
        $data_add['mobile_no_2'] = $data['mobile_no_2'];
        $data_add['mobile_no_3'] = $data['mobile_no_3'];
        $data_add['address'] = $data['address'];
        $data_add['reason_to_join'] = $data['reason_to_join'];
        $data_add['when_join'] = $data['when_join'];
        $data_add['own_system'] = $data['own_system'];
        $data_add['user_status'] = $data['user_status'];
        $data_add['health_issues'] = $data['health_issues'];
        $data_add['overtime'] = $data['overtime'];
        $data_add['long_shift'] = $data['long_shift'];
        $data_add['shift_preferences'] = json_encode($data['shift_preferences']);
        $data_add['educational_details'] = $data['table_data_one'];
        $data_add['details_of_experience'] = $data['table_data_two'];
        $data_add['reference'] = $data['table_data_three'];
        $data_add['job_position'] = $data['position'];
        $data_add['web_view_link'] = '';
        $data_add['file_name'] = '';
        $data_add['marital_status'] = $data['marital_status'];

        // Generate a unique filename
        $uniqueId = uniqid();

        if (isset($_FILES['profile-pic']) && $_FILES['profile-pic']['error'] == UPLOAD_ERR_OK) {
            // Get original file extension
            $fileExtension = pathinfo($_FILES['profile-pic']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '.' . $fileExtension;
    
            $uploadDir = 'uploads/candidate_profile_images/';
            $filePath = $uploadDir . $fileName;

            if(!empty($data['old-profile-pic']) && file_exists($uploadDir.$data['old-profile-pic'])){
                unlink($uploadDir.$data['old-profile-pic']);
            }
    
            // Move the file to the local directory temporarily
            if (move_uploaded_file($_FILES['profile-pic']['tmp_name'], $filePath)) {
                
                $data_add['profile_img'] = $fileName;
            }
        }
    
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] == UPLOAD_ERR_OK) {
            // Get original file extension
            $fileExtension = pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION);
            $fileName = $uniqueId . '.' . $fileExtension;
    
            $uploadDir = 'uploads/';
            $filePath = $uploadDir . $fileName;
    
            // Move the file to the local directory temporarily
            if (move_uploaded_file($_FILES['resume']['tmp_name'], $filePath)) {
                // Upload the file to Google Drive
                $fileId = $this->uploadFileToGoogleDrive($filePath, $fileName);
                if ($fileId) {
                    $data_add['file_name'] = $fileName;
                    $data_add['web_view_link'] = 'https://drive.google.com/file/d/'.$fileId.'/view';;
                }
    
                // Delete the local file after upload
                unlink($filePath);
            }
        }

        if($data['candidate_id']){
            $updated = $this->recruitment_model->update_cadidate($data_add,$data['candidate_id']);
          
            echo 'uddated';die;
            echo $updated; die;
            // $this->load->view('thank_you');

        }else{
            $data_add['candidate_code'] = $this->addNewCode();

            
            $added = $this->recruitment_model->add_candidate($data_add);
            $this->interview_form_model->send_mail_to_hr_for_new_candidate($data_add);
            echo 'added';die;
            echo $added; die;
            // $this->load->view('thank_you');

        }

        // if($this->input->post('candidate_id')){


        //     $this->recruitment_model->add_candidate();
        // }
    }

    public function addNewCode() {
        // Define the prefix you are looking for
        $prefix = 'T2G';
    
        // Fetch the latest code that starts with the prefix
        $this->db->select('candidate_code');
        $this->db->like('candidate_code', $prefix, 'after');  // Search for codes that start with 'T2G'
        $this->db->order_by('candidate_code', 'DESC');        // Sort descending to get the largest value
        $this->db->limit(1);                        // Limit to one result
        $query = $this->db->get('tblrec_candidate');  // Your table where codes are stored
    
        // Default to 'T2G 001' if no record exists
        $new_number = '001';
    
        if ($query->num_rows() > 0) {
            $last_code = $query->row()->candidate_code;  // e.g., 'T2G 001'
    
            // Extract the numeric part of the code
            $numeric_part = (int)substr($last_code, strlen($prefix) + 1);  // Remove prefix and convert to integer
    
            // Increment the numeric part
            $new_number = str_pad($numeric_part + 1, 3, '0', STR_PAD_LEFT);  // Increment and pad with leading zeros
        }
    
        // Construct the new code
        $new_code = $prefix . '-' . $new_number;
    
    
        return $new_code;
    }
    

    public function check_email_or_phone()
    {

        // Get the email and phone from POST data
        $email = $this->input->post('email');
        $phone = $this->input->post('phone');
        $cf_token = $this->input->post('cf_token');

        if (!$cf_token) {
            echo json_encode(["success" => false, "message" => "CAPTCHA token missing."]);
            exit;
        }
    
        // Cloudflare Turnstile secret key
        $secret_key = "0x4AAAAAAA-Z9eFPD_xynXStvrbZKpUqY0Y";
        $verify_url = "https://challenges.cloudflare.com/turnstile/v0/siteverify";
    
        // Using cURL to verify CAPTCHA
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $verify_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'secret'   => $secret_key,
            'response' => $cf_token
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);
        curl_close($ch);
    
        $captcha_result = json_decode($response, true);
    
        if (!$captcha_result || empty($captcha_result['success'])) {
            echo json_encode(["success" => false, "message" => "CAPTCHA verification failed."]);
            exit;
        }

        // Check if either email or phone exists in the database
        $user = $this->interview_form_model->get_user_by_email_or_phone($email, $phone);

        if($user){

            $user['job_position'] = $this->recruitment_model->get_job_position($user['job_position'])->position_id;
        }
        // Return the result as JSON
        echo json_encode(['user' => $user]);
    }


    public function get_candidate_data()
	{
        $candidate = $this->input->post('id');
		$infor = $this->recruitment_model->get_candidates($candidate);
		echo json_encode($infor);
	}

    public function thank_you(){
        $this->load->view('thank_you');
    }

}
