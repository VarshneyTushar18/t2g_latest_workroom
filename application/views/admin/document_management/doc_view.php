<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php 
$document = $document[0];
$description = json_decode($document->description, true);
$file = json_decode($document->file, true);
$dfile = json_decode($document->drive_file_id, true);
$mComment = json_decode($document->manager_comment, true);
$status = json_decode($document->status, true);
$date = json_decode($document->created_at, true);
?>

    <style>
        .scrollable-container {
            max-height: 200px;
            overflow-y: auto;
            font-family: Arial, sans-serif;
        }
        .loader {
            position: fixed;
            z-index: 10000;
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
                <div class="content p-2 row" style="margin-top: -50px;">
                    <div class="panel_s">
                        <div class="panel-body w-100">
                         
                            <div class="row">

                                <div class="tab-content active">

                                </div>

                                <h4><?php echo _l('Document Upload Information'); ?></h4>
                                <hr />
                                <div class="col-md-6">
                                    <table class="table border table-striped ">
                                        <tbody>
                                            <tr>
                                                <td><?php echo _l('subject'); ?></td>
                                                <td><?= $document->subject ?></td>
                                            </tr>
                                            <tr>
                                                <td><?php echo _l('Category'); ?></td>
                                                <td><?= get_doccategory_name_by_categoryid($document->category) ?></td>
                                            </tr>
                                            <tr>
                                                <td><?php echo _l('Uploaded Date'); ?></td>
                                                <td><?= $date[0] ?></td>
                                            </tr>
                                            <tr>
                                                <td><?php echo _l('Uploaded by'); ?></td>
                                                <td><?= get_staff_full_name($document->emp_id); ?> (<?= get_staff_emp_id($document->emp_id); ?>)</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="col-md-6">
                                    <table class="table border table-striped ">
                                        <tbody>
                                            <tr>
                                                <td><?php echo _l('Department'); ?></td>
                                                <td>
                                                    <?= get_department_name_by_departmentid($document->departments); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><?php echo _l('Project'); ?></td>
                                                <td><?= $document->project ?></td>
                                            </tr>
                                            <tr>
                                                <td><?php echo _l('Status'); ?></td>
                                                <td>
                                                    <?php
                                                    if(end($status) == 1){
                                                        echo "<span class='label label-success'>Approved</span>";
                                                    }else if(end($status) == 2){
                                                        echo "<span class='label label-danger'>Rejected</span>";
                                                    }else{
                                                        echo "<span class='label label-info'>Under Review</span>";
                                                    } 
                                                    ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- <div class="col-md-12">
                                    <h4>Document Description</h4>
                                    <hr>
                                    <div class="scrollable-container">
                                        <?= $document->description; ?>
                                    </div>
                                </div> -->

                               
                                
                                <div class="col-md-12" style="margin-top: 30px;">
                                    <h4>Attachments</h4>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Document Version</th>
                                                    <th>Document Link</th>
                                                    <th>Description</th>
                                                    <th>Uploaded Date</th>
                                                    <th>Status</th>
                                                    <th>Admin Comment</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                for ($i = count($dfile) - 1; $i >= 0; $i--) {
                                                ?>
                                                <tr>
                                                    <td>V<?= $i+1 ?></td>
                                                    <td><a href="https://drive.google.com/file/d/<?= $dfile[$i] ?>/view" target="_blank" class="btn btn-warning">View Document</a></td>
                                                    <td><?php echo (!empty($description[$i])) ? '<button class="btn btn-warning descriptionViewBtn" data-description="'.$description[$i].'">View</button>':''; ?></td>
                                                    <td><?= $date[$i] ?></td>
                                                    <td>
                                                        <?php
                                                        if($status[$i] == 1){
                                                            echo "<span class='label label-success'>Approved</span>";
                                                        }else if($status[$i] == 2){
                                                            echo "<span class='label label-danger'>Rejected</span>";
                                                        }else{
                                                            echo "<span class='label label-info'>Under Review</span>";
                                                        } 
                                                        ?>
                                                    </td>
                                                    <td><?php echo (!empty($mComment[$i])) ? '<button class="btn btn-warning managerCommentVBtn" data-comment="'.$mComment[$i].'">View</button>':''; ?></td>
                                                    <?php
                                                    if(is_admin()){
                                                        ?>
                                                        <td>
                                                            <button class="btn btn-success docApproveBtn" <?php echo ($status[$i] != 0) ? 'disabled':''; ?> data-version="<?= $i ?>">Approve</button>
                                                            <button class="btn btn-danger docRejectBtn" <?php echo ($status[$i] != 0) ? 'disabled':''; ?> data-version="<?= $i ?>">Reject</button>
                                                        </td>
                                                        <?php
                                                    }else if(is_in_managers_list() || get_staff_emp_id(get_staff_user_id()) == 1033 || get_staff_emp_id(get_staff_user_id()) == 1611 || get_staff_emp_id(get_staff_user_id()) == 2047){
                                                        ?>
                                                        <td>
                                                            <button class="btn btn-primary docEditBtn" <?php echo ($status[$i] != 2) ? 'disabled':''; ?> data-version="<?= $i ?>" data-description="<?= $description[$i] ?>" data-dFileId="<?= $dfile[$i] ?>">Edit</button>
                                                        </td>
                                                        <?php
                                                    }
                                                    ?>
                                                </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <?php
                                    if(is_in_managers_list() || get_staff_emp_id(get_staff_user_id()) == 1033 || get_staff_emp_id(get_staff_user_id()) == 1611 || get_staff_emp_id(get_staff_user_id()) == 2047){
                                    ?>
                                    <button class="btn btn-primary" id="addNextVersion"><i class="fa-solid fa-plus"></i> Add Next Version</button>
                                    <?php } ?>
                                </div>

                               
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="loader hidden">
    <img src="https://www.icegif.com/wp-content/uploads/2023/07/icegif-1263.gif" alt="Loading...">
</div>

    <!-- The Modal -->
<div class="modal" id="addNextVersionModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Upload Version <?php echo count($dfile)+1; ?></h4>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <?php echo form_open('admin/document_management/upload_next_version_document', array('class' => 'file-upload-form', 'enctype' => 'multipart/form-data')); ?>
                    <div class="form-group">
                        <label><span class="text-danger">*</span> Attached file</label>
                        <input type="file" class="form-control fileInput" name="attachFile" required>
                        <input type="hidden" name="docId" required value="<?= $document->id ?>">
                    </div>
                    <div class="form-group">
                        <label><span class="text-danger">*</span> Document Description</label>
                        <textarea class="form-control" name="description" rows="5" required maxlength="500"></textarea>
                    </div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="closeAddNextVersionBtn">Close</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="docEditModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Edit Document Version <spam id="displayDocVersion"></spam></h4>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <?php echo form_open('admin/document_management/edit_version_document', array('class' => 'file-upload-form', 'enctype' => 'multipart/form-data')); ?>
                    <div class="form-group">
                        <label><span class="text-danger">*</span> Attached file</label>
                        <input type="file" class="form-control fileInput" name="attachFile" required>
                        <input type="hidden" name="docId" required value="<?= $document->id ?>">
                        <input type="hidden" name="status" required value="0">
                        <input type="hidden" name="version" id="docEditVersion" value="">
                        <input type="hidden" name="dFileId" id="docDriveFileId" value="">
                    </div>
                    <div class="form-group">
                        <label><span class="text-danger">*</span> Document Description</label>
                        <textarea class="form-control" name="description" rows="5" required maxlength="500" id="doEditDescription"></textarea>
                    </div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="closeDocEditModalBtn">Close</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="docApproveModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Document Approve</h4>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <?php echo form_open('admin/document_management/updateStatus', array('id' => 'doc-update-status-form')); ?>
                    <div class="form-group">
                        <label>Comment</label>
                        <textarea class="form-control" name="comment" rows="5" placeholder="Please write your comment of approval" required></textarea>
                        <input type="hidden" name="docId" required value="<?= $document->id ?>">
                        <input type="hidden" name="status" required value="1">
                        <input type="hidden" name="version" id="docApproveVersion" value="">
                    </div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="closeDocApprovalModalBtn">Close</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="docRejectModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Document Reject</h4>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <?php echo form_open('admin/document_management/updateStatus', array('id' => 'doc-upload-form')); ?>
                    <div class="form-group">
                        <label>Comment</label>
                        <textarea class="form-control" name="comment" rows="5" placeholder="Please write your comment of rejection" required></textarea>
                        <input type="hidden" name="docId" required value="<?= $document->id ?>">
                        <input type="hidden" name="status" required value="2">
                        <input type="hidden" name="version" id="docRejectVersion" value="">
                    </div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="closeDocRejectionBtn">Close</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="managerCommentVModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Admin Comment</h4>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <div id="managerCommentDisplay">

                </div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="closeManagerCommentVBtn">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="descriptionViewModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Document Description</h4>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <div id="descriptionDisplay">

                </div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="closeDescriptionViewBtn">Close</button>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
    $(document).ready(function () {
        $("#addNextVersion").click(function (e) { 
            e.preventDefault();
            $("#addNextVersionModal").modal('show');
        });

        $("#closeAddNextVersionBtn").click(function (e) { 
            e.preventDefault();
            $("#addNextVersionModal").modal('hide');
        });

        // Approve Modal
        $(".docApproveBtn").click(function (e) { 
            e.preventDefault();
            
            var version = $(this).data('version');

            $("#docApproveVersion").val(version);

            $("#docApproveModal").modal('show');
        });

        $("#closeDocApprovalModalBtn").click(function (e) { 
            e.preventDefault();
            $("#docApproveVersion").val('');
            $("#docApproveModal").modal('hide');
        });

        // Rejection Modal
        $(".docRejectBtn").click(function (e) { 
            e.preventDefault();

            var version = $(this).data('version');

            $("#docRejectVersion").val(version);

            $("#docRejectModal").modal('show');
        });

        $("#closeDocRejectionBtn").click(function (e) { 
            e.preventDefault();
            $("#docRejectVersion").val('');
            $("#docRejectModal").modal('hide');
        });

        // Comment view Modal
        $(".managerCommentVBtn").click(function (e) { 
            e.preventDefault();

            var comment = $(this).data('comment');

            $("#managerCommentDisplay").html(comment);

            $("#managerCommentVModal").modal('show');
        });

        $("#closeManagerCommentVBtn").click(function (e) { 
            e.preventDefault();
            $("#managerCommentDisplay").html('');
            $("#managerCommentVModal").modal('hide');
        });

        // Description view Modal
        $(".descriptionViewBtn").click(function (e) { 
            e.preventDefault();

            var description = $(this).data('description');

            $("#descriptionDisplay").html(description);

            $("#descriptionViewModal").modal('show');
        });

        $("#closeDescriptionViewBtn").click(function (e) { 
            e.preventDefault();
            $("#descriptionDisplay").html('');
            $("#descriptionViewModal").modal('hide');
        });

        // Document Edit Modal
        $(".docEditBtn").click(function (e) { 
            e.preventDefault();

            var version = $(this).data('version');
            var description = $(this).data('description');
            var dFileId = $(this).data('dfileid');

            $("#displayDocVersion").html(version+1);
            $("#docEditVersion").val(version);
            $("#docDriveFileId").val(dFileId);
            $("#doEditDescription").val(description);

            $("#docEditModal").modal('show');
        });

        $("#closeDocEditModalBtn").click(function (e) { 
            e.preventDefault();
            $("#displayDocVersion").html('');
            $("#docEditVersion").val('');
            $("#docDriveFileId").val('');
            $("#doEditDescription").val('');

            $("#docEditModal").modal('hide');
        });
    });
</script>

<script>
  $(document).ready(function () {
    $('.file-upload-form').on('submit', function (e) {
      // Get the file input element specific to this form
      const fileInput = $(this).find('.fileInput')[0];
      const file = fileInput?.files[0];

      // Check if a file is selected
      if (!file) {
        alert('Please select a file to upload.');
        e.preventDefault(); // Prevent form submission
        return;
      }

      // Allowed MIME types
      const validTypes = [
        'image/jpeg', 
        'image/png', 
        'image/gif', 
        'application/pdf', 
        'application/msword', 
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
        'text/plain', 
        'application/vnd.ms-excel', // .xls
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
        'application/zip', // .zip files
        'application/x-zip-compressed', // Additional ZIP type
        'application/x-rar-compressed' // .rar files
      ];

        // Get the file extension
        const fileName = file.name;
        const fileExtension = fileName.split('.').pop().toLowerCase();

        // Allowed file extensions
        const validExtensions = ['jpeg', 'jpg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'xls', 'xlsx', 'zip', 'rar'];

        // Check if the file type or extension is valid
        if (!validTypes.includes(file.type) && !validExtensions.includes(fileExtension)) {
        alert('Invalid file type. Please upload an allowed file type (Image, Document, Excel, Zip, or RAR).');
        e.preventDefault(); // Prevent form submission
        return;
        }

      // Show the loader
      $('.loader').removeClass('hidden');
    });
  });
</script>
