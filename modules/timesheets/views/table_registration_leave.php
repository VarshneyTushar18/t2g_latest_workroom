<?php

defined('BASEPATH') or exit('No direct script access allowed');
$this->ci->load->model('timesheets_model');
$user_id = get_staff_user_id();
$add_query = ' IF(' . db_prefix() . 'timesheets_requisition_leave.type_of_leave = 8,"Leave",IF(' . db_prefix() . 'timesheets_requisition_leave.type_of_leave = 2,"maternity_leave",IF(' . db_prefix() . 'timesheets_requisition_leave.type_of_leave = 4,"private_work_without_pay",IF(' . db_prefix() . 'timesheets_requisition_leave.type_of_leave = 1,"sick_leave", IF(' . db_prefix() . 'timesheets_requisition_leave.type_of_leave = 0,' . db_prefix() . 'timesheets_requisition_leave.type_of_leave_text,"")))))';

$type_of_leave = 'IF(' . db_prefix() . 'MONTH(timesheets_requisition_leave.start_time) = 1,"Jan"' . $add_query . ', IF(' . db_prefix() . 'MONTH(timesheets_requisition_leave.start_time) = 2,"Feb", IF(' . db_prefix() . 'MONTH(timesheets_requisition_leave.start_time) = 3,"March", IF(' . db_prefix() . 'MONTH(timesheets_requisition_leave.start_time) = 4,"April", IF(' . db_prefix() . 'MONTH(timesheets_requisition_leave.start_time) = 5,"May", IF(' . db_prefix() . 'MONTH(timesheets_requisition_leave.start_time) = 6,"June",""))))))';
$aColumns = [
	'1',
	'Name',
	db_prefix() . 'timesheets_requisition_leave.id',
	db_prefix() . 'timesheets_requisition_leave.id',
	db_prefix() . 'timesheets_requisition_leave.staff_id',
	db_prefix() . 'timesheets_requisition_leave.followers_id',
	db_prefix() . 'timesheets_requisition_leave.subject',
	db_prefix() . 'timesheets_requisition_leave.reason',
	db_prefix() . 'timesheets_requisition_leave.start_time',
	db_prefix() . 'timesheets_requisition_leave.end_time',
	'(SELECT GROUP_CONCAT(staffid SEPARATOR ",") FROM ' . db_prefix() . 'timesheets_approval_details WHERE rel_id = ' . db_prefix() . 'timesheets_requisition_leave.id and ' . db_prefix() . 'timesheets_approval_details.rel_type <> "additional_timesheets") as approver',
	db_prefix() . 'timesheets_requisition_leave.status',
	db_prefix() . 'timesheets_requisition_leave.datecreated',
	db_prefix() . 'timesheets_requisition_leave.id',
];
$sIndexColumn = 'id';
$sTable = db_prefix() . 'timesheets_requisition_leave';
$join = ['LEFT JOIN ' . db_prefix() . 'staff b ON b.staffid = ' . db_prefix() . 'timesheets_requisition_leave.staff_id',
	'LEFT JOIN ' . db_prefix() . 'roles ON ' . db_prefix() . 'roles.roleid = b.role',
];
$where = [];

