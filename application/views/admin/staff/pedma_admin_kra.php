<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content col-md-12">
        <div class="panel-body">
            <div class="tw-flex tw-justify-between tw-items-center">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-flex tw-items-center">
                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <rect width="20" height="20" fill="url(#pattern0_15_73)" />
                        <defs>
                            <pattern id="pattern0_15_73" patternContentUnits="objectBoundingBox" width="1" height="1">
                                <use xlink:href="#image0_15_73" transform="scale(0.015625)" />
                            </pattern>
                            <image id="image0_15_73" width="64" height="64" xlink:href="data:image/png;base64,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" />
                        </defs>
                    </svg>Manage Key Responsibility Area (KRA)
                </h4>
                <a href="./pedma_admin_kra_action" class="btn btn-primary">Create KRA</a>
            </div>
            <div>
                <div class="table-responsive">
                    <table class="table table-kra" id="kraTable">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Department</th>
                                <th>KRA Type</th>
                                <th>KRA Name</th>
                                <th>KPI Name</th>
                                <th>KPI Description</th>
                                <th>MAX Score</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $a = 1;
                            foreach($kra as $row){
                                ?>
                                <tr class="kra-row" data-kra-id="<?php echo $a; ?>" >
                                    <td><?php echo $a; ?></td>
                                    <td><?php echo $row->departmentName; ?></td>
                                    <td>
                                        <?php if($row->type == 1){ ?>
                                        Custom
                                        <?php }else{ ?>
                                        Default
                                        <?php } ?>
                                    </td>
                                    <td><?php echo $row->name; ?></td>
                                    <?php if(!empty($row->kpi_data)){ ?>
                                    <td onclick="toggleKpiRows(<?php echo $a; ?>)"><span class="kpi-toggle-icon">+ Show KPIs</span></td>
                                    <?php }else{ ?>
                                    <td><span class="">No KPIs assigned</span></td>
                                    <?php } ?>
                                    <td><?php echo $row->description; ?></td>
                                    <td><?php echo $row->max_score; ?></td>
                                    <td><a href="./pedma_admin_kra_action/<?php echo $row->id; ?>" class="btn btn-success">Edit</a></td>
                                </tr>
                                    <?php 
                                    if(!empty($row->kpi_data)){ 
                                        foreach($row->kpi_data as $kpi){
                                        ?>
                                        <tr class="kpi-row" data-kra-id="<?php echo $a; ?>" style="display: none;">
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td><?php echo $kpi->name; ?></td>
                                            <td><?php echo $kpi->description; ?></td>
                                            <td colspan="2"><?php echo $kpi->max_score; ?></td>
                                        </tr>
                                    <?php
                                        }
                                     }
                            $a++;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>


                <script>
                    function toggleKpiRows(kraId) {
                        const rows = document.querySelectorAll(`.kpi-row[data-kra-id="${kraId}"]`);
                        rows.forEach(row => {
                            row.style.display = row.style.display === 'none' ? '' : 'none';
                        });

                        const toggleIcon = document.querySelector(`.kra-row[data-kra-id="${kraId}"] .kpi-toggle-icon`);
                        toggleIcon.textContent = toggleIcon.textContent === '+ Show KPIs' ? '− Hide KPIs' : '+ Show KPIs';
                    }
                </script>

                <style>
                    .kpi-toggle-icon {
                        cursor: pointer;
                        font-weight: bold;
                    }

                    .btn-success {
                        background-color: #CA8A04 !important;
                        border: none;
                        color: #fff;
                        padding: 5px 10px;
                        border-radius: 4px;
                    }

                    .btn-success:hover {
                        background-color: #F5A623;
                    }
                </style>

            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
    // window onload function
    // window.onload = function() {
       // var kraTable = $("#kraTable");

        // kraTable.dataTable({
           // dom: 'Bfrtip',
          
    // });
    // }
</script>



<!-- Bootstrap JS and custom JavaScript -->
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

</html>