<?php

defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionEdit   = has_permission('projects', '', 'edit');
$hasPermissionDelete = has_permission('projects', '', 'delete');
$hasPermissionCreate = has_permission('projects', '', 'create');

$aColumns = [
    db_prefix() . 'projects.id as id',
    'name',
    'estimated_hours',
    get_sql_select_client_company(),
    '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . db_prefix() . 'projects.id and rel_type="project" ORDER by tag_order ASC) as tags',
    'start_date',
    'deadline',
    '(SELECT GROUP_CONCAT(CONCAT(firstname, ' . "' '" . ', lastname) SEPARATOR ",") FROM ' . db_prefix() . 'project_members JOIN ' . db_prefix() . 'staff on ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'project_members.staff_id WHERE project_id=' . db_prefix() . 'projects.id ORDER BY staff_id) as members',
    'status'
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'projects';

$join = [
    'JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'projects.clientid',
];

$where  = [];
$filter = [];

if ($clientid != '') {
    array_push($where, ' AND clientid=' . $this->ci->db->escape_str($clientid));
}

if (!has_permission('projects', '', 'view') || $this->ci->input->post('my_projects')) {
    array_push($where, ' AND ' . db_prefix() . 'projects.id IN (SELECT project_id FROM ' . db_prefix() . 'project_members WHERE staff_id=' . get_staff_user_id() . ')');
}

$statusIds = [];
foreach ($this->ci->projects_model->get_project_statuses() as $status) {
    if ($this->ci->input->post('project_status_' . $status['id'])) {
        array_push($statusIds, $status['id']);
    }
}
if (count($statusIds) > 0) {
    array_push($filter, 'OR status IN (' . implode(', ', $statusIds) . ')');
}
if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}

$custom_fields = get_table_custom_fields('projects');
foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'projects.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

$aColumns = hooks()->apply_filters('projects_table_sql_columns', $aColumns);

if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'clientid',
    '(SELECT GROUP_CONCAT(staff_id SEPARATOR ",") FROM ' . db_prefix() . 'project_members WHERE project_id=' . db_prefix() . 'projects.id ORDER BY staff_id) as members_ids',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

$this->ci->load->model('departments_model');
$departments = [];
foreach ($this->ci->departments_model->get() as $dept) {
    $departments[$dept['departmentid']] = $dept['name'];
}

