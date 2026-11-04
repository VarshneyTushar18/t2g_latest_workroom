<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();

$selected_department_id = isset($selected_department_id) ? (int) $selected_department_id : 0;
$default_kra = isset($default_kra) && is_array($default_kra) ? $default_kra : [];
$custom_kra = isset($custom_kra) && is_array($custom_kra) ? $custom_kra : [];
$can_edit_pedma_kra = !empty($can_edit_pedma_kra);
$kra_max = function_exists('pedma_kra_rating_max') ? (int) pedma_kra_rating_max() : 5;

if (!function_exists('pedma_render_manage_kra_table_body')) {
    function pedma_render_manage_kra_table_body($rows, $sectionKey, $can_edit, $kra_max)
    {
        if (empty($rows)) {
            echo '<tr><td colspan="8" class="text-muted text-center">No KRAs in this list for the selected department.</td></tr>';

            return;
        }

        $a = 1;
        foreach ($rows as $row) {
            $rowId = (int) ($row->id ?? 0);
            $hasKpi = !empty($row->kpi_data);
            ?>
            <tr class="kra-row" data-kra-id="<?php echo $sectionKey . '-' . $a; ?>">
                <td><?php echo $a; ?></td>
                <td><?php echo html_escape($row->departmentName ?? '—'); ?></td>
                <td><?php echo $sectionKey === 'custom' ? 'Custom' : 'Default'; ?></td>
                <td><?php echo html_escape($row->name ?? ''); ?></td>
                <td>
                    <?php if ($hasKpi) { ?>
                        <span class="kpi-toggle-icon" role="button" tabindex="0" onclick="toggleKpiRows('<?php echo $sectionKey . '-' . $a; ?>')">+ Show KPIs</span>
                    <?php } else { ?>
                        <span class="text-muted">No KPIs assigned</span>
                    <?php } ?>
                </td>
                <td><?php echo html_escape($row->description ?? ''); ?></td>
                <td><?php echo (int) ($row->max_score ?? $kra_max); ?></td>
                <td>
                    <?php if ($can_edit && $rowId > 0) { ?>
                        <a href="<?php echo admin_url('staff/pedma_admin_kra_action/' . $rowId); ?>" class="btn btn-success btn-sm">Edit</a>
                    <?php } else { ?>
                        <span class="text-muted">—</span>
                    <?php } ?>
                </td>
            </tr>
            <?php
            if ($hasKpi) {
                foreach ($row->kpi_data as $kpi) {
                    ?>
                    <tr class="kpi-row" data-kra-id="<?php echo $sectionKey . '-' . $a; ?>" style="display: none;">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><?php echo html_escape($kpi->name ?? ''); ?></td>
                        <td><?php echo html_escape($kpi->description ?? ''); ?></td>
                        <td colspan="2"><?php echo (int) ($kpi->max_score ?? $kra_max); ?></td>
                    </tr>
                    <?php
                }
            }
            $a++;
        }
    }
}
?>

<div id="wrapper">
    <div class="content col-md-12">
        <div class="panel-body">
            <div class="tw-flex tw-justify-between tw-items-center tw-flex-wrap tw-gap-3">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-flex tw-items-center">
                    Manage Key Responsibility Area (KRA)
                </h4>
                <?php if ($can_edit_pedma_kra && $selected_department_id > 0) { ?>
                    <a href="<?php echo admin_url('staff/pedma_admin_kra_action?department=' . $selected_department_id); ?>" class="btn btn-primary">Create KRA</a>
                <?php } ?>
            </div>

            <p class="text-muted mtop10">
                Lists match the <strong>Default KRAs</strong> and <strong>Custom KRAs</strong> shown on the PEDMA Evaluation page for the selected department.
                Only HR and administrators can create or edit KRAs.
            </p>

            <form method="get" action="<?php echo admin_url('staff/pedma_admin_kra'); ?>" class="row mtop15 mbot20">
                <div class="col-md-4">
                    <label for="pedma_kra_department">Department</label>
                    <select name="department" id="pedma_kra_department" class="form-control selectpicker" data-live-search="true" onchange="this.form.submit()">
                        <option value="">Select department</option>
                        <?php foreach ($departments as $dept) {
                            $deptId = (int) (is_array($dept) ? ($dept['departmentid'] ?? 0) : ($dept->departmentid ?? 0));
                            $deptName = is_array($dept) ? ($dept['name'] ?? '') : ($dept->name ?? '');
                            ?>
                            <option value="<?php echo $deptId; ?>" <?php echo $deptId === $selected_department_id ? 'selected' : ''; ?>>
                                <?php echo html_escape($deptName); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </form>

            <?php if ($selected_department_id <= 0) { ?>
                <div class="alert alert-info">Select a department to view its Default and Custom KRA lists.</div>
            <?php } else { ?>

                <h5 class="tw-font-semibold">Default KRAs</h5>
                <p class="text-muted" style="font-size:12px;">Same list as the evaluation sidebar → Default KRAs.</p>
                <div class="table-responsive mbot25">
                    <table class="table table-kra table-bordered">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Department</th>
                                <th>KRA Type</th>
                                <th>KRA Name</th>
                                <th>KPI Name</th>
                                <th>KPI Description</th>
                                <th>Max Score</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php pedma_render_manage_kra_table_body($default_kra, 'default', $can_edit_pedma_kra, $kra_max); ?>
                        </tbody>
                    </table>
                </div>

                <h5 class="tw-font-semibold">Custom KRAs <small class="text-muted">(Dept only)</small></h5>
                <p class="text-muted" style="font-size:12px;">Same list as the evaluation sidebar → Custom KRAs.</p>
                <div class="table-responsive">
                    <table class="table table-kra table-bordered">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Department</th>
                                <th>KRA Type</th>
                                <th>KRA Name</th>
                                <th>KPI Name</th>
                                <th>KPI Description</th>
                                <th>Max Score</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php pedma_render_manage_kra_table_body($custom_kra, 'custom', $can_edit_pedma_kra, $kra_max); ?>
                        </tbody>
                    </table>
                </div>

            <?php } ?>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
    function toggleKpiRows(kraId) {
        const rows = document.querySelectorAll('.kpi-row[data-kra-id="' + kraId + '"]');
        rows.forEach(function(row) {
            row.style.display = row.style.display === 'none' ? '' : 'none';
        });

        const toggleIcon = document.querySelector('.kra-row[data-kra-id="' + kraId + '"] .kpi-toggle-icon');
        if (toggleIcon) {
            toggleIcon.textContent = toggleIcon.textContent.indexOf('+') === 0 ? '− Hide KPIs' : '+ Show KPIs';
        }
    }
</script>

<style>
    .kpi-toggle-icon {
        cursor: pointer;
        font-weight: bold;
        color: #2563eb;
    }

    .btn-success {
        background-color: #CA8A04 !important;
        border: none;
        color: #fff;
    }

    .btn-success:hover {
        background-color: #F5A623;
    }
</style>

</body>
</html>
