<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Holiday_model extends App_Model
{
    // custom function to get staff with their department id
    public function get_staff_based_on_department($id = '')
    {
        $this->load->model('departments_model');
        if ($id) {
            $query = 'select * from tblstaff inner join tblstaff_departments on tblstaff.staffid = tblstaff_departments.staffid where tblstaff.active = 1 and tblstaff_departments.departmentid = ' . $id;
        } else {
            $query = 'select * from tblstaff inner join tblstaff_departments on tblstaff.staffid = tblstaff_departments.staffid where tblstaff.active = 1;';
        }
        $all_staff_data = $this->db->query($query)->result();
        $departments = $this->departments_model->get_staff_departments();

        $staff_with_same_department = [];
        $start = false;

        foreach ($all_staff_data as $staff_data) {
            for ($j = 0; $j < count($staff_with_same_department); $j++) {
                if ($staff_data->staffid == $staff_with_same_department[$j]['staffid']) {
                    $start = true;
                }
            }
            for ($i = 0; $i < count($departments); $i++) {
                if (($staff_data->departmentid == $departments[$i]['departmentid']) && $start == false) {
                    $staff_with_same_department[] = (array) $staff_data;
                }
            }
            $start = false;
        }

        return $staff_with_same_department;
    }

    // this will change column name of staffid to staff_id
    public function _get_staff_based_on_department()
    {
        $this->load->model('departments_model');
        $all_staff_data = $this->db->query('select *,tblstaff.staffid as staff_id from tblstaff inner join tblstaff_departments on tblstaff.staffid = tblstaff_departments.staffid where tblstaff.active = 1;')->result();
        $departments = $this->departments_model->get_staff_departments();
        $staff_with_same_department = [];
        $start = false;
        foreach ($all_staff_data as $staff_data) {
            for ($j = 0; $j < count($staff_with_same_department); $j++) {
                if ($staff_data->staffid == $staff_with_same_department[$j]['staffid']) {
                    $start = true;
                }
            }
            for ($i = 0; $i < count($departments); $i++) {
                if (($staff_data->departmentid == $departments[$i]['departmentid']) && $start == false) {
                    $staff_with_same_department[] = (array) $staff_data;
                }
            }
        }

        return $staff_with_same_department;
    }

    /**
     * Get staff permissions
     *
     * @param  mixed $id staff id
     * @return array
     */
    public function get_staff_permissions($id)
    {
        if (defined('DOING_DATABASE_UPGRADE')) {
            return [];
        }

        $permissions = $this->app_object_cache->get('staff-' . $id . '-permissions');

        if (!$permissions && !is_array($permissions)) {
            $this->db->where('staff_id', $id);
            $permissions = $this->db->get('staff_permissions')->result_array();
            $this->app_object_cache->add('staff-' . $id . '-permissions', $permissions);
        }

        return $permissions;
    }

    /**
     * True when current (or given) staff may manage company holidays.
     * HR + Admin (+ Super HR / Super Admin).
     */
    public function can_manage_company_holidays($staff_id = '')
    {
        $staff_id = $staff_id === '' ? (int) get_staff_user_id() : (int) $staff_id;
        if ($staff_id <= 0) {
            return false;
        }

        if (function_exists('is_admin') && is_admin($staff_id)) {
            return true;
        }

        $role = get_staff_role_slug($staff_id);

        return in_array($role, ['hr', 'super hr', 'admin', 'super admin'], true);
    }

    /**
     * Company holidays for a year (includes repeat_by_year rows from other years).
     *
     * @param int $year
     * @return array
     */
    public function get_company_holidays_for_year($year)
    {
        $year = (int) $year;
        $rows = $this->db->order_by('break_date', 'ASC')->get(db_prefix() . 'day_off')->result_array();
        $out = [];

        foreach ($rows as $row) {
            $break = $row['break_date'] ?? '';
            if ($break === '' || $break === '0000-00-00') {
                continue;
            }
            $y = (int) date('Y', strtotime($break));
            $repeat = (int) ($row['repeat_by_year'] ?? 0);
            if ($y === $year || ($repeat === 1 && $y !== $year)) {
                $display = $break;
                if ($repeat === 1 && $y !== $year) {
                    $display = $year . '-' . date('m-d', strtotime($break));
                }
                $row['display_date'] = $display;
                $row['display_year'] = $year;
                $out[] = $row;
            }
        }

        usort($out, function ($a, $b) {
            return strcmp($a['display_date'], $b['display_date']);
        });

        return $out;
    }

    public function get_company_holiday($id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return null;
        }

        return $this->db->where('id', $id)->get(db_prefix() . 'day_off')->row_array();
    }

    /**
     * Normalize uploaded / form date to Y-m-d.
     */
    public function parse_holiday_date($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        // Excel serial date
        if (is_numeric($value) && (float) $value > 20000 && (float) $value < 80000) {
            $unix = ((int) $value - 25569) * 86400;

            return gmdate('Y-m-d', $unix);
        }

        $formats = ['d/m/Y', 'd-m-Y', 'm/d/Y', 'd.m.Y', 'Y/m/d', 'd M Y', 'd-M-Y'];
        foreach ($formats as $fmt) {
            $dt = DateTime::createFromFormat($fmt, $value);
            if ($dt instanceof DateTime) {
                $errs = DateTime::getLastErrors();
                if (empty($errs['warning_count']) && empty($errs['error_count'])) {
                    return $dt->format('Y-m-d');
                }
            }
        }

        $ts = strtotime($value);
        if ($ts) {
            return date('Y-m-d', $ts);
        }

        return '';
    }

    public function normalize_holiday_type($type)
    {
        $type = strtolower(trim((string) $type));
        $allowed = ['holiday', 'event_break', 'unexpected_break'];
        if (in_array($type, $allowed, true)) {
            return $type;
        }
        if ($type === 'event' || $type === 'event break') {
            return 'event_break';
        }
        if ($type === 'unexpected' || $type === 'unexpected break') {
            return 'unexpected_break';
        }

        return 'holiday';
    }

    /**
     * Duplicate check: same date + same reason (case-insensitive).
     */
    public function holiday_exists($break_date, $off_reason, $exclude_id = 0)
    {
        $this->db->where('break_date', $break_date);
        $this->db->where('LOWER(TRIM(off_reason)) = ' . $this->db->escape(strtolower(trim($off_reason))), null, false);
        if ((int) $exclude_id > 0) {
            $this->db->where('id !=', (int) $exclude_id);
        }

        return $this->db->count_all_results(db_prefix() . 'day_off') > 0;
    }

    /**
     * Save single holiday via timesheets day_off API shape.
     *
     * @return array{success:bool,id?:int,message:string}
     */
    public function save_company_holiday($data)
    {
        $this->load->model('timesheets/timesheets_model');

        $id = isset($data['id']) ? (int) $data['id'] : 0;
        $break_date = $this->parse_holiday_date($data['break_date'] ?? '');
        $reason = trim((string) ($data['leave_reason'] ?? $data['off_reason'] ?? $data['name'] ?? ''));
        $type = $this->normalize_holiday_type($data['leave_type'] ?? $data['off_type'] ?? $data['type'] ?? 'holiday');
        $repeat = !empty($data['repeat_by_year']) ? 1 : 0;

        if ($break_date === '' || $reason === '') {
            return ['success' => false, 'message' => 'Date and holiday name are required.'];
        }

        if ($this->holiday_exists($break_date, $reason, $id)) {
            return ['success' => false, 'message' => 'This holiday already exists for that date.'];
        }

        $payload = [
            'leave_reason' => $reason,
            'leave_type' => $type,
            'break_date' => $break_date,
            'repeat_by_year' => $repeat,
        ];

        if ($id > 0) {
            $ok = $this->timesheets_model->update_day_off($payload, $id);

            return [
                'success' => (bool) $ok,
                'id' => $id,
                'message' => $ok ? 'Holiday updated.' : 'No changes saved.',
            ];
        }

        $insert_id = (int) $this->timesheets_model->add_day_off($payload);

        return [
            'success' => $insert_id > 0,
            'id' => $insert_id,
            'message' => $insert_id > 0 ? 'Holiday added.' : 'Could not add holiday.',
        ];
    }

    public function delete_company_holiday($id)
    {
        $this->load->model('timesheets/timesheets_model');

        return (bool) $this->timesheets_model->delete_day_off((int) $id);
    }

    /**
     * Import rows from CSV/XLSX.
     *
     * @return array{inserted:int,skipped:int,errors:string[]}
     */
    public function import_company_holidays_file($filepath, $ext)
    {
        $rows = $this->read_holiday_upload_rows($filepath, $ext);
        $inserted = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $i => $row) {
            $line = $i + 2;
            $date = $this->parse_holiday_date($row['date'] ?? '');
            $name = trim((string) ($row['name'] ?? ''));
            if ($date === '' && $name === '') {
                continue;
            }
            if ($date === '' || $name === '') {
                $skipped++;
                $errors[] = "Row {$line}: missing date or name.";
                continue;
            }

            $result = $this->save_company_holiday([
                'break_date' => $date,
                'leave_reason' => $name,
                'leave_type' => $row['type'] ?? 'holiday',
                'repeat_by_year' => !empty($row['repeat_by_year']) ? 1 : 0,
            ]);

            if ($result['success']) {
                $inserted++;
            } else {
                $skipped++;
                $errors[] = "Row {$line}: " . $result['message'];
            }
        }

        return compact('inserted', 'skipped', 'errors');
    }

    /**
     * @return array<int,array{date:string,name:string,type:string,repeat_by_year:string}>
     */
    protected function read_holiday_upload_rows($filepath, $ext)
    {
        $ext = strtolower($ext);
        $rows = [];

        if ($ext === 'csv') {
            if (($handle = fopen($filepath, 'r')) === false) {
                return [];
            }
            $header = null;
            while (($data = fgetcsv($handle)) !== false) {
                if ($header === null) {
                    $header = array_map(function ($h) {
                        return strtolower(trim((string) $h));
                    }, $data);
                    continue;
                }
                $assoc = [];
                foreach ($header as $idx => $key) {
                    $assoc[$key] = isset($data[$idx]) ? trim((string) $data[$idx]) : '';
                }
                $rows[] = $this->map_upload_row($assoc);
            }
            fclose($handle);

            return $rows;
        }

        $autoload = FCPATH . 'application/vendor_phpspreadsheet/autoload.php';
        if (!file_exists($autoload)) {
            return [];
        }
        require_once $autoload;

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filepath);
        $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        if (!$sheet || count($sheet) < 2) {
            return [];
        }

        $header = array_map(function ($h) {
            return strtolower(trim((string) $h));
        }, $sheet[0]);

        for ($i = 1; $i < count($sheet); $i++) {
            $assoc = [];
            foreach ($header as $idx => $key) {
                $assoc[$key] = isset($sheet[$i][$idx]) ? trim((string) $sheet[$i][$idx]) : '';
            }
            $rows[] = $this->map_upload_row($assoc);
        }

        return $rows;
    }

    protected function map_upload_row(array $assoc)
    {
        $date = $assoc['date'] ?? ($assoc['break_date'] ?? ($assoc['holiday_date'] ?? ''));
        $name = $assoc['name'] ?? ($assoc['off_reason'] ?? ($assoc['holiday'] ?? ($assoc['title'] ?? '')));
        $type = $assoc['type'] ?? ($assoc['off_type'] ?? ($assoc['leave_type'] ?? 'holiday'));
        $repeat = $assoc['repeat_by_year'] ?? ($assoc['repeat'] ?? '0');

        return [
            'date' => $date,
            'name' => $name,
            'type' => $type,
            'repeat_by_year' => $repeat,
        ];
    }
}