if (!is_admin() && !has_permission('leave_management', '', 'view')) {
	array_push($where, ' AND (
	('
	. get_staff_user_id() . ' in 
	(
	select staffid from ' . db_prefix() . 'staff where team_manage  = '.get_staff_user_id().' or ' . 
	db_prefix() . 'timesheets_requisition_leave.handover_recipients = ' . get_staff_user_id() .' or '.
	db_prefix() . 'timesheets_requisition_leave.staff_id = ' . get_staff_user_id() .	
	')'
	. timesheet_staff_manager_query('leave_management', 'staff_id', 'OR') . ')
	)'
	);
}

if ($this->ci->input->post('status_filter')) {
	$where_status = '';
	$status = $this->ci->input->post('status_filter');
	foreach ($status as $statues) {
		if ($status != '') {
			if ($where_status == '') {
				$where_status .= ' AND (status = "' . $statues . '"';
			} else {
				$where_status .= ' OR status = "' . $statues . '"';
			}
		}
	}
	if ($where_status != '') {
		$where_status .= ')';
		array_push($where, $where_status);
	}
}



if ($this->ci->input->post('rel_type_filter')) {
    $months = $this->ci->input->post('rel_type_filter'); // array: ['07', '08']

    if (is_array($months) && !empty($months)) {
        $month_where = [];
        foreach ($months as $m) {
            $month_where[] = 'MONTH(' . db_prefix() . 'timesheets_requisition_leave.start_time) = "' . $m . '"';
        }
        array_push($where, 'AND (' . implode(' OR ', $month_where) . ')');
    }
}

if ($this->ci->input->post('chose')) {
	$chose = $this->ci->input->post('chose');
	$sql_where = '';
	if ($chose != 'all') {
		if ($sql_where != '') {
			$sql_where .= ' AND (' . get_staff_user_id() . ' IN (SELECT staffid FROM ' . db_prefix() . 'timesheets_approval_details where ' . db_prefix() . 'timesheets_approval_details.rel_type IN ("Leave","maternity_leave","private_work_without_pay","sick_leave","late","early","Go_out","Go_on_bussiness") AND ' . db_prefix() . 'timesheets_approval_details.rel_id = ' . db_prefix() . 'timesheets_requisition_leave.id ))';
		} else {
			$sql_where .= '(' . get_staff_user_id() . ' IN (SELECT staffid FROM ' . db_prefix() . 'timesheets_approval_details where ' . db_prefix() . 'timesheets_approval_details.rel_type IN ("Leave","maternity_leave","private_work_without_pay","sick_leave","late","early","Go_out","Go_on_bussiness") AND ' . db_prefix() . 'timesheets_approval_details.rel_id = ' . db_prefix() . 'timesheets_requisition_leave.id ))';
		}
	} else {
		$sql_where = '';
	}
	if ($sql_where != '') {
		array_push($where, 'AND ' . $sql_where);
	}
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix() . 'timesheets_requisition_leave.start_time', db_prefix() . 'timesheets_requisition_leave.type_of_leave', db_prefix() . 'timesheets_requisition_leave.subject', 'b.firstname','b.staff_identifi', db_prefix() . 'timesheets_requisition_leave.followers_id', db_prefix() . 'timesheets_requisition_leave.status as status', db_prefix() . 'timesheets_requisition_leave.datecreated', 'type_of_leave_text']);

$output = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
	$row = [];
	$row[] = $aRow[db_prefix() . 'timesheets_requisition_leave.id'];

	$row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow[db_prefix() . 'timesheets_requisition_leave.id'] . '"><label></label></div>';
	$row[] = $aRow[db_prefix() . 'timesheets_requisition_leave.id'];
	$row[] = $aRow['staff_identifi'];
	$row[] = $aRow['firstname'];


	$row[] = _d($aRow[db_prefix() . 'timesheets_requisition_leave.start_time']);
	$row[] = _d($aRow[db_prefix() . 'timesheets_requisition_leave.end_time']);
	$row[] = '<div class="row">'	. '<a class="col-md-10" href="' . admin_url('timesheets/requisition_detail' . '/' . ($aRow[db_prefix() . 'timesheets_requisition_leave.id'])) . '">' . $aRow['subject'] . '</a>'		. '</div>';
	$list_member_approve = [];

	$membersOutput = '';

	$members = explode(',', $aRow['approver']);
	$list_member = '';
	$exportMembers = '';
	foreach ($members as $key => $member_id) {
		if ($member_id != '') {
			$member_name = get_staff_full_name($member_id);
			$list_member .= '<li class="text-success mbot10 mtop"><a href="' . admin_url('profile/' . $member_id) . '" class="avatar cover-image text-align-left">' .
			staff_profile_image($member_id, [
				'staff-profile-image-small mright5',
			], 'small', [
				'data-toggle' => 'tooltip',
				'data-title' => $member_name,
			]) . ' ' . $member_name . '</a></li>';
			if ($key <= 2) {
				$membersOutput .= '<span class="avatar cover-image brround">' .
				staff_profile_image($member_id, [
					'staff-profile-image-small mright5',
				], 'small', [
					'data-toggle' => 'tooltip',
					'data-title' => $member_name,
				]) . '</span>';
			}
			// For exporting
			$exportMembers .= $member_name . ', ';
			$list_member_approve[] = $member_id;
		}
	}
	if (count($members) > 3) {
		$membersOutput .= '<span class="avatar bg-secondary brround avatar-none">+' . (count($members) - 3) . '</span>';
	}

	$membersOutput .= '<span class="hide">' . trim($exportMembers, ', ') . '</span>';

	$membersOutput1 = '<div class="task-info task-watched task-info-watched">
    <h5>
    <div class="btn-group">
    <span class="task-single-menu task-menu-watched">
    <div class="avatar-list avatar-list-stacked" data-toggle="dropdown">' . $membersOutput . '</div>
    <ul class="dropdown-menu list-staff" role="menu">
    <li class="dropdown-plus-title">
    ' . _l('approver') . '
    </li>
    ' . $list_member . '
    </ul>
    </span>
    </div>
    </h5>
    </div>';

	$liss = '';
	$approce = '';

	//$row[] = $membersOutput;
