<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .panel-body {
        padding: 20px;
    }

    .section-title {
        font-size: 16px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .mtop10 {
        margin-top: 10px;
    }

    #kpiTable td {
        vertical-align: middle;
    }

    .submit-section {
        text-align: right;
    }

    .form-row {
        gap: 10px;
    }
</style>

<div id="wrapper">
    <div class="content col-md-12">
        <div class="panel-body">
            <div class="">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-flex tw-items-center">
                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <rect width="20" height="20" fill="url(#pattern0_15_73)" />
                        <defs>
                            <pattern id="pattern0_15_73" patternContentUnits="objectBoundingBox" width="1" height="1">
                                <use xlink:href="#image0_15_73" transform="scale(0.015625)" />
                            </pattern>
                            <image id="image0_15_73" width="64" height="64" xlink:href="data:image/png;base64,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" />
                        </defs>
                    </svg>
                    Create Key Result Area (KRA)
                </h4>
                <!-- KRA Form Start -->
                <?php
                if(!empty($kra[0]->id)){
                    echo form_open('admin/staff/pedma_admin_kra_action/'.$kra[0]->id.'', array('id' => 'pedma-form-kra'));
                }else{
                    echo form_open('admin/staff/pedma_admin_kra_action', array('id' => 'pedma-form-kra'));
                }
                ?>
                <div class="loader hidden">
                    <img src="https://www.icegif.com/wp-content/uploads/2023/07/icegif-1263.gif" alt="Loading...">
                </div>

                <!-- Department and KRA Type Selection -->
                <div class="form-row tw-flex">
                    <div class="form-group col-md-6 !tw-p-0">
                    <?php
                    $selected_department_id = !empty($kra[0]->departmentid) ? $kra[0]->departmentid : '';
                    echo render_select('departments', $departments, array('departmentid', 'name'), 'Select Department', $selected_department_id);
                    ?>
                    </div>

                    <div class="form-group col-md-6 !tw-p-0">
                        <label for="kraType">Select KRA Type</label>
                        <select id="kraType" name="kraType" class="form-control" required>
                            <option value="0" <?php echo ($kra[0]->type == 1) ? 'selected' : ''; ?>>Default</option>
                            <option value="1" <?php echo ($kra[0]->type == 1) ? 'selected' : ''; ?>>Custom</option>
                        </select>
                    </div>
                </div>

                <!-- KRA Section -->
                <div class="kra-section">
                    <h4 class="section-title tw-mt-0 tw-font-semibold tw-text-lg">Key Result Area (KRA)</h4>
                    <div class="form-group">
                        <label for="kraName">KRA Name</label>
                        <input type="text" id="kraName" name="kraName" class="form-control" placeholder="Enter KRA Name" required <?php echo !empty($kra[0]->name) ? 'value="'.$kra[0]->name.'"' : ''; ?> >
                    </div>
                    <div class="form-group">
                        <label for="kraDescription">KRA Description</label>
                        <textarea id="kraDescription" name="kraDescription" class="form-control" rows="3" placeholder="Describe the KRA" required><?php echo !empty($kra[0]->description) ? ''.$kra[0]->description.'' : ''; ?></textarea>
                    </div>
                    <div class="form-group" id="kraMaxScoreView">
                        <label for="kraMaxScore">Max Score</label>
                        <input type="number" id="kraMaxScore" name="kraMaxScore" class="form-control" placeholder="Enter Max Score" required <?php echo !empty($kra[0]->max_score) ? 'value="'.$kra[0]->max_score.'"' : 'value="10"'; ?> <?php echo ($kra[0]->type == 1) ? '' : 'readonly=""' ?> min="1">
                    </div>
                </div>
                
                <!-- KPI Section -->
                <div id="kpiSection" <?php echo ($kra[0]->type == 1) ? 'style="display: block;"' : 'style="display: none;"' ?>>
                    <hr>
                    <h4 class="section-title tw-mt-0 tw-font-semibold tw-text-lg">Key Performance Indicators (KPIs)</h4>
                    <!-- Add KPI Button -->
                    <button type="button" class="btn btn-primary" id="addKpiButton">Add KPI</button>
                    <!-- KPI Table -->
                    <table class="table table-bordered mtop10" id="kpiTable">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>KPI Name</th>
                                <th>Description</th>
                                <th>Score</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Rows will be dynamically added -->
                             <?php
                             if(!empty($kpi)){
                                $kpiCount = 1;
                                foreach($kpi as $item){
                                    ?>
                                    <tr>
                                        <td><?= $kpiCount; ?></td>
                                        <td><input type="text" name="kpiName[]" class="form-control" placeholder="KPI Name" required value="<?= $item->name; ?>"></td>
                                        <td><textarea name="kpiDescription[]" class="form-control" rows="2" placeholder="Description" required><?= $item->description; ?></textarea></td>
                                        <td><input type="number" name="kpiScore[]" class="form-control kpi-score" placeholder="Score" required value="<?= $item->max_score; ?>" max="100" min="0"></td>
                                        <td><button type="button" class="btn btn-danger remove-kpi">Remove</button></td>
                                    </tr>
                                    <?php
                                    $kpiCount++;
                                }
                             }
                             ?>
                        </tbody>
                    </table>
                </div>

                <hr>
                <!-- Submit Section -->
                <div class="submit-section">
                    <button type="submit" class="btn btn-success"><?php echo (!empty($kra[0]->id)) ? 'Update' : 'Save'; ?> KRA</button>
                </div>



                </form>

            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>


