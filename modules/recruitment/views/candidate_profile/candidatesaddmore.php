<?php init_head(); ?>

<style>
    .loader {
        position: fixed;
        z-index: 99;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: white;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .loader>img {
        width: 100px;
    }

    .loader .hidden {
        animation: fadeOut 1s;
        animation-fill-mode: forwards;
    }
</style>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="no-margin font-bold"> <?php echo _l('Add Candidate'); ?></h4>
                                <hr />
                                <span id="candidateCodeError" class="text-danger"></span>
                            </div>
                        </div>
                       <?php
                            echo form_open_multipart(admin_url("recruitment/add_candidate_in_bulk"), ["id" => "recruitment-candidate-form", "onsubmit" => "disableButton()"]);
                        ?>
                        
                        <table id="candidateTable" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Recruitment Campaign</th>
                                    <th>Candidate Code</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Gender</th>
                                    <th>Position</th>
                                </tr>
                            </thead>
                            <tbody id="formContainer">
                                <tr class="candidate-row">
                                    <td>
                                        <select name="rec_campaign[]" class="form-control" required>
                                            <option value="">Select Campaign</option>
                                            <?php foreach ($rec_campaigns as $s) { ?>
                                                <option value="<?php echo html_entity_decode($s["cp_id"]); ?>"><?php echo html_entity_decode($s["campaign_code"] . " - " . $s["campaign_name"]); ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td><input type="text" name="candidate_code[]" class="form-control candidateCode" placeholder="Enter Candidate Code" required></td>
                                    <td><input type="text" name="candidate_name[]" class="form-control" placeholder="Enter First Name" required></td>
                                    <td><input type="text" name="last_name[]" class="form-control" placeholder="Enter Last Name" required></td>
                                    <td>
                                        <select name="gender[]" class="form-control" required>
                                            <option value="">Select Gender</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="job_position[]" class="form-control" required>
                                            <option value="">Select Position</option>
                                            <?php foreach ($job_positions as $s) { ?>
                                                <option value="<?php echo html_entity_decode($s["position_id"]); ?>"><?php echo html_entity_decode($s["position_name"]); ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="candidate-row">
                                    <td colspan="2"><textarea name="remarks[]" class="form-control" rows="2" placeholder="Enter Remarks" required></textarea></td>
                                    <td colspan="2"><textarea name="package_details[]" class="form-control" rows="2" placeholder="Enter Package Details" required></textarea></td>
                                    <td><input type="text" name="phonenumber[]" class="form-control" placeholder="Enter Phone Number" required minlength="10" maxlength="10"></td>
                                    <td><input type="email" name="email[]" class="form-control" placeholder="Enter Email" required></td>
                                </tr>
                                <tr class="candidate-row">
                                    <td colspan="5"><input type="file" name="cv_link[]" class="form-control" required></td>
                                    <td><button type="button" class="remove btn btn-danger">X</button></td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <button type="button" id="addMore" class="btn btn-primary">Add More</button>
                        <button type="submit" id="submitButton" class="btn btn-info pull-right">Submit</button>
                        
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="loader hidden">
    <img src="https://www.icegif.com/wp-content/uploads/2023/07/icegif-1263.gif" alt="Loading...">
</div>


<?php init_tail(); ?>

<script>
    $(document).ready(function() {
        $("#addMore").click(function() {
            let newRow = `
                <tr class="candidate-row">
                    <td>
                        <select name="rec_campaign[]" class="form-control" required>
                            <option value="">Select Campaign</option>
                            <?php foreach ($rec_campaigns as $s) { ?>
                                <option value="<?php echo html_entity_decode($s["cp_id"]); ?>"><?php echo html_entity_decode($s["campaign_code"] . " - " . $s["campaign_name"]); ?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <td><input type="text" name="candidate_code[]" class="form-control candidateCode" placeholder="Enter Candidate Code" required></td>
                    <td><input type="text" name="candidate_name[]" class="form-control" placeholder="Enter First Name" required></td>
                    <td><input type="text" name="last_name[]" class="form-control" placeholder="Enter Last Name" required></td>
                    <td>
                        <select name="gender[]" class="form-control" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </td>
                    <td>
                        <select name="job_position[]" class="form-control" required>
                            <option value="">Select Position</option>
                            <?php foreach ($job_positions as $s) { ?>
                                <option value="<?php echo html_entity_decode($s["position_id"]); ?>"><?php echo html_entity_decode($s["position_name"]); ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr class="candidate-row">
                    <td colspan="2"><textarea name="remarks[]" class="form-control" rows="2" placeholder="Enter Remarks" required></textarea></td>
                    <td colspan="2"><textarea name="package_details[]" class="form-control" rows="2" placeholder="Enter Package Details" required></textarea></td>
                    <td><input type="text" name="phonenumber[]" class="form-control" placeholder="Enter Phone Number" required minlength="10" maxlength="10"></td>
                    <td><input type="email" name="email[]" class="form-control" placeholder="Enter Email" required></td>
                </tr>
                <tr class="candidate-row">
                    <td colspan="5"><input type="file" name="cv_link[]" class="form-control" required></td>
                    <td><button type="button" class="remove btn btn-danger">X</button></td>
                </tr>`;

            $("#formContainer").append(newRow);
        });

        // Corrected Remove Functionality
        $(document).on("click", ".remove", function() {
            let row = $(this).closest("tr");
            let index = row.index();
            
            // Remove the three related rows
            row.prev().prev().remove(); // First row
            row.prev().remove();        // Second row
            row.remove();               // Third row (current one)
        });

        $("#recruitment-candidate-form").one("submit", function () {
            $("#submitButton").prop('disabled', true);
            $('.loader').removeClass('hidden');
        });

        $(document).on("change", ".candidateCode", function () { 
            var code = $(this).val().trim();
            var currentInput = $(this);
            var isDuplicate = false;

            // Check if the entered code is already present in other input fields
            $(".candidateCode").not(this).each(function() {
                if ($(this).val().trim() === code && code !== '') {
                    isDuplicate = true;
                    return false; // Break loop
                }
            });

            if (isDuplicate) {
                currentInput.addClass("error");
            } else {
                currentInput.removeClass("error");

                // Proceed with AJAX request only if it's unique on the page
                $.ajax({
                    type: "POST",
                    url: "<?= admin_url('recruitment/checkCandidateCode') ?>",
                    data: { code: code },
                    success: function (response) {
                        if (response == 1) {
                            currentInput.addClass("error");
                        } else {
                            currentInput.removeClass("error");
                        }
                        checkSubmitButton();
                    }
                });
            }
            checkSubmitButton();
        });

        // Function to check if submit button should be enabled or disabled
        function checkSubmitButton() {
            if ($(".candidateCode.error").length > 0) {
                $("#candidateCodeError").html('Candidate code must be unique.');
                $("#submitButton").prop('disabled', true);
            } else {
                $("#candidateCodeError").html('');
                $("#submitButton").prop('disabled', false);
            }
        }


    });
</script>
