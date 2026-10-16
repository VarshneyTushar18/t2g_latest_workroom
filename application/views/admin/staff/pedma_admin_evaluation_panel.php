<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $pedma_panel_partial = !empty($pedma_panel_partial); ?>
<div id="pedmaEvaluationContent">
<input type="hidden" id="panel_staffid" value="<?php echo html_escape($panel_staffid ?? ''); ?>">
<input type="hidden" id="panel_performance_month" value="<?php echo html_escape($panel_month ?? ''); ?>">
                <?php echo form_open('admin/staff/staff_performance', array('id' => 'pedma-form')); ?>
                <?php if ($pedma_panel_partial) { ?>
                <input type="hidden" name="staffid" id="pedma_hidden_staffid" value="<?php echo html_escape($panel_staffid ?? ''); ?>">
                <input type="hidden" name="performance_month" id="pedma_hidden_performance_month" value="<?php echo html_escape($panel_month ?? ''); ?>">
                <?php } ?>
                <div class="row">
                    <?php if (!$pedma_panel_partial) { ?>
                    <div class="col-md-6">
                        <?php echo render_select('departments', $departments, array('departmentid', 'name'), 'department'); ?>
                        <?php echo render_select('staffid', $staffs, array('staffid', array('firstname', 'lastname', 'staff_identifi')), 'Select Employee'); ?>
                        <div class="form-group" app-field-wrapper="performance_month">
                            <label class="control-label" for="performance_month">Please select a month</label>
                            <div class="dropdown bootstrap-select bs3" style="width: 100%;">
                                <input type="month" class="selectpicker form-control" id="performance_month" name="performance_month" style="width: 100%; padding:6px 12px;" required />
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="<?php echo $pedma_panel_partial ? 'col-md-6' : 'col-md-6'; ?>">
                            <div class="form-group">
                                <label class="control-label">KRA Type</label>
                                <div class="radio-options">
                                    <!-- Default Option -->
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="kraOption" id="defaultOption" value="default" checked>
                                        <label class="form-check-label" for="defaultOption">
                                            Default
                                        </label>
                                    </div>

                                    <!-- Custom Option -->
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="kraOption" id="customOption" value="custom">
                                        <label class="form-check-label" for="customOption">
                                            Custom
                                        </label>
                                    </div>

                                    <!-- Previous Month Template -->
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="kraOption" id="previousMonthOption" value="previous">
                                        <label class="form-check-label" for="previousMonthOption">
                                            Previous Month
                                        </label>
                                    </div>
                                    <input type="hidden" name="previous_kra_save_type" id="previous_kra_save_type" value="">
                                    <small id="previousMonthHint" class="text-muted" style="display:none;margin-top:4px;">
                                        Loads last month's KRA as a blank template (scores/comments cleared).
                                    </small>
                                </div>
                            </div>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered pedma-employee-meta-table">

                            <tbody>
                                <tr>
                                    <td class="left-header">Employee Name:</td>
                                    <td class="text-right" id='full-name'></td>
                                </tr>
                                <tr>
                                    <td class="left-header">Date:</td>
                                    <td class="text-right"><?php echo date("Y-m-d"); ?></td>
                                </tr>
                                <tr>
                                    <td class="left-header">Designation:</td>
                                    <td class="text-right" id='designation'></td>
                                </tr>
                                <tr>
                                    <td class="left-header">Department:</td>
                                    <td class="text-right" id='department-name'></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-md-12" id="defaultKraSelectWrapper" style="margin-left: 12px; display:none;">
                        <label for="defaultKraSelect">Select Default KRAs</label>
                        <small class="text-muted" style="display:block;margin-bottom:6px;">
                            Items stay <i class="fa fa-lock"></i> locked until you tick the checkbox to unlock.
                        </small>
                        <div id="defaultKraSelectionSummary" class="alert alert-info tw-py-2 tw-mb-2" style="padding:10px 14px;margin-bottom:10px;">
                            <strong>Selected:</strong> <span id="defaultKraSelectedCount">0</span> KRA(s)
                            &nbsp;|&nbsp;
                            <strong>Total Max Score:</strong> <span id="defaultKraTotalMaxScore">0</span> / 100
                        </div>
                        <div id="defaultKraSelect"></div>
                    </div>

                    <div class="col-md-12" id="customKraSelectWrapper" style="margin-left: 12px;">
                        <label for="customKraSelect">Select KRAs</label>
                        <div id="customKraSelect">

                        </div>
                    </div>
                </div>
                <div class="loader hidden">
                    <img src="https://www.icegif.com/wp-content/uploads/2023/07/icegif-1263.gif" alt="Loading...">
                </div>

                <!-- Default KRA Table -->
                <div id="defaultKraWrapper" style="display: block;">
                    <table class="table performance-table table-bordered" id="defaultKraTable">
                        <thead>
                            <tr>
                                <th style="width: 3%;">S. NO.</th>
                                <th style="width: 28%;">Performance Criteria</th>
                                <th style="width: 8%;">Max Score</th>
                                <th style="width: 12%;">Rating</th>
                                <th style="width: 18%;">Manager Comment</th>
                                <th style="width: 18%;">HR Comment</th>
                                <th style="width: 19%;">HOD Comment</th>
                            </tr>
                        </thead>
                        <tbody id="default-kra-table-body">


                        </tbody>
                        <tfoot id="default-kra-table-foot" style="display:none;">
                            <tr class="active" style="background:#f8fafc;font-weight:600;">
                                <td colspan="2" class="text-right">Total</td>
                                <td id="defaultKraTableTotalMax">0</td>
                                <td id="defaultKraTableTotalEntered">0</td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Custom KRA Table -->
                <div id="customKraWrapper" style="display: none;">
                    <table class="table performance-table table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2">S.No.</th>
                                <th rowspan="2" style="width:220px;">KRA</th>
                                <th colspan="3">KPI</th>
                                <th rowspan="2" style="width:180px;">Manager Comment</th>
                                <th rowspan="2" style="width:180px;">HR Remarks / Notes</th>
                            </tr>
                            <tr>
                                <th style="width:200px;">KPI</th>
                                <th>Max Score</th>
                                <th style="width:150px;">Score</th>
                            </tr>
                        </thead>
                        <tbody id="custom-kra-table-body">
                            <!-- Table rows will be dynamically inserted here -->

                        </tbody>
                    </table>
                </div>

                <div class="row justify-content-between align-items-center mb-4" style="margin-bottom:15px;" id="default_performance_score">
                    <div class="col-md-5">
                        <div class="bg-primary text-white px-4 py-2 text-end rounded" style="padding: 10px; border-radius:10px; width: fit-content;">
                            <span>OVERALL PERFORMANCE SCORE : </span>
                            <h4 id="avg_score_display1" style="margin: 0; margin-top:px"></h4>
                            <input type="hidden" name="avg_score1" id="avg_score1" />
                        </div>
                    </div>
                </div>

                <div class="row justify-content-between align-items-center mb-4" style="margin-bottom:15px;" id="custom_performance_score">
                    <div class="col-md-5">
                        <div class="bg-primary text-white px-4 py-2 text-end rounded" style="padding: 10px; border-radius:10px; width: fit-content;">
                            <span>OVERALL PERFORMANCE SCORE : </span>
                            <h4 id="avg_score_display2" style="margin: 0; margin-top:px"></h4>
                            <input type="hidden" name="avg_score2" id="avg_score2" />
                        </div>
                    </div>
                </div>

                <button class="btn btn-outline-danger" id="fatalButton"><i class="fas fa-exclamation-triangle"></i>&nbsp;&nbsp; Fatal Error</button>
                <button class="btn btn-outline-success disabledbtn" id="addOnButton" disabled><i class="fas fa-trophy"></i>&nbsp;&nbsp; Add-on</button>

                <div class="form-group" style="margin-top: 15px;">
                    <label for="editor" class="control-label">Overall Feedback</label>
                    <textarea name="overall_feedback" id="editor"></textarea>
                </div>

                <div class="form-group" id="staffResponseArea" style="display: none;">
                    <label class="control-label">Staff Response</label>
                    <textarea class="form-control" rows="4" readonly id="staffResp"></textarea>
                </div>

                <!-- <input id="submitBtn" type="submit" class="btn btn-primary" value="Submit"> -->
                <button class="btn btn-primary submitBtn nonEditButton" name="status" type="submit" value="1">Publish</button>
                <button class="btn btn-warning submitBtn nonEditButton" name="status" type="submit" value="0">Draft</button>
                <button class="btn btn-primary submitBtn editButton" name="status" type="submit" value="2" style="display: none;">Update</button>

                </form>

            </div>
        </div>
    </div>
</div>

<?php if (!$pedma_panel_partial) { ?>
<!-- The Modal -->
<div class="modal" id="fatalErrorModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Adjust Score for Fatal Error</h4>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <form id="fatalErrorForm">
                    <div class="form-group">
                        <label>Choose Percentage Adjustment:</label>
                        <div class="percentage-selection">
                            <div id="fatalError0" class="percentage-box" data-value="100">-100%</div>
                            <div id="fatalError50" class="percentage-box" data-value="50">-50%</div>
                            <div id="fatalErrorCustom" class="percentage-box" data-value="custom">Custom</div>
                        </div>
                        <!-- Custom Input Field for Custom Percentage -->
                        <div class="custom-input-field" id="customInputField">
                            <label for="customPercentage">Decrease Score by (in %):</label>
                            <input type="number" id="customPercentage" class="form-control" placeholder="Enter custom percentage" max="100" min="0" value=""/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Select Months to Affect:</label>
                        <div class="month-checkboxes lastsixmonths">

                        </div>
                    </div>
                    <div class="form-group">
                        <label>Comment:</label>
                        <textarea class="form-control" name="fatal_comment" id="fatal_comment" required rows="5" placeholder="Please write a reason of giving fatal error..."></textarea>
                    </div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="closeFatalErrorBtn">Close</button>
                <button type="submit" class="btn btn-primary" id="applyFatalErrorBtn">Save</button>
            </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="addOnModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Adjust Score for Add-on</h4>
            </div>
            <!-- Modal body -->
            <div class="modal-body">
                <form id="highAchievementForm">
                    <div class="form-group">
                        <label for="customPercentageIncrease" class="form-label">Increase Score by (in %):</label>
                        <input type="number" class="form-control" id="customPercentageIncrease" placeholder="Enter percentage" required max="100" min="0" value="">
                    </div>
                    <div class="form-group">
                        <label>Select Months to Affect:</label>
                        <div class="month-checkboxes lastsixmonths">

                        </div>
                    </div>
                    <div class="form-group">
                        <label>Comment:</label>
                        <textarea class="form-control" name="addOn_comment" id="addOn_comment" required rows="5" placeholder="Please write a reason of giving add-on..."></textarea>
                    </div>
            </div>


            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="closeHighAchievementBtn">Close</button>
                <button type="submit" class="btn btn-primary" id="applyHighAchievementBtn">Save</button>
            </div>
            </form>
        </div>
    </div>
</div>
<?php } ?>

<script>
    var pedmaPanelPartial = <?php echo $pedma_panel_partial ? 'true' : 'false'; ?>;

    $(document).ready(function() {
        <?php if (!$pedma_panel_partial) { ?>
        var today = new Date();
        var month = (today.getMonth() + 1).toString().padStart(2, '0');
        var year = today.getFullYear();
        $('#performance_month').val(`${year}-${month}`);
        <?php } ?>
    });

    function pedmaClearEditor() {
        if (typeof editor !== 'undefined' && editor && typeof editor.setData === 'function') {
            editor.setData('');
        }
    }

    const defaultOption = document.getElementById('defaultOption');
    const customOption = document.getElementById('customOption');
    const previousMonthOption = document.getElementById('previousMonthOption');
    const defaultKraWrapper = document.getElementById('defaultKraWrapper');
    const customKraWrapper = document.getElementById('customKraWrapper');
    const customKraSelectWrapper = document.getElementById('customKraSelectWrapper');
    const default_performance_score = document.getElementById('default_performance_score');
    const custom_performance_score = document.getElementById('custom_performance_score');
    const previousMonthHint = document.getElementById('previousMonthHint');
    const defaultKraSelectWrapper = document.getElementById('defaultKraSelectWrapper');

    function hasValue(v) {
        return !(v === undefined || v === null || v === '' || (Array.isArray(v) && v.length === 0));
    }

    function syncKraListLockIcon($checkbox) {
        var $item = $checkbox.closest('.kra-list-item');
        var unlocked = $checkbox.is(':checked');
        var $icon = $item.find('.kra-lock-icon');
        if (unlocked) {
            $item.removeClass('locked').addClass('unlocked');
            $icon.html('<i class="fa fa-unlock"></i>');
        } else {
            $item.removeClass('unlocked').addClass('locked');
            $icon.html('<i class="fa fa-lock"></i>');
        }
    }

    function updateDefaultKraSelectionSummary(allDefaultKra, selectedIndexes) {
        selectedIndexes = (selectedIndexes || []).map(String);
        var selectedCount = selectedIndexes.length;
        var totalMax = checkDefaultMaxScoreSum(allDefaultKra || [], selectedIndexes);
        var $summary = $('#defaultKraSelectionSummary');

        $('#defaultKraSelectedCount').text(selectedCount);
        $('#defaultKraTotalMaxScore').text(totalMax);
        $('#defaultKraTableTotalMax').text(totalMax);

        if (!$summary.length) {
            return;
        }

        $summary.removeClass('alert-info alert-success alert-danger');
        if (totalMax > 100) {
            $summary.addClass('alert-danger');
        } else if (totalMax === 100) {
            $summary.addClass('alert-success');
        } else {
            $summary.addClass('alert-info');
        }

        if (selectedCount > 0) {
            $('#default-kra-table-foot').show();
        } else {
            $('#default-kra-table-foot').hide();
            $('#defaultKraTableTotalEntered').text('0');
        }
    }

    function pedmaRowRatingValue($row) {
        var checked = $row.find('.rating1:checked').val();
        if (checked !== undefined) {
            return checked;
        }
        return $row.find('.rating1').val();
    }

    function pedmaRatingInputHtml(displayIndex, scoreVal) {
        scoreVal = (scoreVal === undefined || scoreVal === null) ? '' : String(scoreVal);
        var html = '<div class="pedma-rating-group">';
        for (var r = 1; r <= 5; r++) {
            var checked = scoreVal === String(r) ? ' checked' : '';
            html += '<label><input type="radio" class="rating1" name="kraData1[' + displayIndex + '][get_score]" value="' + r + '"' + checked + '> ' + r + '</label>';
        }
        html += '</div>';
        return html;
    }

    function updateDefaultTableEnteredTotal() {
        var enteredTotal = 0;
        $('#default-kra-table-body tr[data-original-index]').each(function() {
            var value = pedmaRowRatingValue($(this));
            if (value !== '' && value !== undefined) {
                enteredTotal += parseFloat(value) || 0;
            }
        });
        $('#defaultKraTableTotalEntered').text(enteredTotal);
    }

    function renderDefaultKraList(allDefaultKra, selectedIndexes) {
        selectedIndexes = (selectedIndexes || []).map(String);
        var html = '';
        $.each(allDefaultKra || [], function(index, option) {
            var isSelected = selectedIndexes.indexOf(String(index)) !== -1;
            var lockClass = isSelected ? 'unlocked' : 'locked';
            var lockIcon = isSelected ? 'fa-unlock' : 'fa-lock';
            var checkedAttr = isSelected ? 'checked' : '';
            html += `
                <div class="form-check kra-list-item ${lockClass}">
                    <input type="checkbox" class="form-check-input default-kra-check" id="default_kra_${index}" value="${index}" ${checkedAttr}>
                    <span class="kra-lock-icon"><i class="fa ${lockIcon}"></i></span>
                    <label class="form-check-label" for="default_kra_${index}">${option.name} (Max Score:${option.max_score})</label>
                </div>
            `;
        });
        $('#defaultKraSelect').html(html);
        updateDefaultKraSelectionSummary(allDefaultKra, selectedIndexes);
    }

    function buildDefaultTableFromSelection(allDefaultKra, selectedIndexes, scoreMap) {
        const default_tableBody = $('#default-kra-table-body');
        default_tableBody.empty();
        scoreMap = scoreMap || {};
        selectedIndexes = (selectedIndexes || []).map(Number);

        selectedIndexes.forEach(function(originalIndex, displayIndex) {
            var item = allDefaultKra[originalIndex];
            if (!item) {
                return;
            }
            var scored = scoreMap[originalIndex] || scoreMap[item.name] || {};
            var scoreVal = (scored.get_score !== undefined && scored.get_score !== null && scored.get_score !== '')
                ? scored.get_score
                : ((item.get_score !== undefined && item.get_score !== null) ? item.get_score : '');
            var commentVal = (scored.comment !== undefined)
                ? scored.comment
                : (item.comment || '');
            var hrRemarksVal = (scored.hr_remarks !== undefined)
                ? scored.hr_remarks
                : (item.hr_remarks || '');
            var hodRemarksVal = (scored.hod_remarks !== undefined)
                ? scored.hod_remarks
                : (item.hod_remarks || '');
            var safeName = String(item.name || '').replace(/"/g, '&quot;');
            var safeDesc = String(item.description || '').replace(/"/g, '&quot;');
            var rowHTML = `
                <tr data-original-index="${originalIndex}">
                    <td>${displayIndex + 1}</td>
                    <td class="performance-criteria">
                        <b>${item.name}</b>
                        <input type="hidden" name="kraData1[${displayIndex}][name]" value="${safeName}">
                        <br>${item.description || ''}
                        <input type="hidden" name="kraData1[${displayIndex}][description]" value="${safeDesc}">
                    </td>
                    <td>${item.max_score}</td>
                    <input type="hidden" name="kraData1[${displayIndex}][max_score]" value="${item.max_score}">
                    <td>${pedmaRatingInputHtml(displayIndex, scoreVal)}</td>
                    <td><textarea class="form-control comment" name="kraData1[${displayIndex}][comment]" placeholder="Manager comment...">${commentVal}</textarea></td>
                    <td><textarea class="form-control hr-remarks" name="kraData1[${displayIndex}][hr_remarks]" placeholder="HR comment...">${hrRemarksVal}</textarea></td>
                    <td><textarea class="form-control hod-remarks" name="kraData1[${displayIndex}][hod_remarks]" placeholder="HOD comment...">${hodRemarksVal}</textarea></td>
                </tr>
            `;
            default_tableBody.append(rowHTML);
        });
        if (selectedIndexes.length === 0) {
            default_tableBody.html(
                '<tr class="pedma-kra-empty-row"><td colspan="7" class="text-muted text-center" style="padding:18px;">' +
                'Tick KRAs in the list above to unlock rating and comment fields.</td></tr>'
            );
        }
        updateDefaultKraSelectionSummary(allDefaultKra, selectedIndexes.map(String));
        updateDefaultTableEnteredTotal();
        recalculateDefaultScore();
    }
    window.buildDefaultTableFromSelection = buildDefaultTableFromSelection;

    function getSelectedDefaultIndexes() {
        return $('#defaultKraSelect .default-kra-check:checked').map(function() {
            return $(this).val();
        }).get();
    }

    function checkDefaultMaxScoreSum(allDefaultKra, selectedIndexes) {
        var total = 0;
        selectedIndexes.forEach(function(idx) {
            var item = allDefaultKra[parseInt(idx, 10)];
            if (item) {
                total += parseInt(item.max_score, 10) || 0;
            }
        });
        return total;
    }

    function recalculateDefaultScore() {
        let sum = 0;
        let count = 0;
        $('#default-kra-table-body tr[data-original-index]').each(function() {
            let $row = $(this);
            let value = pedmaRowRatingValue($row);
            let maxScore = parseInt($row.find('[name$="[max_score]"]').val(), 10) || 0;
            if (value !== '' && value !== undefined) {
                sum += parseFloat(value) || 0;
                count += maxScore;
            }
        });
        if (count > 0) {
            let avg = ((sum / count) * 100).toFixed(2);
            $('#avg_score_display1').html(avg + '%');
            $('#avg_score1').val(avg);
        } else {
            $('#avg_score_display1').html('');
            $('#avg_score1').val('');
        }
        updateDefaultTableEnteredTotal();
    }

    function getPreviousMonthValue(ym) {
        if (!ym || ym.indexOf('-') === -1) {
            return '';
        }
        let [year, month] = ym.split('-');
        year = parseInt(year, 10);
        month = parseInt(month, 10) - 1;
        if (month < 1) {
            month = 12;
            year = year - 1;
        }
        return `${year}-${String(month).padStart(2, '0')}`;
    }

    // Keep only KRAs/KPIs that had a score in the previous month.
    function filterPreviousMonthKraWithScores(kraList, isCustom) {
        if (!Array.isArray(kraList)) {
            return [];
        }

        return kraList.filter(function(item) {
            if (!item) {
                return false;
            }

            if (isCustom) {
                if (Array.isArray(item.kpiData) && item.kpiData.length > 0) {
                    return item.kpiData.some(function(kpi) {
                        return kpi && kpi.get_score !== undefined && kpi.get_score !== null && String(kpi.get_score).trim() !== '';
                    });
                }

                return item.get_score !== undefined && item.get_score !== null && String(item.get_score).trim() !== '';
            }

            return item.get_score !== undefined && item.get_score !== null && String(item.get_score).trim() !== '';
        });
    }

    // Clear scores/comments so previous month is used as a blank template.
    function stripScoresAsTemplate(kraList) {
        if (!Array.isArray(kraList)) {
            return [];
        }
        return kraList.map(function(item) {
            var copy = Object.assign({}, item);
            delete copy.get_score;
            delete copy.comment;
            delete copy.hr_remarks;
            if (Array.isArray(copy.kpiData)) {
                copy.kpiData = copy.kpiData.map(function(kpi) {
                    var k = Object.assign({}, kpi);
                    delete k.get_score;
                    delete k.comment;
                    delete k.hr_remarks;
                    return k;
                });
            }
            return copy;
        });
    }

    function normalizePreviousMonthCustomTemplate(kraList) {
        if (!Array.isArray(kraList)) {
            return [];
        }

        return kraList.map(function(item) {
            var copy = Object.assign({}, item);
            delete copy.get_score;
            delete copy.comment;
            delete copy.hr_remarks;
            if (Array.isArray(copy.kpiData)) {
                copy.kpiData = copy.kpiData
                    .filter(function(kpi) {
                        return kpi && kpi.get_score !== undefined && kpi.get_score !== null && String(kpi.get_score).trim() !== '';
                    })
                    .map(function(kpi) {
                        var k = Object.assign({}, kpi);
                        delete k.get_score;
                        delete k.comment;
                        delete k.hr_remarks;
                        return k;
                    });
            }
            return copy;
        });
    }

    function applyPreviousMonthTemplate(response, kraBasedOnDepartment) {
        var existingData = {
            default_kra: '',
            custom_kra: [],
            isUpdate: false,
            isDraft: false
        };

        if (!response || !response.kra_data) {
            alert('No previous month PEDMA found for this employee. Using Default KRA.');
            $('#previous_kra_save_type').val('');
            $("#defaultOption").prop('checked', true);
            toggleKraWrapper();
            updateKraTable(kraBasedOnDepartment, existingData);
            return;
        }

        var kraData = [];
        try {
            kraData = JSON.parse(response.kra_data);
        } catch (e) {
            kraData = [];
        }

        var isCustomPrevious = response.type === 'custom';
        kraData = filterPreviousMonthKraWithScores(kraData, isCustomPrevious);
        if (!kraData.length) {
            alert('No scored KRAs found in the previous month. Please use Default or Custom KRA.');
            $('#previous_kra_save_type').val('');
            $("#defaultOption").prop('checked', true);
            toggleKraWrapper();
            updateKraTable(kraBasedOnDepartment, existingData);
            return;
        }

        if (response.type === 'custom') {
            $('#previous_kra_save_type').val('custom');
            existingData.custom_kra = normalizePreviousMonthCustomTemplate(kraData);
            existingData.isPreviousMonthTemplate = true;
            // Keep custom checkboxes usable while showing previous structure as draft-like rows
            existingData.isDraft = true;
            updateKraTable(kraBasedOnDepartment, existingData);
            showPreviousAsCustom();
        } else {
            $('#previous_kra_save_type').val('default');
            existingData.default_kra = stripScoresAsTemplate(kraData);
            existingData.isPreviousMonthTemplate = true;
            updateKraTable(kraBasedOnDepartment, existingData);
            showPreviousAsDefault();
        }

        editor.setData('');
        $("#avg_score_display1").html('');
        $('#avg_score1').val('');
        $("#avg_score_display2").html('');
        $('#avg_score2').val('');
        $("#staffResponseArea").hide();
        $("#staffResp").val('');
        $(".nonEditButton").show();
        $(".editButton").hide();
        enableSubmitButton();
    }

    function showPreviousAsDefault() {
        defaultKraWrapper.style.display = 'block';
        customKraWrapper.style.display = 'none';
        customKraSelectWrapper.style.display = 'none';
        if (defaultKraSelectWrapper) {
            defaultKraSelectWrapper.style.display = 'block';
        }
        custom_performance_score.style.display = 'none';
        default_performance_score.style.display = 'block';
        if (previousMonthHint) {
            previousMonthHint.style.display = 'block';
        }
    }

    function showPreviousAsCustom() {
        customKraWrapper.style.display = 'block';
        defaultKraWrapper.style.display = 'none';
        if (defaultKraSelectWrapper) {
            defaultKraSelectWrapper.style.display = 'none';
        }
        // Keep original Previous Month custom behavior: show filled table, hide select list
        customKraSelectWrapper.style.display = 'none';
        default_performance_score.style.display = 'none';
        custom_performance_score.style.display = 'block';
        if (previousMonthHint) {
            previousMonthHint.style.display = 'block';
        }
    }

    function loadPreviousMonthKraTemplate() {
        var staffid = $('#staffid').val();
        var month = $('#performance_month').val();
        var department = $('#departments').val();

        if (!hasValue(staffid) || !hasValue(month)) {
            alert('Please select Employee and Month first.');
            $("#defaultOption").prop('checked', true);
            toggleKraWrapper();
            return;
        }

        var prevMonth = getPreviousMonthValue(month);
        $('.loader').removeClass('hidden');

        var kraPromise = (window.lastKraBasedOnDepartment)
            ? $.Deferred().resolve(window.lastKraBasedOnDepartment).promise()
            : fetchKRABasedOnDepartment(department);

        kraPromise.done(function(kraBasedOnDepartment) {
            window.lastKraBasedOnDepartment = kraBasedOnDepartment;
            $.ajax({
                url: "<?php echo base_url('admin/staff/get_previous_month_custom_kra_data'); ?>",
                type: 'POST',
                data: {
                    staffid: staffid,
                    date: prevMonth
                },
                dataType: 'json',
                success: function(response) {
                    applyPreviousMonthTemplate(response, kraBasedOnDepartment);
                },
                error: function() {
                    alert('Failed to load previous month KRA. Please try again.');
                    $("#defaultOption").prop('checked', true);
                    toggleKraWrapper();
                },
                complete: function() {
                    $('.loader').addClass('hidden');
                }
            });
        }).fail(function() {
            $('.loader').addClass('hidden');
            alert('Failed to load department KRA list.');
            $("#defaultOption").prop('checked', true);
            toggleKraWrapper();
        });
    }

    // Function to toggle visibility based on the selected option
    function toggleKraWrapper() {
        if (previousMonthHint) {
            previousMonthHint.style.display = 'none';
        }

        if (defaultOption && defaultOption.checked) {
            $('#previous_kra_save_type').val('');
            defaultKraWrapper.style.display = 'block';
            customKraWrapper.style.display = 'none';
            customKraSelectWrapper.style.display = 'none';
            if (defaultKraSelectWrapper) {
                defaultKraSelectWrapper.style.display = 'block';
            }
            custom_performance_score.style.display = 'none';
            default_performance_score.style.display = 'block';

            // Ensure Default list/table exists when switching back to Default
            if (window.lastKraBasedOnDepartment && $('#defaultKraSelect .default-kra-check').length === 0) {
                updateKraTable(window.lastKraBasedOnDepartment, {
                    default_kra: [],
                    custom_kra: [],
                    isUpdate: false,
                    isDraft: false
                });
            }
        } else if (customOption && customOption.checked) {
            $('#previous_kra_save_type').val('');
            customKraWrapper.style.display = 'block';
            defaultKraWrapper.style.display = 'none';
            if (defaultKraSelectWrapper) {
                defaultKraSelectWrapper.style.display = 'none';
            }
            customKraSelectWrapper.style.display = 'block';
            default_performance_score.style.display = 'none';
            custom_performance_score.style.display = 'block';
        } else if (previousMonthOption && previousMonthOption.checked) {
            var saveType = $('#previous_kra_save_type').val();
            if (saveType === 'custom') {
                showPreviousAsCustom();
            } else if (saveType === 'default') {
                showPreviousAsDefault();
            } else if (previousMonthHint) {
                previousMonthHint.style.display = 'block';
            }
        }
    }

    // Listen for changes on radio options — always allow switching
    if (defaultOption) {
        defaultOption.addEventListener('change', function() {
            if (!defaultOption.checked) {
                return;
            }
            toggleKraWrapper();
            if (window.lastKraBasedOnDepartment) {
                updateKraTable(window.lastKraBasedOnDepartment, {
                    default_kra: [],
                    custom_kra: [],
                    isUpdate: false,
                    isDraft: false
                });
            }
        });
    }
    if (customOption) {
        customOption.addEventListener('change', function() {
            if (!customOption.checked) {
                return;
            }
            toggleKraWrapper();
            if (window.lastKraBasedOnDepartment) {
                updateKraTable(window.lastKraBasedOnDepartment, {
                    default_kra: [],
                    custom_kra: [],
                    isUpdate: false,
                    isDraft: false
                });
            }
        });
    }
    if (previousMonthOption) {
        previousMonthOption.addEventListener('change', function() {
            if (previousMonthOption.checked) {
                toggleKraWrapper();
                loadPreviousMonthKraTemplate();
            }
        });
    }

    // List checkbox unlocks that KRA (Default only)
    $('body').on('change', '.default-kra-check', function() {
        var allDefault = (window.lastKraBasedOnDepartment && window.lastKraBasedOnDepartment.default_kra) || [];
        syncKraListLockIcon($(this));
        var selected = getSelectedDefaultIndexes();
        var total = checkDefaultMaxScoreSum(allDefault, selected);
        if (total > 100) {
            alert('The total max score must not exceed 100.');
            $(this).prop('checked', false);
            syncKraListLockIcon($(this));
            selected = getSelectedDefaultIndexes();
        }
        buildDefaultTableFromSelection(allDefault, selected, window.lastDefaultScoreMap || {});
    });

    // Initial check to apply the right state on page load
    toggleKraWrapper();

    // Function to disable submit button
    function disableSubmitButton() {
        // document.getElementsByClassName('submitBtn').disabled = true;
        $(".submitBtn").prop("disabled", true);
        $(".comment").each(function() {
            $(this).prop("disabled", true);
        });
        $(".hr-remarks").each(function() {
            $(this).prop("disabled", true);
        });
        $(".hod-remarks").each(function() {
            $(this).prop("disabled", true);
        });
        $(".rating1").each(function() {
            $(this).prop("disabled", true);
        });
        $(".rating2").each(function() {
            $(this).prop("disabled", true);
        });

        // $('#overall_feedback').prop("disabled", true);
        // editor.enableReadOnlyMode('overall_feedback');
        editor.setReadOnly(true);

        $('.selectpicker').selectpicker('refresh');
    }

    // Function to enable submit button
    function enableSubmitButton() {
        // document.getElementsByClassName('submitBtn').disabled = false;
        $(".submitBtn").prop("disabled", false);

        $(".comment").each(function() {
            $(this).prop("disabled", false);
        });
        $(".hr-remarks").each(function() {
            $(this).prop("disabled", false);
        });
        $(".hod-remarks").each(function() {
            $(this).prop("disabled", false);
        });

        $(".rating1").each(function() {
            $(this).prop("disabled", false);
        });

        $(".rating2").each(function() {
            $(this).prop("disabled", false);
        });

        // $('#overall_feedback').prop("disabled", false);
        // editor.disableReadOnlyMode('overall_feedback');
        editor.setReadOnly(false);

        $('.selectpicker').selectpicker('refresh');
    }

    // Function to check if the selected month is within the allowed range
    function checkMonthYear(selectedDate) {
        // Get the selected month and year in 'YYYY-MM' format

        const [selectedYear, selectedMonth] = selectedDate.split('-').map(Number);

        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        const currentMonth = currentDate.getMonth() + 1; // Months are zero-based

        // Calculate the allowed date range (two months ago)
        const allowedYear = currentMonth <= 2 ? currentYear - 1 : currentYear;
        const allowedMonth = ((currentMonth + 10) % 12) + 1;

        // Compare selected date with allowed range
        if (
            selectedYear < allowedYear ||
            (selectedYear === allowedYear && selectedMonth < allowedMonth) ||
            (selectedYear === currentYear && selectedMonth > currentMonth)
        ) {
            disableSubmitButton();
        } else {
            enableSubmitButton();
        }
    }

    $('body').on('change', '.rating1', function() {
        recalculateDefaultScore();
    });


    $('body').on('change', '.rating2', function() {
        // Loop through each select input
        var sum = 0;
        var count = 0;

        $('.rating2').each(function() {
            var value = parseFloat($(this).val()) || 0;
            var maxScore = parseFloat($(this).attr('max')) || 0;

            // Add to sum and count only if value is a valid number and not empty
            if (!isNaN(value)) { 
                sum += value;
                count += maxScore;
            }
        });

        if (count > 0) {
            var average = sum / count;
            var percentage = average * 100;
            $('#avg_score_display2').html(percentage.toFixed(2) + "%");
            $('#avg_score2').val(percentage.toFixed(2));

        } else {
            $('#avg_score_display2').html('No numbers selected.');
            $('#avg_score2').val(null);
        }
    });

    // AJAX functions with promises
    function fetchKRABasedOnDepartment(department) {
        return $.ajax({
            url: "<?php echo base_url('admin/staff/get_kra_based_on_department_json'); ?>",
            type: 'POST',
            data: {
                department
            },
            dataType: 'json'
        });
    }

    // Set required prop for staffid
    $("#departments").prop('required', true);
    $("#staffid").prop('required', true);

    <?php if (!$pedma_panel_partial) { ?>
    // On change of departments dropdown (full page only — parent page owns filters when embedded)
    $('#departments').on('change', function() {
        $('.loader').removeClass('hidden'); // Show loader

        let department = $(this).val(); // Get selected department

        // AJAX call to fetch staff based on department
        $.ajax({
            url: "<?php echo base_url('admin/staff/get_staff_department_json'); ?>",
            type: 'POST',
            data: {
                department
            },
            dataType: 'json',
            success: function(response) {
                let staff = '<option value=""></option>'; // Default empty option
                let list = Array.isArray(response) ? response : [];

                // Populate staff dropdown
                list.forEach(element => {
                    staff += `<option value="${element.staffid}">${element.firstname} ${element.lastname} ${element.staff_identifi || ''}</option>`;
                });

                $('#staffid').html(staff); // Insert the staff options
                $('#staffid').selectpicker('refresh'); // Refresh selectpicker UI
            },
            error: function() {
                alert('Failed to fetch staff. Please try again.');
            },
            complete: function() {
                $('.loader').addClass('hidden'); // Hide loader after request completes
            }
        });
    });
    <?php } ?>

    window.pedmaLoadEmployeeEvaluation = function pedmaLoadEmployeeEvaluation() {
        if (!$('#pedmaEvaluationContent').length) {
            return;
        }

        $('.loader').removeClass('hidden');
        let department = $('#departments').val();
        var staffid = $('#staffid').val();
        var month = $('#performance_month').val();

        if (!hasValue(staffid) || !hasValue(month)) {
            $('.loader').addClass('hidden');
            return;
        }

        if (pedmaPanelPartial) {
            $('#pedma_hidden_staffid').val(staffid);
            $('#pedma_hidden_performance_month').val(month);
        }

        checkMonthYear(month);

        $.ajax({
            url: "<?php echo base_url('admin/staff/get_staff_json'); ?>",
            type: 'POST',
            data: {
                staffid: staffid
            },
            dataType: 'json',
            success: function(response) {
                let name = response[0][0].full_name;
                $('#full-name').empty().append(name);
                $('#designation').empty().append(response[0][0].job_name);
                $('#department-name').empty();
                if (response[1]) {
                    $('#department-name').append(response[1].name);
                    if (response[1].departmentid) {
                        department = response[1].departmentid;
                        if (!$('#departments').val()) {
                            $('#departments').selectpicker('val', String(department));
                        }
                    }
                }

                $.ajax({
                    url: "<?php echo base_url('admin/staff/get_staff_performance_month_and_id'); ?>",
                    type: 'POST',
                    data: {
                        staffid: staffid,
                        month: month
                    },
                    dataType: 'json',
                    success: function(perfResponse) {
                        fetchKRABasedOnDepartment(department).done(function(kraResponse) {
                            window.lastKraBasedOnDepartment = kraResponse;

                            if (!perfResponse) {
                                handleNoPerformanceData(kraResponse);
                            } else {
                                handlePerformanceData(perfResponse, kraResponse);
                            }
                            toggleKraWrapper();
                        }).always(function() {
                            $('.loader').addClass('hidden');
                        });
                    },
                    error: function() {
                        alert('Failed to fetch performance data. Please try again.');
                        $('.loader').addClass('hidden');
                    }
                });
            },
            error: function() {
                alert('Failed to fetch staff details. Please try again.');
                $('.loader').addClass('hidden');
            }
        });
    };

    if (!pedmaPanelPartial) {
        $('#staffid').on('change', pedmaLoadEmployeeEvaluation);
    }

    // On change of performance month dropdown
    $('#performance_month').on('change', function() {
        if (pedmaPanelPartial) {
            pedmaLoadEmployeeEvaluation();
            return;
        }

        $('.loader').removeClass('hidden'); // Show loader
        let department = $('#departments').val();
        let staffid = $('#staffid').val();
        let month = $(this).val();

        checkMonthYear(month); // Custom validation (assumed)

        // Fetch performance data based on month and staffid
        $.ajax({
            url: "<?php echo base_url('admin/staff/get_staff_performance_month_and_id'); ?>",
            type: 'POST',
            data: {
                staffid,
                month
            },
            dataType: 'json',
            success: function(response) {
                let kraBasedOnDepartment;

                // Call the function with the desired department
                fetchKRABasedOnDepartment(department).done(function(kraResponse) {
                    kraBasedOnDepartment = kraResponse;
                    window.lastKraBasedOnDepartment = kraResponse;

                    // Check if there's no performance data
                    if (!response) {
                        handleNoPerformanceData(kraBasedOnDepartment);
                    } else {
                        handlePerformanceData(response, kraBasedOnDepartment);
                    }
                    toggleKraWrapper();

                    // Clear editor and score displays

                });
            },
            error: function() {
                alert('Failed to fetch performance data. Please try again.');
            },
            complete: function() {
                $('.loader').addClass('hidden'); // Hide loader after request completes
            }
        });
    });

    // Handle no performance data case
    function handleNoPerformanceData(kraBasedOnDepartment) {
        let existingData = {
            'default_kra': '',
            'custom_kra': [],
            'isUpdate': false,
            'isDraft': false
        };

        $("#defaultOption").prop('disabled', false).prop('checked', true);
        $("#customOption").prop('disabled', false).prop('checked', false);
        $("#previousMonthOption").prop('disabled', false).prop('checked', false);
        $('#previous_kra_save_type').val('');
        toggleKraWrapper();

        $("#staffResponseArea").hide();
        $("#staffResp").val('');
        $(".nonEditButton").show();
        $(".editButton").hide();

        updateKraTable(kraBasedOnDepartment, existingData);

        pedmaClearEditor();
        $("#avg_score_display1").html('');
        $('#avg_score1').val('');
        $("#avg_score_display2").html('');
        $('#avg_score2').val('');
        enableSubmitButton();
    }


    // Handle performance data case
    function handlePerformanceData(response, kraBasedOnDepartment) {
        let existingData = {};
        $('#previous_kra_save_type').val('');
        // Always allow switching between Default / Custom / Previous Month
        $("#defaultOption").prop('disabled', false);
        $("#customOption").prop('disabled', false);
        $("#previousMonthOption").prop('disabled', false);

        if (response.type === "default") {
            $("#defaultOption").prop('checked', true);
            $("#customOption").prop('checked', false);
            $("#previousMonthOption").prop('checked', false);
            toggleKraWrapper();

            existingData['default_kra'] = JSON.parse(response.kra_data);
            existingData['custom_kra'] = '';
            
            existingData['isUpdate'] = false;
            existingData['isDraft'] = false;

            var ascore = response.avg_score;
            var fscore = response.fatal_error_score ? (response.fatal_error_score / 100) * ascore : 0;
            var addscore = response.add_on_score ? (response.add_on_score / 100) * ascore : 0;
            var nscore = (ascore - fscore + addscore).toFixed(2);

            var fscoreDisplay = fscore ? ` - ${response.fatal_error_score}%<span class="fscore">(${fscore.toFixed(2)})</span>` : "";
            var addscoreDisplay = addscore ? ` + ${response.add_on_score}%<span class="fscore">(${addscore.toFixed(2)})</span>` : "";

            if(fscore || addscore){
                $("#avg_score_display1").html(
                    `${ascore}%${fscoreDisplay}${addscoreDisplay} = ${nscore}%`
                );
            }else{
                $("#avg_score_display1").html(
                    `${ascore}%`
                );
            }

            $('#avg_score1').val(response.avg_score);
        } else {
            $("#defaultOption").prop('checked', false);
            $("#customOption").prop('checked', true);
            $("#previousMonthOption").prop('checked', false);
            toggleKraWrapper();

            existingData['default_kra'] = '';
            existingData['custom_kra'] = JSON.parse(response.kra_data);
            if (response.status != 0) {
                existingData['isUpdate'] = true;
            } else {
                existingData['isUpdate'] = false;
                if (response.status == 0) {
                    existingData['isDraft'] = true;
                } else {
                    existingData['isDraft'] = false;
                }
            }

            var ascore = response.avg_score;
            var fscore = response.fatal_error_score ? (response.fatal_error_score / 100) * ascore : 0;
            var addscore = response.add_on_score ? (response.add_on_score / 100) * ascore : 0;
            var nscore = (ascore - fscore + addscore).toFixed(2);

            var fscoreDisplay = fscore ? ` - ${response.fatal_error_score}%<span class="fscore">(${fscore.toFixed(2)})</span>` : "";
            var addscoreDisplay = addscore ? ` + ${response.add_on_score}%<span class="fscore">(${addscore.toFixed(2)})</span>` : "";

            if(fscore || addscore){
                $("#avg_score_display2").html(
                    `${ascore}%${fscoreDisplay}${addscoreDisplay} = ${nscore}%`
                );
            }else{
                $("#avg_score_display2").html(
                    `${ascore}%`
                );
            }

            $('#avg_score2').val(response.avg_score);
        }

        if (hasValue(response.staff_comment)) {
            $("#staffResponseArea").show();
            $("#staffResp").val(response.staff_comment);
        } else {
            $("#staffResponseArea").hide();
            $("#staffResp").val('');
        }

        if (response.status != 0) {
            $(".nonEditButton").hide();
            $(".editButton").show();
        } else {
            $(".nonEditButton").show();
            $(".editButton").hide();
        }

        editor.setData(response.overall_feedback);

        // Update KRA table with performance data
        updateKraTable(kraBasedOnDepartment, existingData);
    }

    // Update KRA table function
    // function updateKraTable(kraBasedOnDepartment, existingData) {
    //     const default_tableBody = $('#default-kra-table-body');
    //     default_tableBody.empty(); // Clear previous rows

    //     let default_kra_data = Array.isArray(existingData.default_kra) && existingData.default_kra.length > 0
    //         ? existingData.default_kra
    //         : Array.isArray(kraBasedOnDepartment.default_kra) ? kraBasedOnDepartment.default_kra : [];

    //     if (default_kra_data && default_kra_data.length > 0) {
    //         // Add rows to default KRA table
    //         default_kra_data.forEach((item, index) => {
    //             let rowHTML = `
    //                 <tr>
    //                     <td>${index + 1}</td>
    //                     <td class="performance-criteria">
    //                         <b>${item.name}</b>
    //                         <input type="hidden" name="kraData1[${index}][name]" value="${item.name}">
    //                         <br>${item.description}
    //                         <input type="hidden" name="kraData1[${index}][description]" value="${item.description}">
    //                     </td>
    //                     <td>${item.max_score}</td>
    //                     <input type="hidden" name="kraData1[${index}][max_score]" value="${item.max_score}">
    //                     <td><input type="number" class="form-control rating1" name="kraData1[${index}][get_score]" max="${item.max_score}" min="0" value="${item.get_score ? item.get_score : ''}"></td>
    //                     <td><textarea class="form-control comment" name="kraData1[${index}][comment]" placeholder="Provide feedback here...">${item.comment ? item.comment : ''}</textarea></td>
    //                 </tr>
    //             `;
    //             default_tableBody.append(rowHTML);
    //         });
    //     }

    //     // For Custom KRA Table
    //     const custom_tableBody = $('#custom-kra-table-body');
    //     custom_tableBody.empty(); // Clear previous rows
    //     $('#customKraSelect').selectpicker({
    //         showTick: false 
    //     });
    //     $('#customKraSelect').html('').attr('multiple', 'multiple').selectpicker('refresh');

    //     let custom_kra_data = Array.isArray(existingData.custom_kra) && existingData.custom_kra.length > 0
    //         ? existingData.custom_kra
    //         : Array.isArray(kraBasedOnDepartment.custom_kra) ? kraBasedOnDepartment.custom_kra : [];


    //     if (existingData.isUpdate) {
    //         editCustomKraTable(custom_kra_data);
    //     }else{
    //         let options = '<option value=""></option>';
    //         let preSelectedOptions = [];
    //         let preSelectedIds = [];

    //         // Get pre-selected options
    //         $.each(existingData.custom_kra, function (index, name) {
    //             preSelectedOptions.push(name.name);
    //         });

    //         // Iterate through the custom_kra array to dynamically add options with default unchecked icon

    //         $.each(kraBasedOnDepartment.custom_kra, function (index, option) {
    //             let isSelected = preSelectedOptions.includes(option.name.toString()) ? 'selected' : '';
    //             options += `<option data-icon="glyphicon-${isSelected?'ok':'unchecked'}" value="${option.id}" ${isSelected}>${option.name}</option>`;
    //             if (isSelected) {
    //                 preSelectedIds.push(option.id);
    //             }
    //         });

    //         // Append and refresh selectpicker
    //         $('#customKraSelect')
    //             .empty()
    //             .html(options)
    //             .attr('multiple', true)
    //             .selectpicker('refresh'); // Refresh the Bootstrap-select to apply changes

    //         // Pre-select the values after refresh and trigger change event to update table
    //         updateCustomKraTable(kraBasedOnDepartment.custom_kra, preSelectedIds);

    //         $('#customKraSelect').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
    //             var selectedValues = $(this).val(); // Get all selected values

    //             // Check if the new selection will exceed the max_score limit before updating the table
    //             let score = checkMaxScoreSum(kraBasedOnDepartment.custom_kra,selectedValues);
    //             console.log("Total Score:", score);

    //             if (score <= 100) {
    //                 updateCustomKraTable(kraBasedOnDepartment.custom_kra, selectedValues); // Update the table rows only if valid
    //                 toggleIcons(clickedIndex, true);
    //             } else {
    //                 alert("The total max_score must not exceed 100.");
    //                 // Revert the selectpicker change if it's invalid
    //                 $(this).selectpicker('val', previousValue);
    //                 toggleIcons(clickedIndex, false);
    //             }
    //         });
    //     }

    //     // Function to toggle icons based on selection
    //     function toggleIcons(clickedIndex, isSelected) {
    //         // Find the corresponding option in the selectpicker menu
    //         let option = $('#customKraSelect option').eq(clickedIndex);

    //         // Change icon based on whether the option was selected or deselected
    //         if (isSelected) {
    //             // Change to glyphicon-ok when selected
    //             option.attr('data-icon', 'glyphicon-ok');
    //         } else {
    //             // Revert to glyphicon-unchecked when deselected
    //             option.attr('data-icon', 'glyphicon-unchecked');
    //         }

    //         // Refresh the selectpicker to update the icons
    //         $('#customKraSelect').selectpicker('refresh');
    //     }
    // }

    function updateKraTable(kraBasedOnDepartment, existingData) {
        window.lastKraBasedOnDepartment = kraBasedOnDepartment;
        window.lastDefaultScoreMap = {};

        let allDefault = Array.isArray(kraBasedOnDepartment.default_kra) ? kraBasedOnDepartment.default_kra : [];
        let existingDefault = Array.isArray(existingData.default_kra) ? existingData.default_kra : [];
        let selectedDefaultIndexes = [];

        if (existingData.isPreviousMonthTemplate && existingDefault.length > 0) {
            // Previous month default: show only last month's scored KRAs, all pre-selected
            allDefault = existingDefault.slice();
            selectedDefaultIndexes = allDefault.map(function(_, idx) {
                return String(idx);
            });
        } else {
            // Map existing scored default KRAs by name for restore
            existingDefault.forEach(function(item) {
                if (!item || !item.name) {
                    return;
                }
                window.lastDefaultScoreMap[item.name] = item;
                allDefault.forEach(function(opt, idx) {
                    if (opt.name === item.name) {
                        selectedDefaultIndexes.push(String(idx));
                        window.lastDefaultScoreMap[idx] = item;
                    }
                });
            });

            // New evaluation: show all Default KRAs locked (none selected) unless editing existing
            if (existingDefault.length === 0) {
                selectedDefaultIndexes = [];
            }
        }

        renderDefaultKraList(allDefault, selectedDefaultIndexes);
        buildDefaultTableFromSelection(allDefault, selectedDefaultIndexes, window.lastDefaultScoreMap);

        const custom_tableBody = $('#custom-kra-table-body');
        custom_tableBody.empty(); // Clear previous rows

        // Prepare container for dynamically added checkboxes
        $('#customKraSelect').empty();

        let custom_kra_data = Array.isArray(existingData.custom_kra) && existingData.custom_kra.length > 0 ?
            existingData.custom_kra :
            Array.isArray(kraBasedOnDepartment.custom_kra) ? kraBasedOnDepartment.custom_kra : [];

        if (existingData.isUpdate) {
            document.getElementById("customKraSelectWrapper").style.display = "none";
            if (defaultKraSelectWrapper) {
                defaultKraSelectWrapper.style.display = 'none';
            }
            editCustomKraTable(custom_kra_data);
        } else {
            let checkboxesHtml = '';
            let preSelectedIds = [];

            // Get pre-selected options
            let preSelectedOptions = Array.isArray(existingData.custom_kra)
                ? existingData.custom_kra.map(option => option.name)
                : [];

            // Original Custom KRA checkboxes (no lock UI)
            $.each(kraBasedOnDepartment.custom_kra || [], function(index, option) {
                let isSelected = preSelectedOptions.includes(option.name.toString());
                let checkedAttr = isSelected ? 'checked' : '';

                checkboxesHtml += `
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="kra_${option.id}" value="${option.id}" ${checkedAttr}>
                    <label class="form-check-label" for="kra_${option.id}">${option.name} (Max Score:${option.max_score})</label>
                </div>
            `;
                if (isSelected) {
                    preSelectedIds.push(option.id);
                }
            });

            // Append the dynamically generated checkboxes
            $('#customKraSelect').html(checkboxesHtml);

            // Pre-select values by updating the table
            if(existingData.isDraft){
                editCustomKraTable(custom_kra_data);
            }else{
                updateCustomKraTable(kraBasedOnDepartment.custom_kra, preSelectedIds);
            }

            // Handle checkbox change event to update table and validate total score
            $('#customKraSelect input[type="checkbox"]').off('change.customKra').on('change.customKra', function() {
                let selectedValues = $('#customKraSelect input[type="checkbox"]:checked')
                    .map(function() {
                        return $(this).val();
                    }).get();

                let score = checkMaxScoreSum(kraBasedOnDepartment.custom_kra, selectedValues);
                if (score <= 100) {
                    updateCustomKraTable(kraBasedOnDepartment.custom_kra, selectedValues);
                } else {
                    alert("The total max score must not exceed 100.");
                    $(this).prop('checked', false);
                }
            });
        }

    }

    // Function to check if the max_score sum will exceed 100
    function checkMaxScoreSum(customKra, selectedValues) {
        let totalScore = 0;

        // Add the scores of the newly selected options from the dropdown
        $.each(customKra, function(index, option) {
            if (selectedValues.includes(option.id.toString())) {
                let newMaxScore = parseInt(option.max_score); // Assuming max_score is a property in the option object
                if (!isNaN(newMaxScore)) {
                    totalScore += newMaxScore;
                }
            }
        });

        console.log("Selected values:", customKra);
        // Return the total score
        return totalScore;
    }

    function editCustomKraTable(customKraEditData) {
        // $('#customKraSelect').style.display = "none";

        const tableBody = $('#custom-kra-table-body');
        tableBody.empty(); // Clear the table before adding new rows

        // Loop through selected KRA IDs and find corresponding data
        customKraEditData.forEach(function(selectedKRA, index) {

            if (selectedKRA) {
                // Create the row for the selected KRA
                let rowHTML = `
            <tr>
                <td rowspan="${selectedKRA.kpiData ? selectedKRA.kpiData.length + 1 : 1}">${index + 1}</td>
                <td class="performance-criteria" rowspan="${selectedKRA.kpiData ? selectedKRA.kpiData.length + 1 : 1}">
                    <b>${selectedKRA.name}</b>
                    <input type="hidden" name="kraData2[${index}][name]" value="${selectedKRA.name}">
                    <br>
                    ${selectedKRA.description ? selectedKRA.description : 'No description available'}
                    <input type="hidden" name="kraData2[${index}][description]" value="${selectedKRA.description}">
                    <br>
                    <b>Max Score: ${selectedKRA.max_score}</b>
                    <input type="hidden" name="kraData2[${index}][max_score]" class="customKRAMAXScore" value="${selectedKRA.max_score}">
                </td>
            `;

                if (selectedKRA.kpiData && selectedKRA.kpiData.length > 0) {
                    // Add rows for each KPI if available
                    selectedKRA.kpiData.forEach((kpi, kpiIndex) => {
                        rowHTML += `
                    <tr>
                        <td>${kpi.name}</td>
                        <input type="hidden" name="kraData2[${index}][kpiData][${kpiIndex}][name]" value="${kpi.name}">
                        <input type="hidden" name="kraData2[${index}][kpiData][${kpiIndex}][description]" value="${kpi.description}">
                        <td>${kpi.max_score}</td>
                        <input type="hidden" name="kraData2[${index}][kpiData][${kpiIndex}][max_score]" value="${kpi.max_score}">
                        <td>
                            <input type="number" class="form-control rating2" name="kraData2[${index}][kpiData][${kpiIndex}][get_score]" max="${kpi.max_score}" min="0" required value="${kpi.get_score ? kpi.get_score : ''}">
                        </td>
                        <td>
                            <textarea class="form-control comment" name="kraData2[${index}][kpiData][${kpiIndex}][comment]" placeholder="Manager comment..." required>${kpi.comment ? kpi.comment : ''}</textarea>
                        </td>
                        <td>
                            <textarea class="form-control hr-remarks" name="kraData2[${index}][kpiData][${kpiIndex}][hr_remarks]" placeholder="HR remarks / notes...">${kpi.hr_remarks ? kpi.hr_remarks : ''}</textarea>
                        </td>
                    </tr>`;
                    });
                } else {
                    // If no KPIs are available
                    rowHTML += `
                    <td>No KPIs</td>
                    <input type="hidden" name="kraData2[${index}][kpiData]" value="">
                    <td>${selectedKRA.max_score}</td>
                    <td>
                        <input type="number" class="form-control rating2" name="kraData2[${index}][get_score]" max="${selectedKRA.max_score}" min="0" required value="${selectedKRA.get_score ? selectedKRA.get_score : ''}">
                    </td>
                    <td>
                        <textarea class="form-control comment" name="kraData2[${index}][comment]" placeholder="Manager comment..." required>${selectedKRA.comment ? selectedKRA.comment : ''}</textarea>
                    </td>
                    <td>
                        <textarea class="form-control hr-remarks" name="kraData2[${index}][hr_remarks]" placeholder="HR remarks / notes...">${selectedKRA.hr_remarks ? selectedKRA.hr_remarks : ''}</textarea>
                    </td>
                </tr>`;
                }

                // Append the generated row HTML to the table body
                tableBody.append(rowHTML);
            }
        });
    }


    function updateCustomKraTable(allKRAData, selectedValues) {

        const tableBody = $('#custom-kra-table-body');
        tableBody.empty(); // Clear the table before adding new rows

        // Loop through selected KRA IDs and find corresponding data
        selectedValues.forEach(function(kraId, index) {
            const selectedKRA = allKRAData.find(kra => kra.id === kraId);

            if (selectedKRA) {
                // Create the row for the selected KRA
                let rowHTML = `
            <tr>
                <td rowspan="${selectedKRA.kpi_data ? selectedKRA.kpi_data.length + 1 : 1}">${index + 1}</td>
                <td class="performance-criteria" rowspan="${selectedKRA.kpi_data ? selectedKRA.kpi_data.length + 1 : 1}">
                    <b>${selectedKRA.name}</b>
                    <input type="hidden" name="kraData2[${index}][name]" value="${selectedKRA.name}">
                    <br>
                    ${selectedKRA.description}
                    <input type="hidden" name="kraData2[${index}][description]" value="${selectedKRA.description}">
                    <br>
                    <b>Max Score: ${selectedKRA.max_score}</b>
                    <input type="hidden" name="kraData2[${index}][max_score]" class="customKRAMAXScore" value="${selectedKRA.max_score}">
                </td>
        `;

                if (selectedKRA.kpi_data && selectedKRA.kpi_data.length > 0) {
                    // Add rows for each KPI if available
                    selectedKRA.kpi_data.forEach((kpi, kpiIndex) => {
                        rowHTML += `
                    <tr>
                        <td>${kpi.name}</td>
                        <input type="hidden" name="kraData2[${index}][kpiData][${kpiIndex}][name]" value="${kpi.name}">
                        <input type="hidden" name="kraData2[${index}][kpiData][${kpiIndex}][description]" value="${kpi.description}">
                        <td>${kpi.max_score}</td>
                        <input type="hidden" name="kraData2[${index}][kpiData][${kpiIndex}][max_score]" value="${kpi.max_score}">
                        <td>
                            <input type="number" class="form-control rating2" name="kraData2[${index}][kpiData][${kpiIndex}][get_score]" max="${kpi.max_score}" min="0" required value="${kpi.get_score ? kpi.get_score : ''}">
                        </td>
                        <td>
                            <textarea class="form-control comment" name="kraData2[${index}][kpiData][${kpiIndex}][comment]" placeholder="Manager comment..." required>${kpi.comment ? kpi.comment : ''}</textarea>
                        </td>
                        <td>
                            <textarea class="form-control hr-remarks" name="kraData2[${index}][kpiData][${kpiIndex}][hr_remarks]" placeholder="HR remarks / notes...">${kpi.hr_remarks ? kpi.hr_remarks : ''}</textarea>
                        </td>
                    </tr>
                `;
                    });
                } else {
                    // If no KPIs are available
                    rowHTML += `
                <td>No KPIs</td>
                <input type="hidden" name="kraData2[${index}][kpiData]" value="">
                <td>${selectedKRA.max_score}</td>
                <td>
                    <input type="number" class="form-control rating2" name="kraData2[${index}][get_score]" max="${selectedKRA.max_score}" min="0" required value="${selectedKRA.get_score ? selectedKRA.get_score : ''}">
                </td>
                <td>
                    <textarea class="form-control comment" name="kraData2[${index}][comment]" placeholder="Manager comment..." required>${selectedKRA.comment ? selectedKRA.comment : ''}</textarea>
                </td>
                <td>
                    <textarea class="form-control hr-remarks" name="kraData2[${index}][hr_remarks]" placeholder="HR remarks / notes...">${selectedKRA.hr_remarks ? selectedKRA.hr_remarks : ''}</textarea>
                </td>
            </tr>
            `;
                }

                // Append the generated row HTML to the table body
                tableBody.append(rowHTML);
            }
        });
    }
</script>
<script>
    if ($('#pedma-form').length) {
        appValidateForm($('#pedma-form'), {
            performance_month: 'required',
            editor: 'required',
            staffid: 'required'
        });
    }

    $('.selectpicker').selectpicker({});
</script>

<script>
    var editor;
    if ($('#editor').length && typeof CKEDITOR !== 'undefined') {
        if (CKEDITOR.instances.editor) {
            CKEDITOR.instances.editor.destroy(true);
        }
        CKEDITOR.replace('editor');
        CKEDITOR.on('instanceReady', function(ev) {
            if (ev.editor.name === 'editor') {
                editor = ev.editor;
            }
        });
    }
</script>

<script>
    let clickedButtonValue = null; // Initialize clickedButtonValue to avoid undefined issues

    $(".submitBtn").on("click", function() {
        clickedButtonValue = $(this).val();
    });

    $('#pedma-form').on('submit', function(e) {
        let editor_val = editor.getData();
        let avg_score1 = $("#avg_score1").val();
        let avg_score2 = $("#avg_score2").val();
        let defaultOption = $("#defaultOption");
        let customOption = $("#customOption");
        let sum = 0;
        let count = 0;
        let maxCount = false;

        if (avg_score1 === '' && avg_score2 === '') {
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "Overall Performance Score is required. Please fill at least one KRA detail.",
            });
            return false;
        }

        var usingDefaultTemplate = defaultOption.is(':checked') ||
            ($('#previousMonthOption').is(':checked') && $('#previous_kra_save_type').val() === 'default');
        if (usingDefaultTemplate && getSelectedDefaultIndexes().length === 0) {
            Swal.fire({
                icon: "warning",
                title: "Default KRA is locked",
                text: "Tick a Default KRA in the list to unlock it before saving.",
            });
            return false;
        }

        if (defaultOption.is(':checked') || ($('#previousMonthOption').is(':checked') && $('#previous_kra_save_type').val() === 'default')) {
            $('#default-kra-table-body .rating1').each(function() {
                let value = $(this).val();
                let maxScore = $(this).attr('max');
                if (value !== "") {
                    sum += parseInt(value);
                    count += parseInt(maxScore);
                }
            });

            // Update the condition with correct logic
            if (count > 100 || (clickedButtonValue !== '0' && count < 100)) {
                maxCount = true;
            }
        } else if (customOption.is(':checked')) {
            $('.rating2').each(function() {
                let value = $(this).val();
                let maxScore = $(this).attr('max');
                if (value !== "") {
                    sum += parseInt(value);
                    count += parseInt(maxScore);
                }
            });

            if (count > 100 || (clickedButtonValue !== '0' && count < 100)) {
                maxCount = true;
            }
        }

        if (maxCount) {
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "The total max score must be exactly 100.",
            });
            return false;
        }

        if (!editor_val) {
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "Overall Feedback is required.",
            });
            return false;
        }
    });


    $("#fatalButton").click(function(e) {
        e.preventDefault();
        Swal.fire({
            title: "Fatal Error!",
            text: "A Fatal Error will negatively affect the score for selected months. You have two options: either reduce the score to 0% or apply a 50% reduction for the selected months. Please proceed carefully.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Proceed"
        }).then((result) => {
            if (result.isConfirmed) {
                lastSixMonths();

                $("#fatalErrorModal").modal('show');
            }
        });
    });
    // Handle Percentage Box Selection for Fatal Error
    document.querySelectorAll('.percentage-selection .percentage-box').forEach(box => {
        box.addEventListener('click', function() {
            document.querySelectorAll('.percentage-box').forEach(b => b.classList.remove('selected'));
            this.classList.add('selected');

            // Show or hide the custom input field
            if (this.getAttribute('data-value') === 'custom') {
                document.getElementById('customInputField').style.display = 'block';
                $("#customInputField").prop("required",true);
            } else {
                document.getElementById('customInputField').style.display = 'none';
                $("#customInputField").prop("required",false);
            }
        });
    });

    $("#fatalErrorForm").submit(function (e) { 
        e.preventDefault();
        const selectedBox = document.querySelector('.percentage-box.selected');
        let scoreAdjustment = selectedBox ? selectedBox.getAttribute('data-value') : '0';

        // If custom is selected, use the input value
        if (scoreAdjustment === 'custom') {
            scoreAdjustment = document.getElementById('customPercentage').value;
        }

        const selectedMonths = Array.from(document.querySelectorAll('#fatalErrorModal .month-checkboxes input:checked'))
            .map(checkbox => checkbox.value);

        const staffid =  $("#staffid").val();
        const fatal_comment = $("#fatal_comment").val();

        if(!empty(staffid)){
            if(!empty(scoreAdjustment) && !empty(selectedMonths)){
                $('#fatalErrorModal').modal('hide');
                $('.loader').removeClass('hidden'); // Show loader

                $.ajax({
                    type: "post",
                    url: "<?php echo base_url('admin/staff/update_score_by_fatal_error'); ?>",
                    data: {
                        staffid,
                        scoreAdjustment,
                        selectedMonths,
                        fatal_comment
                    },
                    dataType: "json",
                    success: function (response) {
                        $('.loader').addClass('hidden'); // Hide loader after request completes

                        if(response.status){
                            Swal.fire({
                                title: 'Changes Applied!',
                                text: `Score reduced by ${scoreAdjustment}% for the following months: ${selectedMonths.join(', ')}`,
                                icon: 'success'
                            });
                        }else{
                            Swal.fire({
                                title: 'Warning !!',
                                text: `Something went wrong on updating Score.`,
                                icon: 'warning'
                            });
                        }     
                    }
                });
            }else{
                $('#fatalErrorModal').modal('hide');

                Swal.fire({
                    title: 'Warning!!',
                    text: `Please fill all details. It seems you missed something.`,
                    icon: 'warning'
                });
            }
        }else{
            $('#fatalErrorModal').modal('hide');

            Swal.fire({
                title: 'Warning!!',
                text: `Please select an employee`,
                icon: 'warning'
            });
        }
    });

    $("#addOnButton").click(function(e) {
        e.preventDefault();
        Swal.fire({
            title: "Add On!",
            text: "This Add-on will lead to an increase in the score for the selected months. This adjustment reflects Extra ordinary performance. Proceed?",
            icon: "success",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Proceed"
        }).then((result) => {
            if (result.isConfirmed) {
                lastSixMonths();

                $("#addOnModal").modal('show');
            }
        });
    });

    $("#highAchievementForm").submit(function (e) { 
        e.preventDefault();
        const selectedMonths = Array.from(document.querySelectorAll('#addOnModal .month-checkboxes input:checked'))
            .map(checkbox => checkbox.value);
        let scoreAdjustment = document.getElementById('customPercentageIncrease').value;

        const staffid =  $("#staffid").val();
        const addOn_comment = $("#addOn_comment").val();

        if(!empty(staffid)){
            if(!empty(scoreAdjustment) && !empty(selectedMonths)){
                $('#addOnModal').modal('hide');
                $('.loader').removeClass('hidden'); // Show loader

                $.ajax({
                    type: "post",
                    url: "<?php echo base_url('admin/staff/update_score_by_add_on'); ?>",
                    data: {
                        staffid,
                        scoreAdjustment,
                        selectedMonths,
                        addOn_comment
                    },
                    dataType: "json",
                    success: function (response) {
                        $('.loader').addClass('hidden'); // Hide loader after request completes

                        if(response.status){
                            Swal.fire({
                                title: 'Changes Applied!',
                                text: `Score increased by ${scoreAdjustment}% for the following months: ${selectedMonths.join(', ')}`,
                                icon: 'success'
                            });
                        }else{
                            Swal.fire({
                                title: 'Warning !!',
                                text: `Something went wrong on updating Score.`,
                                icon: 'warning'
                            });
                        }     
                    }
                });
            }else{
                $('#addOnModal').modal('hide');

                Swal.fire({
                    title: 'Warning!!',
                    text: `Please fill all details. It seems you missed something.`,
                    icon: 'warning'
                });
            }
        }else{
            $('#addOnModal').modal('hide');

            Swal.fire({
                title: 'Warning!!',
                text: `Please select an employee`,
                icon: 'warning'
            });
        }
    });

    document.getElementById('closeHighAchievementBtn').addEventListener('click', function(){
        $('#addOnModal').modal('hide');
    })
    document.getElementById('closeFatalErrorBtn').addEventListener('click', function(){
        $('#fatalErrorModal').modal('hide');
    })
    function lastSixMonths() {
        let content = '';
        let date = $("#performance_month").val();
        let [year, month] = date.split("-");

        // Month names for checkbox labels
        const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

        // Loop for the current month + the previous 5 months
        for (let i = 0; i < 6; i++) {
            // Convert the month to a number, decrement it
            month = parseInt(month);

            // Handle wrapping to previous year if the month is less than 1
            if (month === 0) {
                month = 12; // Set to December
                year = year - 1; // Decrease the year by 1
            }

            // Format the date correctly as YYYY-MM and ensure the month is two digits
            let formattedMonth = String(month).padStart(2, '0');
            date = `${year}-${formattedMonth}`;

            // Get the month name for the checkbox label
            let monthName = monthNames[month - 1]; // Adjust the index for month array

            // Append the checkbox content for each month
            content += `
                <label><input type="checkbox" value="${date}"> ${monthName}</label>
            `;

            // Decrement the month for the next iteration
            month -= 1;
        }

        // Append or insert 'content' into the DOM where needed
        $(".lastsixmonths").html(content);

    }
</script>
</div>