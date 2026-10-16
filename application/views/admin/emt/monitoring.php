<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .snapshot-filters {
        margin-bottom: 16px;
    }
    .snapshot-filters label {
        display: block;
        margin-bottom: 6px;
    }
    .snapshot-filters .bootstrap-select,
    .snapshot-filters .bootstrap-select.bs3 {
        float: none !important;
        width: 100% !important;
        padding: 0 !important;
    }
    .snapshot-filters .form-control {
        height: 36px;
    }
    .snapshot-empty {
        display: block;
        width: 100%;
        padding: 28px 12px;
        color: #6b7280;
        text-align: center;
    }
    #display_img img {
        width: 250px !important;
        margin: 10px 15px;
        border: 1px double;
    }
    #select_file_child {
        padding: 6px 46px;
        border-radius: 5px;
        border-color: #e1e6ee;
    }
    .date_class {
        text-align: center;
        background: gray;
        margin: 0 20px;
        color: white;
        border-radius: 5px;
    }
    #display_img li {
        display: inline-grid;
    }
    #snapshotPreviewModal img {
        max-width: 100%;
        height: auto;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <a href="<?php echo html_escape($dashboard_url ?? admin_url('Emt/getEmployeeId')); ?>" class="btn btn-default tw-mb-3">&larr; Snapshot dashboard</a>
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">Monitoring</h4>
                <p class="text-muted">Browse employee snapshot screenshots from Google Drive.</p>

                <div class="panel_s">
                    <div class="panel-body">
                        <?php if (!empty($drive_error)) { ?>
                        <div class="alert alert-warning"><?php echo htmlspecialchars($drive_error); ?>
                            <?php if (strpos($drive_error, 'not configured') === false) { ?>
                            <br><small>Share each employee snapshot folder with <strong>t2gworkroom@t2g-monitoring.iam.gserviceaccount.com</strong> (Viewer or higher).</small>
                            <?php } ?>
                        </div>
                        <?php } elseif (!empty($drive_hint)) { ?>
                        <div class="alert alert-info"><?php echo htmlspecialchars($drive_hint); ?></div>
                        <?php } ?>

                        <?php
                        $driveFiles = (isset($result) && $result && method_exists($result, 'getFiles')) ? $result->getFiles() : [];
                        $folderDepartments = isset($folder_departments) && is_array($folder_departments) ? $folder_departments : [];
                        $departments = isset($departments) && is_array($departments) ? $departments : [];
                        $staffFolderMap = isset($staff_folder_map) && is_array($staff_folder_map) ? $staff_folder_map : [];
                        ?>

                        <div class="row snapshot-filters">
                            <div class="col-md-3">
                                <label for="select_department">Department</label>
                                <select class="selectpicker" data-live-search="true" data-width="100%" id="select_department" aria-label="Department">
                                    <option value="">All departments</option>
                                    <?php foreach ($departments as $department) {
                                        $deptId = is_array($department) ? $department['departmentid'] : $department->departmentid;
                                        $deptName = is_array($department) ? $department['name'] : $department->name;
                                    ?>
                                    <option value="<?php echo (int) $deptId; ?>"><?php echo html_escape($deptName); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="select_month">Month</label>
                                <select class="selectpicker" data-live-search="true" data-width="100%" id="select_month" aria-label="Month">
                                    <option value="">All months</option>
                                    <?php
                                    $currentMonth = date('Y-m');
                                    for ($i = 0; $i < 18; $i++) {
                                        $ts = strtotime('first day of -' . $i . ' month');
                                        $value = date('Y-m', $ts);
                                        $selected = ($value === $currentMonth) ? ' selected' : '';
                                        echo '<option value="' . $value . '"' . $selected . '>' . date('F Y', $ts) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="select_file">Employee folder</label>
                                <select class="selectpicker" data-live-search="true" data-width="100%" id="select_file" aria-label="Employee folder">
                                    <option value="">Select employee</option>
                                    <?php foreach ($driveFiles as $file) {
                                        $key = strtolower(trim($file->getName()));
                                        $deptIds = isset($folderDepartments[$key]) ? $folderDepartments[$key] : [];
                                    ?>
                                    <option value="<?php echo html_escape($file->getId()); ?>" data-departments="<?php echo html_escape(implode(',', $deptIds)); ?>"><?php echo html_escape($file->getName()); ?></option>
                                    <?php }
                                    if (empty($driveFiles) && empty($drive_error)) { ?>
                                    <option disabled>No employee folders found</option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="select_file_child">Snapshot day</label>
                                <select class="selectpicker" data-live-search="true" data-width="100%" id="select_file_child" aria-label="Snapshot day">
                                    <option value="">Select day</option>
                                </select>
                            </div>
                        </div>

                        <div class="panel_s">
                            <div class="panel-body">
                                <ul class="list-inline" id="display_img">
                                    <li class="snapshot-empty">Select an employee and a day to view snapshots.</li>
                                </ul>
                                <div class="pagination" id="pagination"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="snapshotPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="snapshotPreviewModalTitle">Snapshot</h4>
            </div>
            <div class="modal-body text-center">
                <img id="snapshotPreviewImage" src="" alt="Snapshot preview">
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" title="Close">
                    <span class="glyphicon glyphicon-remove"></span></button>
            </div>
            <div class="modal-body">
                <div id="myGallery" class="carousel slide" data-interval="false">
                    <div class="carousel-inner" id="img_model"></div>
                    <a class="left carousel-control" href="#myGallery" role="button" data-slide="prev">
                        <span class="glyphicon glyphicon-chevron-left"></span></a>
                    <a class="right carousel-control" href="#myGallery" role="button" data-slide="next">
                        <span class="glyphicon glyphicon-chevron-right"></span></a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
function snapshotTimeLabel(fileName) {
    if (!fileName) {
        return '';
    }
    var parts = fileName.split('_');
    if (parts.length < 3) {
        return fileName;
    }
    var timedata = (parts[2] || '').split('.')[0];
    if (!timedata || timedata.length % 2 !== 0) {
        return fileName;
    }
    return timedata.match(/.{1,2}/g).join(':');
}

function snapshotImageUrl(fileId) {
    return '<?php echo admin_url('Emt/snapshotImage?id='); ?>' + encodeURIComponent(fileId);
}

function parseSnapshotJson(res) {
    if (res === null || res === undefined || res === '') {
        return [];
    }
    if (typeof res === 'object') {
        return Array.isArray(res) ? res : [];
    }
    try {
        var parsed = JSON.parse(res);
        return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
        return [];
    }
}

var snapshotDaysCache = [];
var snapshotEmployees = [];

function refreshSelectpicker($el) {
    if ($el.data('selectpicker')) {
        $el.selectpicker('refresh');
    } else {
        $el.selectpicker({ liveSearch: true, width: '100%' });
    }
}

function refreshSnapshotDaySelect(html) {
    var $el = $('#select_file_child');
    $el.html(html);
    refreshSelectpicker($el);
}

function snapshotDayInMonth(day, month) {
    if (!month) {
        return true;
    }
    var name = (day.name || '').toString();
    var key = (day.date_key || '').toString();
    var compact = month.replace('-', '');
    return name.indexOf(month) === 0 || (compact !== '' && key.indexOf(compact) === 0);
}

function renderSnapshotDays() {
    var month = ($('#select_month').val() || '').toString();
    var options = '<option value="">Select day</option>';
    var shown = 0;
    for (var i = 0; i < snapshotDaysCache.length; i++) {
        if (!snapshotDayInMonth(snapshotDaysCache[i], month)) {
            continue;
        }
        shown++;
        var dateKey = (snapshotDaysCache[i].date_key || '').toString().replace(/"/g, '&quot;');
        var name = $('<div>').text(snapshotDaysCache[i].name || '').html();
        options += '<option value="' + snapshotDaysCache[i].id + '" data-date-key="' + dateKey + '">' + name + '</option>';
    }
    if (!shown) {
        options += '<option disabled>' + (month ? 'No days in this month' : 'No days found') + '</option>';
    }
    refreshSnapshotDaySelect(options);
}

function resetSnapshotGallery() {
    $('#display_img').html('<li class="snapshot-empty">Select an employee and a day to view snapshots.</li>');
    $('#img_model').empty();
}

function applyDepartmentFilter() {
    var dept = ($('#select_department').val() || '').toString();
    var $sel = $('#select_file');
    var current = ($sel.val() || '').toString();
    var stillVisible = !current;
    var options = '<option value="">Select employee</option>';
    var shown = 0;
    for (var i = 0; i < snapshotEmployees.length; i++) {
        var emp = snapshotEmployees[i];
        if (dept && emp.departments.indexOf(dept) === -1) {
            continue;
        }
        shown++;
        if (emp.id === current) {
            stillVisible = true;
        }
        options += '<option value="' + emp.id + '">' + $('<div>').text(emp.name).html() + '</option>';
    }
    if (!shown) {
        options += '<option disabled>No employees in this department</option>';
    }
    $sel.html(options);
    if (stillVisible && current) {
        $sel.val(current);
    } else if (current) {
        snapshotDaysCache = [];
        renderSnapshotDays();
        resetSnapshotGallery();
    }
    refreshSelectpicker($sel);
}

$("#select_department").on('change', function () {
    applyDepartmentFilter();
});

$("#select_month").on('change', function () {
    $('#display_img').html('<li class="snapshot-empty">Select a day to view snapshots.</li>');
    $('#img_model').empty();
    if (($('#select_file').val() || '').toString()) {
        renderSnapshotDays();
    }
});

$("#select_file").on('change', function (event) {
    event.preventDefault();
    var folderId = ($(this).val() || '').toString();
    snapshotDaysCache = [];
    resetSnapshotGallery();
    renderSnapshotDays();

    if (!folderId) {
        return;
    }

    $.ajax({
        url: '<?php echo base_url('admin/Emt/getEmployeeDate'); ?>',
        type: 'GET',
        data: { date: folderId },
        cache: false,
        success: function (res) {
            snapshotDaysCache = parseSnapshotJson(res);
            renderSnapshotDays();
        }
    });
});

$("#select_file_child").on('change', function (event) {
    event.preventDefault();
    var dayFolderId = ($(this).val() || '').toString();
    var dayFilter = ($(this).find('option:selected').data('date-key') || '').toString();
    $("#display_img").empty();
    $("#img_model").empty();

    if (!dayFolderId) {
        if (($('#select_file').val() || '').toString()) {
            $('#display_img').html('<li class="snapshot-empty">Select a day to view snapshots.</li>');
        } else {
            resetSnapshotGallery();
        }
        return;
    }

    $.ajax({
        url: '<?php echo base_url('admin/Emt/getImg'); ?>',
        type: 'GET',
        data: { date: dayFolderId, day_filter: dayFilter },
        cache: false,
        success: function (res) {
            var files = parseSnapshotJson(res);
            if (!files.length) {
                $('#display_img').html('<li class="text-muted">No screenshots for this day.</li>');
                return;
            }

            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                var string_value = snapshotTimeLabel(file.name);
                var src = snapshotImageUrl(file.id);
                var itemClass = i === 0 ? 'item active' : 'item';
                var downloadFn = i === 0 ? 'ImageidData' : 'ImageidDataVal';

                $('#display_img').append(
                    '<li><a href="#myGallery" data-slide-to="' + i + '">' +
                    '<img class="img-thumbnail" src="' + src + '" data-toggle="modal" data-target="#myModal" style="height:200px;width:250px; margin:5px 0 5px 0;"></a>' +
                    '<span class="date_class">' + string_value + '</span></li>'
                );
                $('#img_model').append(
                    '<div class="' + itemClass + '">' +
                    '<button class="download" onClick="' + downloadFn + '(\'' + file.id + '\',\'' + string_value + '\')"><i class="fa fa-download" aria-hidden="true"></i> Download</button>' +
                    '<h4>' + string_value + '</h4>' +
                    '<img src="' + src + '" alt="item' + i + '" style="height:400px;width:600px;max-width:100%;">' +
                    '<div class="carousel-caption"></div></div>'
                );
            }
        }
    });
});

function ImageidData(id, time) {
    $.ajax({
        url: '<?php echo base_url('admin/Emt/getDownload'); ?>',
        type: 'POST',
        data: { date: id, time: time },
        xhrFields: { responseType: 'blob' },
        success: function (response) {
            var blobUrl = URL.createObjectURL(response);
            var a = document.createElement('a');
            a.href = blobUrl;
            a.download = 'modified-image.png';
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(blobUrl);
        }
    });
}

function ImageidDataVal(id, time) {
    ImageidData(id, time);
}

$(function () {
    $('#select_file option').each(function () {
        var $opt = $(this);
        var value = ($opt.val() || '').toString();
        if (!value) {
            return;
        }
        snapshotEmployees.push({
            id: value,
            name: $opt.text(),
            departments: ($opt.attr('data-departments') || '').split(',').filter(function (id) {
                return id !== '';
            })
        });
    });
    ['#select_department', '#select_month', '#select_file', '#select_file_child'].forEach(function (selector) {
        var $el = $(selector);
        if (!$el.length) {
            return;
        }
        if ($el.data('selectpicker')) {
            $el.selectpicker('destroy');
        }
        $el.selectpicker({ liveSearch: true, width: '100%' });
    });
    $('#myModal').on('shown.bs.modal', function () {
        $(this).removeAttr('aria-hidden');
    }).on('hidden.bs.modal', function () {
        $(this).attr('aria-hidden', 'true');
    });
    var urlParams = new URLSearchParams(window.location.search);
    var folderPrefill = (urlParams.get('folder') || '').toString();
    if (folderPrefill && $('#select_file option[value="' + folderPrefill.replace(/"/g, '') + '"]').length) {
        $('#select_file').val(folderPrefill);
        refreshSelectpicker($('#select_file'));
        $('#select_file').trigger('change');
    }
});
</script>
</body>
</html>
