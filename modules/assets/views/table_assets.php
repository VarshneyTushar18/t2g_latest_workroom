<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'file_id',
    'asset_image',
    'assets_code',
    'assets_name',
    'asset_group',
    'total_damages',
    'date_buy',
    'total_allocation',
    'amount',
    'unit_price',
    'unit',
    'department',
    'belongs_to',
    // 'images'
];

$show_allocation_info = !empty($show_allocation_info);
if ($show_allocation_info) {
    $alloc_table = db_prefix() . 'assets_acction_1';
    $staff_table = db_prefix() . 'staff';
    $assets_table = db_prefix() . 'assets';
    $allocation_employee_sql = '(SELECT TRIM(CONCAT(COALESCE(' . $staff_table . '.firstname, \'\'), \' \', COALESCE(' . $staff_table . '.lastname, \'\')))
        FROM ' . $alloc_table . ' alloc
        INNER JOIN ' . $staff_table . ' ON ' . $staff_table . '.staffid = alloc.acction_to
        WHERE alloc.assets = ' . $assets_table . '.id AND alloc.type = \'allocation\'
        ORDER BY alloc.time_acction DESC, alloc.id DESC
        LIMIT 1) as allocation_employee_name';
    $allocation_time_sql = '(SELECT alloc.time_acction
        FROM ' . $alloc_table . ' alloc
        WHERE alloc.assets = ' . $assets_table . '.id AND alloc.type = \'allocation\'
        ORDER BY alloc.time_acction DESC, alloc.id DESC
        LIMIT 1) as allocation_time';
    $date_buy_idx = array_search('date_buy', $aColumns, true);
    $total_allocation_idx = array_search('total_allocation', $aColumns, true);
    if ($date_buy_idx !== false) {
        $aColumns[$date_buy_idx] = $allocation_employee_sql;
    }
    if ($total_allocation_idx !== false) {
        $aColumns[$total_allocation_idx] = $allocation_time_sql;
    }
}
$sIndexColumn = 'id';
$sTable       = db_prefix() . 'assets';
$join         = [
    'LEFT JOIN ' . db_prefix() . 'asset_unit on ' . db_prefix() . 'asset_unit.unit_id = ' . db_prefix() . 'assets.unit',
    'LEFT JOIN ' . db_prefix() . 'assets_group on ' . db_prefix() . 'assets_group.group_id = ' . db_prefix() . 'assets.asset_group',
    'LEFT JOIN ' . db_prefix() . 'departments on ' . db_prefix() . 'departments.departmentid = ' . db_prefix() . 'assets.department',
    'LEFT JOIN ' . db_prefix() . 'clients on find_in_set(' . db_prefix() . 'clients.userid, ' . db_prefix() . 'assets.belongs_to)'
];
$where = [];
$CI = &get_instance();

$asset_code_filter = trim($CI->input->post('asset_code_filter'));
$asset_name_filter = trim($CI->input->post('asset_name_filter'));
$asset_group_filter = trim($CI->input->post('asset_group_filter'));
$date_buy_filter = trim($CI->input->post('date_buy_filter'));
$department_filter = trim($CI->input->post('department_filter'));
$belongs_to_filter = trim($CI->input->post('belongs_to_filter'));

if ($asset_code_filter !== '') {
    $where[] = 'AND ' . db_prefix() . 'assets.assets_code LIKE "%' . $CI->db->escape_like_str($asset_code_filter) . '%" ESCAPE "!"';
}
if ($asset_name_filter !== '') {
    $where[] = 'AND ' . db_prefix() . 'assets.assets_name LIKE "%' . $CI->db->escape_like_str($asset_name_filter) . '%" ESCAPE "!"';
}
if ($asset_group_filter !== '') {
    $where[] = 'AND ' . db_prefix() . 'assets.asset_group = ' . (int) $asset_group_filter;
}
if ($date_buy_filter !== '') {
    $parsed_date = date('Y-m-d', strtotime($date_buy_filter));
    if ($parsed_date && $parsed_date !== '1970-01-01') {
        $where[] = 'AND DATE(' . db_prefix() . 'assets.date_buy) = ' . $CI->db->escape($parsed_date);
    }
}
if ($department_filter !== '') {
    $where[] = 'AND ' . db_prefix() . 'assets.department = ' . (int) $department_filter;
}
if ($belongs_to_filter !== '') {
    $where[] = 'AND ' . db_prefix() . 'assets.belongs_to LIKE "%' . $CI->db->escape_like_str($belongs_to_filter) . '%" ESCAPE "!"';
}

