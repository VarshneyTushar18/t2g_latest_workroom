<?php

defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionEdit   = has_permission('projects', '', 'edit');
$hasPermissionDelete = has_permission('projects', '', 'delete');
$hasPermissionCreate = has_permission('projects', '', 'create');

$aColumns = [
    db_prefix() . 'projects.id as id', // Code
    'name',                            // Project Name
    'start_date',                      // Start Date
    'deadline',                        // Deadline
    'status',                          // Status
    '(SELECT GROUP_CONCAT(CONCAT(firstname, " ", lastname) SEPARATOR ",") 
        FROM ' . db_prefix() . 'project_members 
        JOIN ' . db_prefix() . 'staff 
        ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'project_members.staff_id 
        WHERE project_id=' . db_prefix() . 'projects.id 
        ORDER BY staff_id) as members',
    'estimated_hours',                          // Estimated Hours
    db_prefix() . 'projects.id as hours_spent', // Dummy for Hours Spent
    db_prefix() . 'projects.id as team_hours',  // Dummy for Team / Hours
    db_prefix() . 'projects.id as hours_diff'   // Dummy for Difference
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

    // 1️⃣ Project ID
    $row[] = '<a href="' . $link . '">' . $project_id . '</a>';

    // 2️⃣ Project Name
    $name = '<a href="' . $link . '">' . $aRow['name'] . '</a>';
    $name .= '<div class="row-options">';
    $name .= '<a href="' . $link . '">' . _l('view') . '</a>';
    $name .= '</div>';
    $row[] = $name;

    // 3️⃣ Start Date
    $row[] = _d($aRow['start_date']);

    // 4️⃣ End Date
    $row[] = _d($aRow['deadline']);

    // 5️⃣ Status
    $status = get_project_status_by_id($aRow['status']);
    $row[] = '<span class="label project-status-' . $aRow['status'] . '" 
        style="color:' . $status['color'] . ';
        border:1px solid ' . adjust_hex_brightness($status['color'], 0.4) . ';
        background:' . adjust_hex_brightness($status['color'], 0.04) . ';">
        ' . $status['name'] . '</span>';

    // 6️⃣ Members
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

    // 7️⃣ Allocated Hours
    $row[] = !empty($aRow['estimated_hours']) ? $aRow['estimated_hours'] : '0:00';

    // 8️⃣ Spent Hours
    $spentQuery = $this->ci->db->query("
        SELECT SUM(end_time - start_time) AS total_seconds
        FROM " . db_prefix() . "taskstimers tt
        JOIN " . db_prefix() . "tasks t ON t.id = tt.task_id
        WHERE t.rel_id = ? AND tt.end_time > 0 AND tt.start_time > 0
    ", [$project_id])->row();

    $totalSpentFormatted = seconds_to_time_format($spentQuery->total_seconds ?? 0);
    $row[] = $totalSpentFormatted;

    // 9️⃣ Team / Hours Modal
    $modalId = 'team-hours-' . $project_id;

    $staffSpentQuery = $this->ci->db->query("
        SELECT CONCAT(s.firstname, ' ', s.lastname) as staff_name, SUM(tt.end_time - tt.start_time) AS total_seconds
        FROM " . db_prefix() . "taskstimers tt
        JOIN " . db_prefix() . "tasks t ON t.id = tt.task_id
        JOIN " . db_prefix() . "staff s ON s.staffid = tt.staff_id
        WHERE t.rel_id = ? AND tt.end_time > 0 AND tt.start_time > 0
        GROUP BY tt.staff_id
    ", [$project_id])->result_array();

    $staffHoursHtml = '<div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Team Member</th><th>Hours Spent</th></tr></thead><tbody>';
    if (count($staffSpentQuery) > 0) {
        foreach ($staffSpentQuery as $staffTime) {
            $staffHoursHtml .= '<tr>';
            $staffHoursHtml .= '<td>' . $staffTime['staff_name'] . '</td>';
            $staffHoursHtml .= '<td>' . seconds_to_time_format($staffTime['total_seconds']) . '</td>';
            $staffHoursHtml .= '</tr>';
        }
    } else {
        $staffHoursHtml .= '<tr><td colspan="2" class="text-center">No hours logged by any members</td></tr>';
    }
    $staffHoursHtml .= '</tbody></table></div>';

    $row[] = '
    <a href="javascript:void(0);" onclick="$(\'#' . $modalId . '\').modal(\'show\')">
        <span class="label label-info">View</span>
    </a>

    <div class="modal fade" id="' . $modalId . '" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h4 class="modal-title">Team Hours Distribution</h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body">
            <p class="tw-mb-4">Total Time Spent: <strong>' . $totalSpentFormatted . '</strong></p>
            ' . $staffHoursHtml . '
          </div>
        </div>
      </div>
    </div>';

    // 🔟 Difference (Estimated - Spent)
    $estimated_hours_str = !empty($aRow['estimated_hours']) ? $aRow['estimated_hours'] : '0';
    $estimated_seconds = 0;
    
    // Convert HH:MM format (if present) to seconds, or assume it is numeric hours
    if (strpos($estimated_hours_str, ':') !== false) {
        $parts = explode(':', $estimated_hours_str);
        $estimated_seconds = (isset($parts[0]) ? (int)$parts[0] * 3600 : 0) + (isset($parts[1]) ? (int)$parts[1] * 60 : 0);
    } else {
        $estimated_seconds = (float)$estimated_hours_str * 3600;
    }

    $spent_seconds = (int)($spentQuery->total_seconds ?? 0);
    $difference_seconds = $estimated_seconds - $spent_seconds;
    
    $is_negative = $difference_seconds < 0;
    $abs_difference = abs($difference_seconds);
    
    $percentage = 0;
    if ($estimated_seconds > 0) {
        $percentage = round(($spent_seconds / $estimated_seconds) * 100);
    }
    
    $diff_formatted = seconds_to_time_format($abs_difference);
    
    if ($is_negative) {
        $diff_formatted = '-' . $diff_formatted . ' / ' . $percentage . '%';
        $row[] = '<span class="text-danger">' . $diff_formatted . '</span>';
    } else {
        $diff_formatted = $diff_formatted . ' / ' . $percentage . '%';
        $row[] = '<span class="text-success">' . $diff_formatted . '</span>';
    }

    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}