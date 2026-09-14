<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Biometric_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function insert_attendance_bulk($data)
    {
        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($data as $row) {
            $formatted_date = $this->normalize_attendance_date($row['attendance_date'] ?? '');
            if ($formatted_date === false) {
                log_message('error', 'Invalid attendance_date: ' . ($row['attendance_date'] ?? ''));
                $skipped++;
                continue;
            }

            if (trim((string) ($row['employee_code'] ?? '')) === '') {
                $skipped++;
                continue;
            }

            $row['attendance_date'] = $formatted_date;
            $row['employee_code'] = trim((string) $row['employee_code']);
            $row['created_at'] = date('Y-m-d H:i:s');

            // Prefer staff full name when Biomax sends an empty name.
            if (trim((string) ($row['employee_name'] ?? '')) === '') {
                $staff_name = $this->resolve_staff_name_by_code($row['employee_code']);
                if ($staff_name !== '') {
                    $row['employee_name'] = $staff_name;
                }
            }

            // UI shows t_duration; bridge often sends work_duration.
            if (trim((string) ($row['t_duration'] ?? '')) === '' && trim((string) ($row['work_duration'] ?? '')) !== '') {
                $row['t_duration'] = $row['work_duration'];
            }

            $this->db->where('employee_code', $row['employee_code']);
            $this->db->where('attendance_date', $formatted_date);
            $existing = $this->db->get(db_prefix() . 'biometric_report')->row();

            if ($existing) {
                $this->db->where('id', $existing->id);
                $this->db->update(db_prefix() . 'biometric_report', $row);
                $updated++;
            } else {
                $this->db->insert(db_prefix() . 'biometric_report', $row);
                $inserted++;
            }
        }

        $result = [
            'success'  => ($inserted + $updated) > 0,
            'inserted' => $inserted,
            'updated'  => $updated,
            'skipped'  => $skipped,
        ];

        if ($inserted > 0 || $updated > 0) {
            $this->record_sync_run([
                'source'   => 'biomax_api',
                'received' => count($data),
                'inserted' => $inserted,
                'updated'  => $updated,
                'skipped'  => $skipped,
            ]);
        }

        return $result;
    }

    /**
     * Persist last successful biometric sync metadata for the admin dashboard.
     */
    public function record_sync_run(array $meta)
    {
        $payload = [
            'at'       => date('Y-m-d H:i:s'),
            'source'   => (string) ($meta['source'] ?? 'api'),
            'received' => (int) ($meta['received'] ?? 0),
            'inserted' => (int) ($meta['inserted'] ?? 0),
            'updated'  => (int) ($meta['updated'] ?? 0),
            'skipped'  => (int) ($meta['skipped'] ?? 0),
        ];

        update_option('biometric_last_sync_at', $payload['at']);
        update_option('biometric_last_sync_meta', json_encode($payload));

        return $payload;
    }

    /**
     * Dashboard status for live Biomax sync (target: every 2 minutes).
     */
    public function get_sync_status()
    {
        $last_at = (string) get_option('biometric_last_sync_at');
        $meta_raw = (string) get_option('biometric_last_sync_meta');
        $meta = json_decode($meta_raw, true);
        if (!is_array($meta)) {
            $meta = [];
        }

        $target_interval = 120;
        $stale_after = 300;
        $age_seconds = null;
        $is_live = false;

        if ($last_at !== '') {
            $ts = strtotime($last_at);
            if ($ts !== false) {
                $age_seconds = max(0, time() - $ts);
                $is_live = $age_seconds <= $stale_after;
            }
        }

        $table = db_prefix() . 'biometric_report';
        $today = date('d-M-Y');
        $today_rows = (int) $this->db
            ->from($table)
            ->where('attendance_date', $today)
            ->count_all_results();

        return [
            'mode'              => 'auto',
            'source'            => 'Biomax',
            'target_interval_s' => $target_interval,
            'last_sync_at'      => $last_at,
            'last_sync_age_s'   => $age_seconds,
            'is_live'           => $is_live,
            'last_sync'         => $meta,
            'today'             => $today,
            'today_rows'        => $today_rows,
            'manual_import'     => false,
        ];
    }

    /**
     * Normalize many common Excel / export date formats to d-M-Y.
     */
    public function normalize_attendance_date($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return false;
        }

        if (is_numeric($value) && (float) $value > 20000 && (float) $value < 80000) {
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value);
                return $dt->format('d-M-Y');
            } catch (Exception $e) {
                // fall through
            }
        }

        $formats = [
            'd-M-Y',
            'd-M-y',
            'd/M/Y',
            'd/m/Y',
            'd-m-Y',
            'Y-m-d',
            'm/d/Y',
            'd M Y',
            'd-M-Y H:i:s',
            'Y-m-d H:i:s',
        ];

        foreach ($formats as $format) {
            $dt = DateTime::createFromFormat($format, $value);
            if ($dt instanceof DateTime) {
                $errors = DateTime::getLastErrors();
                if (empty($errors['warning_count']) && empty($errors['error_count'])) {
                    return $dt->format('d-M-Y');
                }
            }
        }

        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return date('d-M-Y', $timestamp);
        }

        return false;
    }

    public function get_all_attendance()
    {
        $table = db_prefix() . 'biometric_report';
        $staff = db_prefix() . 'staff';

        $this->db->select($table . '.*, ' . $staff . '.firstname, ' . $staff . '.lastname');
        $this->db->from($table);
        $this->db->join($staff, $staff . '.staff_identifi = ' . $table . '.employee_code', 'left');
        $this->db->where($staff . '.staffid', get_staff_user_id());
        $this->db->limit(50);

        return $this->db->get()->result_array();
    }

    public function get_attendance_paginated($limit, $offset)
    {
        return $this->get_attendance_filtered($limit, $offset, [
            'staff' => get_staff_user_id(),
        ]);
    }

    public function get_attendance_count()
    {
        return $this->get_attendance_filtered_count([
            'staff' => get_staff_user_id(),
        ]);
    }

    public function get_attendance_filtered($limit, $offset, $filters)
    {
        $table = db_prefix() . 'biometric_report';
        $staff = db_prefix() . 'staff';
        $this->db->select(
            $table . '.*, '
            . $staff . '.firstname AS staff_firstname, '
            . $staff . '.lastname AS staff_lastname',
            false
        );
        $this->db->from($table);
        $this->apply_attendance_filters($filters, false);
        $this->db->order_by($table . '.id', 'DESC');
        $this->db->limit((int) $limit, (int) $offset);

        $query = $this->db->get();
        if ($query === false) {
            log_message('error', 'get_attendance_filtered failed: ' . $this->db->error()['message']);
            return [];
        }

        $rows = $query->result_array();
        foreach ($rows as &$row) {
            if (trim((string) ($row['employee_name'] ?? '')) === '') {
                $row['employee_name'] = trim(
                    trim((string) ($row['staff_firstname'] ?? '')) . ' ' . trim((string) ($row['staff_lastname'] ?? ''))
                );
            }
            if (trim((string) ($row['t_duration'] ?? '')) === '' && trim((string) ($row['work_duration'] ?? '')) !== '') {
                $row['t_duration'] = $row['work_duration'];
            }
            unset($row['staff_firstname'], $row['staff_lastname']);
        }
        unset($row);

        return $rows;
    }

    public function get_attendance_filtered_count($filters)
    {
        $table = db_prefix() . 'biometric_report';
        // Avoid CI count_all_results()+GROUP BY (duplicate staffid in subquery).
        $this->db->select('COUNT(DISTINCT ' . $table . '.id) AS cnt', false);
        $this->db->from($table);
        $this->apply_attendance_filters($filters, true);

        $query = $this->db->get();
        if ($query === false) {
            log_message('error', 'get_attendance_filtered_count failed: ' . $this->db->error()['message']);
            return 0;
        }
        $row = $query->row_array();

        return (int) ($row['cnt'] ?? 0);
    }

    public function get_latest_attendance_month()
    {
        $table = db_prefix() . 'biometric_report';
        // Prefer latest imported row; avoids full STR_TO_DATE scan
        $row = $this->db->select('attendance_date')
            ->from($table)
            ->where('attendance_date IS NOT NULL', null, false)
            ->where('attendance_date !=', '')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($row['attendance_date'])) {
            return null;
        }

        $dt = DateTime::createFromFormat('d-M-Y', $row['attendance_date']);
        if (!$dt) {
            $ts = strtotime($row['attendance_date']);
            return $ts ? date('Y-m', $ts) : null;
        }

        return $dt->format('Y-m');
    }

    /**
     * Expand punch_records into individual IN/OUT swipe rows.
     */
    public function get_swipes($filters)
    {
        $from = $filters['from'] ?? date('Y-m-d');
        $to = $filters['to'] ?? date('Y-m-d');
        $staff_id = $filters['staff'] ?? '';
        if ($staff_id === null || $staff_id === '#' || $staff_id === 'all') {
            $staff_id = '';
        }
        // Empty staff = all staff (caller decides); non-admins should pass their own staffid.

        $from_dt = DateTime::createFromFormat('Y-m-d', $from) ?: new DateTime();
        $to_dt = DateTime::createFromFormat('Y-m-d', $to) ?: new DateTime();
        if ($to_dt < $from_dt) {
            $tmp = $from_dt;
            $from_dt = $to_dt;
            $to_dt = $tmp;
        }

        $months = [];
        $cursor = clone $from_dt;
        $cursor->modify('first day of this month');
        $end_month = clone $to_dt;
        $end_month->modify('first day of this month');
        while ($cursor <= $end_month) {
            $months[] = $cursor->format('Y-m');
            $cursor->modify('+1 month');
        }

        $table = db_prefix() . 'biometric_report';
        $staff = db_prefix() . 'staff';
        $this->db->select($table . '.employee_name, ' . $table . '.employee_code, ' . $table . '.attendance_date, ' . $table . '.shift, ' . $table . '.punch_records, ' . $staff . '.firstname, ' . $staff . '.lastname, ' . $staff . '.staffid');
        $this->db->from($table);
        $info = db_prefix() . 'staff_info';
        // Match Emp ID on staff_identifi OR staff_info.empid.
        $this->db->join(
            $staff,
            '(TRIM(' . $staff . '.staff_identifi) = TRIM(' . $table . '.employee_code)
              OR ' . $staff . '.staffid = (
                    SELECT si.staffid FROM ' . $info . ' si
                    WHERE TRIM(COALESCE(si.empid, \'\')) = TRIM(' . $table . '.employee_code)
                    LIMIT 1
              ))',
            'left',
            false
        );
        if (!empty($staff_id) && is_numeric($staff_id)) {
            $this->db->where($staff . '.staffid', (int) $staff_id);
        } elseif (!empty($filters['staff_ids']) && is_array($filters['staff_ids'])) {
            $ids = array_values(array_unique(array_filter(array_map('intval', $filters['staff_ids']))));
            if ($ids) {
                $this->db->where_in($staff . '.staffid', $ids);
            } else {
                $this->db->where('1 = 0', null, false);
            }
        }

        $like_group = [];
        foreach ($months as $month) {
            $mdt = DateTime::createFromFormat('Y-m', $month);
            if ($mdt) {
                $like_group[] = $table . ".attendance_date LIKE '%-" . $this->db->escape_like_str($mdt->format('M-Y')) . "'";
            }
        }
        if ($like_group) {
            $this->db->where('(' . implode(' OR ', $like_group) . ')', null, false);
        }

        $query = $this->db->get();
        if ($query === false) {
            log_message('error', 'get_swipes failed: ' . ($this->db->error()['message'] ?? 'unknown'));
            return [];
        }
        $rows = $query->result_array();
        $swipes = [];
        $from_ts = strtotime($from_dt->format('Y-m-d'));
        $to_ts = strtotime($to_dt->format('Y-m-d'));

        foreach ($rows as $row) {
            $att_dt = DateTime::createFromFormat('d-M-Y', $row['attendance_date']);
            if (!$att_dt) {
                $ts = strtotime($row['attendance_date']);
                if (!$ts) {
                    continue;
                }
                $att_dt = new DateTime(date('Y-m-d', $ts));
            }
            $att_ts = strtotime($att_dt->format('Y-m-d'));
            if ($att_ts < $from_ts || $att_ts > $to_ts) {
                continue;
            }

            $name = trim(trim((string) ($row['firstname'] ?? '')) . ' ' . trim((string) ($row['lastname'] ?? '')));
            if ($name === '') {
                $name = trim((string) ($row['employee_name'] ?? '')) ?: 'Unknown';
            }

            $punches = preg_split('/\s*,\s*/', (string) ($row['punch_records'] ?? ''));
            foreach ($punches as $punch) {
                $punch = trim($punch);
                if ($punch === '') {
                    continue;
                }
                // Accept: "10:55 (in)", "10:55:42 (IN)", "10:55 in", "IN 10:55"
                if (!preg_match('/(\d{1,2}:\d{2}(?::\d{2})?)\s*(?:\()?[\s\-]*(in|out)(?:\))?/i', $punch, $m)
                    && !preg_match('/\b(in|out)\b\s*[:\-]?\s*(\d{1,2}:\d{2}(?::\d{2})?)/i', $punch, $m2)) {
                    continue;
                }
                if (!empty($m)) {
                    $time = $m[1];
                    $dir = strtoupper($m[2]);
                } else {
                    $dir = strtoupper($m2[1]);
                    $time = $m2[2];
                }
                if (strlen($time) === 5) {
                    $time .= ':00';
                }
                $swipes[] = [
                    'employee_name' => $name,
                    'employee_code' => $row['employee_code'],
                    'staffid' => $row['staffid'] ?? '',
                    'swipe_time' => $time,
                    'swipe_date' => $att_dt->format('d M, Y'),
                    'swipe_sort' => $att_dt->format('Y-m-d') . ' ' . $time,
                    'shift' => $row['shift'] ?: '',
                    'in_out' => $dir,
                    'received_on' => $att_dt->format('d M, Y') . ' ' . $time,
                    'door' => 'Biometrics swipe',
                    'status' => 'Approved',
                ];
            }
        }

        usort($swipes, function ($a, $b) {
            return strcmp($b['swipe_sort'], $a['swipe_sort']);
        });

        return $swipes;
    }

    /**
     * @param bool $for_count when true, skip GROUP BY (count uses COUNT DISTINCT)
     */
    private function apply_attendance_filters($filters, $for_count = false)
    {
        $table = db_prefix() . 'biometric_report';
        $staff = db_prefix() . 'staff';
        $staff_dept = db_prefix() . 'staff_departments';

        $department = $filters['department'] ?? '';
        $staff_id = $filters['staff'] ?? '';

        // Always join staff (TRIM) so names resolve even when Biomax left employee_name blank.
        $this->db->join(
            $staff,
            'TRIM(' . $staff . '.staff_identifi) = TRIM(' . $table . '.employee_code)',
            'left',
            false
        );

        if ($department !== '' && $department !== null && $department !== '#' && is_numeric($department)) {
            $this->db->join(
                $staff_dept,
                $staff . '.staffid = ' . $staff_dept . '.staffid AND ' . $staff_dept . '.departmentid = ' . (int) $department,
                'inner',
                false
            );
            // Only needed for row queries (join can multiply rows if multi-dept); count uses DISTINCT.
            if (!$for_count) {
                $this->db->group_by($table . '.id');
            }
        }

        if ($staff_id !== '' && $staff_id !== null && is_numeric($staff_id)) {
            $this->db->where($staff . '.staffid', (int) $staff_id);
        } elseif (!empty($filters['staff_ids']) && is_array($filters['staff_ids'])) {
            $ids = array_values(array_unique(array_filter(array_map('intval', $filters['staff_ids']))));
            if ($ids) {
                $this->db->where_in($staff . '.staffid', $ids);
            } else {
                $this->db->where('1 = 0', null, false);
            }
        }

        $month = $filters['month'] ?? '';
        $day = $filters['day'] ?? '';

        // Exact day wins over month (Today / Pick a day).
        if (!empty($day)) {
            $formatted = $this->normalize_attendance_date($day);
            if ($formatted !== false) {
                $this->db->where($table . '.attendance_date', $formatted);
            }
        } elseif (!empty($month) && preg_match('/^\d{4}-\d{2}$/', $month)) {
            // attendance_date stored as d-M-Y, e.g. 08-Nov-2025
            $dt = DateTime::createFromFormat('Y-m', $month);
            if ($dt) {
                $suffix = '-' . $dt->format('M-Y'); // -Nov-2025
                $this->db->like($table . '.attendance_date', $suffix, 'before');
            }
        }
    }

    /**
     * Resolve Workroom staff display name from Emp ID / staff_identifi.
     */
    private function resolve_staff_name_by_code($employee_code)
    {
        $employee_code = trim((string) $employee_code);
        if ($employee_code === '') {
            return '';
        }

        $staff = db_prefix() . 'staff';
        $info = db_prefix() . 'staff_info';
        $row = $this->db->query(
            'SELECT s.firstname, s.lastname
             FROM ' . $staff . ' s
             LEFT JOIN ' . $info . ' i ON i.staffid = s.staffid
             WHERE TRIM(COALESCE(s.staff_identifi, \'\')) = ?
                OR TRIM(COALESCE(i.empid, \'\')) = ?
             LIMIT 1',
            [$employee_code, $employee_code]
        )->row_array();

        if (!$row) {
            return '';
        }

        return trim(trim((string) ($row['firstname'] ?? '')) . ' ' . trim((string) ($row['lastname'] ?? '')));
    }

    /**
     * Today's Biomax sheet row for a staff member (navbar Check in pill).
     * Daily only: matches tblbiometric_report.attendance_date for today (d-M-Y).
     * No punch for today → null (pill hidden until the office bridge syncs today's row).
     *
     * @return array{a_in_time?:string,a_out_time?:string,punch_records?:string,attendance_date?:string}|null
     */
    public function get_staff_today_punch($staff_id)
    {
        $staff_id = (int) $staff_id;
        if ($staff_id <= 0) {
            return null;
        }

        $staff = db_prefix() . 'staff';
        $info = db_prefix() . 'staff_info';
        $codes = $this->db->query(
            'SELECT TRIM(COALESCE(s.staff_identifi, \'\')) AS identifi,
                    TRIM(COALESCE(i.empid, \'\')) AS empid
             FROM ' . $staff . ' s
             LEFT JOIN ' . $info . ' i ON i.staffid = s.staffid
             WHERE s.staffid = ?
             LIMIT 1',
            [$staff_id]
        )->row_array();

        if (!$codes) {
            return null;
        }

        $employee_codes = [];
        foreach (['identifi', 'empid'] as $k) {
            $c = trim((string) ($codes[$k] ?? ''));
            if ($c !== '' && !in_array($c, $employee_codes, true)) {
                $employee_codes[] = $c;
            }
        }
        if (empty($employee_codes)) {
            return null;
        }

        // Ensure timezone is aligned with application default_timezone.
        if (function_exists('get_option')) {
            $tz = (string) get_option('default_timezone');
            if ($tz !== '' && date_default_timezone_get() !== $tz) {
                date_default_timezone_set($tz);
            }
        }

        // Strictly today's calendar date key (e.g. 12-Sep-2026). Never matches previous or next days.
        $today = date('d-M-Y');

        $table = db_prefix() . 'biometric_report';
        $placeholders = implode(',', array_fill(0, count($employee_codes), '?'));
        $params = array_merge([$today], $employee_codes);
        $row = $this->db->query(
            'SELECT * FROM ' . $table . '
             WHERE attendance_date = ?
               AND TRIM(employee_code) IN (' . $placeholders . ')
             ORDER BY id DESC
             LIMIT 1',
            $params
        )->row_array();

        // Soft fallback: case-insensitive day match if format casing differs (Sep vs SEP).
        if (!$row) {
            $params2 = array_merge([$today], $employee_codes);
            $row = $this->db->query(
                'SELECT * FROM ' . $table . '
                 WHERE LOWER(attendance_date) = LOWER(?)
                   AND TRIM(employee_code) IN (' . $placeholders . ')
                 ORDER BY id DESC
                 LIMIT 1',
                $params2
            )->row_array();
        }

        if ($row) {
            $has_punch = !empty(trim((string) ($row['a_in_time'] ?? '')))
                || !empty(trim((string) ($row['a_out_time'] ?? '')))
                || !empty(trim((string) ($row['punch_records'] ?? '')));
            if (!$has_punch) {
                return null;
            }
        }

        return $row ?: null;
    }

    /**
     * Get summary of Biomax punches for today:
     * - first_time_formatted: original arrival punch (e.g. '10:49:00 AM')
     * - first_ts: unix timestamp of first punch
     * - last_time_formatted: latest punch (e.g. '02:28:00 PM')
     * - last_ts: unix timestamp of latest punch
     * - last_type: 'in' | 'out'
     * - is_checked_out: true if last swipe is 'out'
     *
     * @param array|null $row
     * @return array{first_time_formatted:string,first_ts:int,last_time_formatted:string,last_ts:int,last_type:string,is_checked_out:bool}|null
     */
    public function get_biomax_punch_summary($row)
    {
        if (!is_array($row) || empty($row)) {
            return null;
        }

        $date_str = trim((string) ($row['attendance_date'] ?? ''));
        $first_time = '';
        $last_time = '';
        $last_type = '';

        $records = trim((string) ($row['punch_records'] ?? ''));
        if ($records !== '' && preg_match_all('/(\d{1,2}:\d{2}(?::\d{2})?)\s*\(?\s*(in|out)\s*\)?/i', $records, $matches, PREG_SET_ORDER)) {
            $first_time = $matches[0][1];
            $last_match = end($matches);
            $last_time = $last_match[1];
            $last_type = strtolower($last_match[2]);
        }

        if ($first_time === '' && !empty($row['a_in_time'])) {
            $first_time = trim((string) $row['a_in_time']);
        }

        if ($last_time === '' && !empty($row['a_out_time'])) {
            $last_time = trim((string) $row['a_out_time']);
            $last_type = 'out';
        }

        if ($first_time === '' && $last_time === '') {
            return null;
        }

        $is_checked_out = ($last_type === 'out');
        if (!$is_checked_out && $last_type === '' && !empty($row['a_out_time'])) {
            $out_clean = trim((string) $row['a_out_time']);
            if ($out_clean !== '' && $out_clean !== '00:00' && $out_clean !== '00:00:00' && strtolower($out_clean) !== 'nil') {
                $is_checked_out = true;
                $last_type = 'out';
                if ($last_time === '') {
                    $last_time = $out_clean;
                }
            }
        }

        $first_ts = 0;
        if ($first_time !== '' && $date_str !== '') {
            $clean_first = trim(preg_replace('/\s*\(.*$/', '', $first_time));
            $t = strtotime($date_str . ' ' . $clean_first);
            if ($t !== false) {
                $first_ts = $t;
            }
        }

        $last_ts = 0;
        if ($last_time !== '' && $date_str !== '') {
            $clean_last = trim(preg_replace('/\s*\(.*$/', '', $last_time));
            $t = strtotime($date_str . ' ' . $clean_last);
            if ($t !== false) {
                $last_ts = $t;
            }
        }

        return [
            'first_time_formatted' => $this->format_punch_time_display($first_time),
            'first_ts'             => $first_ts,
            'last_time_formatted'  => $this->format_punch_time_display($last_time),
            'last_ts'              => $last_ts,
            'last_type'            => $last_type,
            'is_checked_out'       => $is_checked_out,
        ];
    }

    /**
     * Check if a Biomax row has a recorded out punch or the last swipe was OUT.
     *
     * @param array|null $row
     * @return bool
     */
    public function is_biomax_checked_out($row)
    {
        if (!is_array($row) || empty($row)) {
            return false;
        }

        // 1. Inspect punch_records for the latest punch type (in vs out).
        $records = trim((string) ($row['punch_records'] ?? ''));
        if ($records !== '') {
            if (preg_match_all('/(\d{1,2}:\d{2}(?::\d{2})?)\s*\(?\s*(in|out)\s*\)?/i', $records, $matches)) {
                if (!empty($matches[2])) {
                    $last_type = strtolower((string) end($matches[2]));
                    return $last_type === 'out';
                }
            }
        }

        // 2. Fallback to a_out_time presence.
        $out = trim((string) ($row['a_out_time'] ?? ''));
        if ($out !== '' && $out !== '00:00' && $out !== '00:00:00' && strtolower($out) !== 'nil') {
            return true;
        }

        return false;
    }

    /**
     * Format Biomax sheet time (HH:MM / HH:MM:SS) as h:i:s A for navbar.
     */
    public function format_punch_time_display($time)
    {
        $time = trim((string) $time);
        if ($time === '' || $time === '00:00' || $time === '00:00:00' || strtolower($time) === 'nil') {
            return '';
        }

        // Strip trailing (in)/(out) if present in punch snippets.
        $time = preg_replace('/\s*\((in|out)\)\s*$/i', '', $time);
        $time = trim((string) $time);

        $formats = ['H:i:s', 'H:i', 'h:i:s A', 'h:i A', 'h:i:s a', 'h:i a'];
        foreach ($formats as $fmt) {
            $dt = DateTime::createFromFormat($fmt, $time);
            if ($dt instanceof DateTime) {
                $errors = DateTime::getLastErrors();
                if (empty($errors['warning_count']) && empty($errors['error_count'])) {
                    return $dt->format('h:i:s A');
                }
            }
        }

        $ts = strtotime($time);
        if ($ts !== false) {
            return date('h:i:s A', $ts);
        }

        return $time;
    }
}