<script>
    $(document).ready(function () {
        $("#departments").prop('required', true);
    });
    let kpiCount = 0;

    // Function to add a new KPI row
    function addKpiRow() {
        const kpiTable = document.querySelector('#kpiTable tbody');

        kpiCount++;
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
                        <td>${kpiCount}</td>
                        <td><input type="text" name="kpiName[]" class="form-control" placeholder="KPI Name" required></td>
                        <td><textarea name="kpiDescription[]" class="form-control" rows="2" placeholder="Description" required></textarea></td>
                        <td><input type="number" name="kpiScore[]" class="form-control kpi-score" placeholder="Score" required></td>
                        <td><button type="button" class="btn btn-danger remove-kpi">Remove</button></td>
                    `;
        kpiTable.appendChild(newRow);
        updateSerialNumbers();
    }

    // Update S.No in the KPI table
    function updateSerialNumbers() {
        const rows = document.querySelectorAll('#kpiTable tbody tr');
        rows.forEach((row, index) => {
            row.cells[0].textContent = index + 1;
        });
    }

    // Function to remove a KPI row
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-kpi')) {
            e.target.closest('tr').remove();
            updateSerialNumbers();
        }
    });

    // Show/hide KPI section based on KRA Type selection and add default KPI row if 'custom' is selected
    document.getElementById('kraType').addEventListener('change', function() {
        const kpiSection = document.getElementById('kpiSection');
        const kraMaxScoreView = document.getElementById('kraMaxScoreView');
        const kraMaxScore = document.getElementById('kraMaxScore');

        if (this.value == 1) {
            kpiSection.style.display = 'block';
            // kraMaxScoreView.style.display = 'block';
            kraMaxScore.value = '';
            kraMaxScore.max = '100';
            kraMaxScore.removeAttribute('readonly');
            // if (kpiCount === 0) {
               // addKpiRow(); // Add default KPI row
            // }
        } else {
            kpiSection.style.display = 'none';
            document.querySelector('#kpiTable tbody').innerHTML = ''; // Clear KPI rows if not custom
            kpiCount = 0; // Reset counter
            // kraMaxScoreView.style.display = 'none';
            kraMaxScore.value = '10';
            kraMaxScore.max = '10';
            kraMaxScore.setAttribute('readonly', true);
        }
    });

    // Add new KPI row when the button is clicked
    document.getElementById('addKpiButton').addEventListener('click', addKpiRow);

    // Validate form before submission
    document.getElementById('pedma-form-kra').addEventListener('submit', function(e) {
        const kraType = document.getElementById('kraType');

        var rowCount = $('#kpiTable tbody tr').length;

        if(rowCount > 0){
        
            let totalKpiScore = 0;
            const kraMaxScore = parseInt(document.getElementById('kraMaxScore').value);
            const kpiScores = document.querySelectorAll('.kpi-score');

            // Sum up all KPI scores
            kpiScores.forEach(function(kpiScore) {
                totalKpiScore += parseInt(kpiScore.value) || 0;
            });

            // Check if total KPI score less than KRA Max Score
            if (totalKpiScore < kraMaxScore) {
                alert('Total KPI scores cannot less than the KRA Max Score.');
                e.preventDefault(); // Prevent form submission
            }

            // Check if total KPI score exceeds KRA Max Score
            if (totalKpiScore > kraMaxScore) {
                alert('Total KPI scores cannot exceed the KRA Max Score.');
                e.preventDefault(); // Prevent form submission
            }
        }
    });
</script>

<!-- Bootstrap JS and custom JavaScript -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

</html>