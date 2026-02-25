<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

<div id="wrapper">
    <div class="content col-md-12">
        <div class="panel-body">
            
            <div class="tw-flex tw-justify-between tw-items-center">
                <a href="/admin/document_management" class="btn btn-primary">Back</a>
            </div>
      
            <div>
                <div class="table-responsive">
                    <table class="table" id="catTable">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Department</th>
                                <th>Name</th>
                                <th>Created By</th>
                                <th>Created At</th>
                                <th>Edit</th>
                            </tr>
                        </thead>
                        <tbody>
                           <?php
                           $a = 1;
                           foreach($category as $cat){
                            ?>
                            <tr>
                                <td><?= $a ?></td>
                                <td><?= get_department_name_by_departmentid($cat->departmentid); ?></td>
                                <td><?= $cat->name ?></td>
                                <td><?= get_staff_full_name($cat->staffid); ?></td>
                                <td><?= date("Y-m-d",strtotime($cat->created_at)) ?></td>
                                <td><button class="btn btn-warning catEditBtn" data-id="<?= $cat->id ?>" data-name="<?= $cat->name ?>">Edit</button></td>
                            </tr>
                            <?php
                            $a++;
                           }
                           ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="catEditModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Edit Name</h4>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <?php echo form_open('admin/document_management/updateCategory', array('id' => 'doc-update-cat-form')); ?>
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" id="catName" required value="" maxlength="100" class="form-control">
                        <input type="hidden" name="id" id="catId" value="">
                    </div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="closeCatEditModalBtn">Close</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
            </form>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $("#catTable").dataTable();

    $(".catEditBtn").click(function (e) { 
        e.preventDefault();
        
        var id = $(this).data('id');
        var name = $(this).data('name');

        $("#catName").val(name);
        $("#catId").val(id);

        $("#catEditModal").modal('show');
    });

    $("#closeCatEditModalBtn").click(function (e) { 
        e.preventDefault();
        $("#catName").val('');
        $("#catId").val('');
        $("#catEditModal").modal('hide');
    });
</script>


