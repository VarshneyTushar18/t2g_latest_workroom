<?php

use app\services\utilities\Date;

defined('BASEPATH') or exit('No direct script access allowed');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

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
		 $data['title'] = 'Biometric Attendance';
		$data['can_filter_all'] = $this->can_view_all_biometric();
		$data['sync_status'] = $this->biometric_model->get_sync_status();
		$data['default_month'] = $this->biometric_model->get_latest_attendance_month() ?: date('Y-m');

		$filters = [
			'month'      => $data['default_month'],
			'department' => '',
			'staff'      => $this->can_view_all_biometric() ? '' : get_staff_user_id(),
		];

		$data['initial_rows'] = $this->biometric_model->get_attendance_filtered(10, 0, $filters);
		$data['initial_total'] = $this->biometric_model->get_attendance_filtered_count($filters);
		foreach ($data['initial_rows'] as &$row) {
			$row['break_time'] = $this->calculate_total_break($row['punch_records'] ?? '');
		}
		unset($row);

		if ($data['can_filter_all']) {
			$departments = $this->departments_model->get();
			$data['result'] = [];
			foreach ((array) $departments as $dept) {
				$data['result'][] = [
					'departmentid' => $dept['departmentid'],
					'name'         => $dept['name'],
				];
			}
		} else {
			$data['result'] = $this->departments_model->get_staff_departments_biometric();
		}

		$this->load->view('admin/biometric/attendance_import_view', $data);
		}

	private function can_view_all_biometric()
	{
		return is_admin() || is_manager() || is_super_admin() || is_HR() || is_admin2();
	}

	public function sync_status()
	{
		$this->output
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($this->biometric_model->get_sync_status()));
	}
	public function calculate_total_break($punch_string)
{
    $entries = explode(',', trim($punch_string, ','));
    $timestamps = [];

    foreach ($entries as $entry) {
        if (preg_match('/(\d{2}:\d{2}(?::\d{2})?)\s*\(\s*(in|out)\s*\)/i', trim($entry), $matches)) {
            $time = $matches[1];
            $type = strtolower($matches[2]);

            // Normalize to minutes
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
            // out → in OR out → out both count as break
            if ($next['type'] === 'in' || $next['type'] === 'out') {
                $break = $next['time'] - $curr['time'];
                if ($break > 0 && $break < 600) { // limit break to < 10 hrs
                    $total_break_mins += $break;
                }
            }
        }
        $i++;
    }

    // Convert to H:i:s
    $h = floor($total_break_mins / 60);
    $m = floor($total_break_mins % 60);
    $s = round(($total_break_mins - floor($total_break_mins)) * 60);

    return sprintf('%02d:%02d:%02d', $h, $m, $s);
}



	public function fetch_attendance_data()
		{
			try {
				$limit = (int) ($this->input->get('limit') ?? 10);
				$offset = (int) ($this->input->get('offset') ?? 0);
				if ($limit < 1) {
					$limit = 10;
				}
				if ($limit > 100) {
					$limit = 100;
				}
				if ($offset < 0) {
					$offset = 0;
				}

				$month = $this->input->get('month');
				$department = $this->input->get('department');
				$staff = $this->input->get('staff');

				if ($department === '#' || $department === 'all' || $department === null) {
					$department = '';
				}
				if ($staff === '#' || $staff === 'all' || $staff === null) {
					$staff = '';
				}

				if (!$this->can_view_all_biometric()) {
					$staff = get_staff_user_id();
					$department = '';
				}

				$filters = [
					'month' => $month,
					'department' => $department,
					'staff' => $staff,
				];

				$data = $this->biometric_model->get_attendance_filtered($limit, $offset, $filters);
				$total = $this->biometric_model->get_attendance_filtered_count($filters);

				foreach ($data as &$row) {
					$row['break_time'] = $this->calculate_total_break($row['punch_records'] ?? '');
				}
				unset($row);

				$payload = json_encode(
					['data' => $data, 'total' => $total],
					JSON_INVALID_UTF8_SUBSTITUTE
				);
				if ($payload === false) {
					$payload = json_encode(['data' => [], 'total' => 0, 'error' => 'json_encode_failed']);
				}

				$this->output
					->set_content_type('application/json', 'utf-8')
					->set_output($payload);
			} catch (Throwable $e) {
				log_message('error', 'fetch_attendance_data: ' . $e->getMessage());
				$this->output
					->set_status_header(500)
					->set_content_type('application/json', 'utf-8')
					->set_output(json_encode([
						'data' => [],
						'total' => 0,
						'error' => $e->getMessage(),
					]));
			}
		}




    public function save_attendance_bulk_data()
    {
        set_alert(
            'warning',
            'Manual Excel import is disabled. Attendance syncs automatically from Biomax every 2 minutes.'
        );
        redirect(admin_url('biometric/index'));
        return;

        $this->load_phpspreadsheet();

        $header_to_field = [
            'Attendance Date'      => 'attendance_date',
            'Company'              => 'company',
            'Location'             => 'location',
            'Employee Code'        => 'employee_code',
            'Employee Name'        => 'employee_name',
            'Shift'                => 'shift',
            'Scheduled In Time'    => 's_in_time',
            'Scheduled Out Time'   => 's_out_time',
            'Actual In Time'       => 'a_in_time',
            'Actual Out Time'      => 'a_out_time',
            'Work Duration'        => 'work_duration',
            'Total Duration'       => 't_duration',
            'Late By'              => 'late_by',
            'Early Going By'       => 'early_going_by',
            'Over Time'            => 'over_time',
            'Status'               => 'status',
            'Punch Records'        => 'punch_records',
            'Remarks'              => 'remark',
        ];
        $optional_headers = ['S.No', 'S No', 'SNo', 'Sr.No', 'Sr No', 'Serial No'];

        if (!isset($_FILES['excelFile']['tmp_name']) || empty($_FILES['excelFile']['tmp_name'])) {
            set_alert('warning', 'Please upload a file.');
            redirect(admin_url('biometric/index'));
        }

        $file = $_FILES['excelFile']['tmp_name'];
        $file_name = $_FILES['excelFile']['name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($ext, ['xls', 'xlsx'])) {
            set_alert('warning', 'Invalid file type. Only Excel files allowed.');
            redirect(admin_url('biometric/index'));
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, true, true, false);

            if (count($data) <= 1) {
                set_alert('warning', 'The file must contain data.');
                redirect(admin_url('biometric/index'));
            }

            // Find the real header row (title rows are common in biometric exports)
            $header_index = null;
            $headers = [];
            foreach ($data as $i => $row) {
                $normalized = array_map(function ($h) {
                    return trim((string) $h);
                }, $row);
                if (in_array('Attendance Date', $normalized, true) && in_array('Employee Code', $normalized, true)) {
                    $header_index = $i;
                    $headers = $normalized;
                    break;
                }
            }

            if ($header_index === null) {
                set_alert('warning', 'Could not find required headers (Attendance Date, Employee Code).');
                redirect(admin_url('biometric/index'));
            }

            $column_map = [];
            $invalid = [];
            foreach ($headers as $col => $header) {
                if ($header === '') {
                    continue;
                }
                if (isset($header_to_field[$header])) {
                    $column_map[$col] = $header_to_field[$header];
                } elseif (!in_array($header, $optional_headers, true)) {
                    $invalid[] = $header;
                }
            }

            if (!empty($invalid)) {
                set_alert('warning', 'Invalid fields: ' . implode(', ', $invalid));
                redirect(admin_url('biometric/index'));
            }

            if (!in_array('attendance_date', $column_map, true) || !in_array('employee_code', $column_map, true)) {
                set_alert('warning', 'Required columns missing: Attendance Date and Employee Code.');
                redirect(admin_url('biometric/index'));
            }

            $insert_data = [];
            for ($i = $header_index + 1; $i < count($data); $i++) {
                $row = $data[$i];
                $mapped = [];
                foreach ($column_map as $col => $field) {
                    $mapped[$field] = isset($row[$col]) ? trim((string) $row[$col]) : '';
                }

                if ($mapped['employee_code'] === '' && ($mapped['attendance_date'] ?? '') === '') {
                    continue;
                }

                $insert_data[] = $mapped;
            }

            if (empty($insert_data)) {
                set_alert('warning', 'No valid records to import.');
            } else {
                $result = $this->biometric_model->insert_attendance_bulk($insert_data);
                if (!empty($result['success'])) {
                    set_alert(
                        'success',
                        'Attendance imported. Inserted: ' . (int) $result['inserted']
                        . ', Updated: ' . (int) $result['updated']
                        . ', Skipped: ' . (int) $result['skipped']
                    );
                } else {
                    set_alert('warning', 'No records were inserted. Skipped: ' . (int) ($result['skipped'] ?? 0));
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
    if (!$this->can_view_all_biometric()) {
        echo json_encode([]);
        return;
    }

    $dept_id = $this->input->get('dept_id');
    if ($dept_id === '#' || $dept_id === 'all') {
        $dept_id = '';
    }

    $this->db->select('s.staffid, s.firstname, s.lastname');
    $this->db->from(db_prefix() . 'staff_departments sd');
    $this->db->join(db_prefix() . 'staff s', 's.staffid = sd.staffid');
    $this->db->where('s.active', 1);

    if ($dept_id !== '' && $dept_id !== null && is_numeric($dept_id)) {
        $this->db->where('sd.departmentid', (int) $dept_id);
    }

    $this->db->group_by('s.staffid');
    $this->db->order_by('s.firstname', 'ASC');
    $staff = $this->db->get()->result_array();

    $result = array_map(function ($s) {
        return [
            'staffid' => $s['staffid'],
            'full_name' => $s['firstname'] . ' ' . $s['lastname'],
        ];
    }, $staff);

    echo json_encode($result);
}

    public function swipes()
    {
        // Old menu URL — send users to the merged Biometric page (Punch Swipes tab).
        redirect(admin_url('biometric?tab=swipes'));
    }

    public function fetch_swipes()
    {
        $from = $this->input->get('from') ?: date('Y-m-d');
        $to = $this->input->get('to') ?: date('Y-m-d');
        $staff = $this->input->get('staff');

        if (!$this->can_view_all_biometric()) {
            $staff = get_staff_user_id();
        } elseif ($staff === '#' || $staff === 'all' || $staff === null) {
            $staff = '';
        }

        $swipes = $this->biometric_model->get_swipes([
            'from' => $from,
            'to' => $to,
            'staff' => $staff,
        ]);

        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode(['data' => $swipes, 'total' => count($swipes)]));
    }

}
