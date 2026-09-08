<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = ['id', 'name'];
$sIndexColumn = 'id';
$sTable = db_prefix() . 'task_templates';

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], [
    '(SELECT COUNT(*) FROM ' . db_prefix() . 'task_template_items WHERE template_id=' . db_prefix() . 'task_templates.id) as task_count',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row   = [];
    $row[] = $aRow['id'];

    $name = html_escape($aRow['name']);
    $name .= '<div class="row-options">';
    $name .= '<a href="#" class="open-task-template-edit" data-id="' . $aRow['id'] . '">' . _l('edit') . '</a>';
    $name .= ' | <a href="' . admin_url('task_templates/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    $name .= '</div>';
    $row[] = $name;

    $row[] = (int) $aRow['task_count'];
    $row[] = '<a href="#" class="btn btn-default btn-icon open-task-template-edit" data-id="' . $aRow['id'] . '"><i class="fa fa-pencil-square-o"></i></a> '
        . icon_btn('task_templates/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');

    $output['aaData'][] = $row;
}

