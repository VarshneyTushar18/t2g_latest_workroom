<?php

defined('BASEPATH') or exit('No direct script access allowed');

$tbl = db_prefix() . 'timesheets_additional_timesheet';

if (!$this->ci->db->field_exists('rejection_comment', $tbl)) {
    $this->ci->db->query('ALTER TABLE `' . $tbl . '` ADD COLUMN `rejection_comment` TEXT NULL');
}

$deptSubquery = '(SELECT d.name FROM ' . db_prefix() . 'staff_departments sd
    JOIN ' . db_prefix() . 'departments d ON d.departmentid = sd.departmentid
    WHERE sd.staffid = ' . $tbl . '.creator
    ORDER BY sd.departmentid ASC LIMIT 1) as department_name';

$aColumns = [
    'id',
    'creator',
    $deptSubquery,
    'additional_day',
    $tbl . '.time_in as time_in',
    $tbl . '.time_out as time_out',
    'timekeeping_value',
    $tbl . '.reason as reason',
    '(SELECT GROUP_CONCAT(staffid SEPARATOR ",") FROM ' . db_prefix() . 'timesheets_approval_details WHERE rel_id = ' . $tbl . '.id and rel_type = "additional_timesheets") as approver',
    'status',
    $tbl . '.rejection_comment as rejection_comment',
];
$sIndexColumn = 'id';
$sTable       = $tbl;
$join = ['LEFT JOIN ' . db_prefix() . 'staff b ON b.staffid = ' . $tbl . '.creator'];
$where = [];

if (!is_admin() && !has_permission('additional_timesheets_management', '', 'view')) {
    if (!$this->ci->input->post('chose_ats') || $this->ci->input->post('chose_ats') == 'all') {
        array_push($where, timesheet_staff_manager_query('additional_timesheets_management', 'creator', 'AND'));
        array_push($where, 'OR (' . get_staff_user_id() . ' in (select staffid from ' . db_prefix() . 'timesheets_approval_details where rel_type = "additional_timesheets" and rel_id = ' . $tbl . '.id))');
    }
}

if ($this->ci->input->post('status_filter_ats')) {
    $where_status = '';
    $status = $this->ci->input->post('status_filter_ats');
    foreach ($status as $statues) {
        if ($status != '') {
            if ($where_status == '') {
                $where_status .= ' AND (' . $tbl . '.status = "' . $statues . '"';
            } else {
                $where_status .= ' or ' . $tbl . '.status = "' . $statues . '"';
            }
        }
    }
    if ($where_status != '') {
        $where_status .= ')';
        array_push($where, $where_status);
    }
}

if ($this->ci->input->post('department_ats')) {
    $where_dpm = '';
    $department = $this->ci->input->post('department_ats');
    foreach ($department as $statues) {
        if ($department != '') {
            if ($where_dpm == '') {
                $where_dpm = ' AND (' . $tbl . '.creator IN (SELECT staffid FROM ' . db_prefix() . 'staff_departments WHERE departmentid = ' . $statues . ')';
            } else {
                $where_dpm .= 'OR ' . $tbl . '.creator IN (SELECT staffid FROM ' . db_prefix() . 'staff_departments WHERE departmentid = ' . $statues . ')';
            }
        }
    }
    if ($where_dpm != '') {
        $where_dpm .= ')';
        array_push($where, $where_dpm);
    }
}

