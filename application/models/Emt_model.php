<?php



defined('BASEPATH') or exit('No direct script access allowed');



class Emt_model extends App_Model
{

public function get()
  {
    $staff = db_prefix() . 'staff';
    $interval = db_prefix() . 'timeinterval';
    $staffDepartments = db_prefix() . 'staff_departments';
    $departments = db_prefix() . 'departments';

    $sql = "SELECT ti.id, ti.staffid, ti.time_interval, s.email, s.firstname, s.lastname,
            s.team_manage, s.staff_identifi, s.active,
            GROUP_CONCAT(DISTINCT d.name ORDER BY d.name SEPARATOR ', ') AS department_names,
            GROUP_CONCAT(DISTINCT d.departmentid ORDER BY d.departmentid SEPARATOR ',') AS department_ids
        FROM {$interval} ti
        LEFT JOIN {$staff} s ON ti.staffid = s.staffid
        LEFT JOIN {$staffDepartments} sd ON sd.staffid = s.staffid
        LEFT JOIN {$departments} d ON d.departmentid = sd.departmentid
        WHERE s.active = '1'
        GROUP BY ti.id, ti.staffid, ti.time_interval, s.email, s.firstname, s.lastname,
            s.team_manage, s.staff_identifi, s.active
        ORDER BY s.firstname, s.lastname";

    return $this->db->query($sql)->result();
    }

    /**
     * Map Drive folder name (email prefix or firstname.lastname) to department ids.
     */
    public function getFolderDepartmentMap()
    {
        $staff = db_prefix() . 'staff';
        $staffDepartments = db_prefix() . 'staff_departments';
        $rows = $this->db->query(
            "SELECT LOWER(SUBSTRING_INDEX(s.email, '@', 1)) AS email_key,
                    s.firstname, s.lastname, sd.departmentid
             FROM {$staff} s
             JOIN {$staffDepartments} sd ON sd.staffid = s.staffid
             WHERE s.active = 1"
        )->result();

        $map = [];
        foreach ($rows as $row) {
            $keys = array_filter([
                strtolower(trim((string) $row->email_key)),
                strtolower(trim($row->firstname . '.' . $row->lastname)),
                strtolower(preg_replace('/\s+/', '', trim($row->firstname . '.' . $row->lastname))),
            ]);
            foreach ($keys as $key) {
                if (!isset($map[$key])) {
                    $map[$key] = [];
                }
                $deptId = (int) $row->departmentid;
                if (!in_array($deptId, $map[$key], true)) {
                    $map[$key][] = $deptId;
                }
            }
        }

        return $map;
    }

    public function updateInterval($id, $time)
    {
        $id = (int) $id;
        if ($id < 1) {
            return false;
        }

        get_instance()->load->helper('custom');
        $time = snapshot_normalize_interval_time($time);
        if ($time === false) {
            return false;
        }

        $table = db_prefix() . 'timeinterval';
        $exists = $this->db->where('id', $id)->get($table)->row();
        if (!$exists) {
            return false;
        }

        $this->db->where('id', $id);
        $this->db->update($table, ['time_interval' => $time]);

        return true;
    }

    /**
     * @param int[] $ids tbltimeinterval.id
     */
    public function updateIntervalsBulk(array $ids, $time)
    {
        get_instance()->load->helper('custom');
        $time = snapshot_normalize_interval_time($time);
        if ($time === false) {
            return 0;
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return 0;
        }

        $table = db_prefix() . 'timeinterval';
        $this->db->where_in('id', $ids);
        $this->db->update($table, ['time_interval' => $time]);

        return $this->db->affected_rows();
    }

    /**
     * Ensure each active staff has a tbltimeinterval row (default interval for new rows only).
     */
    public function ensureStaffIntervalRows()
    {
        get_instance()->load->helper('custom');

        $staffIds = $this->db->select('staffid')
            ->from(db_prefix() . 'staff')
            ->where('active', 1)
            ->get()
            ->result();

        foreach ($staffIds as $row) {
            snapshot_ensure_staff_timeinterval((int) $row->staffid);
        }

        return true;
    }

    /**
     * Migration helper: set every row to the standard interval (one-time normalize).
     */
    public function syncAllSnapshotIntervals()
    {
        get_instance()->load->helper('custom');
        $interval = snapshot_capture_interval_time();
        $table = db_prefix() . 'timeinterval';
        if ($this->db->table_exists($table)) {
            $this->db->update($table, ['time_interval' => $interval]);
        }

        return $this->ensureStaffIntervalRows();
    }

    /**
     * Whether to show Workroom popup: checked in, on duty, no snapshot activity since first check-in today.
     *
     * @param array<string, mixed> $punchContext from timesheets_admin_navbar_punch_context()
     */
    public function getSnapshotLoginReminderStatus($staffId, array $punchContext = [])
    {
        $staffId = (int) $staffId;
        if ($staffId < 1) {
            return ['show' => false, 'reason' => 'invalid_staff'];
        }

        $staffRow = $this->db->select('active')->where('staffid', $staffId)->get(db_prefix() . 'staff')->row();
        if (!$staffRow || (int) $staffRow->active !== 1) {
            return ['show' => false, 'reason' => 'inactive'];
        }

        $workroomOnDuty = (int) ($punchContext['type_check_in_out'] ?? 0) === 1;
        $bioOnDuty = !empty($punchContext['biometric_navbar_active']) && empty($punchContext['biometric_is_checked_out']);

        if (!$workroomOnDuty) {
            $workroomOnDuty = $this->isWorkroomCheckedInNow($staffId);
        }
        if (!$bioOnDuty) {
            $bioOnDuty = $this->isBiometricOnDutyNow($staffId);
        }

        $onDuty = $workroomOnDuty || $bioOnDuty;
        if (!$onDuty) {
            return ['show' => false, 'reason' => 'not_on_duty'];
        }

        $sessionCheckinTs = $this->resolveSessionCheckinTimestamp($staffId, $workroomOnDuty, $bioOnDuty);
        if ($sessionCheckinTs < 1) {
            return ['show' => false, 'reason' => 'no_checkin_time'];
        }

        if ($this->hasSnapshotActivitySince($staffId, $sessionCheckinTs)) {
            return ['show' => false, 'reason' => 'snapshot_active'];
        }

        return [
            'show'                => true,
            'reason'              => 'pending_snapshot_login',
            'first_checkin_time'  => date('H:i', $sessionCheckinTs),
            'first_checkin_label' => date('g:i A', $sessionCheckinTs),
            'message'             => 'You checked in at ' . date('g:i A', $sessionCheckinTs)
                . ' but the Snapshot monitor is not logged in. Open the Snapshot application on your computer and sign in with your Workroom credentials.',
        ];
    }

