<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

<div id="wrapper">
    <div class="content col-md-12">
        <div class="panel-body">
            <?php
            if(is_in_managers_list() || get_staff_emp_id(get_staff_user_id()) == 1033 || get_staff_emp_id(get_staff_user_id()) == 1611 || get_staff_emp_id(get_staff_user_id()) == 2047){
            ?>
            <div class="tw-flex tw-justify-between tw-items-center">
                <button class="btn btn-primary" id="manageDocumentBtn"><i class="fa-solid fa-upload"></i> Upload Document</button>
            </div>
            <?php } ?>
            <?php
            if(is_admin()){
            ?>
            <div class="tw-flex tw-justify-between tw-items-center">
                <a href="/admin/document_management/category" class="btn btn-primary">View Category</a>
            </div>
            <?php } ?>
            <div>
                <div class="table-responsive">
                    <table class="table" id="documentUploadTable">
                        <thead>
                            <tr>
                                <th>Emp ID</th>
                                <th>Emp Name</th>
                                <th>Project</th>
                                <th>Subject</th>
                                <th>Department</th>
                                <th>Category</th>
                                <th>Upload Date</th>
                                <th>Status</th>
                                <th>View</th>
                            </tr>
                        </thead>
                        <tbody>
                           <?php
                           foreach($documents as $document){
                            $date = json_decode($document->created_at, true);
                            $status = json_decode($document->status, true);
                            ?>
                            <tr>
                                <td><?= get_staff_emp_id($document->emp_id); ?></td>
                                <td><?= get_staff_full_name($document->emp_id); ?></td>
                                <td><?= substr($document->project, 0, 15) . '...'; ?></td>
                                <td><?= substr($document->subject, 0, 15) . '...'; ?></td>
                                <td><?= get_department_name_by_departmentid($document->departments); ?></td>
                                <td><?= get_doccategory_name_by_categoryid($document->category) ?></td>
                                <td><?= $date[0] ?></td>
                                <td>
                                    <?php
                                    if(end($status) == 1){
                                        echo "<span class='label label-success  mr-1 mb-1 mt-1'>Approved</span>";
                                    }else if(end($status) == 2){
                                        echo "<span class='label label-danger mr-1 mb-1'>Rejected</span>";
                                    }else{
                                        echo "<span class='label label-info mr-1 mb-1'>Under Review</span>";
                                    } 
                                    ?>
                                </td>
                                <td><a href="/admin/document_management/view/<?= $document->id ?>" class="btn btn-warning">View</a></td>
                            </tr>
                            <?php
                           }
                           ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .dropdown-menu {
        padding: 10px !important;
    }
    .dropdown-item-input {
        display: flex !important;
        align-items: center !important;
    }
    .dropdown-item-input input {
        flex-grow: 1 !important;
        margin-right: 5px !important;
    }
    #dt-length-0 {
        margin-right: 10px;
    }
    #categoryList{
        display: flex;
        flex-wrap: nowrap; 
        gap: 3px;
        margin-top: 5px; 
        max-height: 250px;
        overflow-y: auto;
        flex-direction: column;
    }
    #categoryList .dropdown-item{
        flex: 1 1 calc(50% - 10px); 
        box-sizing: border-box; 
        padding: 4px;
        background-color: #f0f0f0;
        text-align: center;
        border: 1px solid #ccc;
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

