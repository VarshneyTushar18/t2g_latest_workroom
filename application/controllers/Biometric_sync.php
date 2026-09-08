<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Machine-to-machine biometric sync endpoint (no staff login).
 * Auth: header X-Workroom-Token must match BIOMETRIC_SYNC_TOKEN.
 *
 * POST /biometric_sync/attendance
 * GET  /biometric_sync/health
 */
class Biometric_sync extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('biometric_model');
    }

    public function health()
    {
        $this->json_response([
            'ok'      => true,
            'service' => 'workroom-biometric-sync',
            'time'    => date('c'),
        ]);
    }

    public function attendance()
    {
        if (strtoupper($this->input->method(true)) !== 'POST') {
            $this->json_response(['ok' => false, 'error' => 'POST required'], 405);
            return;
        }

        if (!$this->authorize_request()) {
            return;
        }

        $raw = $this->input->raw_input_stream;
        $payload = json_decode($raw, true);

        if (!is_array($payload)) {
            $this->json_response(['ok' => false, 'error' => 'Invalid JSON body'], 400);
            return;
        }

        // Accept either { "records": [...] } or a bare array
        $records = isset($payload['records']) && is_array($payload['records'])
            ? $payload['records']
            : $payload;

        if (!is_array($records) || empty($records)) {
            $this->json_response(['ok' => false, 'error' => 'No records provided'], 400);
            return;
        }

        if (count($records) > 5000) {
            $this->json_response(['ok' => false, 'error' => 'Max 5000 records per request'], 413);
            return;
        }

        $normalized = [];
        $row_errors = [];

        foreach ($records as $i => $row) {
            if (!is_array($row)) {
                $row_errors[] = "Row {$i}: not an object";
                continue;
            }

            $employee_code = trim((string) ($row['employee_code'] ?? ''));
            $attendance_date = trim((string) ($row['attendance_date'] ?? ''));

            if ($employee_code === '' || $attendance_date === '') {
                $row_errors[] = "Row {$i}: employee_code and attendance_date are required";
                continue;
            }

            $normalized[] = [
                'attendance_date' => $attendance_date,
                'company'         => (string) ($row['company'] ?? ''),
                'location'        => (string) ($row['location'] ?? ''),
                'employee_code'   => $employee_code,
                'employee_name'   => (string) ($row['employee_name'] ?? ''),
                'shift'           => (string) ($row['shift'] ?? ''),
                's_in_time'       => (string) ($row['s_in_time'] ?? ''),
                's_out_time'      => (string) ($row['s_out_time'] ?? ''),
                'a_in_time'       => (string) ($row['a_in_time'] ?? ''),
                'a_out_time'      => (string) ($row['a_out_time'] ?? ''),
                'work_duration'   => (string) ($row['work_duration'] ?? ''),
                't_duration'      => (string) ($row['t_duration'] ?? ''),
                'late_by'         => (string) ($row['late_by'] ?? ''),
                'early_going_by'  => (string) ($row['early_going_by'] ?? ''),
                'over_time'       => (string) ($row['over_time'] ?? ''),
                'status'          => (string) ($row['status'] ?? ''),
                'punch_records'   => (string) ($row['punch_records'] ?? ''),
                'remark'          => (string) ($row['remark'] ?? ($row['remarks'] ?? '')),
            ];
        }

        if (empty($normalized)) {
            $this->json_response([
                'ok'         => false,
                'error'      => 'No valid records after validation',
                'row_errors' => $row_errors,
            ], 400);
            return;
        }

        $result = $this->biometric_model->insert_attendance_bulk($normalized);

        $this->json_response([
            'ok'            => !empty($result['success']),
            'received'      => count($records),
            'accepted'      => count($normalized),
            'inserted'      => (int) ($result['inserted'] ?? 0),
            'updated'       => (int) ($result['updated'] ?? 0),
            'skipped'       => (int) ($result['skipped'] ?? 0),
            'row_errors'    => $row_errors,
            'checkpoint_hint' => $payload['checkpoint'] ?? null,
            'time'          => date('c'),
        ], !empty($result['success']) ? 200 : 422);
    }

    private function authorize_request()
    {
        $expected = defined('BIOMETRIC_SYNC_TOKEN') ? (string) BIOMETRIC_SYNC_TOKEN : '';
        if ($expected === '') {
            $this->json_response(['ok' => false, 'error' => 'Sync token not configured on server'], 500);
            return false;
        }

        $token = (string) $this->input->get_request_header('X-Workroom-Token', true);
        if ($token === '') {
            $auth = (string) $this->input->get_request_header('Authorization', true);
            if (stripos($auth, 'Bearer ') === 0) {
                $token = trim(substr($auth, 7));
            }
        }

        if (!hash_equals($expected, $token)) {
            $this->json_response(['ok' => false, 'error' => 'Unauthorized'], 401);
            return false;
        }

        $allowed = defined('BIOMETRIC_SYNC_ALLOWED_IPS') ? trim((string) BIOMETRIC_SYNC_ALLOWED_IPS) : '';
        if ($allowed !== '') {
            $ip = $this->input->ip_address();
            $list = array_filter(array_map('trim', explode(',', $allowed)));
            if (!in_array($ip, $list, true)) {
                $this->json_response(['ok' => false, 'error' => 'IP not allowed', 'ip' => $ip], 403);
                return false;
            }
        }

        return true;
    }

    private function json_response(array $data, $status = 200)
    {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data));
    }
}