    private function isWorkroomCheckedInNow($staffId)
    {
        $row = $this->db->query(
            'SELECT type_check, `date` FROM ' . db_prefix() . 'check_in_out
             WHERE staff_id = ? ORDER BY `date` DESC LIMIT 1',
            [(int) $staffId]
        )->row();
        if (!$row || (int) $row->type_check !== 1 || empty($row->date)) {
            return false;
        }
        $hours = (time() - strtotime($row->date)) / 3600;

        return $hours >= 0 && $hours < 13;
    }

    private function isBiometricOnDutyNow($staffId)
    {
        $CI = &get_instance();
        if (!file_exists(APPPATH . 'models/Biometric_model.php')) {
            return false;
        }
        $CI->load->model('biometric_model');
        $bioRow = null;
        if (method_exists($CI->biometric_model, 'get_staff_active_punch')) {
            $bioRow = $CI->biometric_model->get_staff_active_punch((int) $staffId, 13);
        } elseif (method_exists($CI->biometric_model, 'get_staff_today_punch')) {
            $bioRow = $CI->biometric_model->get_staff_today_punch((int) $staffId);
        }
        if (!is_array($bioRow) || !method_exists($CI->biometric_model, 'get_biometric_punch_summary')) {
            return false;
        }
        $summary = $CI->biometric_model->get_biometric_punch_summary($bioRow);

        return is_array($summary) && empty($summary['is_checked_out']);
    }

    private function resolveSessionCheckinTimestamp($staffId, $workroomOnDuty, $bioOnDuty)
    {
        $candidates = [];

        $todayRow = $this->db->query(
            'SELECT MIN(`date`) AS first_in FROM ' . db_prefix() . 'check_in_out
             WHERE staff_id = ? AND type_check = 1 AND DATE(`date`) = CURDATE()',
            [(int) $staffId]
        )->row();
        if ($todayRow && !empty($todayRow->first_in)) {
            $ts = strtotime($todayRow->first_in);
            if ($ts !== false) {
                $candidates[] = $ts;
            }
        }

        if ($workroomOnDuty) {
            $row = $this->db->query(
                'SELECT `date` FROM ' . db_prefix() . 'check_in_out
                 WHERE staff_id = ? AND type_check = 1 ORDER BY `date` DESC LIMIT 1',
                [(int) $staffId]
            )->row();
            if ($row && !empty($row->date)) {
                $ts = strtotime($row->date);
                if ($ts !== false) {
                    $candidates[] = $ts;
                }
            }
        }

        if ($bioOnDuty) {
            $CI = &get_instance();
            $CI->load->model('biometric_model');
            $bioRow = null;
            if (method_exists($CI->biometric_model, 'get_staff_active_punch')) {
                $bioRow = $CI->biometric_model->get_staff_active_punch((int) $staffId, 13);
            } elseif (method_exists($CI->biometric_model, 'get_staff_today_punch')) {
                $bioRow = $CI->biometric_model->get_staff_today_punch((int) $staffId);
            }
            if (is_array($bioRow) && method_exists($CI->biometric_model, 'get_biometric_punch_summary')) {
                $summary = $CI->biometric_model->get_biometric_punch_summary($bioRow);
                if (!empty($summary['first_ts'])) {
                    $candidates[] = (int) $summary['first_ts'];
                }
            }
        }

        if (empty($candidates)) {
            return 0;
        }

        return min($candidates);
    }

    private function hasSnapshotActivitySince($staffId, $sinceTs)
    {
        $table = db_prefix() . 'staff_drive_data';
        if (!$this->db->table_exists($table)) {
            return false;
        }

        $since = date('Y-m-d H:i:s', (int) $sinceTs);
        $row = $this->db->query(
            'SELECT 1 FROM ' . $table . ' WHERE staff_id = ? AND date_time >= ? LIMIT 1',
            [(int) $staffId, $since]
        )->row();

        return (bool) $row;
    }

    /** @return int[] */
    private function snapshotDashboardExcludedStaffIds()
    {
        return [1, 292, 178];
    }

    /**
     * @param int[]|null $scopeDepartmentIds when set, restrict to these departments (manager view)
     */
    private function snapshotDashboardDepartmentFilterSql($departmentId, array &$params, array $scopeDepartmentIds = null)
    {
        if ($departmentId === null) {
            $departmentId = 0;
        }

        if ($scopeDepartmentIds !== null) {
            $scopeDepartmentIds = array_values(array_unique(array_filter(array_map('intval', $scopeDepartmentIds))));
            if ($scopeDepartmentIds === []) {
                return ' AND 1=0 ';
            }

            $departmentId = (int) $departmentId;
            if ($departmentId > 0) {
                if (!in_array($departmentId, $scopeDepartmentIds, true)) {
                    $departmentId = (int) $scopeDepartmentIds[0];
                }
                $params[] = $departmentId;

                return ' AND s.staffid IN (SELECT staffid FROM ' . db_prefix() . 'staff_departments WHERE departmentid = ?) ';
            }

            $ph = implode(',', array_fill(0, count($scopeDepartmentIds), '?'));
            foreach ($scopeDepartmentIds as $id) {
                $params[] = $id;
            }

            return ' AND s.staffid IN (SELECT staffid FROM ' . db_prefix() . 'staff_departments WHERE departmentid IN (' . $ph . ')) ';
        }

        $departmentId = (int) $departmentId;
        if ($departmentId < 1) {
            return '';
        }
        $params[] = $departmentId;

        return ' AND s.staffid IN (SELECT staffid FROM ' . db_prefix() . 'staff_departments WHERE departmentid = ?) ';
    }

