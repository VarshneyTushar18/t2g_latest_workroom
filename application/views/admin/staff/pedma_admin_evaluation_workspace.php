<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$pedmaCommentSections = isset($pedma_comment_sections) && is_array($pedma_comment_sections)
    ? $pedma_comment_sections
    : pedma_comment_sections();
$pedmaCanManagerComment = in_array('manager', $pedmaCommentSections, true);
$pedmaCanHrComment = in_array('hr', $pedmaCommentSections, true);
$pedmaCanHodComment = in_array('hod', $pedmaCommentSections, true);
$pedmaCanOverallFeedback = isset($pedma_can_overall_feedback)
    ? (bool) $pedma_can_overall_feedback
    : pedma_can_edit_overall_feedback();
?>
<div id="pedmaEvalPlaceholder" class="pedma-filters-hint">
    Select month, department, and employee to load KRAs and the evaluation table.
</div>

<div id="pedmaEvalWorkspace" style="display:none;">
    <?php echo form_open('admin/staff/staff_performance', ['id' => 'pedma-form']); ?>
    <input type="hidden" name="staffid" id="pedma_hidden_staffid" value="">
    <input type="hidden" name="performance_month" id="pedma_hidden_performance_month" value="">
    <input type="hidden" name="kraOption" id="pedma_kra_option" value="default">
    <input type="hidden" name="avg_score1" id="avg_score1" value="">
    <input type="hidden" name="avg_score2" id="avg_score2" value="">
    <input type="hidden" name="status" id="pedma_submit_status" value="0">

    <div class="row mbot15">
        <div class="col-md-6">
            <table class="table table-bordered pedma-employee-meta-table">
                <tbody>
                    <tr>
                        <td class="left-header">Employee Name:</td>
                        <td class="text-right" id="pedma-full-name"></td>
                    </tr>
                    <tr>
                        <td class="left-header">Date:</td>
                        <td class="text-right"><?php echo date('Y-m-d'); ?></td>
                    </tr>
                    <tr>
                        <td class="left-header">Designation:</td>
                        <td class="text-right" id="pedma-designation"></td>
                    </tr>
                    <tr>
                        <td class="left-header">Department:</td>
                        <td class="text-right" id="pedma-department-name"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <div class="bg-primary text-white px-4 py-2 text-end rounded" style="padding:10px;border-radius:10px;">
                <span>OVERALL PERFORMANCE SCORE</span>
                <h4 id="avg_score_display1" style="margin:0;"></h4>
            </div>
        </div>
    </div>

    <div class="pedma-kra-layout">
        <div class="pedma-kra-col">
            <div class="pedma-kra-sidebar">
                <p class="pedma-kra-sidebar-title">Key result areas</p>
                <p class="text-muted" style="font-size:12px;margin:0 0 10px;">
                    Tick KRAs to add them to the evaluation table.
                </p>
                <div id="pedmaKraSidebar" class="pedma-kra-accordion">
                    <div class="pedma-kra-panel" id="pedmaDefaultKraPanel" data-scope="default">
                        <button type="button" class="pedma-kra-panel-head" aria-expanded="false" aria-controls="pedmaDefaultKraPanelBody">
                            <span class="pedma-kra-chevron" aria-hidden="true">▸</span>
                            <span class="pedma-kra-panel-title">Default KRAs</span>
                        </button>
                        <div class="pedma-kra-panel-body" id="pedmaDefaultKraPanelBody" hidden>
                            <label class="pedma-kra-all-row">
                                <input type="checkbox" id="pedmaDefaultKraAll" class="pedma-kra-all" data-scope="default">
                                <span>Select all</span>
                            </label>
                            <div id="pedmaDefaultKraList" class="pedma-kra-check-list"></div>
                        </div>
                    </div>
                    <div class="pedma-kra-panel" id="pedmaCustomKraPanel" data-scope="custom">
                        <button type="button" class="pedma-kra-panel-head" aria-expanded="false" aria-controls="pedmaCustomKraPanelBody">
                            <span class="pedma-kra-chevron" aria-hidden="true">▸</span>
                            <span class="pedma-kra-panel-title">Custom KRAs</span>
                            <span class="pedma-kra-panel-meta">Dept only</span>
                        </button>
                        <div class="pedma-kra-panel-body" id="pedmaCustomKraPanelBody" hidden>
                            <label class="pedma-kra-all-row">
                                <input type="checkbox" id="pedmaCustomKraAll" class="pedma-kra-all" data-scope="custom">
                                <span>Select all</span>
                            </label>
                            <div id="pedmaCustomKraList" class="pedma-kra-check-list"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="pedma-kra-main pedma-kra-table-wrap">
            <table class="table performance-table table-bordered" id="pedmaEvalTable">
                <thead>
                    <tr>
                        <th style="width:28%;">KRA name</th>
                        <th style="width:14%;">Rating (1–5)</th>
                        <?php if ($pedmaCanManagerComment) { ?>
                        <th style="width:22%;">Manager comment</th>
                        <?php } ?>
                        <?php if ($pedmaCanHodComment) { ?>
                        <th style="width:22%;">HOD comment</th>
                        <?php } ?>
                        <?php if ($pedmaCanHrComment) { ?>
                        <th style="width:22%;">HR comment</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody id="default-kra-table-body">
                    <tr class="pedma-kra-empty-row">
                        <td colspan="<?php echo 2 + ($pedmaCanManagerComment ? 1 : 0) + ($pedmaCanHodComment ? 1 : 0) + ($pedmaCanHrComment ? 1 : 0); ?>" class="text-muted text-center" style="padding:18px;">
                            Select one or more KRAs on the left.
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="form-group mtop15" id="pedmaOverallFeedbackGroup">
                <label for="pedma_overall_feedback" class="control-label">Overall feedback</label>
                <?php if ($pedmaCanOverallFeedback) { ?>
                <textarea name="overall_feedback" id="pedma_overall_feedback" class="form-control" rows="4" placeholder="Manager overall feedback…"></textarea>
                <?php } else { ?>
                <input type="hidden" name="overall_feedback" id="pedma_overall_feedback_hidden" value="">
                <div id="pedma_overall_feedback_readonly" class="form-control pedma-readonly-feedback" style="min-height:88px;background:#f8fafc;white-space:pre-wrap;"></div>
                <p class="text-muted" style="font-size:12px;margin-top:6px;">Only managers can edit overall feedback.</p>
                <?php } ?>
            </div>

            <div class="form-group" id="pedmaStaffResponseArea" style="display:none;">
                <label class="control-label">Staff response</label>
                <textarea class="form-control" rows="3" readonly id="pedmaStaffResp"></textarea>
            </div>

            <button type="button" class="btn btn-primary" id="pedmaPublishBtn">Publish</button>
            <button type="button" class="btn btn-warning" id="pedmaDraftBtn">Save draft</button>
            <button type="button" class="btn btn-primary" id="pedmaUpdateBtn" style="display:none;">Update</button>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<script>
window.pedmaCommentSections = <?php echo json_encode(array_values($pedmaCommentSections)); ?>;
window.pedmaCanManagerComment = <?php echo $pedmaCanManagerComment ? 'true' : 'false'; ?>;
window.pedmaCanHrComment = <?php echo $pedmaCanHrComment ? 'true' : 'false'; ?>;
window.pedmaCanHodComment = <?php echo $pedmaCanHodComment ? 'true' : 'false'; ?>;
window.pedmaEvalTableColspan = <?php echo (int) (2 + ($pedmaCanManagerComment ? 1 : 0) + ($pedmaCanHodComment ? 1 : 0) + ($pedmaCanHrComment ? 1 : 0)); ?>;
window.pedmaCanOverallFeedback = <?php echo $pedmaCanOverallFeedback ? 'true' : 'false'; ?>;
</script>
