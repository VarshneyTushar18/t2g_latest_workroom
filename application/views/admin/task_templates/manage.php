<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
#task-template-items-wrapper .template-item-row { border:1px solid #e5e7eb; border-radius:8px; padding:15px; margin-bottom:12px; background:#fafafa; }
#task-template-items-wrapper .template-item-row .item-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-mb-2 sm:tw-mb-4">
                    <a href="#" class="btn btn-primary open-task-template-new">
                        <i class="fa-regular fa-plus tw-mr-1"></i> <?php echo _l('new_task_template'); ?>
                    </a>
                </div>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php render_datatable([_l('id'), _l('task_template_name'), _l('task_template_tasks_count'), _l('options')], 'task-templates'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="task_template_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">
                    <span class="edit-title"><?php echo _l('task_template_edit_title'); ?></span>
                    <span class="add-title"><?php echo _l('task_template_add_title'); ?></span>
                </h4>
            </div>
            <?php echo form_open('admin/task_templates/manage', ['id' => 'task_template_form']); ?>
            <?php echo form_hidden('templateid'); ?>
            <div class="modal-body">
                <?php echo render_input('name', 'task_template_name'); ?>
                <hr />
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-3">
                    <h5 class="tw-font-semibold tw-m-0"><?php echo _l('task_template_tasks_in_template'); ?></h5>
                    <button type="button" class="btn btn-default btn-sm" id="add-template-item"><i class="fa fa-plus"></i> <?php echo _l('task_template_add_task'); ?></button>
                </div>
                <div id="task-template-items-wrapper"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="button" class="btn btn-default" id="task-template-save-add-more"><?php echo _l('save_and_add_more'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script type="text/template" id="task-template-item-template">
<div class="template-item-row" data-index="{index}">
    <div class="item-header">
        <strong><?php echo _l('task'); ?> #{number}</strong>
        <button type="button" class="btn btn-danger btn-xs remove-template-item"><i class="fa fa-times"></i></button>
    </div>
    <div class="row">
        <div class="col-md-12"><div class="form-group"><label class="control-label"><span class="text-danger">*</span> <?php echo _l('task_add_edit_subject'); ?></label><input type="text" class="form-control" name="items[{index}][name]" required></div></div>
        <div class="col-md-6"><div class="form-group"><label class="control-label"><?php echo _l('Quantity'); ?></label><input type="number" class="form-control" name="items[{index}][qty]"></div></div>
        <div class="col-md-6"><div class="form-group"><label class="control-label"><span class="text-danger">*</span> <?php echo _l('Task hours(AHT)'); ?></label><input type="number" step="0.01" min="0" class="form-control" name="items[{index}][hourly_rate]" required></div></div>
        <div class="col-md-6"><div class="form-group"><label class="control-label"><?php echo _l('task_add_edit_priority'); ?></label><select name="items[{index}][priority]" class="selectpicker template-item-select" data-width="100%"><option value=""></option><?php foreach (get_tasks_priorities() as $priority) { ?><option value="<?php echo $priority['id']; ?>"><?php echo $priority['name']; ?></option><?php } ?></select></div></div>
        <div class="col-md-6"><div class="form-group"><label class="control-label"><span class="text-danger">*</span> <?php echo _l('department'); ?></label><select name="items[{index}][task_deptid]" class="selectpicker template-item-select" data-width="100%" required><option value=""></option><?php foreach ($departments as $department) { ?><option value="<?php echo $department['departmentid']; ?>"><?php echo $department['name']; ?></option><?php } ?></select><p class="text-muted tw-mb-0"><?php echo _l('task_template_department_help'); ?></p></div></div>
        <div class="col-md-12"><div class="form-group"><label class="control-label"><?php echo _l('task_add_edit_description'); ?></label><textarea class="form-control" rows="2" name="items[{index}][description]"></textarea></div></div>
    </div>
</div>
</script>

<?php init_tail(); ?>
<script>
var taskTemplateItemIndex = 0;
function addTemplateItemRow(item){
    item = item || {};
    var html = $('#task-template-item-template').html()
        .replace(/\{index\}/g, taskTemplateItemIndex)
        .replace(/\{number\}/g, ($('#task-template-items-wrapper .template-item-row').length + 1));

    var $row = $(html);
    $('#task-template-items-wrapper').append($row);

    // Use explicit null/undefined checks so 0 values are preserved in edit mode.
    if (typeof item.name !== 'undefined' && item.name !== null) {
        $row.find('input[name="items[' + taskTemplateItemIndex + '][name]"]').val(item.name);
    }
    if (typeof item.qty !== 'undefined' && item.qty !== null) {
        $row.find('input[name="items[' + taskTemplateItemIndex + '][qty]"]').val(item.qty);
    }
    if (typeof item.hourly_rate !== 'undefined' && item.hourly_rate !== null) {
        $row.find('input[name="items[' + taskTemplateItemIndex + '][hourly_rate]"]').val(item.hourly_rate);
    }
    if (typeof item.description !== 'undefined' && item.description !== null) {
        $row.find('textarea[name="items[' + taskTemplateItemIndex + '][description]"]').val(item.description);
    }

    init_selectpicker($row.find('.template-item-select'));

    if (typeof item.priority !== 'undefined' && item.priority !== null && item.priority !== '') {
        $row.find('select[name="items[' + taskTemplateItemIndex + '][priority]"]').selectpicker('val', item.priority);
    }
    if (typeof item.task_deptid !== 'undefined' && item.task_deptid !== null && item.task_deptid !== '') {
        $row.find('select[name="items[' + taskTemplateItemIndex + '][task_deptid]"]').selectpicker('val', item.task_deptid);
    }

    taskTemplateItemIndex++;
    renumberTemplateItems();
}
function renumberTemplateItems(){ $('#task-template-items-wrapper .template-item-row').each(function(i){ $(this).find('.item-header strong').text('<?php echo _l('task'); ?> #'+(i+1)); }); }
function resetTemplateItems(){ taskTemplateItemIndex = 0; $('#task-template-items-wrapper').html(''); }
function validateTemplateItems(){ if($('#task-template-items-wrapper .template-item-row').length===0){ alert_float('warning','<?php echo _l('task_template_add_at_least_one_task'); ?>'); return false; } var missingDept=false,missingRate=false; $('#task-template-items-wrapper .template-item-row').each(function(){ if(!$(this).find('select[name*="[task_deptid]"]').val()){missingDept=true;} if(!$.trim($(this).find('input[name*="[hourly_rate]"]').val())){missingRate=true;} }); if(missingRate){alert_float('warning','<?php echo _l('task_hourly_rate_required'); ?>');return false;} if(missingDept){alert_float('warning','<?php echo _l('task_template_department_help'); ?>');return false;} return true;}
function submit_task_template(form, addMore){ if(!validateTemplateItems()){return false;} var data=$(form).serialize(); $.post(form.action,data).done(function(r){r=JSON.parse(r); if(r.success){$('.table-task-templates').DataTable().ajax.reload(); alert_float('success',r.message); if(r.id){$('input[name="templateid"]').val(r.id); $('#task_template_modal .add-title').addClass('hide'); $('#task_template_modal .edit-title').removeClass('hide');} if(addMore){addTemplateItemRow();} else {$('#task_template_modal').modal('hide');}} else if(r.message){alert_float('warning',r.message);} }); return false; }
function openTaskTemplateModal(id){
    var form = $('#task_template_form');
    form[0].reset();
    form.find('input[name="templateid"]').val('');
    resetTemplateItems();
    $('#task_template_modal .add-title').removeClass('hide');
    $('#task_template_modal .edit-title').addClass('hide');

    if (id) {
        $('#task_template_modal .add-title').addClass('hide');
        $('#task_template_modal .edit-title').removeClass('hide');
        form.find('input[name="templateid"]').val(id);
        $.get(admin_url+'task_templates/get/'+id, function(response){
            if(response.success){
                form.find('input[name="name"]').val(response.template.name);
                if(response.items && response.items.length){
                    $.each(response.items, function(i,item){ addTemplateItemRow(item); });
                } else {
                    addTemplateItemRow();
                }
            }
        }, 'json');
    } else {
        addTemplateItemRow();
    }

    $('#task_template_modal').modal('show');
}

$(function(){
    initDataTable('.table-task-templates', admin_url + 'task_templates', [3], [3], undefined, [1,'asc']);
    appValidateForm($('#task_template_form'), {name:'required'}, manage_task_template);

    $('body').on('click', '.open-task-template-new', function(e){
        e.preventDefault();
        openTaskTemplateModal(null);
    });

    $('body').on('click', '.open-task-template-edit', function(e){
        e.preventDefault();
        openTaskTemplateModal($(this).data('id'));
    });

    $('#add-template-item').on('click', function(){ addTemplateItemRow(); });
    $('#task-template-save-add-more').on('click', function(){ submit_task_template($('#task_template_form')[0], true); });
    $('body').on('click', '.remove-template-item', function(){ $(this).closest('.template-item-row').remove(); renumberTemplateItems(); });
});
function manage_task_template(form){ return submit_task_template(form, false); }
</script>
</body>
</html>