    /**
     * @param int[] $departmentIds
     * @return array<int, true> staffid => true
     */
    public function getActiveStaffIdsInDepartmentScope(array $departmentIds)
    {
        $departmentIds = array_values(array_unique(array_filter(array_map('intval', $departmentIds))));
        if ($departmentIds === []) {
            return [];
        }

        $excluded = implode(',', array_map('intval', $this->snapshotDashboardExcludedStaffIds()));
        $ph = implode(',', array_fill(0, count($departmentIds), '?'));
        $params = $departmentIds;
        $rows = $this->db->query(
            'SELECT DISTINCT s.staffid FROM ' . db_prefix() . 'staff s
             INNER JOIN ' . db_prefix() . 'staff_departments sd ON sd.staffid = s.staffid
             WHERE s.active = 1 AND s.staffid NOT IN (' . $excluded . ')
             AND sd.departmentid IN (' . $ph . ')',
            $params
        )->result();

        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row->staffid] = true;
        }

        return $out;
    }

    public function findStaffIdForDriveFile($fileId)
    {
        $fileId = trim((string) $fileId);
        if ($fileId === '' || !$this->db->table_exists(db_prefix() . 'staff_drive_data')) {
            return 0;
        }

        $row = $this->db->select('staff_id')
            ->from(db_prefix() . 'staff_drive_data')
            ->where('file_id', $fileId)
            ->order_by('date_time', 'DESC')
            ->limit(1)
            ->get()
            ->row();

        return $row ? (int) $row->staff_id : 0;
    }

    /**
     * @return array{0:string,1:string} start/end datetime for "today" in app timezone
     */
    public function snapshotTodayDatetimeBounds()
    {
        return $this->snapshotDatetimeBoundsForYmd(date('Y-m-d'));
    }

    /**
     * Completed-day report date: yesterday in app timezone (today's report is not available until day ends).
     */
    public function snapshotDefaultReportDateYmd()
    {
        $tz = function_exists('get_option') ? trim((string) get_option('default_timezone')) : '';
        $previous = date_default_timezone_get();
        if ($tz !== '') {
            date_default_timezone_set($tz);
        }
        $ymd = date('Y-m-d', strtotime('-1 day'));
        if ($tz !== '') {
            date_default_timezone_set($previous);
        }

        return $ymd;
    }

    /**
     * @return array{0:string,1:string} start/end datetime for a calendar day (Y-m-d) in app timezone
     */
    public function snapshotDatetimeBoundsForYmd($ymd)
    {
        $ymd = trim((string) $ymd);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $ymd)) {
            $ymd = $this->snapshotDefaultReportDateYmd();
        }

        return [$ymd . ' 00:00:00', $ymd . ' 23:59:59'];
    }

    private function formatSnapshotReportTime($value)
    {
        if ($value === null || $value === '') {
            return '';
        }
        $ts = is_numeric($value) ? (int) $value : strtotime((string) $value);
        if (!$ts) {
            return '';
        }

        return date('g:i A', $ts);
    }

    /**
     * Min/max snapshot activity for staff on one day (DB), including rows tied to date folders.
     *
     * @param int[] $staffIds
     * @param string[] $dayFolderIds
     * @return array<int, array{login:string, logout:string}>
     */
    public function snapshotDriveActivityByStaffForDay(array $staffIds, $dayStart, $dayEnd, array $dayFolderIds = [])
    {
        $staffIds = array_values(array_unique(array_filter(array_map('intval', $staffIds))));
        if (empty($staffIds) || !$this->db->table_exists(db_prefix() . 'staff_drive_data')) {
            return [];
        }

        $dayFolderIds = array_values(array_unique(array_filter(array_map('strval', $dayFolderIds))));
        $whereParts = ['(date_time >= ? AND date_time <= ?)'];
        $whereParams = [$dayStart, $dayEnd];
        if (!empty($dayFolderIds)) {
            $folderPh = implode(',', array_fill(0, count($dayFolderIds), '?'));
            $whereParts[] = 'directory_id IN (' . $folderPh . ')';
            $whereParts[] = 'parent_directory_id IN (' . $folderPh . ')';
            $whereParams = array_merge($whereParams, $dayFolderIds, $dayFolderIds);
        }

        $idPh = implode(',', array_fill(0, count($staffIds), '?'));
        $driveTable = db_prefix() . 'staff_drive_data';
        $sql = 'SELECT staff_id,
                MIN(CASE WHEN date_time >= ? AND date_time <= ? THEN date_time ELSE NULL END) AS snapshot_login,
                MAX(CASE WHEN date_time >= ? AND date_time <= ? THEN date_time ELSE NULL END) AS snapshot_logout
            FROM ' . $driveTable . '
            WHERE staff_id IN (' . $idPh . ') AND (' . implode(' OR ', $whereParts) . ')
            GROUP BY staff_id';

        $queryParams = array_merge(
            [$dayStart, $dayEnd, $dayStart, $dayEnd],
            $staffIds,
            $whereParams
        );

        $out = [];
        foreach ($this->db->query($sql, $queryParams)->result() as $row) {
            $sid = (int) $row->staff_id;
            $login = (string) ($row->snapshot_login ?? '');
            $logout = (string) ($row->snapshot_logout ?? '');
            if ($login === '' && $logout === '' && empty($dayFolderIds)) {
                continue;
            }
            if ($logout === '' && $login !== '') {
                $logout = $login;
            }
            $out[$sid] = ['login' => $login, 'logout' => $logout];
        }

        return $out;
    }

    /**
     * @param array<int, string> $dayFolderByStaffId staffid => Drive folder id for report date (Y-m-d)
     */
    public function getSnapshotDailyReportRows($reportDateYmd, $departmentId = null, array $dayFolderByStaffId = [], array $scopeDepartmentIds = null)
    {
        $reportDateYmd = trim((string) $reportDateYmd);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $reportDateYmd)) {
            $reportDateYmd = $this->snapshotDefaultReportDateYmd();
        }

        $maxDate = $this->snapshotDefaultReportDateYmd();
        if ($reportDateYmd > $maxDate) {
            $reportDateYmd = $maxDate;
        }

        [$dayStart, $dayEnd] = $this->snapshotDatetimeBoundsForYmd($reportDateYmd);
        $excluded = implode(',', array_map('intval', $this->snapshotDashboardExcludedStaffIds()));
        $params = [];
        if ($departmentId === -1) {
            return [];
        }

        $deptSql = $this->snapshotDashboardDepartmentFilterSql($departmentId, $params, $scopeDepartmentIds);

        $staffSql = 'SELECT s.staffid, s.firstname, s.lastname, s.staff_identifi,
                TRIM(COALESCE(i.empid, \'\')) AS empid,
                GROUP_CONCAT(DISTINCT d.name ORDER BY d.name SEPARATOR \', \') AS department_name
            FROM ' . db_prefix() . 'staff s
            LEFT JOIN ' . db_prefix() . 'staff_info i ON i.staffid = s.staffid
            LEFT JOIN ' . db_prefix() . 'staff_departments sd ON sd.staffid = s.staffid
            LEFT JOIN ' . db_prefix() . 'departments d ON d.departmentid = sd.departmentid
            WHERE s.active = 1 AND s.staffid NOT IN (' . $excluded . ')' . $deptSql . '
            GROUP BY s.staffid, s.firstname, s.lastname, s.staff_identifi, i.empid
            ORDER BY s.firstname, s.lastname';

        $staffRows = $this->db->query($staffSql, $params)->result();

        $workroomCheckins = [];
        $cio = db_prefix() . 'check_in_out';
        if ($this->db->table_exists($cio)) {
            foreach ($this->db->query(
                'SELECT staff_id, MIN(`date`) AS first_checkin FROM ' . $cio . '
                 WHERE type_check = 1 AND `date` >= ? AND `date` <= ?
                 GROUP BY staff_id',
                [$dayStart, $dayEnd]
            )->result() as $row) {
                $workroomCheckins[(int) $row->staff_id] = (string) $row->first_checkin;
            }
        }

        $staffIds = array_map(function ($r) {
            return (int) $r->staffid;
        }, $staffRows);
        $dayFolderIds = [];
        foreach ($dayFolderByStaffId as $sid => $folderId) {
            if (in_array((int) $sid, $staffIds, true)) {
                $dayFolderIds[] = (string) $folderId;
            }
        }
        $snapshotTimes = $this->snapshotDriveActivityByStaffForDay($staffIds, $dayStart, $dayEnd, $dayFolderIds);

        $bioFirstByStaff = $this->snapshotBiometricFirstCheckinForDate($reportDateYmd, $staffIds);

        $out = [];
        foreach ($staffRows as $row) {
            $staffId = (int) $row->staffid;
            $firstCheckinRaw = '';
            $firstCandidates = [];

            if (!empty($workroomCheckins[$staffId])) {
                $firstCandidates[] = strtotime($workroomCheckins[$staffId]);
            }
            if (!empty($bioFirstByStaff[$staffId])) {
                $firstCandidates[] = (int) $bioFirstByStaff[$staffId];
            }
            if (!empty($firstCandidates)) {
                $firstCheckinRaw = min($firstCandidates);
            }

            $snapLogin = $snapshotTimes[$staffId]['login'] ?? '';
            $snapLogout = $snapshotTimes[$staffId]['logout'] ?? '';
            $hasDayFolder = !empty($dayFolderByStaffId[$staffId]);
            $snapshotLoggedIn = ($snapLogin !== '' || $snapLogout !== '' || $hasDayFolder);

            $out[] = [
                'staffid'              => $staffId,
                'name'                 => trim($row->firstname . ' ' . $row->lastname),
                'department_name'      => (string) ($row->department_name ?: '—'),
                'first_checkin'        => $this->formatSnapshotReportTime($firstCheckinRaw),
                'first_checkin_raw'    => is_numeric($firstCheckinRaw) ? date('Y-m-d H:i:s', (int) $firstCheckinRaw) : (string) $firstCheckinRaw,
                'snapshot_login'       => $this->formatSnapshotReportTime($snapLogin),
                'snapshot_logout'      => $this->formatSnapshotReportTime($snapLogout),
                'snapshot_login_raw'   => $snapLogin,
                'snapshot_logout_raw'  => $snapLogout,
                'snapshot_logged_in'   => $snapshotLoggedIn,
                'snapshot_day_folder'  => (string) ($dayFolderByStaffId[$staffId] ?? ''),
            ];
        }

        return $out;
    }

    /**
     * @param string $loginFilter all|logged_in|not_logged_in
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    public function filterSnapshotDailyReportRows(array $rows, $loginFilter)
    {
        $loginFilter = (string) $loginFilter;
        if ($loginFilter === 'logged_in') {
            return array_values(array_filter($rows, function ($row) {
                return !empty($row['snapshot_logged_in']);
            }));
        }
        if ($loginFilter === 'not_logged_in') {
            return array_values(array_filter($rows, function ($row) {
                return empty($row['snapshot_logged_in']);
            }));
        }

        return $rows;
    }

    /**
     * Apply Drive-derived login/logout times onto report rows (mutates login/logout fields).
     *
     * @param array<int, array<string, mixed>> $rows
     * @param array<int, array{login:string,logout:string}> $timesByStaff staffid => raw datetimes
     * @return array<int, array<string, mixed>>
     */
    public function mergeSnapshotDailyReportDriveTimes(array $rows, array $timesByStaff)
    {
        foreach ($rows as &$row) {
            $sid = (int) ($row['staffid'] ?? 0);
            if ($sid < 1 || !isset($timesByStaff[$sid])) {
                continue;
            }
            $login = trim((string) ($timesByStaff[$sid]['login'] ?? ''));
            $logout = trim((string) ($timesByStaff[$sid]['logout'] ?? ''));
            if ($login !== '') {
                $row['snapshot_login_raw'] = $login;
                $row['snapshot_login'] = $this->formatSnapshotReportTime($login);
                $row['snapshot_logged_in'] = true;
            }
            if ($logout !== '') {
                $row['snapshot_logout_raw'] = $logout;
                $row['snapshot_logout'] = $this->formatSnapshotReportTime($logout);
                $row['snapshot_logged_in'] = true;
            }
            if ($row['snapshot_login_raw'] !== '' && $row['snapshot_logout_raw'] === '') {
                $row['snapshot_logout_raw'] = $row['snapshot_login_raw'];
                $row['snapshot_logout'] = $row['snapshot_login'];
            }
        }
        unset($row);

        return $rows;
    }

    /**
     * @param int[] $staffIds
     * @return array<int, int> staffid => unix first IN
     */
    private function snapshotBiometricFirstCheckinForDate($reportDateYmd, array $staffIds)
    {
        $staffIds = array_values(array_unique(array_filter(array_map('intval', $staffIds))));
        if (empty($staffIds) || !file_exists(APPPATH . 'models/Biometric_model.php')) {
            return [];
        }

        $attendanceDate = date('d-M-Y', strtotime($reportDateYmd . ' 12:00:00'));
        $table = db_prefix() . 'biometric_report';
        if (!$this->db->table_exists($table)) {
            return [];
        }

        $staff = db_prefix() . 'staff';
        $info = db_prefix() . 'staff_info';
        $idPh = implode(',', array_fill(0, count($staffIds), '?'));
        $codeRows = $this->db->query(
            'SELECT s.staffid,
                TRIM(COALESCE(s.staff_identifi, \'\')) AS identifi,
                TRIM(COALESCE(i.empid, \'\')) AS empid
             FROM ' . $staff . ' s
             LEFT JOIN ' . $info . ' i ON i.staffid = s.staffid
             WHERE s.staffid IN (' . $idPh . ')',
            $staffIds
        )->result_array();

        $codeToStaff = [];
        foreach ($codeRows as $cr) {
            $sid = (int) ($cr['staffid'] ?? 0);
            foreach (['identifi', 'empid'] as $k) {
                $code = trim((string) ($cr[$k] ?? ''));
                if ($code !== '' && $sid > 0) {
                    $codeToStaff[$code] = $sid;
                }
            }
        }
        if (empty($codeToStaff)) {
            return [];
        }

        $bioRows = $this->db->query(
            'SELECT * FROM ' . $table . ' WHERE attendance_date = ? OR LOWER(attendance_date) = LOWER(?)',
            [$attendanceDate, $attendanceDate]
        )->result_array();

        $CI = &get_instance();
        $CI->load->model('biometric_model');
        $byStaff = [];
        foreach ($bioRows as $bioRow) {
            $code = trim((string) ($bioRow['employee_code'] ?? ''));
            if ($code === '' || !isset($codeToStaff[$code])) {
                continue;
            }
            $sid = (int) $codeToStaff[$code];
            if (!method_exists($CI->biometric_model, 'get_biometric_punch_summary')) {
                continue;
            }
            $summary = $CI->biometric_model->get_biometric_punch_summary($bioRow);
            if (!is_array($summary) || empty($summary['first_ts'])) {
                continue;
            }
            $ts = (int) $summary['first_ts'];
            if (!isset($byStaff[$sid]) || $ts < $byStaff[$sid]) {
                $byStaff[$sid] = $ts;
            }
        }

        return $byStaff;
    }

    /**
     * Staff with snapshot activity today (DB rows and/or Drive day-folder detection).
     *
     * @param array<int, string> $staffIdToFolderId staffid => employee Drive folder id
     * @param array<int, int> $driveLoggedStaffIds extra staff ids from Drive (today date folder exists)
     * @return array<int, true> staffid => true
     */
    public function snapshotLoggedInStaffIdsToday($departmentId = null, array $staffIdToFolderId = [], array $driveLoggedStaffIds = [], array $scopeDepartmentIds = null)
    {
        $departmentId = $departmentId !== null ? (int) $departmentId : 0;
        if ($departmentId === -1) {
            return [];
        }
        $driveTable = db_prefix() . 'staff_drive_data';
        $excluded = $this->snapshotDashboardExcludedStaffIds();
        $folderIdToStaffId = [];
        foreach ($staffIdToFolderId as $staffId => $folderId) {
            $staffId = (int) $staffId;
            $folderId = trim((string) $folderId);
            if ($staffId > 0 && $folderId !== '') {
                $folderIdToStaffId[$folderId] = $staffId;
            }
        }

        $logged = [];
        foreach ($driveLoggedStaffIds as $sid) {
            $sid = (int) $sid;
            if ($sid > 0) {
                $logged[$sid] = true;
            }
        }

        if ($this->db->table_exists($driveTable)) {
            [$todayStart, $todayEnd] = $this->snapshotTodayDatetimeBounds();
            $rows = $this->db->query(
                'SELECT staff_id, parent_directory_id FROM ' . $driveTable . '
                 WHERE date_time >= ? AND date_time <= ?',
                [$todayStart, $todayEnd]
            )->result();
            foreach ($rows as $row) {
                $sid = (int) ($row->staff_id ?? 0);
                if ($sid > 0) {
                    $logged[$sid] = true;
                }
                $parent = trim((string) ($row->parent_directory_id ?? ''));
                if ($parent !== '' && isset($folderIdToStaffId[$parent])) {
                    $logged[(int) $folderIdToStaffId[$parent]] = true;
                }
            }
        }

        if (empty($logged)) {
            return [];
        }

        $ids = array_keys($logged);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = $ids;
        $deptSql = $this->snapshotDashboardDepartmentFilterSql($departmentId, $params, $scopeDepartmentIds);
        $excludedSql = implode(',', array_map('intval', $excluded));
        $activeRows = $this->db->query(
            'SELECT s.staffid FROM ' . db_prefix() . 'staff s
             WHERE s.active = 1 AND s.staffid NOT IN (' . $excludedSql . ')
             AND s.staffid IN (' . $placeholders . ')' . $deptSql,
            $params
        )->result();

        $filtered = [];
        foreach ($activeRows as $row) {
            $filtered[(int) $row->staffid] = true;
        }

        return $filtered;
    }

    public function findStaffIdByEmailPrefix($folderName)
    {
        $folderName = strtolower(trim((string) $folderName));
        if ($folderName === '') {
            return 0;
        }
        $row = $this->db->query(
            'SELECT staffid FROM ' . db_prefix() . 'staff
             WHERE LOWER(SUBSTRING_INDEX(email, \'@\', 1)) = ?
             LIMIT 1',
            [$folderName]
        )->row();

        return $row ? (int) $row->staffid : 0;
    }

    /**
     * @param array<int, object> $driveFolderFiles
     */
    public function buildStaffDriveFolderMap(array $driveFolderFiles)
    {
        $map = [];
        foreach ($driveFolderFiles as $file) {
            if (!is_object($file) || !method_exists($file, 'getName') || !method_exists($file, 'getId')) {
                continue;
            }
            $staffId = $this->findStaffIdByEmailPrefix($file->getName());
            if ($staffId > 0) {
                $map[$staffId] = $file->getId();
            }
        }

        return $map;
    }

    public function getSnapshotDashboardSummary($departmentId = null, array $staffIdToFolderId = [], array $driveLoggedStaffIds = [], array $scopeDepartmentIds = null)
    {
        if ($departmentId === -1) {
            return [
                'total_active'    => 0,
                'logged_in_today' => 0,
                'not_logged_in'   => 0,
            ];
        }

        $staffTable = db_prefix() . 'staff';
        $excluded = implode(',', $this->snapshotDashboardExcludedStaffIds());
        $params = [];
        $deptSql = $this->snapshotDashboardDepartmentFilterSql($departmentId, $params, $scopeDepartmentIds);

        $totalRow = $this->db->query(
            "SELECT COUNT(DISTINCT s.staffid) AS cnt
             FROM {$staffTable} s
             WHERE s.active = 1 AND s.staffid NOT IN ({$excluded}) {$deptSql}",
            $params
        )->row();
        $total = $totalRow ? (int) $totalRow->cnt : 0;

        $loggedIn = count($this->snapshotLoggedInStaffIdsToday($departmentId, $staffIdToFolderId, $driveLoggedStaffIds, $scopeDepartmentIds));

        return [
            'total_active'    => $total,
            'logged_in_today' => $loggedIn,
            'not_logged_in'   => max(0, $total - $loggedIn),
        ];
    }

    /**
     * Per-department snapshot login counts (all active staff in each department).
     *
     * @return array<int, array{departmentid:int,name:string,total_active:int,logged_in_today:int,not_logged_in:int}>
     */
    public function getSnapshotDashboardSummaryByDepartment(array $staffIdToFolderId = [], array $driveLoggedStaffIds = [], array $scopeDepartmentIds = null)
    {
        $staffTable = db_prefix() . 'staff';
        $deptTable = db_prefix() . 'departments';
        $sdTable = db_prefix() . 'staff_departments';
        $excluded = implode(',', $this->snapshotDashboardExcludedStaffIds());

        $scopeSql = '';
        $scopeParams = [];
        if ($scopeDepartmentIds !== null) {
            $scopeDepartmentIds = array_values(array_unique(array_filter(array_map('intval', $scopeDepartmentIds))));
            if ($scopeDepartmentIds === []) {
                return [];
            }
            $ph = implode(',', array_fill(0, count($scopeDepartmentIds), '?'));
            $scopeSql = ' AND d.departmentid IN (' . $ph . ') ';
            $scopeParams = $scopeDepartmentIds;
        }

        $rows = $this->db->query(
            "SELECT d.departmentid, d.name,
                COUNT(DISTINCT s.staffid) AS total_active
             FROM {$deptTable} d
             INNER JOIN {$sdTable} sd ON sd.departmentid = d.departmentid
             INNER JOIN {$staffTable} s ON s.staffid = sd.staffid
                AND s.active = 1 AND s.staffid NOT IN ({$excluded})
             WHERE 1=1 {$scopeSql}
             GROUP BY d.departmentid, d.name
             ORDER BY d.name ASC",
            $scopeParams
        )->result_array();

        $out = [];
        foreach ($rows as $row) {
            $deptId = (int) ($row['departmentid'] ?? 0);
            $total = (int) ($row['total_active'] ?? 0);
            $logged = count($this->snapshotLoggedInStaffIdsToday($deptId, $staffIdToFolderId, $driveLoggedStaffIds, $scopeDepartmentIds));
            $out[] = [
                'departmentid'      => $deptId,
                'name'              => (string) ($row['name'] ?? ''),
                'total_active'      => $total,
                'logged_in_today'   => $logged,
                'not_logged_in'     => max(0, $total - $logged),
            ];
        }

        return $out;
    }

    /**
     * Latest snapshot file + earliest login time for today (DB), including rows tied to today's Drive date folder.
     *
     * @param int[] $staffIds
     * @param string[] $todayFolderIds Drive folder ids named Y-m-d
     * @return array<int, array{login_time:string, screenshot_file_id:string}>
     */
    public function snapshotTodayDriveFieldsByStaff(array $staffIds, array $todayFolderIds = [])
    {
        $staffIds = array_values(array_unique(array_filter(array_map('intval', $staffIds))));
        if (empty($staffIds) || !$this->db->table_exists(db_prefix() . 'staff_drive_data')) {
            return [];
        }

        [$todayStart, $todayEnd] = $this->snapshotTodayDatetimeBounds();
        $todayFolderIds = array_values(array_unique(array_filter(array_map('strval', $todayFolderIds))));

        $whereParts = ['(date_time >= ? AND date_time <= ?)'];
        $whereParams = [$todayStart, $todayEnd];
        if (!empty($todayFolderIds)) {
            $folderPh = implode(',', array_fill(0, count($todayFolderIds), '?'));
            $whereParts[] = 'directory_id IN (' . $folderPh . ')';
            $whereParts[] = 'parent_directory_id IN (' . $folderPh . ')';
            $whereParams = array_merge($whereParams, $todayFolderIds, $todayFolderIds);
        }

        $idPh = implode(',', array_fill(0, count($staffIds), '?'));
        $driveTable = db_prefix() . 'staff_drive_data';
        $sql = "SELECT staff_id,
                MIN(CASE WHEN date_time >= ? AND date_time <= ? THEN date_time ELSE NULL END) AS login_time,
                SUBSTRING_INDEX(
                    GROUP_CONCAT(file_id ORDER BY
                        CASE WHEN date_time >= ? AND date_time <= ? THEN 0 ELSE 1 END,
                        date_time DESC
                    SEPARATOR '||'),
                    '||', 1
                ) AS screenshot_file_id
            FROM {$driveTable}
            WHERE staff_id IN ({$idPh}) AND (" . implode(' OR ', $whereParts) . ')
            GROUP BY staff_id';

        $queryParams = array_merge(
            [$todayStart, $todayEnd, $todayStart, $todayEnd],
            $staffIds,
            $whereParams
        );
        $out = [];
        foreach ($this->db->query($sql, $queryParams)->result() as $row) {
            $sid = (int) $row->staff_id;
            $out[$sid] = [
                'login_time'         => (string) ($row->login_time ?: ''),
                'screenshot_file_id' => (string) ($row->screenshot_file_id ?: ''),
            ];
        }

        return $out;
    }

    public function getSnapshotDashboardStaffList($type, $departmentId = null, array $staffIdToFolderId = [], array $driveLoggedStaffIds = [], array $todayFolderByStaffId = [], array $scopeDepartmentIds = null)
    {
        $type = (string) $type;
        if (!in_array($type, ['active', 'logged_in', 'not_logged_in'], true)) {
            $type = 'active';
        }

        if ($departmentId === -1) {
            return [];
        }

        $staffTable = db_prefix() . 'staff';
        $driveTable = db_prefix() . 'staff_drive_data';
        $excluded = implode(',', $this->snapshotDashboardExcludedStaffIds());
        $params = [];
        $deptSql = $this->snapshotDashboardDepartmentFilterSql($departmentId, $params, $scopeDepartmentIds);
        [$todayStart, $todayEnd] = $this->snapshotTodayDatetimeBounds();

        $loggedSet = $this->snapshotLoggedInStaffIdsToday($departmentId, $staffIdToFolderId, $driveLoggedStaffIds, $scopeDepartmentIds);
        $typeSql = '';
        if ($type === 'logged_in') {
            if (empty($loggedSet)) {
                return [];
            }
            $typeSql = ' AND s.staffid IN (' . implode(',', array_map('intval', array_keys($loggedSet))) . ') ';
        } elseif ($type === 'not_logged_in') {
            if (!empty($loggedSet)) {
                $typeSql = ' AND s.staffid NOT IN (' . implode(',', array_map('intval', array_keys($loggedSet))) . ') ';
            }
        }

        $sql = "SELECT s.staffid, s.firstname, s.lastname, s.email,
                GROUP_CONCAT(DISTINCT d.name ORDER BY d.name SEPARATOR ', ') AS department_name,
                today_drv.login_time,
                today_drv.screenshot_file_id
            FROM {$staffTable} s
            LEFT JOIN " . db_prefix() . 'staff_departments sd ON sd.staffid = s.staffid
            LEFT JOIN ' . db_prefix() . "departments d ON d.departmentid = sd.departmentid
            LEFT JOIN (
                SELECT staff_id,
                    MIN(date_time) AS login_time,
                    SUBSTRING_INDEX(GROUP_CONCAT(file_id ORDER BY date_time DESC SEPARATOR '||'), '||', 1) AS screenshot_file_id
                FROM {$driveTable}
                WHERE date_time >= ? AND date_time <= ?
                GROUP BY staff_id
            ) today_drv ON today_drv.staff_id = s.staffid
            WHERE s.active = 1 AND s.staffid NOT IN ({$excluded}) {$typeSql} {$deptSql}
            GROUP BY s.staffid, s.firstname, s.lastname, s.email, today_drv.login_time, today_drv.screenshot_file_id
            ORDER BY s.firstname, s.lastname";

        $queryParams = array_merge([$todayStart, $todayEnd], $params);
        $rows = $this->db->query($sql, $queryParams)->result();
        $out = [];
        $staffIds = [];
        foreach ($rows as $row) {
            $staffIds[] = (int) $row->staffid;
            $loginTime = '';
            if (!empty($row->login_time)) {
                $loginTime = date('g:i A', strtotime($row->login_time));
            }
            $out[] = [
                'staffid'            => (int) $row->staffid,
                'name'               => trim($row->firstname . ' ' . $row->lastname),
                'email'              => (string) $row->email,
                'department_name'    => (string) ($row->department_name ?: '—'),
                'login_time'         => $loginTime,
                'login_time_raw'     => (string) ($row->login_time ?: ''),
                'screenshot_file_id' => (string) ($row->screenshot_file_id ?: ''),
            ];
        }

        if (!empty($out)) {
            $todayFolderIds = [];
            foreach ($todayFolderByStaffId as $sid => $folderId) {
                if (in_array((int) $sid, $staffIds, true)) {
                    $todayFolderIds[] = (string) $folderId;
                }
            }
            $dbExtra = $this->snapshotTodayDriveFieldsByStaff($staffIds, $todayFolderIds);
            foreach ($out as &$item) {
                $sid = (int) $item['staffid'];
                if (!empty($item['screenshot_file_id']) && $item['login_time'] !== '') {
                    continue;
                }
                if (!isset($dbExtra[$sid])) {
                    continue;
                }
                if ($item['login_time'] === '' && !empty($dbExtra[$sid]['login_time'])) {
                    $item['login_time_raw'] = $dbExtra[$sid]['login_time'];
                    $item['login_time'] = date('g:i A', strtotime($dbExtra[$sid]['login_time']));
                }
                if ($item['screenshot_file_id'] === '' && !empty($dbExtra[$sid]['screenshot_file_id'])) {
                    $item['screenshot_file_id'] = $dbExtra[$sid]['screenshot_file_id'];
                }
            }
            unset($item);
        }

        return $out;
    }

/*public function addIntervalData()
{
	$data = array(  
		'staffid'     => $_POST['staffid'],  
		'time'  => $_POST['time']
        );  
     $this->db->insert('tbltimeinterval',$data); 
	 return true;	 
}*/
	
public function update()
  {      
  //print_r($_POST);die;
  $id = $_POST['date'];
  $time = $_POST['time'];
  //echo $id;echo "<pre>";echo $time;die;
  //echo "UPDATE `tbltimeinterval` SET `time_interval` = ".$time." WHERE `id` = ".$id."";die;
	$this->db->set('time_interval', $time);
	$this->db->where('id', $id);
	$this->db->update('tbltimeinterval'); // gives UPDATE `mytable` SET `field` = 'field+1' WHERE `id` = 2
   //$sql="UPDATE `tbltimeinterval` SET `time_interval` = ".$time." WHERE `id` = ".$id."";    
  //  $query = $this->db->query($sql);
    return $this->db->affected_rows();

    }
	
public function getDepartment()
	{
		$staff_id = get_staff_user_id();
		//echo "SELECT staffid,email,firstname,lastname,team_manage,hod_team_manage,file_id,directory_id,parent_directory_id,status,date_time from tblstaff left join tblstaff_drive_data ON tblstaff.staffid= tblstaff_drive_data.staff_id where team_manage=$staff_id AND active=1 GROUP BY staffid";die;
		$sql="SELECT 
    s.staffid,
    s.email,
    s.firstname,
    s.lastname,
    s.team_manage,
    s.hod_team_manage,
    d.name AS department_name,
    sdd.file_id,
    sdd.directory_id,
    sdd.parent_directory_id,
    sdd.status,
    sdd.date_time AS last_login
FROM tblstaff s
JOIN tblstaff_departments sd ON s.staffid = sd.staffid
JOIN tbldepartments d ON sd.departmentid = d.departmentid
LEFT JOIN (
    SELECT sdd1.staff_id,
           sdd1.date_time,
           sdd1.file_id,
           sdd1.directory_id,
           sdd1.parent_directory_id,
           sdd1.status
    FROM tblstaff_drive_data sdd1
    INNER JOIN (
        SELECT staff_id, MAX(date_time) AS max_dt
        FROM tblstaff_drive_data
        GROUP BY staff_id
    ) latest ON latest.staff_id = sdd1.staff_id
           AND latest.max_dt = sdd1.date_time
) sdd ON s.staffid = sdd.staff_id
WHERE 
    s.active = 1
    AND s.staffid NOT IN (1, 292, 178)
    AND sd.departmentid IN (
        SELECT departmentid
        FROM tblstaff_departments
        WHERE staffid = $staff_id
    )
ORDER BY d.name, s.firstname, s.lastname;";    
		$query = $this->db->query($sql);
		if (!$query) {
			log_message('error', 'Emt_model::getDepartment query failed: ' . $this->db->error()['message']);
			return [];
		}
		return $query->result(); 
	}
	
public function getEmtDepartmentCount()
	{
		$staff_id = get_staff_user_id();
		$sql="SELECT d.departmentid, d.name AS department_name, COUNT(DISTINCT CASE WHEN DATE(sdd.date_time) = CURDATE() THEN sdd.staff_id ELSE NULL END) AS login_count_today FROM tbldepartments d JOIN tblstaff_departments sd ON d.departmentid = sd.departmentid JOIN tblstaff s ON sd.staffid = s.staffid AND s.active = 1 LEFT JOIN tblstaff_drive_data sdd ON s.staffid = sdd.staff_id WHERE d.departmentid IN ( SELECT sd2.departmentid FROM tblstaff_departments sd2 WHERE sd2.staffid = $staff_id ) GROUP BY d.departmentid, d.name ORDER BY d.name;";    
		$query = $this->db->query($sql);
		return $query->result(); 
	}
	
public function getEmpStaffNotLoggedin(){
	$staff_id = get_staff_user_id();
	$sql = "SELECT s.staffid, CONCAT(s.firstname, ' ', s.lastname) AS name, d.name AS department_name FROM tblstaff s JOIN tblstaff_departments sd ON s.staffid = sd.staffid JOIN tbldepartments d ON sd.departmentid = d.departmentid WHERE s.active = 1 AND s.staffid NOT IN (1, 292, 178,403,662) AND sd.departmentid IN ( SELECT sd2.departmentid FROM tblstaff_departments sd2 WHERE sd2.staffid = $staff_id ) AND s.staffid NOT IN ( SELECT DISTINCT sdd.staff_id FROM tblstaff_drive_data sdd WHERE sdd.date_time >= NOW() - INTERVAL 1 HOUR ) ORDER BY d.name, name;";
	$query  = $this->db->query($sql);
	return $query->result();
}
public function getEmpStaffYesterdayLoggedin(){
	$staff_id = get_staff_user_id();
	$sql = "SELECT d.departmentid, d.name AS department_name, COUNT(DISTINCT CASE WHEN DATE(sdd.date_time) = CURDATE() - INTERVAL 1 DAY THEN sdd.staff_id ELSE NULL END) AS login_count_yesterday FROM tbldepartments d JOIN tblstaff_departments sd ON d.departmentid = sd.departmentid JOIN tblstaff s ON sd.staffid = s.staffid AND s.active = 1 LEFT JOIN tblstaff_drive_data sdd ON s.staffid = sdd.staff_id WHERE d.departmentid IN ( SELECT sd2.departmentid FROM tblstaff_departments sd2 WHERE sd2.staffid = $staff_id ) GROUP BY d.departmentid, d.name ORDER BY d.name;";
	$query  = $this->db->query($sql);
	return $query->result();
}	

}
  
