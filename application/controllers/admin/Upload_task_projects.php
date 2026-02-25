<?php

defined('BASEPATH') or exit('No direct script access allowed');

require 'application/vendor_phpspreadsheet/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class Upload_task_projects extends AdminController
{

    public function __construct()
    {

        parent::__construct();

        $this->load->model('upload_task_projects_model');
    }



    public function index()
    {

        $data['val'] = 'hi';
        $this->load->view('admin/upload_projects_tasks/export_project', $data);
    }

    public function exportProjectData()
    {

        $data = $this->upload_task_projects_model->get_project_data();
        // echo '<pre>';
        // print_r($project);
        // die;


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add header row with custom column
        $headers = array_keys($data[0]);

        $headers = [
            'id',
            'name',
            'description',
            'status',
            'billing_type',
            'start_date',
            'deadline',
            'project_created',
            'date_finished',
            'progress',
            'progress_from_tasks',
            'project_cost',
            'project_rate_per_hour',
            'estimated_hours',
            'staff_id',
            'custom_field',
            'custom_value'
        ];
   
        $sheet->fromArray($headers, NULL, 'A1');

        // Add data rows with an empty custom column
        foreach ($data as $index => $row) {

            $sheet->fromArray(array_values($row), NULL, 'A' . ($index + 2));
        }

        // Save the file
        $date = date('d-m-y-' . substr((string)microtime(), 1, 8));
        $date = str_replace(".", "", $date);
        $filename = "export_" . $date . ".xlsx";
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($filename) . '"');
        $writer->save('php://output');

        echo "Data exported successfully with a custom column!";
    }
}
