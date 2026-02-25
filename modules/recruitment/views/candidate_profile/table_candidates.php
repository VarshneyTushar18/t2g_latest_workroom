<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'candidate_code',
    'candidate_name',
    'position_name',
    // 'rate',
    'status',
    'email',
    'phonenumber',
    // 'birthday',
    'doj',
    'home_town',
    "(SELECT token 
      FROM tblonboard_tokens 
      WHERE staffid = (
          SELECT staffid 
          FROM tblstaff 
          WHERE personal_email = tblrec_candidate.email 
          LIMIT 1)
     ) AS token",
     "(SELECT recommendation 
     FROM tblrec_interview_feedback 
     WHERE tblrec_interview_feedback.candidate = tblrec_candidate.id 
     ORDER BY tblrec_interview_feedback.id DESC 
     LIMIT 1) AS recommendation"
];
$sIndexColumn = 'id';
$sTable       = db_prefix() . 'rec_candidate';
$join = [
    'LEFT JOIN tblrec_job_position ON tblrec_job_position.position_id = tblrec_candidate.job_position'
];
$where = [];
$string_query = '';

$campaign_filter = $this->ci->input->post('campaign_filter');
$status_filter = $this->ci->input->post('status_filter');

if (isset($campaign_filter) && ($campaign_filter != '')) {
    $campaign_filter = implode(',', $campaign_filter);
    $string_query .= " rec_campaign IN (" . $campaign_filter . ") AND";
}

if (isset($status_filter) && ($status_filter != '')) {
    $status_filter = implode(',', $status_filter);
    $string_query .= " status IN (" . $status_filter . ") AND";
}

if ($string_query != '') {
    $string_query = rtrim($string_query, " AND");
    $where = ["where" . ' ' . $string_query];
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['id', 'last_name']);

$output  = $result['output'];
$rResult = $result['rResult'];

// echo "<pre>";
// print_r($rResult);
// echo "</pre>";
// die;

foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];

        if ($aColumns[$i] == 'id') {
            $_data = $aRow['id'];

        } elseif ($aColumns[$i] == 'candidate_name') {
            $name = '<a href="' . admin_url('recruitment/candidate/' . $aRow['id']) . '">' . candidate_profile_image($aRow['id'], [
                'staff-profile-image-small mright5',
            ], 'small') . '</a>';

            $name .= '<a href="' . admin_url('recruitment/candidate/' . $aRow['id']) . '" >' . $aRow['candidate_name'] . ' ' . $aRow['last_name'] . '</a>';

            $name .= '<div class="row-options">';

            $name .= '<a href="' . admin_url('recruitment/candidate/' . $aRow['id']) . '" >' . _l('view') . '</a>';

            if (has_permission('recruitment', '', 'edit') || is_admin()) {
                $name .= ' | <a href="' . admin_url('recruitment/candidates/' . $aRow['id']) . '" >' . _l('edit') . '</a>';
            }

            if (has_permission('recruitment', '', 'delete') || is_admin()) {
                $name .= ' | <a href="' . admin_url('recruitment/delete_candidate/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
            }

            $name .= '</div>';

            $_data = $name;
        } elseif ($aColumns[$i] == 'birthday') {
            $_data = _d($aRow['birthday']);
        } elseif ($aColumns[$i] == 'rec_campaign') {
            if ($aRow['rec_campaign'] != null) {

                $cp = get_rec_campaign_hp($aRow['rec_campaign']);
                if (isset($cp)) {
                    $_data = $cp->campaign_code . ' - ' . $cp->campaign_name;
                } else {
                    $_data = '';
                }
            } else {
                $_data = '';
            }
        } elseif ($aColumns[$i] == 'rate') {
            if (has_permission('recruitment', '', 'edit') || is_admin()) {
                if ($aRow['status'] == 6) {
                    $_data = '<a href="' . admin_url('recruitment/transfer_to_hr/' . $aRow['id']) . '" class="btn btn-success" >' . _l('tranfer_personnels') . '</a>';
                } else {
                    $_data = '';
                }
            } else {
                $_data = '';
            }
        } elseif ($aColumns[$i] == 'status') {
            $_data = get_status_candidate($aRow['status']);
        } elseif ($aColumns[$i] == 'doj') {
            if ($aRow['doj']) {
                $_data = $aRow['doj'];
            } else {
                $_data = '<a class="btn btn-success btn-sm" candidate-id ="' . $aRow['id'] . '" '. (($aRow['recommendation'] != "selected") ? 'disabled':'onclick="open_joining(this);return false; "') .'>Select Joining</a>';
            }
        } elseif ($aColumns[$i] == 'home_town') {
            $_data = '<a class="btn btn-warning btn-sm" data-id ="' . $aRow['id'] . '" '. (($aRow['recommendation'] != "selected" || $aRow['doj'] == '') ? 'disabled':'onclick="send_link(this); return false;"') .'>Send Link</a>';
        } elseif($i==9) {
            if ($aRow['token']) {
                $_data = '<a class="btn btn-success btn-sm" onclick="copyButtonValue(this)">'. $aRow['token'] .'</a>'; 
            } else {
                $_data = 'NA';
            }
        }

        $row[] = $_data;
    }
    $output['aaData'][] = $row;
}