foreach ($rResult as $aRow) {
    $row = [];
    $project_id = $aRow['id'];
    $link = admin_url('projects/view/' . $project_id);

    $row[] = '<a href="' . $link . '">' . $project_id . '</a>';

    $name = '<a href="' . $link . '">' . $aRow['name'] . '</a>';
    $name .= '<div class="row-options">';
    $name .= '<a href="' . $link . '">' . _l('view') . '</a>';
    $name .= '</div>';
    $row[] = $name;

    $row[] = _d($aRow['start_date']);
    $row[] = _d($aRow['deadline']);

    $status = get_project_status_by_id($aRow['status']);
    $row[] = '<span class="label project-status-' . $aRow['status'] . '" style="color:' . $status['color'] . ';border:1px solid ' . adjust_hex_brightness($status['color'], 0.4) . ';background: ' . adjust_hex_brightness($status['color'], 0.04) . ';">' . $status['name'] . '</span>';

    // Project members
$membersOutput = '';
$members = explode(',', $aRow['members']);
$members_ids = explode(',', $aRow['members_ids']);
$exportMembers = '';
foreach ($members as $key => $member) {
    if ($member != '') {
        $member_id = $members_ids[$key] ?? 0;
        $membersOutput .= '<a href="' . admin_url('profile/' . $member_id) . '">' .
            staff_profile_image($member_id, ['staff-profile-image-small mright5'], 'small', [
                'data-toggle' => 'tooltip',
                'data-title'  => $member,
            ]) . '</a>';
        $exportMembers .= $member . ', ';
    }
}
$membersOutput .= '<span class="hide">' . trim($exportMembers, ', ') . '</span>';
$row[] = $membersOutput;

// ✅ Allotted Hours
$row[] = $aRow['project_cost'];

// ✅ Hours Spent
$project_id = $aRow['id'];
$spentQuery = $this->ci->db->query("
    SELECT SUM(end_time - start_time) AS total_seconds
    FROM " . db_prefix() . "taskstimers tt
    JOIN " . db_prefix() . "tasks t ON t.id = tt.task_id
    WHERE t.rel_id = ? AND tt.end_time > 0 AND tt.start_time > 0
", [$project_id])->row();

$totalSpentFormatted = seconds_to_time_format($spentQuery->total_seconds ?? 0);
$row[] = $totalSpentFormatted;

// ✅ Team / Hours Modal Trigger
$teamQuery = $this->ci->db->query("
    SELECT tt.staff_id, SUM(tt.end_time - tt.start_time) as total_seconds
    FROM " . db_prefix() . "taskstimers tt
    JOIN " . db_prefix() . "tasks t ON t.id = tt.task_id
    WHERE t.rel_id = ? AND tt.end_time > 0 AND tt.start_time > 0
    GROUP BY tt.staff_id
", [$project_id])->result();

$modalId = 'team-hours-' . $project_id; // ✅ Initialize modal ID
$modalBody = "<div class='table-responsive' style='display:contents'><table class='table table-bordered'><thead><tr><th style='background:#141e46;color:#ffffff'>Staff</th><th style='background:#141e46;color:#ffffff'>Time</th></tr></thead><tbody>";
$totalSeconds = 0;

// Team time log
foreach ($teamQuery as $entry) {
    $staffName = get_staff_full_name($entry->staff_id);
    $timeFormatted = seconds_to_time_format((int) $entry->total_seconds);
    $totalSeconds += (int) $entry->total_seconds;

    $staff = $this->ci->staff_model->get($entry->staff_id);
    $isInactive = ($staff && $staff->active == 0);
    $rowClass = $isInactive ? 'table-danger' : '';

    $modalBody .= "<tr class='$rowClass'>
        <td>$staffName</td>
        <td>$timeFormatted</td>
    </tr>";
}

$modalBody .= "</tbody></table></div>";
$modalBody .= "<div class='row'><div class='mb-2 col-md-6'>
  <span style='display:inline-block; width:15px; height:15px; background:#f8d7da; margin-right:5px;'></span> Inactive Employee</div>";
$modalBody .= "<div class='col-md-6 text-end text-right fw-bold'>Total: " . seconds_to_time_format($totalSeconds) . "</div></div>";
// Get staff assigned to this project
$assignedStaff = $this->ci->db->select('staff.*')
    ->from(db_prefix() . 'project_members')
    ->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'project_members.staff_id')
    ->where('project_id', $project_id)
    ->get()->result_array();
	

// date range filter 
$dateRangeFilter = '
<div class="row mb-2">
  <div class="col-md-4">
    <label>From</label>
    <input type="date" class="form-control form-control-sm date-from" data-project-id="' . $project_id . '" />
  </div>
  <div class="col-md-4">
    <label>To</label>
    <input type="date" class="form-control form-control-sm date-to" data-project-id="' . $project_id . '" />
  </div>
';
$staffFilterDropdown = '
  <div class="col-md-4">
    <label>Staff</label>
    <select class="form-control form-control-sm staff-filter" data-project-id="' . $project_id . '">
      <option value="">All Staff</option>';
foreach ($assignedStaff as $staff) {
    $staffFilterDropdown .= '<option value="' . $staff['staffid'] . '">' . get_staff_full_name($staff['staffid']) . '</option>';
}
$staffFilterDropdown .= '</select>
  </div>
</div>';
$loaderHtml = '<div class="text-center mb-2" id="loader-' . $project_id . '" style="display:none;">
  <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
</div>';


$row[] = '
<a href="javascript:void(0);" onclick="$(\'#' . $modalId . '\').modal(\'show\')">
    <i class="fa fa-eye text-primary"></i>
</a>

<div class="modal fade" id="' . $modalId . '" tabindex="-1" role="dialog" aria-labelledby="' . $modalId . '-label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h3 class="modal-title" id="' . $modalId . '-label">Team Hours</h3>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		  ' . $monthDropdown . '
		  ' . $dateRangeFilter . '
		  ' . $staffFilterDropdown . '
		  
		  ' . $loaderHtml . '
		  <div class="team-hours-table" id="team-hours-table-' . $project_id . '">
			' . $modalBody . '
		  </div>
		</div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>';
 


// Add custom fields if any
foreach ($customFieldsColumns as $customFieldColumn) {
    $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
}

$row['DT_RowClass'] = 'has-row-options';
$row = hooks()->apply_filters('projects_table_row_data', $row, $aRow);
$output['aaData'][] = $row;
}