if (isset($status)) {
    if (1 == $status) {
        array_push($where, 'AND total_allocation = 0');
    } elseif (2 == $status) {
        array_push($where, 'AND total_allocation > 0');
    } elseif (3 == $status) {
        array_push($where, 'AND total_liquidation > 0');
    } elseif (4 == $status) {
        array_push($where, 'AND total_warranty > 0');
    } elseif (5 == $status) {
        array_push($where, 'AND total_lost > 0');
    } elseif (6 == $status) {
        array_push($where, 'AND total_damages > 0');
    } elseif (7 == $status) {
        array_push($where, 'AND status = 3 AND status_override = 1');
    }
}
$additionalSelect = [
    'id',
    'description',
    'warranty_period',
    'asset_location',
    'depreciation',
    'series',
    'supplier_name',
    'supplier_address',
    'supplier_phone',
    'unit_name',
    'group_name',
    db_prefix() . 'departments.name as dpm_name',
    'visible_to_client',
    'company',
    'total_lost',
    'total_liquidation',
    'total_warranty',
    'status',
    'status_override',
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect, ' GROUP BY tblassets.id, file_id, asset_image, assets_code, assets_name, asset_group, date_buy, total_allocation, amount, total_damages, unit_price, unit, department, belongs_to, description, warranty_period, asset_location, depreciation, series, supplier_name, supplier_address, supplier_phone, unit_name, group_name, tbldepartments.name, visible_to_client, company, total_lost, total_liquidation, total_warranty, status, status_override');