<!-- The Modal -->
<div class="modal" id="manageDocumentModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Manage Document</h4>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <?php echo form_open('admin/document_management/upload_project_document', array('id' => 'doc-upload-form', 'enctype' => 'multipart/form-data')); ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?php echo render_select('departments', $departments, array('departmentid', 'name'), 'department'); ?>
                            </div>        
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label><span class="text-danger">*</span> Category</label>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <a href="/admin/document_management/category" target="_blank"><u>View</u></a>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle form-control text-left" type="button" id="categoryDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Select Category
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="categoryDropdown">
                                        <div class="dropdown-item-input">
                                            <input type="text" id="newCategoryName" class="form-control" placeholder="Add Category" maxlength="100">
                                            <button class="btn btn-primary" type="button" onclick="addCategory()">+ Add</button>
                                        </div>
                                        <div class="dropdown-divider"></div>
                                        <div id="categoryList">
                                            
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="category" id="selectedCategory">

                            </div>        
                        </div>
                    </div>
                    <div class="form-group">
                        <label><span class="text-danger">*</span> Project</label>
                        <input type="text" class="form-control" name="project" required>
                    </div>
                    <div class="form-group">
                        <label><span class="text-danger">*</span> Subject</label>
                        <input type="text" class="form-control" name="subject" required maxlength="150">
                    </div>
                    <div class="form-group">
                        <label><span class="text-danger">*</span> Document Description</label>
                        <textarea class="form-control" name="description[]" rows="5" required maxlength="500"></textarea>
                    </div>
                    <div class="form-group">
                        <label><span class="text-danger">*</span> Attached file</label>
                        <input type="file" class="form-control" id="fileInput" name="attachFile" required>
                    </div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="closeManageDocumentBtn">Close</button>
                <button type="submit" class="btn btn-primary" id="applyFatalErrorBtn">Save</button>
            </div>
            </form>
        </div>
    </div>
</div>

<div class="loader hidden">
    <img src="https://www.icegif.com/wp-content/uploads/2023/07/icegif-1263.gif" alt="Loading...">
</div>

<?php init_tail(); ?>

<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>

<script>
    $("#documentUploadTable").dataTable();

    $("#departments").prop("required", true);

    $("#manageDocumentBtn").click(function (e) { 
        e.preventDefault();
        $("#manageDocumentModal").modal('show');
    });

    $("#closeManageDocumentBtn").click(function (e) { 
        e.preventDefault();
        $("#manageDocumentModal").modal('hide');
    });

    $('#departments').on('change', function() {
        $('.loader').removeClass('hidden');

        let department = $(this).val();
        $('#categoryList').html('');

        $.ajax({
            url: "<?php echo base_url('admin/document_management/get_doc_cat_department_json'); ?>",
            type: 'POST',
            data: {
                department
            },
            dataType: 'json',
            success: function(response) {
                let cat = ''; 

                // Populate staff dropdown
                response.forEach(element => {
                    cat += `<a class="dropdown-item" href="#" onclick="selectCategory('${element.id}','${element.name}')">${element.name}</a>`;
                });

                $('#categoryList').html(cat);
            },
            error: function() {
                alert('Failed to fetch category. Please try again.');
            },
            complete: function() {
                $('.loader').addClass('hidden'); // Hide loader after request completes
            }
        });
    });
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  $(document).ready(function () {
    $('#doc-upload-form').on('submit', function (e) {
        const fileInput = $('#fileInput')[0];
        const file = fileInput.files[0];

        if (file) {
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
        }

        $('.loader').removeClass('hidden');
    });

  });
</script>

<script>
  function addCategory() {
    const newCategoryName = $('#newCategoryName').val().trim();
    const departmentId = $('#departments').val();

    if (!departmentId) {
      alert("Please select a department.");
      return;
    }

    if (!newCategoryName) {
      alert("Please enter a category name.");
      return;
    }

    $.ajax({
        type: "post",
        url: "<?php echo base_url('admin/document_management/add_doc_category'); ?>",
        data: {
            departmentId,
            newCategoryName
        },
        dataType: "json",
        success: function (response) {
            if(response.status == 200){
                const $categoryList = $('#categoryList');
                const $newCategoryItem = $('<a></a>', {
                class: 'dropdown-item',
                href: '#',
                text: newCategoryName,
                click: function () {
                    selectCategory(response.id ,newCategoryName);
                }
                });

                $categoryList.append($newCategoryItem);
                selectCategory(response.id,newCategoryName);
                $('#newCategoryName').val('');
            }else{
                alert("Something went wrong while adding new category.");
            }
        }
    });

  }

  function selectCategory(id, name) {
    $('#categoryDropdown').text(name);
    $('#selectedCategory').val(id);
  }
</script>


