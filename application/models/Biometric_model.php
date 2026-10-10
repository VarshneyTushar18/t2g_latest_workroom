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

            // Prefer staff full name when Biometric sends an empty name.
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

            $force_replace = !empty($row['force_replace'])
                || stripos((string) ($row['remark'] ?? ''), 'department_report') !== false;
            unset($row['force_replace']);

            $row = $this->prepare_biometric_report_row($row);

            $this->db->where('employee_code', $row['employee_code']);
            $this->db->where('attendance_date', $formatted_date);
            $this->db->order_by('id', 'ASC');
            $existing_rows = $this->db->get(db_prefix() . 'biometric_report')->result();

            if (!empty($existing_rows)) {
                // Keep one row per employee/day; delete accidental duplicates.
                $keeper = $existing_rows[0];
                $best_score = $this->biometric_row_completeness_score((array) $keeper);
                foreach ($existing_rows as $cand) {
                    $score = $this->biometric_row_completeness_score((array) $cand);
                    if ($score > $best_score) {
                        $keeper = $cand;
                        $best_score = $score;
                    }
                }
                foreach ($existing_rows as $cand) {
                    if ((int) $cand->id === (int) $keeper->id) {
                        continue;
                    }
                    $this->db->where('id', (int) $cand->id)->delete(db_prefix() . 'biometric_report');
                }

                $keeper_is_official = stripos((string) ($keeper->remark ?? ''), 'department_report') !== false;
                $incoming_is_official = stripos((string) ($row['remark'] ?? ''), 'department_report') !== false;
                $keeper_remark = strtolower((string) ($keeper->remark ?? ''));
                $incoming_remark = strtolower((string) ($row['remark'] ?? ''));

                // Official department report rows are not overwritten by routine API sync.
                if ($keeper_is_official && !$incoming_is_official && !$force_replace) {
                    $skipped++;
                    continue;
                }

                // New office bridge always replaces legacy biomax-bridge rows.
                if (!$force_replace
                    && $incoming_remark === 'biometric-bridge'
                    && $keeper_remark === 'biomax-bridge') {
                    $force_replace = true;
                }

                // Never replace a richer punch day with a thinner sync payload (unless official report import).
                $incoming_score = $this->biometric_row_completeness_score($row);
                $keeper_score = $this->biometric_row_completeness_score((array) $keeper);
                if (!$force_replace && $incoming_score < $keeper_score) {
                    $skipped++;
                    continue;
                }

                unset($row['created_at']);
                $this->db->where('id', (int) $keeper->id);
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
                'source'   => 'biometric_import',
                'received' => count($data),
                'inserted' => $inserted,
                'updated'  => $updated,
                'skipped'  => $skipped,
            ]);
        }

        return $result;
    }

    /**
     * Normalize punch_records text and derive reliable a_in_time / a_out_time from punches.
     *
     * @param array $row
     * @return array
     */
    public function prepare_biometric_report_row(array $row)
    {
        $allowed = [
            'attendance_date', 'company', 'location', 'employee_code', 'employee_name', 'shift',
            's_in_time', 's_out_time', 'a_in_time', 'a_out_time', 'work_duration', 't_duration',
            'late_by', 'early_going_by', 'over_time', 'status', 'punch_records', 'remark', 'created_at',
        ];

        $punch_records = $this->normalize_punch_records_string($row['punch_records'] ?? '');
        $row['punch_records'] = $punch_records;

        $summary = $this->derive_summary_times_from_punch_records($punch_records, $row['attendance_date'] ?? '');
        if ($punch_records !== '') {
            $row['a_in_time'] = $summary['a_in_time'];
            $row['a_out_time'] = $summary['a_out_time'];
        } else {
            $row['a_in_time'] = '';
            $row['a_out_time'] = '';
            if (!empty($row['force_clear_punches'])) {
                $row['work_duration'] = '';
                $row['t_duration'] = '';
            }
        }

        unset($row['force_clear_punches']);

        $clean = [];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $row)) {
                $clean[$key] = $row[$key];
            }
        }

        return $clean;
    }

    /**
     * Standardize punch list: "09:32:57(in)" -> "09:32:57 (in)" with seconds kept when present.
     */
    public function normalize_punch_records_string($raw)
    {
        $raw = trim((string) $raw);
        if ($raw === '' || strtolower($raw) === 'nan') {
            return '';
        }

        $parts = preg_split('/\s*,\s*/', $raw);
        $normalized = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            if (preg_match('/^(\d{1,2}:\d{2}(?::\d{2})?)\s*(?:\()?[\s\-]*(in|out)(?:\))?$/i', $part, $m)) {
                $time = $this->normalize_punch_time_token($m[1]);
                $normalized[] = $time . ' (' . strtolower($m[2]) . ')';
                continue;
            }
            if (preg_match('/^\s*(in|out)\b\s*[:\-]?\s*(\d{1,2}:\d{2}(?::\d{2})?)\s*$/i', $part, $m)) {
                $time = $this->normalize_punch_time_token($m[2]);
                $normalized[] = $time . ' (' . strtolower($m[1]) . ')';
                continue;
            }
            $normalized[] = $part;
        }

        return implode(', ', $normalized);
    }

    /**
     * @return array{a_in_time:string,a_out_time:string,last_type:string}
     */
    public function derive_summary_times_from_punch_records($punch_records, $attendance_date = '')
    {
        $punch_records = trim((string) $punch_records);
        if ($punch_records === '') {
            return ['a_in_time' => '', 'a_out_time' => '', 'last_type' => ''];
        }

        $date_str = trim((string) $attendance_date);
        if ($date_str === '') {
            $date_str = date('d-M-Y');
        }

        $events = $this->parse_biometric_punch_events(['punch_records' => $punch_records], $date_str);
        if (empty($events)) {
            return ['a_in_time' => '', 'a_out_time' => '', 'last_type' => ''];
        }

        $a_in = '';
        $a_out = '';
        $last_type = '';
        $day_shift_in = '';

        foreach ($events as $ev) {
            $time = $this->normalize_punch_time_token($ev['time'] ?? '', false);
            $type = strtoupper((string) ($ev['type'] ?? ''));
            $last_type = $type;
            if ($type === 'IN') {
                if ($a_in === '') {
                    $a_in = $time;
                }
                if ($day_shift_in === '' && $this->punch_time_hour($time) >= 6) {
                    $day_shift_in = $time;
                }
            }
            if ($type === 'OUT') {
                $a_out = $time;
            }
        }

        if ($day_shift_in !== '') {
            $a_in = $day_shift_in;
        }

        if ($last_type === 'IN') {
            $a_out = '';
        }

        return ['a_in_time' => $a_in, 'a_out_time' => $a_out, 'last_type' => $last_type];
    }

    /**
     * Assign Unix timestamps to time-only punches on an attendance sheet row.
     * Night-shift rows store after-midnight punches as HH:MM only; advance the
     * calendar day when a punch would fall before an earlier punch on the row.
     *
     * @param string $attendance_ymd Y-m-d (session start / attendance_date)
     * @param array<int, array{time:string}> $punches in sheet order
     * @return array<int, array{time:string,at:int}>
     */
    public function attach_punch_timestamps($attendance_ymd, array $punches)
    {
        $attendance_ymd = date('Y-m-d', strtotime((string) $attendance_ymd));
        $last_at = null;
        $resolved = [];

        foreach ($punches as $pr) {
            $time = trim((string) ($pr['time'] ?? ''));
            if ($time === '') {
                continue;
            }
            if (strlen($time) === 5) {
                $time .= ':00';
            }
            $at = strtotime($attendance_ymd . ' ' . substr($time, 0, 8));
            if ($at === false) {
                continue;
            }
            while ($last_at !== null && $at <= $last_at) {
                $at = strtotime('+1 day', $at);
            }
            $last_at = $at;
            $pr['time'] = substr($time, 0, 8);
            $pr['at'] = $at;
            $resolved[] = $pr;
        }

        return $resolved;
    }

    /**
     * Convert attendance_date (d-M-Y or parseable string) to Y-m-d for timestamp anchoring.
     */
    public function attendance_date_to_ymd($date_str)
    {
        $date_str = trim((string) $date_str);
        if ($date_str === '') {
            return date('Y-m-d');
        }

        $dt = DateTime::createFromFormat('d-M-Y', $date_str);
        if ($dt instanceof DateTime) {
            return $dt->format('Y-m-d');
        }

        $ts = strtotime($date_str);
        if ($ts !== false) {
            return date('Y-m-d', $ts);
        }

        return date('Y-m-d');
    }

    /**
     * True when a post-midnight swipe likely ends a night shift (not a short break return).
     */
    public function is_night_final_exit_after_out($prev_ts, $prev_label, $last_ts)
    {
        $prev_ts = (int) $prev_ts;
        $last_ts = (int) $last_ts;
        if ($prev_ts <= 0 || $last_ts <= 0) {
            return false;
        }
        if (strtolower((string) $prev_label) !== 'out') {
            return false;
        }

        $gap = $last_ts - $prev_ts;
        $min_gap = 90 * 60;
        $max_gap = 240 * 60;

        return date('Y-m-d', $last_ts) !== date('Y-m-d', $prev_ts)
            && (int) date('G', $last_ts) < 6
            && (int) date('G', $prev_ts) >= 15
            && $gap >= $min_gap
            && $gap < $max_gap;
    }

    /**
     * Flip a terminal early-morning IN that should have been OUT (night shift exit).
     *
     * @param array<int, array{type?:string,ts?:int,at?:int}> $events
     * @return array<int, array{type?:string,ts?:int,at?:int}>
     */
    public function fix_trailing_night_exit_mistake_in(array $events)
    {
        if (count($events) < 2) {
            return $events;
        }

        $prev = $events[count($events) - 2];
        $last = $events[count($events) - 1];
        if (strtoupper((string) ($last['type'] ?? '')) !== 'IN') {
            return $events;
        }

        $prev_ts = (int) ($prev['ts'] ?? $prev['at'] ?? 0);
        $last_ts = (int) ($last['ts'] ?? $last['at'] ?? 0);
        if ($this->is_night_final_exit_after_out($prev_ts, $prev['type'] ?? '', $last_ts)) {
            $events[count($events) - 1]['type'] = 'OUT';
        }

        return $events;
    }

    /**
     * @param string $token
     * @param bool $with_seconds
     * @return string
     */
    /**
     * Hour component from HH:MM or HH:MM:SS punch token.
     */
    public function punch_time_hour($token)
    {
        $token = trim((string) $token);
        if (!preg_match('/^(\d{1,2}):/', $token, $m)) {
            return -1;
        }

        return (int) $m[1];
    }

    public function normalize_punch_time_token($token, $with_seconds = true)
    {
        $token = trim((string) $token);
        if (!preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $token, $m)) {
            return $token;
        }
        $h = (int) $m[1];
        $min = $m[2];
        $sec = $m[3] ?? null;
        if ($with_seconds || $sec !== null) {
            $sec = $sec ?? '00';
            return sprintf('%02d:%s:%s', $h, $min, $sec);
        }

        return sprintf('%02d:%s', $h, $min);
    }

    /**
     * Rank a biometric day row by how complete its punch data is.
     * Used to keep the best row when duplicates exist for the same employee/date.
     *
     * @param array $row
     * @return int
     */
    public function biometric_row_completeness_score(array $row)
    {
        $records = trim((string) ($row['punch_records'] ?? ''));
        $score = 0;
        if ($records !== '') {
            $score += 1000 + strlen($records);
            $score += substr_count($records, ',') * 10;
            if (preg_match_all('/\b(in|out)\b/i', $records, $m)) {
                $score += count($m[0]) * 5;
            }
        }
        if (trim((string) ($row['a_in_time'] ?? '')) !== '') {
            $score += 20;
        }
        if (trim((string) ($row['a_out_time'] ?? '')) !== '' && trim((string) ($row['a_out_time'] ?? '')) !== '00:00') {
            $score += 30;
        }
        if (trim((string) ($row['work_duration'] ?? $row['t_duration'] ?? '')) !== '') {
            $score += 10;
        }

        return $score;
    }

    /**
     * Prefer the biometric day row with the richest punch data.
     *
     * @param array<int, array> $rows
     * @return array|null
     */
    public function pick_best_biometric_row(array $rows)
    {
        $best = null;
        $best_score = -1;
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $score = $this->biometric_row_completeness_score($row);
            if ($score > $best_score) {
                $best = $row;
                $best_score = $score;
            }
        }

        return $best;
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
     * Dashboard status for live Biometric sync (target: every 2 minutes).
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
            'source'            => 'Biometric',
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
        // Collapse duplicate employee/date rows — keep the richest punch_records.
        $best_by_key = [];
        foreach ($rows as $row) {
            $key = trim((string) ($row['employee_code'] ?? '')) . '|' . trim((string) ($row['attendance_date'] ?? ''));
            if ($key === '|') {
                continue;
            }
            if (!isset($best_by_key[$key])
                || $this->biometric_row_completeness_score($row) > $this->biometric_row_completeness_score($best_by_key[$key])) {
                $best_by_key[$key] = $row;
            }
        }
        $rows = array_values($best_by_key);

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

            $parsed_punches = [];
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
                $parsed_punches[] = [
                    'time' => $time,
                    'type' => $dir,
                ];
            }

            $resolved_punches = $this->attach_punch_timestamps($att_dt->format('Y-m-d'), $parsed_punches);
            foreach ($resolved_punches as $pr) {
                $time = (string) ($pr['time'] ?? '');
                $dir = strtoupper((string) ($pr['type'] ?? ''));
                $at = (int) ($pr['at'] ?? 0);
                if ($time === '' || $at <= 0) {
                    continue;
                }
                $swipe_ymd = date('Y-m-d', $at);
                $swipes[] = [
                    'employee_name' => $name,
                    'employee_code' => $row['employee_code'],
                    'staffid' => $row['staffid'] ?? '',
                    'swipe_time' => $time,
                    'swipe_date' => date('d M, Y', $at),
                    'swipe_sort' => date('Y-m-d H:i:s', $at),
                    'shift' => $row['shift'] ?: '',
                    'in_out' => $dir,
                    'received_on' => date('d M, Y H:i:s', $at),
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

        // Always join staff (TRIM) so names resolve even when Biometric left employee_name blank.
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
     * Today's Biometric sheet row for a staff member (navbar Check in pill).
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

        if (function_exists('get_option')) {
            $tz = (string) get_option('default_timezone');
            if ($tz !== '' && date_default_timezone_get() !== $tz) {
                date_default_timezone_set($tz);
            }
        }

        return $this->get_staff_punch_for_date($staff_id, date('d-M-Y'));
    }

    /**
     * Biometric row for navbar / Workroom fallback.
     * Workroom check-in is only for staff without an active biometric session.
     *
     * Night shift: merges yesterday + today punches into one 13h session (anchored on first IN).
     * Session resets when first IN + carryover_hours elapses — not at calendar midnight.
     *
     * @param int $staff_id
     * @param float $night_carryover_hours
     * @return array|null
     */
    public function get_staff_active_punch($staff_id, $night_carryover_hours = 13)
    {
        $staff_id = (int) $staff_id;
        $night_carryover_hours = (float) $night_carryover_hours;
        if ($staff_id <= 0) {
            return null;
        }

        $resolved = $this->resolve_staff_navbar_session($staff_id, $night_carryover_hours);
        if ($resolved && !empty($resolved['row']) && !empty($resolved['session'])) {
            $row = $resolved['row'];
            $row['_navbar_session'] = $resolved['session'];

            return $row;
        }

        // Calendar-day fallback: any punch on today's sheet → Biometric navbar (not Workroom check-in).
        return $this->resolve_calendar_day_navbar_fallback($staff_id);
    }

    /**
     * When no 13h session is open, still show Biometric if today's sheet has punches.
     *
     * @param int $staff_id
     * @return array|null
     */
    protected function resolve_calendar_day_navbar_fallback($staff_id)
    {
        $staff_id = (int) $staff_id;
        if ($staff_id <= 0) {
            return null;
        }

        $this->ensure_app_timezone();

        $today_str = date('d-M-Y');
        $today_row = $this->get_staff_punch_for_date($staff_id, $today_str);
        if (!$today_row) {
            return null;
        }

        $events = $this->parse_biometric_punch_events($today_row, $today_str);
        $events = $this->dedupe_biometric_punch_events($events);
        if (empty($events)) {
            return null;
        }

        $day_start = strtotime(date('Y-m-d 00:00:00'));
        $day_end = strtotime(date('Y-m-d 23:59:59'));
        $first_in_ts = $this->navbar_first_in_ts_for_calendar_day($events, $day_start, $day_end);
        if ($first_in_ts <= 0) {
            return null;
        }

        $last_ev = $events[count($events) - 1];
        $last_ts = (int) ($last_ev['ts'] ?? 0);
        $last_type = strtolower((string) ($last_ev['type'] ?? ''));

        $today_row['_navbar_session'] = [
            'first_ts'              => $first_in_ts,
            'last_ts'                 => $last_ts,
            'last_type'               => $last_type,
            'end_at'                  => $day_end,
            'events'                  => $events,
            'is_checked_out'          => $last_type === 'out',
            'calendar_day_fallback'   => true,
        ];

        return $today_row;
    }

    /**
     * Navbar first check-in: keep night-session start unless today has a real morning IN.
     *
     * @param int $session_first_ts
     * @param array<int, array{type?:string,ts?:int}> $events
     */
    protected function resolve_navbar_display_first_ts($session_first_ts, array $events)
    {
        $session_first_ts = (int) $session_first_ts;
        $day_start = strtotime(date('Y-m-d 00:00:00'));
        $day_end = strtotime(date('Y-m-d 23:59:59'));

        if ($session_first_ts >= $day_start) {
            $preferred = $this->navbar_first_in_ts_for_calendar_day($events, $day_start, $day_end);

            return $preferred > 0 ? $preferred : $session_first_ts;
        }

        $preferred = $this->navbar_first_in_ts_for_calendar_day($events, $day_start, $day_end);
        if ($preferred > 0 && (int) date('G', $preferred) >= 6) {
            return $preferred;
        }

        return $session_first_ts;
    }

    /**
     * First IN on calendar day; prefer IN at/after 06:00 (skip overnight carryover on today's row).
     *
     * @param array<int, array{type?:string,ts?:int}> $events
     */
    protected function navbar_first_in_ts_for_calendar_day(array $events, $day_start, $day_end)
    {
        $first_any = 0;
        $first_day_shift = 0;

        foreach ($events as $ev) {
            if (strtoupper((string) ($ev['type'] ?? '')) !== 'IN') {
                continue;
            }
            $ts = (int) ($ev['ts'] ?? 0);
            if ($ts < $day_start || $ts > $day_end) {
                continue;
            }
            if ($first_any <= 0) {
                $first_any = $ts;
            }
            if ((int) date('G', $ts) >= 6 && $first_day_shift <= 0) {
                $first_day_shift = $ts;
            }
        }

        return $first_day_shift > 0 ? $first_day_shift : $first_any;
    }

    /**
     * Whether a biometric row should drive the navbar (current session only).
     */
    public function biometric_row_is_navbar_active($row)
    {
        if (!is_array($row) || empty($row)) {
            return false;
        }

        if (!empty($row['_navbar_session']['end_at']) && time() > (int) $row['_navbar_session']['end_at']) {
            return false;
        }

        $sum = $this->get_biometric_punch_summary($row);
        if (!$sum || empty($sum['last_ts'])) {
            return false;
        }

        $last_type = strtolower((string) ($sum['last_type'] ?? ''));
        if ($last_type === 'in') {
            return true;
        }

        if ($last_type === 'out') {
            $break = $this->get_biometric_break_summary($row);

            return is_array($break) && !empty($break['on_break']);
        }

        return false;
    }

    /**
     * @param array|null $row
     * @param float $carryover_hours
     * @return array|null
     */
    protected function pick_navbar_active_punch_row($row, $carryover_hours)
    {
        if (!is_array($row) || empty($row)) {
            return null;
        }

        if (!empty($row['_navbar_session'])) {
            $session = $row['_navbar_session'];
            $now = time();
            if ($now > (int) ($session['end_at'] ?? 0)) {
                return null;
            }

            return $row;
        }

        $this->ensure_app_timezone();

        $sum = $this->get_biometric_punch_summary($row);
        if (!$sum || empty($sum['last_ts'])) {
            return null;
        }

        $elapsed_hours = (time() - (int) $sum['last_ts']) / 3600;
        if ($elapsed_hours < 0 || $elapsed_hours >= (float) $carryover_hours) {
            return null;
        }

        $has_punch = !empty(trim((string) ($row['punch_records'] ?? '')))
            || !empty(trim((string) ($row['a_in_time'] ?? '')))
            || !empty(trim((string) ($row['a_out_time'] ?? '')));
        if (!$has_punch) {
            return null;
        }

        return $row;
    }

    /**
     * Active navbar session across midnight (yesterday + today biometric rows).
     *
     * @param int $staff_id
     * @param float $carryover_hours
     * @return array{row:array,session:array}|null
     */
    protected function resolve_staff_navbar_session($staff_id, $carryover_hours = 13)
    {
        $staff_id = (int) $staff_id;
        $carryover_hours = (float) $carryover_hours;
        if ($staff_id <= 0) {
            return null;
        }

        $this->ensure_app_timezone();

        $carryover_sec = (int) (max(1, $carryover_hours) * 3600);
        $now = time();
        $events = [];
        $rows_by_date = [];

        foreach (['-1 day', 'today'] as $offset) {
            $ts = strtotime($offset);
            if ($ts === false) {
                continue;
            }
            $date_str = date('d-M-Y', $ts);
            $row = $this->get_staff_punch_for_date($staff_id, $date_str);
            if (!$row) {
                continue;
            }
            $rows_by_date[$date_str] = $row;
            foreach ($this->parse_biometric_punch_events($row, $date_str) as $ev) {
                $events[] = $ev;
            }
        }

        if (empty($events)) {
            return null;
        }

        usort($events, function ($a, $b) {
            return ($a['ts'] ?? 0) <=> ($b['ts'] ?? 0);
        });
        $events = $this->dedupe_biometric_punch_events($events);

        $sessions = [];
        $n = count($events);
        $i = 0;
        while ($i < $n) {
            while ($i < $n && strtoupper((string) ($events[$i]['type'] ?? '')) !== 'IN') {
                $i++;
            }
            if ($i >= $n) {
                break;
            }

            $start_at = (int) ($events[$i]['ts'] ?? 0);
            $end_at = $start_at + $carryover_sec;
            $session_events = [];
            while ($i < $n && (int) ($events[$i]['ts'] ?? 0) <= $end_at) {
                $session_events[] = $events[$i];
                $i++;
            }

            if (!empty($session_events)) {
                $sessions[] = [
                    'start_at' => $start_at,
                    'end_at' => $end_at,
                    'events' => $session_events,
                ];
            }
        }

        if (empty($sessions)) {
            return null;
        }

        $today_start = strtotime(date('Y-m-d 00:00:00'));
        $candidates = [];
        for ($s = count($sessions) - 1; $s >= 0; $s--) {
            $session = $sessions[$s];
            if ($now > (int) ($session['end_at'] ?? 0)) {
                continue;
            }

            $session_events = $session['events'];
            $last = $session_events[count($session_events) - 1];
            if (!$this->navbar_session_event_is_open($last)) {
                continue;
            }

            $candidates[] = $session;
        }

        if (empty($candidates)) {
            return null;
        }

        $active = $candidates[0];
        foreach ($candidates as $session) {
            $start_at = (int) ($session['start_at'] ?? 0);
            if ($start_at >= $today_start && (int) date('G', $start_at) >= 6) {
                $active = $session;
                break;
            }
        }

        $session_events = $active['events'];
        $first_ts = (int) ($session_events[0]['ts'] ?? 0);
        $last_ev = $session_events[count($session_events) - 1];
        $last_ts = (int) ($last_ev['ts'] ?? 0);
        $last_type = strtolower((string) ($last_ev['type'] ?? ''));

        $start_date_str = date('d-M-Y', $first_ts);
        $today_str = date('d-M-Y');
        $primary_row = $rows_by_date[$today_str] ?? ($rows_by_date[$start_date_str] ?? reset($rows_by_date));
        if (!is_array($primary_row)) {
            return null;
        }

        return [
            'row' => $primary_row,
            'session' => [
                'first_ts' => $first_ts,
                'last_ts' => $last_ts,
                'last_type' => $last_type,
                'end_at' => (int) $active['end_at'],
                'events' => $session_events,
                'is_checked_out' => $last_type === 'out',
            ],
        ];
    }

    /**
     * @param array{type?:string,ts?:int} $event
     */
    protected function navbar_session_event_is_open($event)
    {
        if (!is_array($event)) {
            return false;
        }

        $type = strtolower((string) ($event['type'] ?? ''));
        if ($type === 'in') {
            return true;
        }

        if ($type === 'out') {
            return true;
        }

        return false;
    }

    /**
     * @param array<int, array{type:string,time:string,ts:int}> $events
     * @return array<int, array{type:string,time:string,ts:int}>
     */
    protected function dedupe_biometric_punch_events(array $events)
    {
        $deduped = [];
        foreach ($events as $ev) {
            $type = strtoupper((string) ($ev['type'] ?? ''));
            if ($type !== 'IN' && $type !== 'OUT') {
                $deduped[] = $ev;
                continue;
            }
            $ev['type'] = $type;
            $keep = true;
            if (!empty($deduped)) {
                $prev = $deduped[count($deduped) - 1];
                if (strtoupper((string) ($prev['type'] ?? '')) === $type
                    && abs((int) ($ev['ts'] ?? 0) - (int) ($prev['ts'] ?? 0)) <= 120) {
                    $keep = false;
                }
            }
            if ($keep) {
                $deduped[] = $ev;
            }
        }

        return $deduped;
    }

    /**
     * True when office biometric punches are available — Workroom web punch must stay hidden.
     */
    public function staff_biometric_available($staff_id, $night_carryover_hours = 13)
    {
        if (function_exists('timesheets_staff_uses_workroom_attendance')
            && timesheets_staff_uses_workroom_attendance($staff_id, date('Y-m-d'))) {
            return false;
        }

        return $this->get_staff_active_punch($staff_id, $night_carryover_hours) !== null;
    }

    /**
     * Biometric sheet row for one staff on a specific attendance_date (d-M-Y).
     *
     * @return array|null
     */
    public function get_staff_punch_for_date($staff_id, $attendance_date)
    {
        $staff_id = (int) $staff_id;
        $attendance_date = trim((string) $attendance_date);
        if ($staff_id <= 0 || $attendance_date === '') {
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

        $table = db_prefix() . 'biometric_report';
        $placeholders = implode(',', array_fill(0, count($employee_codes), '?'));
        $params = array_merge([$attendance_date], $employee_codes);
        $rows = $this->db->query(
            'SELECT * FROM ' . $table . '
             WHERE attendance_date = ?
               AND TRIM(employee_code) IN (' . $placeholders . ')',
            $params
        )->result_array();

        if (empty($rows)) {
            $params2 = array_merge([$attendance_date], $employee_codes);
            $rows = $this->db->query(
                'SELECT * FROM ' . $table . '
                 WHERE LOWER(attendance_date) = LOWER(?)
                   AND TRIM(employee_code) IN (' . $placeholders . ')',
                $params2
            )->result_array();
        }

        $row = $this->pick_best_biometric_row($rows);

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
     * Get summary of Biometric punches for today:
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
    public function get_biometric_punch_summary($row)
    {
        if (!is_array($row) || empty($row)) {
            return null;
        }

        if (!empty($row['_navbar_session']) && is_array($row['_navbar_session'])) {
            $sess = $row['_navbar_session'];
            $first_ts = (int) ($sess['first_ts'] ?? 0);
            $last_ts = (int) ($sess['last_ts'] ?? 0);
            if ($first_ts <= 0 || $last_ts <= 0) {
                return null;
            }

            if (!empty($sess['events']) && is_array($sess['events'])) {
                $first_ts = $this->resolve_navbar_display_first_ts($first_ts, $sess['events']);
            }

            $last_type = strtolower((string) ($sess['last_type'] ?? ''));
            if ($last_type !== 'in' && $last_type !== 'out') {
                $last_type = 'in';
            }

            return [
                'first_time_formatted' => date('H:i:s', $first_ts),
                'first_ts'             => $first_ts,
                'last_time_formatted'  => date('H:i:s', $last_ts),
                'last_ts'              => $last_ts,
                'last_type'            => $last_type,
                'is_checked_out'       => !empty($sess['is_checked_out']),
            ];
        }

        $date_str = trim((string) ($row['attendance_date'] ?? ''));
        $events = $this->parse_biometric_punch_events($row, $date_str);
        if (empty($events)) {
            return null;
        }

        $first_ev = $events[0];
        $last_ev = $events[count($events) - 1];
        $first_ts = (int) ($first_ev['ts'] ?? 0);
        $last_ts = (int) ($last_ev['ts'] ?? 0);
        $last_type = strtolower((string) ($last_ev['type'] ?? ''));
        if ($last_type !== 'in' && $last_type !== 'out') {
            $last_type = 'in';
        }

        $first_formatted = date('H:i:s', $first_ts);
        $last_formatted = date('H:i:s', $last_ts);

        return [
            'first_time_formatted' => $first_formatted,
            'first_ts'             => $first_ts,
            'last_time_formatted'  => $last_formatted,
            'last_ts'              => $last_ts,
            'last_type'            => $last_type,
            'is_checked_out'       => $last_type === 'out',
        ];
    }

    /** @deprecated Use get_biometric_punch_summary() */
    public function get_biomax_punch_summary($row)
    {
        return $this->get_biometric_punch_summary($row);
    }

    /**
     * Navbar label: first IN of the active attendance session (may start previous calendar day).
     *
     * @param array|null $row
     */
    public function get_navbar_first_checkin_display($row)
    {
        if (!is_array($row) || empty($row)) {
            return '';
        }

        if (!empty($row['_navbar_session']['first_ts'])) {
            $first_ts = (int) $row['_navbar_session']['first_ts'];
            if (!empty($row['_navbar_session']['events']) && is_array($row['_navbar_session']['events'])) {
                $first_ts = $this->resolve_navbar_display_first_ts(
                    $first_ts,
                    $row['_navbar_session']['events']
                );
            }

            return date('H:i:s', $first_ts);
        }

        $date_str = trim((string) ($row['attendance_date'] ?? ''));
        $events = $this->parse_biometric_punch_events($row, $date_str);
        foreach ($events as $ev) {
            if (($ev['type'] ?? '') === 'IN' && !empty($ev['ts'])) {
                return date('H:i:s', (int) $ev['ts']);
            }
        }

        $sum = $this->get_biometric_punch_summary($row);
        if (!empty($sum['first_ts'])) {
            return date('H:i:s', (int) $sum['first_ts']);
        }

        $a_in = trim((string) ($row['a_in_time'] ?? ''));
        if ($a_in !== '' && strtolower($a_in) !== 'nil') {
            $formatted = $this->format_punch_time_display($a_in);
            if ($formatted !== '') {
                return $formatted;
            }
        }

        return '';
    }

    /**
     * Short tea/rest breaks from OUT→IN swipe gaps (1–60 min). Lunch/away gaps are excluded.
     *
     * @param array<int, array{type:string,ts:int}> $events
     * @return array{breaks:array,away_gaps:array,completed_secs:int}
     */
    public function calculate_short_break_from_events(array $events, $min_break_secs = 60, $max_break_secs = 3600)
    {
        $this->ensure_app_timezone();

        $on_break = false;
        $break_start_ts = 0;
        $breaks = [];
        $away_gaps = [];
        $completed_secs = 0;

        foreach ($events as $ev) {
            $type = strtoupper((string) ($ev['type'] ?? ''));
            $ts = (int) ($ev['ts'] ?? 0);

            if ($type === 'OUT') {
                if (!$on_break) {
                    $on_break = true;
                    $break_start_ts = $ts;
                }
                continue;
            }

            if ($type === 'IN' && $on_break && $break_start_ts > 0) {
                $dur = max(0, $ts - $break_start_ts);
                $gap = [
                    'out_time'        => date('H:i', $break_start_ts),
                    'in_time'         => date('H:i', $ts),
                    'duration_secs'   => $dur,
                    'duration_label'  => $this->format_break_duration($dur),
                ];
                if ($dur >= $min_break_secs && $dur <= $max_break_secs) {
                    $breaks[] = $gap;
                    $completed_secs += $dur;
                } elseif ($dur > $max_break_secs) {
                    $away_gaps[] = $gap;
                }
                $on_break = false;
                $break_start_ts = 0;
            }
        }

        return [
            'breaks'         => $breaks,
            'away_gaps'      => $away_gaps,
            'completed_secs' => $completed_secs,
        ];
    }

    /**
     * Short-break seconds for one attendance_date row (today's sheet only).
     */
    public function calculate_short_break_seconds_from_punch_records($punch_records, $attendance_date = null)
    {
        $date_str = trim((string) ($attendance_date ?: date('d-M-Y')));
        if ($date_str === '' || trim((string) $punch_records) === '') {
            return 0;
        }

        $events = $this->parse_biometric_punch_events(
            ['punch_records' => $punch_records, 'attendance_date' => $date_str],
            $date_str
        );

        return (int) ($this->calculate_short_break_from_events($events)['completed_secs'] ?? 0);
    }

    /**
     * HH:MM:SS label for biometric attendance table Break column.
     */
    public function format_short_break_hms($seconds)
    {
        $seconds = max(0, (int) $seconds);
        $h = (int) floor($seconds / 3600);
        $m = (int) floor(($seconds % 3600) / 60);
        $s = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }

    /**
     * Break periods from today's biometric sheet only (not 13h session merge).
     *
     * @param array|null $row biometric_report row
     * @return array{
     *   breaks: array<int, array{out_time:string,in_time:string,duration_secs:int,duration_label:string}>,
     *   completed_secs:int,
     *   current_break_secs:int,
     *   total_secs:int,
     *   on_break:bool,
     *   current_out_ts:int,
     *   tooltip:string,
     *   total_label:string,
     *   current_label:string
     * }|null
     */
    public function get_biometric_break_summary($row)
    {
        if (!is_array($row) || empty($row)) {
            return null;
        }

        $this->ensure_app_timezone();

        // Always use this row's calendar-day punches — never _navbar_session (yesterday merge).
        $date_str = trim((string) ($row['attendance_date'] ?? ''));
        if ($date_str === '') {
            $date_str = date('d-M-Y');
        }

        $events = $this->parse_biometric_punch_events($row, $date_str);
        if (empty($events)) {
            return null;
        }

        $max_break_secs = 3600;
        $calc = $this->calculate_short_break_from_events($events);
        $breaks = $calc['breaks'];
        $away_gaps = $calc['away_gaps'];
        $completed_secs = (int) $calc['completed_secs'];

        $on_break = false;
        $break_start_ts = 0;
        $current_break_secs = 0;
        $last_event = $events[count($events) - 1];
        $last_is_out = ($last_event['type'] ?? '') === 'OUT';

        if ($last_is_out) {
            $trailing_out_ts = (int) ($last_event['ts'] ?? 0);
            if ($trailing_out_ts > 0) {
                $current_break_secs = max(0, time() - $trailing_out_ts);
                if ($current_break_secs <= $max_break_secs) {
                    $on_break = true;
                    $break_start_ts = $trailing_out_ts;
                } else {
                    $current_break_secs = 0;
                }
            }
        }

        $total_secs = $completed_secs;

        $tooltip_lines = [];
        foreach ($breaks as $i => $b) {
            $tooltip_lines[] = 'Break ' . ($i + 1) . ': ' . $b['out_time'] . '–' . $b['in_time'] . ' (' . $b['duration_label'] . ')';
        }
        if ($on_break && $break_start_ts > 0) {
            $tooltip_lines[] = 'On break since ' . date('H:i', $break_start_ts) . ' (' . $this->format_break_duration($current_break_secs) . ', not added until you punch IN)';
        }
        foreach ($away_gaps as $i => $g) {
            $tooltip_lines[] = 'Away ' . ($i + 1) . ': ' . $g['out_time'] . '–' . $g['in_time'] . ' (' . $g['duration_label'] . ', lunch — not counted)';
        }

        return [
            'breaks'              => $breaks,
            'completed_secs'      => $completed_secs,
            'current_break_secs'  => $current_break_secs,
            'total_secs'          => $total_secs,
            'on_break'            => $on_break,
            'current_out_ts'      => 0,
            'tooltip'             => $tooltip_lines ? implode("\n", $tooltip_lines) : 'No breaks recorded yet',
            'total_label'         => $this->format_break_duration($total_secs),
            'completed_label'     => $this->format_break_duration($completed_secs),
            'current_label'       => $this->format_break_duration($current_break_secs),
        ];
    }

    /**
     * Parse punch_records text into ordered time/type rows (no timestamps yet).
     *
     * @return array<int, array{time:string,type:string}>
     */
    protected function parse_raw_punch_tokens_from_records($records)
    {
        $records = trim((string) $records);
        if ($records === '') {
            return [];
        }

        $rows = [];
        if (preg_match_all('/(\d{1,2}:\d{2}(?::\d{2})?)\s*\(?\s*(in|out)\s*\)?/i', $records, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $time = trim(preg_replace('/\s*\(.*$/', '', $m[1]));
                if (strlen($time) === 5) {
                    $time .= ':00';
                }
                $rows[] = [
                    'time' => substr($time, 0, 8),
                    'type' => strtoupper($m[2]) === 'OUT' ? 'OUT' : 'IN',
                ];
            }

            return $rows;
        }

        $parts = preg_split('/\s*,\s*/', $records);
        foreach ($parts as $punch) {
            $punch = trim($punch);
            if ($punch === '') {
                continue;
            }
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
            $time = trim(preg_replace('/\s*\(.*$/', '', $time));
            if (strlen($time) === 5) {
                $time .= ':00';
            }
            $rows[] = [
                'time' => substr($time, 0, 8),
                'type' => $dir === 'OUT' ? 'OUT' : 'IN',
            ];
        }

        return $rows;
    }

    /**
     * Build timestamped punch events with midnight rollover and night-exit correction.
     *
     * @return array<int, array{type:string,time:string,ts:int}>
     */
    protected function parse_biometric_punch_events($row, $date_str)
    {
        $this->ensure_app_timezone();

        $raw_punches = $this->parse_raw_punch_tokens_from_records($row['punch_records'] ?? '');
        if (empty($raw_punches)) {
            $in = trim((string) ($row['a_in_time'] ?? ''));
            $out = trim((string) ($row['a_out_time'] ?? ''));
            if ($in !== '') {
                $raw_punches[] = ['time' => $this->normalize_punch_time_token($in), 'type' => 'IN'];
            }
            if ($out !== '' && $out !== '00:00' && $out !== '00:00:00' && strtolower($out) !== 'nil') {
                $raw_punches[] = ['time' => $this->normalize_punch_time_token($out), 'type' => 'OUT'];
            }
        }

        if (empty($raw_punches)) {
            return [];
        }

        $ymd = $this->attendance_date_to_ymd($date_str);
        $with_at = $this->attach_punch_timestamps($ymd, $raw_punches);

        $events = [];
        foreach ($with_at as $pr) {
            $events[] = [
                'type' => strtoupper((string) ($pr['type'] ?? 'IN')) === 'OUT' ? 'OUT' : 'IN',
                'time' => substr((string) ($pr['time'] ?? ''), 0, 8),
                'ts'   => (int) ($pr['at'] ?? 0),
            ];
        }

        usort($events, function ($a, $b) {
            return ($a['ts'] ?? 0) <=> ($b['ts'] ?? 0);
        });

        $events = $this->dedupe_biometric_punch_events($events);

        return $this->fix_trailing_night_exit_mistake_in($events);
    }

    /**
     * @param int $seconds
     * @return string
     */
    protected function ensure_app_timezone()
    {
        if (!function_exists('get_option')) {
            return;
        }
        $tz = (string) get_option('default_timezone');
        if ($tz !== '' && date_default_timezone_get() !== $tz) {
            date_default_timezone_set($tz);
        }
    }

    public function format_break_duration($seconds)
    {
        $seconds = max(0, (int) $seconds);
        if ($seconds < 60) {
            return $seconds . 's';
        }
        $mins = (int) floor($seconds / 60);
        if ($mins < 60) {
            return $mins . 'm';
        }
        $h = (int) floor($mins / 60);
        $m = $mins % 60;

        return $m > 0 ? ($h . 'h ' . $m . 'm') : ($h . 'h');
    }

    /**
     * Check if a Biometric row has a recorded out punch or the last swipe was OUT.
     *
     * @param array|null $row
     * @return bool
     */
    public function is_biometric_checked_out($row)
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

    /** @deprecated Use is_biometric_checked_out() */
    public function is_biomax_checked_out($row)
    {
        return $this->is_biometric_checked_out($row);
    }

    /**
     * Format Biometric sheet time (HH:MM / HH:MM:SS / 12h) as 24-hour H:i:s for display.
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
                    return $dt->format('H:i:s');
                }
            }
        }

        $ts = strtotime($time);
        if ($ts !== false) {
            return date('H:i:s', $ts);
        }

        return $time;
    }
}
