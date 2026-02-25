<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Document_upload_model extends App_Model
{
    public function insert_into_tbldocument_upload($data)
    {
        $this->db->insert('tbldocument_upload', $data);
        return $this->db->insert_id();
    }

    public function insert_into_tbldocument_upload_category($data)
    {
        $this->db->insert('tbldocument_upload_category', $data);
        return $this->db->insert_id();
    }

    public function update_into_tbldocument_upload($data, $id)
    {
        $this->db->set($data);
        $this->db->where('id', $id);
        $this->db->update('tbldocument_upload');

        return $this->db->affected_rows();
    }

    public function get_documents_based_on_department(){
        $this->load->model('departments_model');
        $departmentsId = $this->departments_model->get_staff_departments('', true);

        // Initialize the query and bind parameters
        if (!empty($departmentsId)) {
            $this->db->select('*');
            $this->db->from('tbldocument_upload');
            $this->db->where_in('departments', $departmentsId);
            $this->db->order_by('id', 'DESC');
        } else {
            $this->db->select('*');
            $this->db->from('tbldocument_upload');
            $this->db->where('departments IS NULL', null, false);
        }
    
        // Execute the query
        $result = $this->db->get()->result();

        return $result;
    }

    public function get_documents_of_all_department(){
        
        $this->db->select('*');
        $this->db->from('tbldocument_upload');
        $this->db->order_by('id', 'DESC');

        $result = $this->db->get()->result();

        return $result;
    }

    public function get_documents_cat_based_on_department(){
        $this->load->model('departments_model');
        $departmentsId = $this->departments_model->get_staff_departments('', true);

        // Initialize the query and bind parameters
        if (!empty($departmentsId)) {
            $this->db->select('*');
            $this->db->from('tbldocument_upload_category');
            $this->db->where_in('departmentid', $departmentsId);
            $this->db->order_by('id', 'DESC');
        } else {
            $this->db->select('*');
            $this->db->from('tbldocument_upload_category');
            $this->db->where('departmentid IS NULL', null, false);
        }
    
        // Execute the query
        $result = $this->db->get()->result();

        return $result;
    }

    public function get_documents_cat_of_all_department(){
        
        $this->db->select('*');
        $this->db->from('tbldocument_upload_category');
        $this->db->order_by('id', 'DESC');

        $result = $this->db->get()->result();

        return $result;
    }

    public function get_document_by_id($id){

        $this->load->model('departments_model');
        $departmentsId = $this->departments_model->get_staff_departments('', true);

        if(!empty($id)){
            $this->db->select('*');
            $this->db->from('tbldocument_upload');
            if(is_manager()){
            $this->db->where_in('departments', $departmentsId);
            }
            $this->db->where('id', $id);
        }else{
            $this->db->select('*');
            $this->db->from('tbldocument_upload');
            $this->db->where('id IS NULL', null, false);
        }

        $result = $this->db->get()->result();
        return $result;
    }

    public function send_document_upload_email($data, $id)
    {
        $date = json_decode($data['created_at'], true);
        $manager_email = get_staff_email_id($data['emp_id']);

        $this->email->set_mailtype("html");
        $this->email->from('noreply@t2gworkroom.com', 'T2G Workroom');
        $this->email->to(array('sarabjeet@tech2globe.net','harpreet@tech2globe.net'));
        $this->email->cc($manager_email);
        $subject = 'New document has been uploaded on Workroom by '. get_staff_full_name($data['emp_id']);

        $message = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Document Upload</title>
            <style>
            table, td{
                border : 1px solid black;
            }
            td{
                padding : 10px;
            }
            </style>
        </head>
        <body>
            <p>A document has been uploaded by '. get_staff_full_name($data['emp_id']) .' in the workroom:</p></br></br>
            <table>
                <tr>
                    <td><b>Employee ID</b></td>
                    <td>' . get_staff_emp_id($data['emp_id']) . '</td>
                </tr>
                <tr>
                    <td><b>Employee Name</b></td>
                    <td>' . get_staff_full_name($data['emp_id']) . '</td>
                </tr>
                <tr>
                    <td><b>Project</b></td>
                    <td>' . $data['project'] . '</td>
                </tr>
                <tr>
                    <td><b>Subject</b></td>
                    <td>' . $data['subject'] . '</td>
                </tr>
                <tr>
                    <td><b>Category</b></td>
                    <td>' . get_doccategory_name_by_categoryid($data['category']) . '</td>
                </tr>
                <tr>
                    <td><b>Department</b></td>
                    <td>' . get_department_name_by_departmentid($data['departments']) . '</td>
                </tr>
                <tr>
                    <td><b>Upload Date</b></td>
                    <td>' . $date[0] . '</td>
                </tr>
            </table>

            <p><b>Click here for more details :</b> https://t2gworkroom.com/admin/document_management/view/'.$id.' </p></br>

            <p><em>Kind Regards,<br>
            T2G Workroom</em></p>
        </body>
        </html>
        ';
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
    }

    public function send_status_change_email($data, $id, $status, $version)
    {   
        $data = (array)$data;

        $date = json_decode($data['created_at'], true);
        $manager_email = get_staff_email_id($data['emp_id']);

        if($status == 1){
            $status = "Approved";
        }else if($status == 2){
            $status = "Rejected";
        }else{
            $status = "Pending";
        }

        $this->email->set_mailtype("html");
        $this->email->from('noreply@t2gworkroom.com', 'T2G Workroom');
        $this->email->to($manager_email);
        $this->email->cc(array('sarabjeet@tech2globe.net','harpreet@tech2globe.net'));
        $subject = 'Status of a document has been changed on Workroom';

        $message = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Document status change</title>
            <style>
            table, td{
                border : 1px solid black;
            }
            td{
                padding : 10px;
            }
            </style>
        </head>
        <body>
            <p>The status of the following document has been changed to '. $status .' in the workroom.</p></br></br>
            <table>
                <tr>
                    <td><b>Employee ID</b></td>
                    <td>' . get_staff_emp_id($data['emp_id']) . '</td>
                </tr>
                <tr>
                    <td><b>Employee Name</b></td>
                    <td>' . get_staff_full_name($data['emp_id']) . '</td>
                </tr>
                <tr>
                    <td><b>Project</b></td>
                    <td>' . $data['project'] . '</td>
                </tr>
                <tr>
                    <td><b>Subject</b></td>
                    <td>' . $data['subject'] . '</td>
                </tr>
                <tr>
                    <td><b>Department</b></td>
                    <td>' . get_department_name_by_departmentid($data['departments']) . '</td>
                </tr>
                <tr>
                    <td><b>Upload Date</b></td>
                    <td>' . $date[0] . '</td>
                </tr>
                <tr>
                    <td><b>Status</b></td>
                    <td>' . $status . '</td>
                </tr>
                <tr>
                    <td><b>Version</b></td>
                    <td>' . $version+1 . '</td>
                </tr>
                <tr>
                    <td><b>Status changed On</b></td>
                    <td>' . date('Y-m-d') . '</td>
                </tr>
            </table>

            <p><b>Click here for more details :</b> https://t2gworkroom.com/admin/document_management/view/'.$id.' </p></br>

            <p><em>Kind Regards,<br>
            T2G Workroom</em></p>
        </body>
        </html>
        ';
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
    }

    public function send_document_edit_email($data, $id, $version)
    {   
        $data = (array)$data;

        $date = json_decode($data['created_at'], true);
        $manager_email = get_staff_email_id($data['emp_id']);

        $this->email->set_mailtype("html");
        $this->email->from('noreply@t2gworkroom.com', 'T2G Workroom');
        $this->email->to(array('sarabjeet@tech2globe.net','harpreet@tech2globe.net'));
        $this->email->cc($manager_email);
        $subject = 'A Document has been edited on Workroom by '. get_staff_full_name($data['emp_id']);

        $message = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Document Edited</title>
            <style>
            table, td{
                border : 1px solid black;
            }
            td{
                padding : 10px;
            }
            </style>
        </head>
        <body>
            <p>A document has been edited in the workroom by ' . get_staff_full_name($data['emp_id']) . '. The following are the details:</p></br></br>
            <table>
                <tr>
                    <td><b>Employee Name</b></td>
                    <td>' . get_staff_full_name($data['emp_id']) . '</td>
                </tr>
                <tr>
                    <td><b>Project</b></td>
                    <td>' . $data['project'] . '</td>
                </tr>
                <tr>
                    <td><b>Subject</b></td>
                    <td>' . $data['subject'] . '</td>
                </tr>
                <tr>
                    <td><b>Department</b></td>
                    <td>' . get_department_name_by_departmentid($data['departments']) . '</td>
                </tr>
                <tr>
                    <td><b>Upload Date</b></td>
                    <td>' . $date[0] . '</td>
                </tr>
                <tr>
                    <td><b>Version</b></td>
                    <td>' . $version+1 . '</td>
                </tr>
                <tr>
                    <td><b>Edited On</b></td>
                    <td>' . date('Y-m-d') . '</td>
                </tr>
            </table>

            <p><b>Click here for more details :</b> https://t2gworkroom.com/admin/document_management/view/'.$id.' </p></br>

            <p><em>Kind Regards,<br>
            T2G Workroom</em></p>
        </body>
        </html>
        ';
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
    }

    public function send_new_version_upload_email($data, $id, $version)
    {   
        $data = (array)$data;

        $manager_email = get_staff_email_id($data['emp_id']);

        $this->email->set_mailtype("html");
        $this->email->from('noreply@t2gworkroom.com', 'T2G Workroom');
        $this->email->to(array('sarabjeet@tech2globe.net','harpreet@tech2globe.net'));
        $this->email->cc($manager_email);
        $subject = 'A new version of a document has been uploaded on workroom by '. get_staff_full_name($data['emp_id']);

        $message = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Document status change</title>
            <style>
            table, td{
                border : 1px solid black;
            }
            td{
                padding : 10px;
            }
            </style>
        </head>
        <body>
            <p>The version for the following document has been updated by ' . get_staff_full_name($data['emp_id']) . '. Here are the details:</p></br></br>
            <table>
                <tr>
                    <td><b>Employee ID</b></td>
                    <td>' . get_staff_emp_id($data['emp_id']) . '</td>
                </tr>
                <tr>
                    <td><b>Employee Name</b></td>
                    <td>' . get_staff_full_name($data['emp_id']) . '</td>
                </tr>
                <tr>
                    <td><b>Project</b></td>
                    <td>' . $data['project'] . '</td>
                </tr>
                <tr>
                    <td><b>Subject</b></td>
                    <td>' . $data['subject'] . '</td>
                </tr>
                <tr>
                    <td><b>Department</b></td>
                    <td>' . get_department_name_by_departmentid($data['departments']) . '</td>
                </tr>
                <tr>
                    <td><b>Upload Date</b></td>
                    <td>' . date('Y-m-d') . '</td>
                </tr>
                <tr>
                    <td><b>Status</b></td>
                    <td>Pending</td>
                </tr>
                <tr>
                    <td><b>Version</b></td>
                    <td>' . $version . '</td>
                </tr>
            </table>

            <p><b>Click here for more details :</b> https://t2gworkroom.com/admin/document_management/view/'.$id.' </p></br>

            <p><em>Kind Regards,<br>
            T2G Workroom</em></p>
        </body>
        </html>
        ';
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
    }
}