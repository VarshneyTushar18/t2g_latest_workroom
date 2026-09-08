<?php

defined('BASEPATH') or exit('No direct script access allowed');

$table_data = [
   'Project ID',
   _l('project_name'),
   _l('project_start_date'),
   'End Date',
   _l('project_status'),
   _l('project_members'),
   'Allocated Hours',
   'Spent Hours',
   _l('Team / Hours'),
   _l('Difference')
];


/* $custom_fields = get_custom_fields('projects', ['show_on_table' => 1]);
foreach ($custom_fields as $field) {
    array_push($table_data, [
     'name'     => $field['name'],
     'th_attrs' => ['data-type' => $field['type'], 'data-custom-field' => 1],
 ]);
}*/

$table_data = hooks()->apply_filters('projects_table_columns', $table_data);

render_datatable($table_data, isset($class) ?  $class : 'projects', ['number-index-1'], [
  'data-last-order-identifier' => 'projects',
  'data-default-order'         => get_table_last_order('projects'),
]);