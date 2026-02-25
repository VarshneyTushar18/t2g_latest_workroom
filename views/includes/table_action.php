<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'tblstaff.staff_identifi',
    'CONCAT(tblstaff.firstname, \' \', tblstaff.lastname)',
    'manager_table.staff_identifi',
    'CONCAT(manager_table.firstname, \' \', manager_table.lastname)',
    'time_acction',
    'tblassets.assets_name',
    'acction_code',
    'type',
    'tblassets_acction_1.amount',
    'acction_from',
    'images'
];
$sIndexColumn = 'id';
$sTable       = db_prefix() . 'assets_acction_1';
$join         = [
    'LEFT JOIN ' . db_prefix() . 'staff on ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'assets_acction_1.acction_to',
   'LEFT JOIN ' .   db_prefix() . 'staff AS manager_table on ' . db_prefix() . 'assets_acction_1.reporting_manager = manager_table.staffid',
   'LEFT JOIN ' . db_prefix() . 'assets on ' . db_prefix() . 'assets.id = ' . db_prefix() . 'assets_acction_1.assets'
];
$where        = [];

if (isset($type)) {
    array_push($where, 'AND type = "' . $type . '"');
}

if (isset($asset_id)) {
    array_push($where, 'AND assets = ' . $asset_id);
}
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, []);

// print_r($result);die;

$output  = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); ++$i) {


        $_data = $aRow[$aColumns[$i]];
        if ('time_acction' == $aColumns[$i]) {
            $_data = _dt($aRow['time_acction']);
        } elseif ('type' == $aColumns[$i]) {
            $_data = _l($aRow['type']);
        } elseif ('acction_from' == $aColumns[$i]) {
            // $_data = ' <a href="' . admin_url('staff/profile/' . $aRow['acction_from']) . '">' . get_staff_full_name($aRow['acction_from']) . '</a>';
            $_data =  get_staff_full_name($aRow['acction_from']);
        } elseif ('tblassets.assets_name' == $aColumns[$i]) {
            $_data = ' <a href="' . admin_url('assets/manage_assets#' . $aRow['tblassets.id']) . '">' . $aRow['tblassets.assets_name'] . '</a>';
        } elseif ('CONCAT(tblstaff.firstname, \' \', tblstaff.lastname)' == $aColumns[$i]) {
            $_data = $aRow['CONCAT(tblstaff.firstname, \' \', tblstaff.lastname)'];
        } 
        elseif ('images' == $aColumns[$i]) {


            $images = json_decode($aRow['images'], true);
            if (is_array($images)) {
                // print_r($images);die;
                // $image_data = urlencode($aRow['images']);
                // $_data = '<a href = "' . admin_url('assets/documents/') . $image_data . '" class="btn btn-primary" >Show Documents</a>';
                $_data = form_open('admin/assets/documents');
                $inc = 0;
                foreach ($images as $key => $value) {
                    $_data .= '<input type="hidden" name="' . 'image' . $inc . '" value="' . $value['file_name'] . '">';
                    $inc++;
                }
                $_data .= '<input type = "submit" class = "btn btn-primary" value="Show Images">';
                $_data .= '</form>';
                // $_data .= '<script>document.getElementById("myForm").submit();</script>';
            }
        } elseif ('tblstaff.staff_indentifi' == $aColumns[$i]) {
            $_data = $aRow['tblstaff.staff_indentifi'];
        } elseif ('CONCAT(manager_table.firstname, \' \', manager_table.lastname)' == $aColumns[$i]) {
            // $_data = ' <a href="' . admin_url('staff/profile/' . $aRow['reporting_manager']) . '">' . get_staff_full_name($aRow['reporting_manager']) . '</a>';
            $_data =  $aRow['CONCAT(manager_table.firstname, \' \', manager_table.lastname)'];
        } elseif ('manager_table.staff_identifi' == $aColumns[$i]) {

            $_data =  $aRow['staff_identifi'];
        }
        $row[] = $_data;
    }

    $output['aaData'][] = $row;
}