$row[] = '<p>' . $aRow['type_of_leave_text'] . '</p>';

	//$row[] = $aRow['reason'];
		$row[] = '<p>' . $aRow['type_of_leave'] . '</p>';


	if ($aRow['status'] == 0) {
		$row[] = '<span class="label label-primary  mr-1 mb-1 mt-1">' . _l('Pending') . '</span>';
	} else if ($aRow['status'] == 1) {
		$row[] = '<span class="label label-success  mr-1 mb-1 mt-1">' . _l('approved') . '</span>';
	} else if ($aRow['status'] == 2) {
		$row[] = '<span class="label label-danger  mr-1 mb-1 mt-1">' . _l('Reject') . '</span>';
	}
	else {
		$row[] = '<span class="label label-danger  mr-1 mb-1 mt-1">' . _l('Absent') . '</span>';
	}

	$row[] = _d(date('Y-m-d', strtotime($aRow[db_prefix() . 'timesheets_requisition_leave.datecreated'])));

	$action_option = '<div class="row">';
	if (in_array($user_id, $list_member_approve)) {
		$data_check_approve_status = $this->ci->timesheets_model->check_approval_details(($aRow[db_prefix() . 'timesheets_requisition_leave.id']), $rel_type);
		if (isset($data_check_approve_status['staffid'])) {
			if ($data_check_approve_status['staffid']) {
				if (in_array($user_id, $data_check_approve_status['staffid'])) {
					$action_option .= '<span data-placement="top" data-toggle="tooltip" data-title="' . _l('approve') . '" onclick="approve_request(' . ($aRow[db_prefix() . 'timesheets_requisition_leave.id']) . ',\'' . $rel_type . '\');" class="btn btn-success btn-icon mright5"><i class="fa fa-check"></i></span>';
					$action_option .= '<span data-placement="top" data-toggle="tooltip" data-title="' . _l('deny') . '" onclick="deny_request(' . ($aRow[db_prefix() . 'timesheets_requisition_leave.id']) . ',\'' . $rel_type . '\');" class="btn btn-primary btn-icon"><i class="fa fa-ban"></i></span>';
				}
			}
		}
	}

		$action_option .= '<div class="row">'	. '<a class="col-md-10" href="' . admin_url('timesheets/requisition_detail' . '/' . ($aRow[db_prefix() . 'timesheets_requisition_leave.id'])) . '">' . '<div class="btn btn-warning">view</div>' . '</a>'		. '</div>';
	$row[] = $action_option . '</div>';

	$row['DT_RowClass'] = 'has-row-options';
	$output['aaData'][] = $row;
}