$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); ++$i) {
        // echo $aColumns[$i];

        $_data = $aRow[$aColumns[$i]];
        if ('date_buy' == $aColumns[$i]) {
            $_data = _d($aRow['date_buy']);
        } elseif ('unit_price' == $aColumns[$i]) {
            $op    = $aRow['unit_price'] * $aRow['amount'];
            $_data = app_format_money($op, '');
        } elseif ('unit' == $aColumns[$i]) {
            $_data = $aRow['unit_name'];
        } elseif ('asset_group' == $aColumns[$i]) {
            $_data = $aRow['group_name'];
        } elseif ('department' == $aColumns[$i]) {
            $_data = $aRow['dpm_name'];
        } elseif ('amount' == $aColumns[$i]) {
            $_data = $aRow['amount'] - $aRow['total_allocation'];
        } elseif (stripos($aColumns[$i], 'allocation_employee_name') !== false) {
            $name = trim((string) ($aRow['allocation_employee_name'] ?? ''));
            $_data = $name !== '' ? htmlspecialchars($name) : '—';
        } elseif (stripos($aColumns[$i], 'allocation_time') !== false) {
            $_data = !empty($aRow['allocation_time']) ? _dt($aRow['allocation_time']) : '—';
        } elseif ('total_damages' == $aColumns[$i]) {
            $status_labels = [
                1 => ['Available', '#dcfce7', '#22c55e', '#166534'],
                2 => ['Allocate', '#dbeafe', '#3b82f6', '#1d4ed8'],
                3 => ['Sold', '#f3e8ff', '#a855f7', '#7e22ce'],
                4 => ['Liquidated', '#fef3c7', '#f59e0b', '#92400e'],
                5 => ['Lost', '#fee2e2', '#ef4444', '#b91c1c'],
                6 => ['Broken', '#ffedd5', '#f97316', '#c2410c'],
                7 => ['Under repair', '#cffafe', '#06b6d4', '#0e7490'],
            ];
            $current_status = (int) $aRow['status'];
            if (!(int) $aRow['status_override']) {
                $current_status = (int) $aRow['total_damages'] > 0 ? 6 : ((int) $aRow['total_allocation'] > 0 ? 2 : 1);
            }
            $status_labels[2][0] = (int) $aRow['total_allocation'] > 0 ? 'Allocated' : 'Allocate';
            $status_options = '';
            foreach ($status_labels as $status_value => $status_label) {
                $selected = $current_status === $status_value ? ' selected' : '';
                $status_options .= '<option value="' . $status_value . '"' . $selected . '>' . $status_label[0] . '</option>';
            }
            $current_colors = $status_labels[$current_status];
            $status_style = 'background-color:' . $current_colors[1] . ' !important;border-color:' . $current_colors[2] . ' !important;color:' . $current_colors[3] . ' !important;';
            $_data = '<select style="' . $status_style . '" class="form-control input-sm asset-status-select asset-status-' . $current_status . '" data-asset-id="' . (int) $aRow['id'] . '" data-previous-value="' . $current_status . '"' . (has_permission('assets', '', 'edit') || is_admin() ? '' : ' disabled') . '>' . $status_options . '</select>';
        } elseif ('assets_name' == $aColumns[$i]) {
            $name = '<a href="' . admin_url('assets/manage_assets/' . $aRow['id']) . '" onclick="init_asset(' . $aRow['id'] . '); return false;">' . $aRow['assets_name'] . '</a>';

            $name .= '<div class="row-options">';

            $name .= '<a href="' . admin_url('assets/manage_assets/' . $aRow['id']) . '" onclick="init_asset(' . $aRow['id'] . '); return false;">' . _l('view') . '</a>';
            if (has_permission('assets', '', 'edit') || is_admin()) {
                $options    = '';
                if (!empty($aRow['belongs_to'])) {
                    $belongs_to = explode(',', $aRow['belongs_to']);
                    foreach ($belongs_to as $value) {
                        $options .= "<option value='" . $value . "' selected>" . get_company_name($value) . '</option>';
                    }
                }
                if (!empty($aRow['belongs_to'])) {
                    $aRow['visible_to_client'] = "1";
                }

                $name .= ' | <a href="#" onclick="edit_asset(this,' . $aRow['id'] . ' , \'' . $aRow['file_id'].  '\'); return false;" data-assets_name="' . $aRow['assets_name'] . '" data-assets_code="' . $aRow['assets_code'] . '" data-date_buy="' . $aRow['date_buy'] . '" data-amount="' . $aRow['amount'] . '" data-unit_price="' . $aRow['unit_price'] . '" data-description="' . $aRow['description'] . '" data-supplier_phone="' . $aRow['supplier_phone'] . '" data-supplier_name="' . $aRow['supplier_name'] . '" data-supplier_address="' . $aRow['supplier_address'] . '" data-warranty_period="' . $aRow['warranty_period'] . '" data-depreciation="' . $aRow['depreciation'] . '" data-series="' . $aRow['series'] . '" data-unit="' . $aRow['unit'] . '" data-department="' . $aRow['department'] . '" data-asset_image_url="https://drive.google.com/thumbnail?id=' . $aRow['file_id'] . '" data-belongs_to_option="' . $options . '" data-visible_to_client="' . $aRow['visible_to_client'] . '" data-asset_group="' . $aRow['asset_group'] . '" data-asset_location="' . $aRow['asset_location'] . '" file_id="' . $aRow['file_id'] .'" >' . _l('edit') . '</a>';
            }

            if (has_permission('assets', '', 'delete') || is_admin()) {
                $name .= ' | <a href="' . admin_url('assets/delete_assets/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
            }

            $name .= '</div>';

            $_data = '<div class="asset-name-cell">' . $name . '</div>';
        } elseif ('assets_code' == $aColumns[$i]) {
            $_data = '<a href="' . admin_url('assets/manage_assets/' . $aRow['id']) . '" onclick="init_asset(' . $aRow['id'] . '); return false;">' . $aRow['assets_code'] . '</a>';
        } elseif ('asset_image' == $aColumns[$i]) {

            if($aRow['asset_image']){
                
                if (0 == $i) {
                    $_data = "<img alt='" . module_dir_url('assets', 'uploads') . '/' . $aRow['asset_image'] . "' src='https://drive.google.com/thumbnail?id=/{$aRow['file_id']}' class='img-thumbnail img-responsive zoom' onerror=\"this.src='" . module_dir_url('assets', 'uploads') . "/image-not-available.png'\">";
                }
                if (1 == $i) {
                    $_data = module_dir_url('assets', 'uploads') . '/' . $aRow['asset_image'];
                }
            } else{
                $_data = '<img src="https://www.freeiconspng.com/uploads/no-image-icon-15.png" width="100" alt="No Save Icon Format" />';
            }
            
        } elseif ('belongs_to' == $aColumns[$i]) {
            $_data      = 'No';
            if (!empty($aRow['belongs_to'])) {
                $_data      = 'Yes';
                $belongs_to = explode(',', $aRow['belongs_to']);
                foreach ($belongs_to as $value) {
                    $_data .= ' - <a href="' . admin_url('clients/client/' . $value) . '">' . get_company_name($value) . '</a>, <br>';
                }
                $_data = trim($_data, ", <br>");
            }
        } elseif ('images' == $aColumns[$i]) {
            $images = json_decode($aRow['images'], true);
            if (is_array($images)) {
                $_data = form_open('admin/assets/documents');
                $inc = 0;
                foreach ($images as $key => $value) {
                    $_data .= '<input type="hidden" name="' . 'image' . $inc . '" value="' . $value['file_name'] . '">';
                    $inc++;
                }
                $_data .= '<input type = "submit" class = "btn btn-primary" value="Display documents">';
                $_data .= '</form>';
            }
        } elseif('file_id'== $aColumns[$i]) {
            if($aRow['file_id']){
                
                if (0 == $i) {
                    $_data = "<img  src='https://drive.google.com/thumbnail?id=" . $aRow['file_id'] . "' class='img-thumbnail img-responsive zoom' >";
                }
                if (1 == $i) {
                    $_data = 'https://drive.google.com/thumbnail?id=' . $aRow['file_id'];
                }
            } else{
                $_data = '<img src="https://www.freeiconspng.com/uploads/no-image-icon-15.png" width="100" alt="No Save Icon Format" />';
            }
        }

        $row[] = $_data;
    }

    $output['aaData'][] = $row;
}