if ($this->ci->input->post('chose_ats')) {
    $chose = $this->ci->input->post('chose_ats');
    $sql_where = '';
    if ($chose != 'all') {
        $sql_where .= '("' . get_staff_user_id() . '" IN (SELECT staffid FROM ' . db_prefix() . 'timesheets_approval_details where ' . db_prefix() . 'timesheets_approval_details.rel_type IN ("additional_timesheets") AND ' . db_prefix() . 'timesheets_approval_details.rel_id = ' . $tbl . '.id ))';
    }
    if ($sql_where != '') {
        array_push($where, 'AND ' . $sql_where);
    }
}

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['b.firstname']);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $_data = '<a href="' . admin_url('staff/profile/' . $aRow['creator']) . '">' . staff_profile_image($aRow['creator'], [
        'staff-profile-image-small',
    ]) . '</a>';
    $_data .= ' <a href="' . admin_url('staff/profile/' . $aRow['creator']) . '">' . get_staff_full_name($aRow['creator']) . '</a>';
    $row[] = $_data;
    $row[] = html_escape($aRow['department_name'] ?: '—');
    $row[] = _d($aRow['additional_day']);
    $row[] = html_escape($aRow['time_in'] ?: '—');
    $row[] = html_escape($aRow['time_out'] ?: '—');
    $row[] = $aRow['timekeeping_value'] !== '' && $aRow['timekeeping_value'] !== null
        ? html_escape($aRow['timekeeping_value']) . 'h'
        : '—';

    $reason = trim((string) ($aRow['reason'] ?? ''));
    if (strlen($reason) > 60) {
        $reason = substr($reason, 0, 57) . '...';
    }
    $row[] = $reason !== '' ? html_escape($reason) : '<span class="text-muted">—</span>';

    $membersOutput = '';
    $members       = explode(',', (string) $aRow['approver']);
    $list_member = '';
    $exportMembers = '';
    $memberCount = 0;
    foreach ($members as $key => $member_id) {
        if ($member_id != '') {
            $memberCount++;
            $member_name = get_staff_full_name($member_id);
            $list_member .= '<li class="text-success mbot10 mtop"><a href="' . admin_url('profile/' . $member_id) . '" class="avatar cover-image text-align-left">' .
            staff_profile_image($member_id, [
                'staff-profile-image-small mright5',
            ], 'small', [
                'data-toggle' => 'tooltip',
                'data-title'  => $member_name,
            ]) . ' ' . $member_name . '</a></li>';
            if ($key <= 2) {
                $membersOutput .= '<span class="avatar cover-image brround">' .
                staff_profile_image($member_id, [
                    'staff-profile-image-small mright5',
                ], 'small', [
                    'data-toggle' => 'tooltip',
                    'data-title'  => $member_name,
                ]) . '</span>';
            }
            $exportMembers .= $member_name . ', ';
        }
    }
    if ($memberCount === 0) {
        $row[] = '<span class="text-muted">—</span>';
    } else {
        if (count($members) > 3) {
            $membersOutput .= '<span class="avatar bg-secondary brround avatar-none">+' . (count($members) - 3) . '</span>';
        }
        $membersOutput .= '<span class="hide">' . trim($exportMembers, ', ') . '</span>';
        $row[] = '<div class="task-info task-watched task-info-watched"><h5><div class="btn-group"><span class="task-single-menu task-menu-watched"><div class="avatar-list avatar-list-stacked" data-toggle="dropdown">' . $membersOutput . '</div><ul class="dropdown-menu list-staff" role="menu"><li class="dropdown-plus-title">' . _l('approver') . '</li>' . $list_member . '</ul></span></div></h5></div>';
    }

    if ((int) $aRow['status'] === 1) {
        $row[] = '<span class="label label-success">Approved</span>';
    } elseif ((int) $aRow['status'] === 2) {
        $rej = trim((string) ($aRow['rejection_comment'] ?? ''));
        $statusHtml = '<span class="label label-danger">Rejected</span>';
        if ($rej !== '') {
            $short = strlen($rej) > 50 ? substr($rej, 0, 47) . '...' : $rej;
            $statusHtml .= '<div class="text-danger" style="font-size:12px;margin-top:4px;" title="' . html_escape($rej) . '">' . html_escape($short) . '</div>';
        }
        $row[] = $statusHtml;
    } else {
        $row[] = '<span class="label label-warning">Pending</span>';
    }

    $rel_type = 'additional_timesheets';
    $row_id = (int) $aRow['id'];
    $user_id = get_staff_user_id();
    $options = '<div class="tw-flex tw-flex-wrap tw-items-center tw-gap-1">';

    if ((int) $aRow['status'] === 0 && timesheets_can_approve_attendance($user_id)) {
            $options .= '<span data-placement="top" data-toggle="tooltip" data-title="' . _l('approve') . '" onclick="approve_request(' . $row_id . ',\'' . $rel_type . '\'); return false;" class="btn btn-success btn-icon btn-sm"><i class="fa fa-check"></i></span>';
            $options .= '<span data-placement="top" data-toggle="tooltip" data-title="' . _l('deny') . '" onclick="deny_request(' . $row_id . ',\'' . $rel_type . '\'); return false;" class="btn btn-danger btn-icon btn-sm"><i class="fa fa-times"></i></span>';
    }

    $options .= '<a href="Javascript:void(0);" onclick="view_additional_timesheets(' . $row_id . '); return false" class="btn btn-default btn-sm" data-toggle="sidebar-right" data-target=".additional-timesheets-sidebar" title="Review"><i class="fa fa-eye"></i></a>';

    $options .= '</div>';
    $row[] = $options;

    $output['aaData'][] = $row;
}
