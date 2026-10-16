<?php

use app\services\utilities\Date;

defined('BASEPATH') or exit('No direct script access allowed');

class Biometric extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('biometric_model');
		$this->load->model('departments_model');
		$this->load->model('staff_model');
    }

    private function load_phpspreadsheet()
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }

        $autoload = FCPATH . 'vendor/autoload.php';
        if (!is_file($autoload)) {
            show_error('PhpSpreadsheet is not installed (vendor/autoload.php missing).');
        }

        require_once $autoload;
        $loaded = true;
    }

    public function index()
		{
		 $data['title'] = 'Attendance Import';
		$data['attendance_data'] = $this->biometric_model->get_all_attendance(); 
        $data['result'] = $this->departments_model->get_staff_departments_biometric();
		$this->load->view('admin/biometric/attendance_import_view', $data);
		}

	public function calculate_total_break($punch_string)
{
    $entries = explode(',', trim($punch_string, ','));
    $timestamps = [];

    foreach ($entries as $entry) {
        if (preg_match('/(\d{2}:\d{2}(?::\d{2})?)\s*\(\s*(in|out)\s*\)/i', trim($entry), $matches)) {
            $time = $matches[1];
            $type = strtolower($matches[2]);

            [$h, $m, $s] = explode(':', strlen($time) === 5 ? $time . ':00' : $time);
            $minutes = ((int)$h * 60) + (int)$m + ((int)$s / 60);

            $timestamps[] = [
                'time' => $minutes,
                'type' => $type
            ];
        }
    }

    $total_break_mins = 0;
    $i = 0;

    while ($i < count($timestamps) - 1) {
        $curr = $timestamps[$i];
        $next = $timestamps[$i + 1];

        if ($curr['type'] === 'out') {
            if ($next['type'] === 'in' || $next['type'] === 'out') {
                $break = $next['time'] - $curr['time'];
                if ($break > 0 && $break < 600) {
                    $total_break_mins += $break;
                }
            }
        }
        $i++;
    }

    $h = floor($total_break_mins / 60);
    $m = floor($total_break_mins % 60);
    $s = round(($total_break_mins - floor($total_break_mins)) * 60);

    return sprintf('%02d:%02d:%02d', $h, $m, $s);
}

	public function fetch_attendance_data()
		{
			$limit = $this->input->get('limit') ?? 10;
			$offset = $this->input->get('offset') ?? 0;
			$month = $this->input->get('month');
			$department = $this->input->get('department'); 
			$staff = $this->input->get('staff');

			if (empty($staff)) {
				$staff = get_staff_user_id();
			}

			$filters = [
				'month' => $month,
				'department' => $department,
				'staff' => $staff
			];

			$data = $this->biometric_model->get_attendance_filtered($limit, $offset, $filters);
			$total = $this->biometric_model->get_attendance_filtered_count($filters);

			foreach ($data as &$row) {
				$row['break_time'] = $this->calculate_total_break($row['punch_records'] ?? '');
			}
			unset($row);

			$this->output
				->set_content_type('application/json', 'utf-8')
				->set_output(json_encode(['data' => $data, 'total' => $total]));
		}

    public function save_attendance_bulk_data()
    {
        $this->load_phpspreadsheet();

        $allowed_fields = [
            "Attendance Date", "Company", "Location", "Employee Code", "Employee Name",
            "Shift", "Scheduled In Time", "Scheduled Out Time",
            "Actual In Time", "Actual Out Time", "Work Duration", "Total Duration",
            "Late By", "Early Going By", "Over Time", "Status", "Punch Records", "Remarks"
        ];

        $allowed_fields_name = [
            "attendance_date", "company", "location", "employee_code", "employee_name",
            "shift", "s_in_time", "s_out_time",
            "a_in_time", "a_out_time", "work_duration", "t_duration",
            "late_by", "early_going_by", "over_time", "status", "punch_records", "remark"
        ];

        if (!isset($_FILES['excelFile']['tmp_name']) || empty($_FILES['excelFile']['tmp_name'])) {
            set_alert('warning', 'Please upload a file.');
            redirect(admin_url('biometric/index'));
        }

        $file = $_FILES['excelFile']['tmp_name'];
        $file_name = $_FILES['excelFile']['name'];
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);

        if (!in_array($ext, ['xls', 'xlsx'])) {
            set_alert('warning', 'Invalid file type. Only Excel files allowed.');
            redirect(admin_url('biometric/index'));
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();

            if (count($data) <= 1) {
                set_alert('warning', 'The file must contain data.');
                redirect(admin_url('biometric/index'));
            }

            $headers = array_filter(array_map(function($h) {
                return trim((string) $h);
            }, $data[0]), function($val) {
                return $val !== '';
            });

            $invalid = [];
            foreach ($headers as $header) {
                if (!in_array($header, $allowed_fields)) {
                    $invalid[] = $header;
                }
            }

            if (!empty($invalid)) {
                set_alert('warning', 'Invalid fields: ' . implode(", ", $invalid));
                redirect(admin_url('biometric/index'));
            }

            $insert_data = [];
				foreach ($data as $index => $row) {
						if ($index < 2) continue; 
						array_shift($row);
						$row = array_slice($row, 0, count($allowed_fields_name));
						if (count($row) != count($allowed_fields_name)) {
							continue;
						}
						$insert_data[] = array_combine($allowed_fields_name, $row);
					}

            if (empty($insert_data)) {
                set_alert('warning', 'No valid records to import.');
            } else {
                if ($this->biometric_model->insert_attendance_bulk($insert_data)) {
                    set_alert('success', 'Attendance records imported successfully.');
                } else {
                    set_alert('warning', 'No records were inserted.');
                }
            }

            redirect(admin_url('biometric/index'));

        } catch (Exception $e) {
            set_alert('danger', 'Error reading file: ' . $e->getMessage());
            redirect(admin_url('biometric/index'));
        } 
    }

public function get_staff_by_department()
{
    $dept_id = $this->input->get('dept_id');

    $this->db->select('s.staffid, s.firstname, s.lastname');
    $this->db->from('tblstaff_departments sd');
    $this->db->join('tblstaff s', 's.staffid = sd.staffid');
    $this->db->where('s.active', 1);

    if (!empty($dept_id)) {
        $this->db->where('sd.departmentid', $dept_id);
    }

    $staff = $this->db->get()->result_array();

    $result = array_map(function ($s) {
        return [
            'staffid' => $s['staffid'],
            'full_name' => $s['firstname'] . ' ' . $s['lastname']
        ];
    }, $staff);

    echo json_encode($result);
}

}
