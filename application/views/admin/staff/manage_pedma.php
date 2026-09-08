<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
?>
<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"> -->
<!-- Custom CSS -->
<style>
    .card {
        border: 1px solid #ddd;
        border-radius: 8px;
        margin-bottom: 20px;
        /* box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); */
    }

    .card-title {
        background-color: #141e46;
        color: #fff;
        font-size: 0.9rem;
        margin: 0;
        font-weight: 500;
        padding: 10px;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }

    .card-body {
        padding: 15px;
        padding-bottom: 0;
    }

    .score {
        font-size: 1.2rem;
        font-weight: 500;
    }

    .comment {
        font-size: 14px;
        color: #555;
        padding-top: 10px;
    }

    .low {
        color: green;
    }

    .medium {
        color: green;
    }

    .high {
        color: green;
    }

    .info-icon {
        cursor: pointer;
    }

    .popover-header {
        font-size: 14px;
        background-color: unset;
        border-bottom: 0;
    }

    .hr {
        border: 1px dashed #cfcfcf;
        margin-top: 4px;
    }

    .ellipsis {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        overflow: hidden;
        -webkit-line-clamp: 3;
    }

    .popover-body {
        padding: 8px;
    }

    #wrapper .row {
        display: flex;
        flex-wrap: wrap;
    }

    .h-100 {
        height: 100%;
    }

    .justify-content-between {
        justify-content: space-between;
    }

    .align-items-center {
        align-items: center;
    }

    .text-end {
        text-align: end;
    }

    .rounded {
        border-radius: 10px;
    }

    .p-0 {
        padding: 0;
    }

    .p-i-4 {
        padding-inline: 4px;
    }

    .mb-0 {
        margin-bottom: 0;
    }

    .popup {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .popup-content {
        background-color: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        max-width: 60%;
        color: #000;

        overflow: auto;
    }

    .close-btn {
        text-align: end;
        top: 10px;
        right: 10px;
        cursor: pointer;
    }

    .icon-toggle {
        font-weight: bold;
        margin-right: 10px;
    }

    .collapse-content {
        display: none;
    }

    .expanded {
        display: table-row;
    }

    canvas {
        width: 100% !important;
    }

    #twoChart .card {
        height: 100% !important;
    }

    #twoChart>div {
        margin-bottom: 20px;
    }

    .pedma-doughnut-legend {
        margin-top: 12px;
        font-size: 12px;
        color: #334155;
    }

    .pedma-doughnut-legend .legend-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 4px 0;
        border-bottom: 1px dashed #e2e8f0;
    }

    .pedma-doughnut-legend .legend-row:last-child {
        border-bottom: 0;
    }

    .pedma-doughnut-legend .legend-left {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    .pedma-doughnut-legend .swatch {
        width: 12px;
        height: 12px;
        border-radius: 2px;
        flex: 0 0 12px;
    }

    .pedma-doughnut-legend .legend-name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .pedma-doughnut-legend .legend-score {
        font-weight: 600;
        white-space: nowrap;
    }

    .pedma-export-wrap {
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px solid #e2e8f0;
    }

    .pedma-export-meta {
        font-size: 12px;
        color: #64748b;
        margin-bottom: 8px;
        line-height: 1.4;
        word-break: break-word;
    }

    .pedma-export-wrap .btn-block {
        width: 100%;
    }

    .pedma-filters-row {
        display: flex;
        flex-wrap: nowrap;
        align-items: flex-end;
        gap: 8px;
        width: 100%;
    }

    .pedma-filter-item {
        flex: 1 1 0;
        min-width: 0;
    }

    .pedma-filter-item label {
        display: block;
        font-size: 11px;
        margin-bottom: 3px;
        color: #64748b;
        white-space: nowrap;
    }

    .pedma-filter-item .form-control,
    .pedma-filter-item .bootstrap-select {
        width: 100% !important;
    }

    .pedma-filter-dept,
    .pedma-filter-emp {
        flex: 1.3 1 0;
    }

    .pedma-filter-period {
        flex: 1.1 1 0;
    }

    .pedma-filter-from,
    .pedma-filter-to {
        flex: 0.9 1 0;
    }

    .pedma-filter-grade {
        flex: 1.15 1 0;
    }
    .pedma-filter-staff-status {
        flex: 0.95 1 0;
    }

    #pedmaGradeTable td {
        vertical-align: middle;
    }

    #pedmaGradeTable .hr-notes-cell {
        max-width: 320px;
        white-space: normal;
        font-size: 12px;
        color: #475569;
    }

    @media (max-width: 991px) {
        .pedma-filters-row {
            flex-wrap: wrap;
        }
        .pedma-filter-item {
            flex: 1 1 calc(50% - 8px);
        }
    }

    .loader {

        position: fixed;

        z-index: 99999;

        top: 100px;
        left: 280px;
        width: 75%;

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
    <div class="content col-md-12" style="background-color: #f1f5f9;">
        <div class="panel-body">
            <div class=" row">
                <div class="container">
                    <div class="row align-items-center mb-4" style="margin-bottom: 20px;">
                        <div class="col-md-8 p-0">
                            <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-flex tw-items-center"><svg width="20px" height="20px" class="tw-w-5 tw-h-5 tw-text-neutral-500 tw-mr-1.5" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M14.7179 20.9008L11.2187 24.0772L10.0782 22.9367C9.77309 22.6316 9.27894 22.6316 8.97388 22.9367C8.66881 23.2417 8.66881 23.7358 8.97388 24.0409L10.6405 25.7074C10.7927 25.8596 10.9923 25.936 11.1923 25.936C11.3798 25.936 11.5678 25.869 11.7173 25.7332L12.9974 24.5713V26.639H6.05129V20.3353H13.778C14.2089 20.3353 14.5585 19.9857 14.5585 19.5548C14.5585 19.1239 14.2089 18.7743 13.778 18.7743H5.27076C4.83982 18.7743 4.49023 19.1239 4.49023 19.5548V27.4195C4.49023 27.8504 4.83982 28.2 5.27076 28.2H13.778C14.2089 28.2 14.5585 27.8504 14.5585 27.4195V23.1538L15.7672 22.0567C16.0866 21.7671 16.1102 21.2729 15.8205 20.9542C15.5309 20.6348 15.0373 20.6112 14.7179 20.9008Z" fill="#1E293B" />
                                    <path d="M21.1171 36.9891H18.5397C18.1088 36.9891 17.7592 37.3387 17.7592 37.7696C17.7592 38.2005 18.1088 38.5501 18.5397 38.5501H21.1171C21.5481 38.5501 21.8977 38.2005 21.8977 37.7696C21.8982 37.3387 21.5486 36.9891 21.1171 36.9891Z" fill="#1E293B" />
                                    <path d="M14.7179 6.61922L11.2187 9.79554L10.0782 8.65505C9.77309 8.35001 9.27894 8.35001 8.97388 8.65505C8.66881 8.9601 8.66881 9.45422 8.97388 9.75926L10.6405 11.4257C10.7927 11.578 10.9923 11.6544 11.1923 11.6544C11.3798 11.6544 11.5678 11.5873 11.7173 11.4516L12.9974 10.2897V12.3574H6.05129V6.05419H13.778C14.2089 6.05419 14.5585 5.70463 14.5585 5.27372C14.5585 4.84281 14.2089 4.49324 13.778 4.49324H5.27076C4.83982 4.49324 4.49023 4.84281 4.49023 5.27372V13.1384C4.49023 13.5693 4.83982 13.9189 5.27076 13.9189H13.778C14.2089 13.9189 14.5585 13.5693 14.5585 13.1384V8.87271L15.7672 7.77564C16.0866 7.48599 16.1102 6.99187 15.8205 6.67308C15.5309 6.35319 15.0373 6.32956 14.7179 6.61922Z" fill="#1E293B" />
                                    <path d="M30.3087 39.8796C29.8778 39.8796 29.5282 40.2292 29.5282 40.6601V49.2195C29.5282 49.6504 29.8778 50 30.3087 50C30.7396 50 31.0892 49.6504 31.0892 49.2195V40.6601C31.0892 40.2287 30.7396 39.8796 30.3087 39.8796Z" fill="#1E293B" />
                                    <path d="M14.7179 35.1819L11.2187 38.3583L10.0782 37.2178C9.77309 36.9127 9.27894 36.9127 8.97388 37.2178C8.66881 37.5228 8.66881 38.0169 8.97388 38.322L10.6405 39.9885C10.7927 40.1407 10.9923 40.2171 11.1923 40.2171C11.3798 40.2171 11.5678 40.1501 11.7173 40.0143L12.9974 38.8524V40.9201H6.05129V34.6169H13.778C14.2089 34.6169 14.5585 34.2673 14.5585 33.8364C14.5585 33.4055 14.2089 33.056 13.778 33.056H5.27076C4.83982 33.056 4.49023 33.4055 4.49023 33.8364V41.7011C4.49023 42.132 4.83982 42.4816 5.27076 42.4816H13.778C14.2089 42.4816 14.5585 42.132 14.5585 41.7011V37.4354L15.7672 36.3384C16.0866 36.0487 16.1102 35.5546 15.8205 35.2358C15.5309 34.9165 15.0373 34.8923 14.7179 35.1819Z" fill="#1E293B" />
                                    <path d="M43.6607 39.8796C43.2297 39.8796 42.8802 40.2292 42.8802 40.6601V49.2195C42.8802 49.6504 43.2297 50 43.6607 50C44.0916 50 44.4412 49.6504 44.4412 49.2195V40.6601C44.4412 40.2287 44.0916 39.8796 43.6607 39.8796Z" fill="#1E293B" />
                                    <path d="M28.055 22.708H18.5397C18.1088 22.708 17.7592 23.0576 17.7592 23.4885C17.7592 23.9194 18.1088 24.269 18.5397 24.269H28.055C28.486 24.269 28.8356 23.9194 28.8356 23.4885C28.8356 23.0576 28.486 22.708 28.055 22.708Z" fill="#1E293B" />
                                    <path d="M44.2252 32.3947C42.3541 31.9309 40.4852 31.645 38.6268 31.5362C41.2988 30.6128 43.2704 27.4931 43.2704 23.7958C43.2704 19.7812 40.8623 17.2876 36.9861 17.2876C35.5492 17.2876 34.3147 17.6305 33.3291 18.2659V0.780477C33.3291 0.349566 32.9795 0 32.5486 0H0.780529C0.349589 0 0 0.349566 0 0.780477V46.1938C0 46.6247 0.349589 46.9743 0.780529 46.9743H23.9688V49.2195C23.9688 49.6504 24.3184 50 24.7493 50C25.1803 50 25.5299 49.6504 25.5299 49.2195V38.1049C25.5299 35.6145 28.0309 34.4482 30.129 33.9084C34.5961 32.7603 39.2122 32.7608 43.8492 33.9101C45.5631 34.3349 48.4389 35.4452 48.4389 38.1049V49.2195C48.4389 49.6504 48.7885 50 49.2195 50C49.6504 50 50 49.6504 50 49.2195V38.1049C50.0005 35.3864 47.8953 33.3049 44.2252 32.3947ZM29.7403 32.3964C26.0185 33.3539 23.9688 35.3809 23.9688 38.1049V45.4133H1.56106V1.56095H31.7675V19.8153C31.0755 20.8662 30.699 22.2117 30.699 23.7958C30.699 25.4161 31.0738 26.9655 31.7675 28.2791V31.9523C31.0887 32.076 30.4131 32.2238 29.7403 32.3964ZM33.3291 31.7127V30.3287C33.9321 30.8789 34.6021 31.2845 35.3151 31.5313C34.6511 31.5686 33.9887 31.6297 33.3291 31.7127ZM32.26 23.7958C32.26 20.6519 33.9827 18.8491 36.9855 18.8491C39.9872 18.8491 41.7083 20.6524 41.7083 23.7958C41.7083 27.3568 39.5898 30.2539 36.9855 30.2539C34.3801 30.2539 32.26 27.3568 32.26 23.7958Z" fill="#1E293B" />
                                    <path d="M28.055 4.49269H18.5397C18.1088 4.49269 17.7592 4.84226 17.7592 5.27317C17.7592 5.70408 18.1088 6.05365 18.5397 6.05365H28.055C28.486 6.05365 28.8356 5.70408 28.8356 5.27317C28.8356 4.84226 28.486 4.49269 28.055 4.49269Z" fill="#1E293B" />
                                    <path d="M28.055 18.7743H18.5397C18.1088 18.7743 17.7592 19.1239 17.7592 19.5548C17.7592 19.9857 18.1088 20.3353 18.5397 20.3353H28.055C28.486 20.3353 28.8356 19.9857 28.8356 19.5548C28.8356 19.1239 28.486 18.7743 28.055 18.7743Z" fill="#1E293B" />
                                    <path d="M28.055 8.42641H18.5397C18.1088 8.42641 17.7592 8.77597 17.7592 9.20688C17.7592 9.6378 18.1088 9.98736 18.5397 9.98736H28.055C28.486 9.98736 28.8356 9.6378 28.8356 9.20688C28.8356 8.77597 28.486 8.42641 28.055 8.42641Z" fill="#1E293B" />
                                    <path d="M22.2885 33.0554H18.5403C18.1094 33.0554 17.7598 33.405 17.7598 33.8359C17.7598 34.2668 18.1094 34.6164 18.5403 34.6164H22.2885C22.7194 34.6164 23.069 34.2668 23.069 33.8359C23.069 33.405 22.7194 33.0554 22.2885 33.0554Z" fill="#1E293B" />
                                </svg> Performance Evaluation and Discussion Management (PEDMA)</h4>
                            <hr>
                        </div>
                        <div class="col-md-3 p-0" style="margin-left: auto;">
                            <div class="text-end" style="margin-bottom: 8px;">
                                <span id="pedmaExportMeta" class="pedma-export-meta-top" style="display:inline-block; margin-right:8px; font-size:12px; color:#64748b; vertical-align:middle;"></span>
                                <button type="button" id="pedmaExportBtn" class="btn btn-primary btn-sm">
                                    <i class="fa fa-download"></i> Export CSV
                                </button>
                            </div>
                            <div class="bg-primary text-white px-4 py-2 rounded pedma-score-card" style="padding:10px; display:flex; align-items:center; justify-content:space-between; gap:12px;">
                                <div class="pedma-days-wrap" style="text-align:left;">
                                    <div style="font-size:11px; opacity:0.9; text-transform:uppercase; letter-spacing:0.3px;">Days of data</div>
                                    <h4 id="pedma_days_of_data" style="margin:4px 0 0; font-size:20px;">-</h4>
                                </div>
                                <div style="text-align:right;">
                                    <span>OVERALL PERFORMANCE SCORE</span>
                                    <h4 id="avg_score" style="margin:4px 0 0;"></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="margin-bottom: 16px;">
                        <div class="col-md-12 p-0">
                            <div class="pedma-filters-row">
                                <div class="pedma-filter-item pedma-filter-dept">
                                    <label for="departmentFilter">Department</label>
                                    <form id="multiSelectForm" style="margin:0;">
                                        <select id="departmentFilter" name="department" class="form-control selectpicker" aria-label="Select Department" data-live-search="true" data-none-selected-text="Filter by department" data-width="100%">
                                            <option value="">Filter by department</option>
                                            <?php foreach ($result as $res) { ?>
                                                <option value="<?php echo $res['departmentid']; ?>"><?php echo $res['name']; ?></option>
                                            <?php  } ?>
                                        </select>
                                    </form>
                                </div>
                                <div class="pedma-filter-item pedma-filter-emp">
                                    <label for="designationFilter">Employee</label>
                                    <select id="designationFilter" name="employee" class="form-control selectpicker" aria-label="Select Staff" data-live-search="true" data-none-selected-text="Filter By Employee" data-width="100%">
                                    </select>
                                </div>
                                <div class="pedma-filter-item pedma-filter-staff-status">
                                    <label for="pedma_staff_active_filter">Staff Status</label>
                                    <select id="pedma_staff_active_filter" class="form-control">
                                        <option value="1" selected>Active</option>
                                        <option value="0">Inactive</option>
                                        <option value="all">All</option>
                                    </select>
                                </div>
                                <div class="pedma-filter-item pedma-filter-period">
                                    <label for="pedma_range_preset">Period</label>
                                    <select id="pedma_range_preset" class="form-control">
                                        <option value="current_month">Current month</option>
                                        <option value="last_3">Last 3 months</option>
                                        <option value="last_6" selected>Last 6 months</option>
                                        <option value="last_12">Last 12 months</option>
                                        <option value="custom">Custom range</option>
                                    </select>
                                </div>
                                <div class="pedma-filter-item pedma-filter-from">
                                    <label for="performance_from">From</label>
                                    <input type="month" id="performance_from" name="performance_from" class="form-control" />
                                </div>
                                <div class="pedma-filter-item pedma-filter-to">
                                    <label for="performance_to">To</label>
                                    <input type="month" id="performance_to" name="performance_to" class="form-control" />
                                </div>
                                <?php if (function_exists('can_view_pedma_grade_filter') && can_view_pedma_grade_filter()) { ?>
                                <div class="pedma-filter-item pedma-filter-grade">
                                    <label for="pedma_grade_filter">Filter by Grade</label>
                                    <select id="pedma_grade_filter" class="form-control">
                                        <option value="">Filter by Grade</option>
                                        <option value="A+">A+ (98-100)</option>
                                        <option value="A">A (90-97)</option>
                                        <option value="B">B (80-89)</option>
                                        <option value="C">C (70-79)</option>
                                        <option value="D">D (&lt;70)</option>
                                    </select>
                                </div>
                                <?php } ?>
                                <input type="hidden" id="performance_month" name="performance_month" value="" />
                            </div>
                        </div>
                    </div>
                    <?php if (can_manage_pedma()) { ?>
                    <div class="row" id="pedmaGradeResultsWrap" style="display:none; margin-bottom: 20px;" data-can-schedule="<?php echo (function_exists('can_view_pedma_grade_filter') && can_view_pedma_grade_filter()) ? '1' : '0'; ?>">
                        <div class="col-md-12 p-0">
                            <div class="card">
                                <div class="card-title" style="display:flex;justify-content:space-between;align-items:center; gap:10px;">
                                    <span id="pedmaListResultsHeading">Department data — <span id="pedmaGradeResultsTitle">-</span></span>
                                    <span style="display:flex; align-items:center; gap:12px;">
                                        <span id="pedmaGradeResultsCount" style="font-weight:500; opacity:0.9;"></span>
                                        <button type="button" class="btn btn-default btn-xs" id="pedmaGradeCloseBtn" title="Close">Close</button>
                                    </span>
                                </div>
                                <div class="card-body" style="padding:0; overflow-x:auto;">
                                    <table class="table table-bordered table-striped" id="pedmaGradeTable" style="margin:0;">
                                        <thead>
                                            <tr>
                                                <th>Emp ID</th>
                                                <th>Emp Name</th>
                                                <th>Department</th>
                                                <th>Month</th>
                                                <th>Score</th>
                                                <th>Grade</th>
                                                <th>HR Notes</th>
                                                <?php if (function_exists('can_view_pedma_grade_filter') && can_view_pedma_grade_filter()) { ?>
                                                <th style="min-width:150px;">Schedule Meeting</th>
                                                <?php } ?>
                                            </tr>
                                        </thead>
                                        <tbody id="pedmaGradeTableBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="row" id="twoChart">
                        <!-- KRA Performance Chart Card -->
                        <div class="col-md-8 mb-4 p-i-4">
                            <div class="card">
                                <div class="card-body">
                                    <canvas id="kraChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Doughnut Chart for KRA Distribution -->
                        <div class="col-md-4 mb-4 p-i-4">
                            <div class="card">
                                <div class="card-body">
                                    <canvas id="kraDoughnutChart"></canvas>
                                    <div id="kraDoughnutLegend" class="pedma-doughnut-legend"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8 p-0">
                            <div class="row g-2" style="row-gap: 10px; margin:0;" id="kra-card-data">

                            </div>
                        </div>
                        <div class="col-sm-12 col-md-4 p-i-4 performance_card mt-md-0 mt-2">
                            <div class="card h-100">
                                <div class="card-title">
                                    Overall Feedback
                                </div>
                                <div class="card-body pb-3 pt-1 comment" id="overall_feedback"></div>
                                <div id="feedback_ack_manager_status" class="px-3 pb-3" style="display:none;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row tw-my-5">
                        <div class="col p-0">
                            <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-flex tw-items-center">
                                PEDMA Scores for Year &nbsp;<span class="selected_year"></span>
                            </h4>
                            <select id="performance_year" name="performance_year" class="form-control">
                                <option value="">Select Year</option>
                            </select>
                        </div>
                        <div class="col-md-12 p-0" style="margin-top: 10px;">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Month</th>
                                        <th colspan="2">Details (Click to Expand)</th>
                                        <th>Comment</th>
                                    </tr>
                                </thead>
                                <tbody id="kraTableBody">
                                    <!-- Table content will be generated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="popup" id="popup">
        <div class="popup-content">
            <div class="close-btn"><i class="fa-solid fa-circle-xmark fa-xl"></i></div>
            <h3 id="popup-title" style="margin:0;"></h3>
            <hr>
            <div id="popup-content" style="margin-top: 10px; color:#000;"></div>
        </div>
    </div>

    <!-- The Modal -->
    <div class="modal" id="myModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <?php
                echo form_open('admin/staff/pedma_staff_reply', array('id' => 'pedma-form-staff-reply'));
                ?>
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Reply to Manager</h4>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="hidden" name="staffid" value="<?php echo $staffid; ?>">
                            <label>Month</label>
                            <input type="text" class="form-control" id="replyMonth" readonly required name="month">
                        </div>
                        <div class="col-md-4">
                            <label>Year</label>
                            <input type="text" class="form-control" id="replyYear" readonly required name="year">
                        </div>
                        <div class="col-md-4">
                            <label>Score</label>
                            <input type="text" class="form-control" id="replyScore" readonly required name="score">
                        </div>
                        <div class="col-md-12" style="margin-top: 5px;">
                            <textarea class="form-control" name="comment" required placeholder="Please write your comment related to pedma score..." rows="5"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Send</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal" id="messageViewModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Your Comment</h4>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <p id="replyMessageView"></p>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>

    <input type="hidden" id="staffData" value="">
    <div class="loader hidden">
        <img src="https://www.icegif.com/wp-content/uploads/2023/07/icegif-1263.gif" alt="Loading...">
    </div>
    <?php init_tail(); ?>


    <!-- Bootstrap JS and custom JavaScript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.3/umd/popper.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>

        $(document).ready(function() {
            function loadPedmaEmployeesByDepartment() {
                $('.loader').removeClass('hidden');
                var value = $("#departmentFilter").val();
                var staffActive = ($('#pedma_staff_active_filter').val() || '1').toString();
                if (!value) {
                    $('#designationFilter').html('<option value="">Filter By Employee</option>');
                    $('#designationFilter').selectpicker('refresh');
                    $('.loader').addClass('hidden');
                    return;
                }

                $.ajax({
                    url: "<?php echo base_url('admin/UtilizationReport/getDeptemp'); ?>",
                    data: {
                        department: value,
                        staff_active: staffActive
                    },
                    type: "post",
                    success: function(response) {
                        $('.loader').addClass('hidden');
            
                        let dept = JSON.parse(response);
                        let options = '';
                        for (var i = 0; i < dept.length; i++) {
                            var first = (dept[i]['firstname'] || '').trim();
                            var last = (dept[i]['lastname'] || '').trim();
                            var fullName = (first + ' ' + last).trim();
                            var initials = ((first.charAt(0) || '') + (last.charAt(0) || '')).toLowerCase();
                            var empCode = (dept[i]['staff_identifi'] || '').toString().trim();
                            var statusTag = String(dept[i]['active']) === '1' ? '' : ' (Inactive)';
                            var tokens = [
                                fullName.toLowerCase(),
                                initials,
                                empCode.toLowerCase(),
                                String(dept[i]['staffid'])
                            ].join(' ');
                            options += '<option class="empid' + dept[i]['staffid'] + '" value="' + dept[i]['staffid'] + '" data-tokens="' + tokens.replace(/"/g, '&quot;') + '">' + fullName + (empCode ? ' [' + empCode + ']' : '') + statusTag + '</option>';
                        }
    
                        $('#designationFilter').html('<option value="">Filter By Employee</option>' + options);
                        $('#designationFilter').selectpicker('refresh');
                        $('#staffData').val('');
                        // Department only → show all PEDMA data for this department
                        if (typeof loadPedmaListResults === 'function') {
                            loadPedmaListResults();
                        }
                    }

                });
            }

            $('#departmentFilter').change(function() {
                loadPedmaEmployeesByDepartment();
            });

            // Ensure employee list is available even when a department is pre-selected.
            setTimeout(function () {
                if ($('#departmentFilter').val()) {
                    loadPedmaEmployeesByDepartment();
                }
            }, 100);
        });
    </script>

    <script>
        function monthKeyFromDate(d) {
            var y = d.getFullYear();
            var m = String(d.getMonth() + 1).padStart(2, '0');
            return y + '-' + m;
        }

        function addMonthsToKey(monthKey, delta) {
            var parts = monthKey.split('-').map(Number);
            var d = new Date(parts[0], parts[1] - 1 + delta, 1);
            return monthKeyFromDate(d);
        }

        function compareMonthKeys(a, b) {
            if (a === b) return 0;
            return a < b ? -1 : 1;
        }

        function getPedmaSelectedRange() {
            var from = $('#performance_from').val() || '';
            var to = $('#performance_to').val() || '';
            if (from && to && compareMonthKeys(from, to) > 0) {
                var tmp = from;
                from = to;
                to = tmp;
                $('#performance_from').val(from);
                $('#performance_to').val(to);
            }
            $('#performance_month').val(to || from || '');
            return { from: from, to: to };
        }

        function applyPedmaRangePreset(preset, triggerLoad) {
            var now = new Date();
            var to = monthKeyFromDate(now);
            var from = to;
            if (preset === 'last_3') {
                from = addMonthsToKey(to, -2);
            } else if (preset === 'last_6') {
                from = addMonthsToKey(to, -5);
            } else if (preset === 'last_12') {
                from = addMonthsToKey(to, -11);
            } else if (preset === 'current_month') {
                from = to;
            }
            // custom: leave inputs as-is
            if (preset !== 'custom') {
                $('#performance_from').val(from);
                $('#performance_to').val(to);
                $('#performance_month').val(to);
            }
            $('#performance_from, #performance_to').attr('max', to);
            if (triggerLoad) {
                reloadPedmaForCurrentFilters();
            }
        }

        function filterStaffDataByRange(staffData, from, to) {
            if (!Array.isArray(staffData) || !from || !to) {
                return [];
            }
            return staffData.filter(function (item) {
                if (!item.date_created) return false;
                var key = monthKeyFromDate(new Date(item.date_created));
                return compareMonthKeys(key, from) >= 0 && compareMonthKeys(key, to) <= 0;
            }).sort(function (a, b) {
                return new Date(a.date_created) - new Date(b.date_created);
            });
        }

        function computeFinalScoreNumber(row) {
            var ascore = parseFloat(row.avg_score || 0) || 0;
            var fscore = row.fatal_error_score ? (parseFloat(row.fatal_error_score) / 100) * ascore : 0;
            var addscore = row.add_on_score ? (parseFloat(row.add_on_score) / 100) * ascore : 0;
            return ascore - fscore + addscore;
        }

        function getPedmaDaysOfData(selectedMonth) {
            if (!selectedMonth || !/^\d{4}-\d{2}$/.test(selectedMonth)) {
                return { days: 0, total: 0, label: '-' };
            }
            var parts = selectedMonth.split('-').map(Number);
            var year = parts[0];
            var month = parts[1]; // 1-12
            var totalDays = new Date(year, month, 0).getDate();
            var now = new Date();
            var curY = now.getFullYear();
            var curM = now.getMonth() + 1;
            var curD = now.getDate();
            var days = 0;

            if (year < curY || (year === curY && month < curM)) {
                days = totalDays; // full past month
            } else if (year === curY && month === curM) {
                days = Math.min(curD, totalDays); // month so far
            } else {
                days = 0; // future month
            }

            return {
                days: days,
                total: totalDays,
                label: String(totalDays)
            };
        }

        function setPedmaDaysOfDataRange(from, to, hasData) {
            if (!hasData || !from || !to) {
                $('#pedma_days_of_data').html('-');
                return;
            }
            var days = 0;
            var total = 0;
            var cursor = from;
            while (compareMonthKeys(cursor, to) <= 0) {
                var info = getPedmaDaysOfData(cursor);
                days += info.days;
                total += info.total;
                cursor = addMonthsToKey(cursor, 1);
            }
            $('#pedma_days_of_data').html(total + ' <small style="font-size:12px;font-weight:500;">days</small>');
        }

        function parseIsoDate(isoDate) {
            if (!isoDate || !/^\d{4}-\d{2}-\d{2}$/.test(isoDate)) {
                return null;
            }
            var parts = isoDate.split('-').map(Number);
            return new Date(parts[0], parts[1] - 1, parts[2]);
        }

        function endOfMonthDate(monthKey) {
            var parts = monthKey.split('-').map(Number);
            return new Date(parts[0], parts[1], 0);
        }

        function setPedmaDaysForEmployeeJoinDate(from, to, joinDateIso, hasData) {
            if (!hasData || !from || !to) {
                $('#pedma_days_of_data').html('-');
                return;
            }

            var joinDate = parseIsoDate(joinDateIso);
            if (!joinDate) {
                setPedmaDaysOfDataRange(from, to, hasData);
                return;
            }

            var fromParts = from.split('-').map(Number);
            var rangeStart = new Date(fromParts[0], fromParts[1] - 1, 1);
            var rangeEnd = endOfMonthDate(to);

            var now = new Date();
            if (rangeEnd > now) {
                rangeEnd = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            }

            var effectiveStart = (joinDate > rangeStart) ? joinDate : rangeStart;
            if (effectiveStart > rangeEnd) {
                $('#pedma_days_of_data').html('0 <small style="font-size:12px;font-weight:500;">days</small>');
                return;
            }

            var oneDay = 24 * 60 * 60 * 1000;
            var days = Math.floor((rangeEnd - effectiveStart) / oneDay) + 1;
            if (days < 0) days = 0;
            $('#pedma_days_of_data').html(days + ' <small style="font-size:12px;font-weight:500;">days</small>');
        }

        function reloadPedmaForCurrentFilters() {
            var range = getPedmaSelectedRange();
            var staffData = $('#staffData').val();
            var empVal = ($('#designationFilter').val() || '').toString();
            var empSelected = empVal && empVal !== 'Filter By Employee';

            if (empSelected && staffData) {
                try {
                    staffData = JSON.parse(staffData);
                    loadKraData(range.from, range.to, staffData);
                } catch (e) {
                    // ignore invalid cache
                }
            }
            loadPedmaListResults();
        }

        function isEmployeeSelected() {
            var empVal = ($('#designationFilter').val() || '').toString().trim();
            return empVal !== '' && empVal !== 'Filter By Employee';
        }

        function loadPedmaListResults() {
            if (!$('#pedmaGradeResultsWrap').length) {
                return;
            }

            var grade = ($('#pedma_grade_filter').val() || '').toString();
            var department = ($('#departmentFilter').val() || '').toString();
            var staffActive = ($('#pedma_staff_active_filter').val() || '1').toString();
            var range = getPedmaSelectedRange();

            // Show list when: grade selected, OR department selected without a single employee
            var showDeptAll = department && !isEmployeeSelected();
            if (!grade && !showDeptAll) {
                $('#pedmaGradeResultsWrap').hide();
                $('#pedmaGradeTableBody').empty();
                return;
            }

            if (!range.from || !range.to) {
                return;
            }

            $('.loader').removeClass('hidden');
            $.ajax({
                url: "<?php echo base_url('admin/Staff/manage_pedma_by_grade'); ?>",
                type: 'post',
                dataType: 'json',
                data: {
                    grade: grade,
                    from: range.from,
                    to: range.to,
                    department: department,
                    staff_active: staffActive
                },
                success: function (resp) {
                    $('.loader').addClass('hidden');
                    if (!resp || !resp.success) {
                        alert_float('danger', (resp && resp.message) ? resp.message : 'Unable to load department data.');
                        return;
                    }
                    renderPedmaListTable(resp);
                },
                error: function () {
                    $('.loader').addClass('hidden');
                    alert_float('danger', 'Unable to load department data.');
                }
            });
        }

        // Back-compat alias
        function loadPedmaGradeResults() {
            loadPedmaListResults();
        }

        function renderPedmaListTable(resp) {
            var rows = resp.rows || [];
            var gradeLabel = {
                'A+': 'A+ (98-100)',
                'A': 'A (90-97)',
                'B': 'B (80-89)',
                'C': 'C (70-79)',
                'D': 'D (<70)'
            };
            var canSchedule = $('#pedmaGradeResultsWrap').attr('data-can-schedule') === '1';
            var colCount = canSchedule ? 8 : 7;
            var deptName = ($('#departmentFilter option:selected').text() || 'Department').trim();

            if (resp.mode === 'grade' && resp.grade) {
                $('#pedmaListResultsHeading').html('Grade filter — <span id="pedmaGradeResultsTitle"></span>');
                $('#pedmaGradeResultsTitle').text(gradeLabel[resp.grade] || resp.grade);
            } else {
                $('#pedmaListResultsHeading').html('Department data — <span id="pedmaGradeResultsTitle"></span>');
                $('#pedmaGradeResultsTitle').text(deptName);
            }

            $('#pedmaGradeResultsCount').text(rows.length + ' record(s)');
            var body = $('#pedmaGradeTableBody');
            body.empty();

            if (!rows.length) {
                body.html('<tr><td colspan="' + colCount + '" class="text-center">No PEDMA data found for the selected filters.</td></tr>');
                $('#pedmaGradeResultsWrap').show();
                $('#avg_score').html('-');
                setPedmaDaysOfDataRange(getPedmaSelectedRange().from, getPedmaSelectedRange().to, false);
                return;
            }

            rows.forEach(function (row) {
                var tr = $('<tr></tr>');
                tr.append($('<td></td>').text(row.emp_id || ''));
                tr.append($('<td></td>').text(row.emp_name || ''));
                tr.append($('<td></td>').text(row.department || '-'));
                tr.append($('<td></td>').text(row.month_label || row.month || ''));
                tr.append($('<td></td>').html('<b>' + (row.score || '-') + '%</b>'));
                tr.append($('<td></td>').text(row.grade || '-'));
                tr.append($('<td class="hr-notes-cell"></td>').text(row.hr_notes || '-'));
                if (canSchedule) {
                    var btn = $('<button type="button" class="btn btn-info btn-sm pedma-schedule-meeting-btn"></button>')
                        .html('<i class="fa fa-calendar"></i> Schedule Meeting')
                        .attr('data-staffid', row.staffid)
                        .attr('data-month-label', row.month_label || '')
                        .attr('data-score', row.score || '')
                        .attr('data-grade', row.grade || '');
                    tr.append($('<td></td>').append(btn));
                }
                body.append(tr);
            });

            $('#pedmaGradeResultsWrap').show();

            // Department overview score when no single employee is selected
            if (!isEmployeeSelected() && resp.avg_score !== undefined) {
                $('#avg_score').html(resp.avg_score + '%');
                var range = getPedmaSelectedRange();
                setPedmaDaysOfDataRange(range.from, range.to, true);
                renderDepartmentOverviewCharts(rows);
            }
        }

        function renderDepartmentOverviewCharts(rows) {
            // Latest score per employee for chart
            var byEmp = {};
            rows.forEach(function (row) {
                var key = String(row.staffid);
                if (!byEmp[key] || String(row.date_created) > String(byEmp[key].date_created)) {
                    byEmp[key] = row;
                }
            });
            var list = Object.keys(byEmp).map(function (k) { return byEmp[k]; });
            list.sort(function (a, b) { return parseFloat(b.score) - parseFloat(a.score); });

            var labels = list.map(function (r) { return r.emp_name; });
            var scores = list.map(function (r) { return parseFloat(r.score) || 0; });

            $('#twoChart').show();
            if (kraChart) kraChart.destroy();
            if (kraDoughnutChart) kraDoughnutChart.destroy();

            var kraCtx = document.getElementById('kraChart').getContext('2d');
            kraChart = new Chart(kraCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Score %',
                        data: scores,
                        backgroundColor: '#4CAF50',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, max: 100, grid: { display: true }, title: { display: true, text: 'Score %' } }
                    },
                    plugins: { title: { display: true, text: 'Department PEDMA Scores' } }
                }
            });

            var doughnutColors = ['#FF6384', '#36A2EB', '#FFCE56', '#8C9EFF', '#4CAF50', '#FF9F40', '#9966FF'];
            var gradeCounts = { 'A+': 0, 'A': 0, 'B': 0, 'C': 0, 'D': 0 };
            list.forEach(function (r) {
                var g = r.grade || 'D';
                if (gradeCounts[g] === undefined) gradeCounts[g] = 0;
                gradeCounts[g] += 1;
            });
            var gLabels = Object.keys(gradeCounts).filter(function (k) { return gradeCounts[k] > 0; });
            var gData = gLabels.map(function (k) { return gradeCounts[k]; });

            var kraDoughnutCtx = document.getElementById('kraDoughnutChart').getContext('2d');
            kraDoughnutChart = new Chart(kraDoughnutCtx, {
                type: 'doughnut',
                data: {
                    labels: gLabels,
                    datasets: [{
                        data: gData,
                        backgroundColor: doughnutColors,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    plugins: {
                        title: { display: true, text: 'Grade Distribution' },
                        legend: { display: false }
                    },
                    legend: { display: false }
                }
            });

            var legendHtml = '';
            gLabels.forEach(function (name, idx) {
                legendHtml +=
                    '<div class="legend-row">' +
                        '<div class="legend-left">' +
                            '<span class="swatch" style="background:' + doughnutColors[idx % doughnutColors.length] + ';"></span>' +
                            '<span class="legend-name">Grade ' + name + '</span>' +
                        '</div>' +
                        '<span class="legend-score">' + gradeCounts[name] + ' emp</span>' +
                    '</div>';
            });
            $('#kraDoughnutLegend').html(legendHtml);
            $('#pedmaExportMeta').html('<strong>Department overview</strong> · ' + list.length + ' employees');
            $('#overall_feedback').html(rows.length + ' PEDMA record(s) in selected department / period.');
            $('#feedback_ack_manager_status').hide().empty();
            $('#kra-card-data').html('<div class="col-md-12"><p class="text-muted">Select an employee for detailed KRA cards.</p></div>');
        }

        function safeParseKraData(rawValue) {
            if (Array.isArray(rawValue)) {
                return rawValue;
            }
            if (!rawValue) {
                return [];
            }
            try {
                const parsed = JSON.parse(rawValue);
                return Array.isArray(parsed) ? parsed : [];
            } catch (e) {
                return [];
            }
        }

        $(document).on('click', '.commentbutton', function(e) {
            e.preventDefault();
            var month = $(this).data('month');
            var year = $(this).data('year');
            var score = $(this).data('score');

            $("#replyMonth").val(month);
            $("#replyYear").val(year);
            $("#replyScore").val(score + "%");

            $("#myModal").modal('show'); // Opens the modal with ID 'myModal'
        });

        $(document).on('click', '.commentViewbutton', function(e) {
            e.preventDefault();
            var comment = $(this).data('comment');

            $("#replyMessageView").html(comment);

            $("#messageViewModal").modal('show'); // Opens the modal with ID 'myModal'
        });


        $(window).on('load', function() {
            applyPedmaRangePreset($('#pedma_range_preset').val() || 'last_6', false);
            $('#performance_month').trigger('change');
        });

        $(document).ready(function() {
            $('[data-toggle="popover"]').popover({
                trigger: 'click', // Show popover on click
            });
            $('[data-toggle="popover"]').on('shown.bs.popover', function() {
                $('.popover').css('opacity', 1);
            });
        });

        let kraChart = null; // Define chart instances outside the change event
        let kraDoughnutChart = null;

        $(document).ready(function () {
            $('#pedma_range_preset').on('change', function () {
                var preset = $(this).val();
                if (preset === 'custom') {
                    return;
                }
                applyPedmaRangePreset(preset, true);
            });

            $('#performance_from, #performance_to').on('change', function () {
                $('#pedma_range_preset').val('custom');
                reloadPedmaForCurrentFilters();
            });

            $('#performance_month').on('change', function () {
                reloadPedmaForCurrentFilters();
            });

            $('#pedma_grade_filter').on('change', function () {
                loadPedmaListResults();
            });

            $('#pedmaGradeCloseBtn').on('click', function () {
                $('#pedma_grade_filter').val('');
                loadPedmaListResults();
            });

            $('#departmentFilter').on('changed.bs.select', function () {
                // Employee list reload is handled in the other ready(); also refresh dept table
                setTimeout(function () {
                    if (typeof loadPedmaListResults === 'function') {
                        loadPedmaListResults();
                    }
                }, 50);
            });

            $('#pedma_staff_active_filter').on('change', function () {
                // refresh employee list for selected department and reset selected employee
                $('#designationFilter').val('');
                $('#designationFilter').selectpicker('refresh');
                if ($('#departmentFilter').val()) {
                    $('#departmentFilter').trigger('change');
                } else {
                    loadPedmaListResults();
                }
            });

            $(document).on('click', '.pedma-schedule-meeting-btn', function () {
                var btn = $(this);
                if (!confirm('Send a PEDMA meeting request email to this employee?')) {
                    return;
                }
                btn.prop('disabled', true);
                $.ajax({
                    url: "<?php echo base_url('admin/Staff/pedma_schedule_meeting'); ?>",
                    type: 'post',
                    dataType: 'json',
                    data: {
                        staffid: btn.data('staffid'),
                        month_label: btn.data('month-label'),
                        score: btn.data('score'),
                        grade: btn.data('grade')
                    },
                    success: function (resp) {
                        btn.prop('disabled', false);
                        if (resp && resp.success) {
                            alert_float('success', resp.message || 'Meeting request sent.');
                        } else {
                            alert_float('danger', (resp && resp.message) ? resp.message : 'Failed to send meeting request.');
                        }
                    },
                    error: function () {
                        btn.prop('disabled', false);
                        alert_float('danger', 'Failed to send meeting request.');
                    }
                });
            });

            $('#designationFilter').change(function () {
                $('.loader').removeClass('hidden');
                var value = $("#designationFilter").val();
                let currentYear = new Date().getFullYear();
                var range = getPedmaSelectedRange();

                if (!isEmployeeSelected()) {
                    $('.loader').addClass('hidden');
                    $('#staffData').val('');
                    loadPedmaListResults();
                    return;
                }

                $.ajax({
                    url: "<?php echo base_url('admin/Staff/manage_pedma_staff'); ?>",
                    data: { data: value },
                    type: "post",
                    success: function (response) {
                        $('.loader').addClass('hidden');
                        $("#staffData").val(response);
                        if (response) {
                            response = JSON.parse(response); // Convert to JSON
                            loadKraData(range.from, range.to, response);
                            loadPerformanceData(currentYear);
                        }
                        if (!($('#pedma_grade_filter').val() || '')) {
                            $('#pedmaGradeResultsWrap').hide();
                        } else {
                            loadPedmaListResults();
                        }
                    }
                });
            });
        });

        function loadKraData(fromMonth, toMonth, staffData) {
            const kraCardData = $("#kra-card-data");
            kraCardData.empty();

            // Back-compat: old calls passed a single month as first arg only
            if (arguments.length === 2) {
                staffData = toMonth;
                toMonth = fromMonth;
            }

            var from = fromMonth || '';
            var to = toMonth || from;

            if (!from || !to || !staffData || !staffData.length) {
                kraCardData.html("No Data available for this period");
                $('#avg_score').html('-');
                setPedmaDaysOfDataRange(from, to, false);
                $('#overall_feedback').html('-');
                $('#feedback_ack_manager_status').hide().empty();
                $('#twoChart').hide();
                $('#kraDoughnutLegend').empty();
                $('#pedmaExportMeta').empty();
                return;
            }

            const filteredData = filterStaffDataByRange(staffData, from, to);
            const isRange = from !== to;

            let cardHTML = '';

            if (filteredData.length === 0) {
                kraCardData.html("No Data available for this period");
                $('#avg_score').html('-');
                setPedmaDaysOfDataRange(from, to, false);
                $('#overall_feedback').html('-');
                $('#feedback_ack_manager_status').hide().empty();
                $('#twoChart').hide();
                $('#kraDoughnutLegend').empty();
                $('#pedmaExportMeta').empty();
                return;
            } else {
                $('#twoChart').show();
                var selectedJoinDate = (filteredData[0] && filteredData[0].staff_join_date) ? filteredData[0].staff_join_date : '';
                setPedmaDaysForEmployeeJoinDate(from, to, selectedJoinDate, true);
                var empLabel = ($('#designationFilter option:selected').text() || '').trim();
                var rangeLabel = isRange ? (from + ' → ' + to) : from;
                var scoreValues = filteredData.map(computeFinalScoreNumber);
                var avgAcross = (scoreValues.reduce(function (s, n) { return s + n; }, 0) / scoreValues.length).toFixed(2);
                $('#pedmaExportMeta').html(
                    '<strong>Export</strong> ' +
                    (empLabel ? empLabel : 'Employee') +
                    ' · ' + rangeLabel +
                    ' · Avg ' + avgAcross + '%'
                );

                if (isRange) {
                    // Month summary cards for the selected range
                    filteredData.forEach(function (data) {
                        var monthLabel = new Date(data.date_created).toLocaleString('en-US', { month: 'long', year: 'numeric' });
                        var score = computeFinalScoreNumber(data).toFixed(2);
                        cardHTML += `
                            <div class="col-sm-6 col-md-4 p-i-4 performance_card">
                                <div class="card h-100">
                                    <h5 class="card-title">${monthLabel}</h5>
                                    <div class="card-body">
                                        <div class="score high">${score}%</div>
                                        <div class="hr"></div>
                                        <div class="comment"><span style="font-weight:bold;">Feedback : </span>${data.overall_feedback || '-'}</div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    kraCardData.html(cardHTML);

                    $('#avg_score').html(avgAcross + '%');
                    $('#overall_feedback').html(
                        filteredData.length + ' month(s) in range. Showing latest feedback below.<br><br>' +
                        (filteredData[filteredData.length - 1].overall_feedback || '-')
                    );
                    $('#feedback_ack_manager_status').hide().empty();

                    if (kraChart) kraChart.destroy();
                    if (kraDoughnutChart) kraDoughnutChart.destroy();

                    var monthLabels = filteredData.map(function (row) {
                        return new Date(row.date_created).toLocaleString('en-US', { month: 'short', year: '2-digit' });
                    });
                    var monthScores = scoreValues;

                    const kraCtx = document.getElementById('kraChart').getContext('2d');
                    kraChart = new Chart(kraCtx, {
                        type: 'bar',
                        data: {
                            labels: monthLabels,
                            datasets: [{
                                label: 'Overall Score %',
                                data: monthScores,
                                backgroundColor: '#4CAF50',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                x: { grid: { display: false } },
                                y: { beginAtZero: true, max: 100, grid: { display: true }, title: { display: true, text: 'Score %' } }
                            },
                            plugins: { title: { display: true, text: 'PEDMA Score by Month (' + from + ' to ' + to + ')' } }
                        }
                    });

                    const doughnutColors = ['#FF6384', '#36A2EB', '#FFCE56', '#8C9EFF', '#4CAF50', '#FF9F40', '#9966FF'];
                    const kraDoughnutCtx = document.getElementById('kraDoughnutChart').getContext('2d');
                    const totalScore = monthScores.reduce(function (s, n) { return s + (Number(n) || 0); }, 0) || 1;
                    kraDoughnutChart = new Chart(kraDoughnutCtx, {
                        type: 'doughnut',
                        data: {
                            labels: monthLabels,
                            datasets: [{
                                data: monthScores,
                                backgroundColor: doughnutColors,
                                hoverBackgroundColor: doughnutColors,
                                borderWidth: 2,
                                borderColor: '#fff'
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                title: { display: true, text: 'Score Share by Month' },
                                legend: { display: false }
                            },
                            legend: { display: false }
                        }
                    });

                    var legendHtml = '';
                    monthLabels.forEach(function (name, idx) {
                        var score = Number(monthScores[idx] || 0);
                        var pct = ((score / totalScore) * 100).toFixed(1);
                        var color = doughnutColors[idx % doughnutColors.length];
                        legendHtml +=
                            '<div class="legend-row">' +
                                '<div class="legend-left">' +
                                    '<span class="swatch" style="background:' + color + ';"></span>' +
                                    '<span class="legend-name">' + name + '</span>' +
                                '</div>' +
                                '<span class="legend-score">' + score.toFixed(2) + '% · ' + pct + '%</span>' +
                            '</div>';
                    });
                    $('#kraDoughnutLegend').html(legendHtml);
                    return;
                }

                // Single-month detail view (existing behaviour)
                filteredData.forEach((data) => {
                    let kra_data = safeParseKraData(data.kra_data);

                    kra_data.forEach((item) => {
                        let totalKpiMaxScore = 0;
                        let totalKpiGetScore = 0;

                        if (data.type === "custom" && item.kpiData) {
                            item.kpiData.forEach(kpi => {
                                totalKpiMaxScore += parseFloat(kpi.max_score);
                                totalKpiGetScore += parseFloat(kpi.get_score);
                            });
                        }

                        const scoreToDisplay = (data.type === "custom" && item.kpiData) ? totalKpiGetScore : item.get_score;

                        cardHTML += `
                            <div class="col-sm-6 col-md-4 p-i-4 performance_card">
                                <div class="card h-100">
                                    <h5 class="card-title">
                                        ${item.name}
                                        <i class="fa-regular fa-circle-question ml-2 info-icon" data-toggle="popover" data-bs-content="${item.description}"></i>
                                    </h5>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="score high">${scoreToDisplay}/${item.max_score}</div>
                                                <div class="hr"></div>
                        `;

                        if (data.type === "custom" && item.kpiData.length > 0) {
                            cardHTML += `<p class="h4">KPI Details :</p>`;
                            item.kpiData.forEach((kpi) => {
                                cardHTML += `
                                    <p class="mb-0"><b>${kpi.name}: ${kpi.get_score}/${kpi.max_score}</b></p>
                                    <p class="">Manager Comment: ${kpi.comment || '-'}</p>
                                    <p class="">HR Remarks: ${kpi.hr_remarks || '-'}</p>
                                `;
                            });
                        } else {
                            cardHTML += `
                                <div class="comment">
                                    <span style="font-weight: bold;">Manager Comment : </span>${item.comment || '-'}
                                </div>
                                <div class="comment">
                                    <span style="font-weight: bold;">HR Remarks : </span>${item.hr_remarks || '-'}
                                </div>
                            `;
                        }

                        cardHTML += `
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                });

                kraCardData.html(cardHTML);
                $('[data-toggle="popover"]').popover();

                // Ensure there's valid data before calculating scores
                if (filteredData[0]) {
                    var ascore = filteredData[0].avg_score || 0;
                    var fscore = filteredData[0].fatal_error_score ? (filteredData[0].fatal_error_score / 100) * ascore : 0;
                    var addscore = filteredData[0].add_on_score ? (filteredData[0].add_on_score / 100) * ascore : 0;
                    var nscore = (ascore - fscore + addscore).toFixed(2);

                    $('#avg_score').html(`${nscore}%`);
                    var joinDate = filteredData[0].staff_join_date || '';
                    setPedmaDaysForEmployeeJoinDate(from, to, joinDate, true);
                    $('#overall_feedback').html(filteredData[0].overall_feedback || '-');
                    if (parseInt(filteredData[0].feedback_accepted, 10) === 1) {
                        var when = filteredData[0].feedback_accepted_at || '';
                        $('#feedback_ack_manager_status').html(
                            '<div class="alert alert-success tw-mb-0"><i class="fa fa-check-circle"></i> Employee accepted this feedback' +
                            (when ? ' on <b>' + when + '</b>' : '') + '.</div>'
                        ).show();
                    } else if (parseInt(filteredData[0].feedback_accepted, 10) === 2) {
                        var whenMeet = filteredData[0].feedback_accepted_at || '';
                        $('#feedback_ack_manager_status').html(
                            '<div class="alert alert-danger tw-mb-0"><i class="fa fa-users"></i> Employee requested <b>Need Meeting</b>' +
                            (whenMeet ? ' on <b>' + whenMeet + '</b>' : '') + '.</div>'
                        ).show();
                    } else {
                        $('#feedback_ack_manager_status').html(
                            '<div class="alert alert-warning tw-mb-0"><i class="fa fa-clock-o"></i> Waiting for employee acknowledgment.</div>'
                        ).show();
                    }
                }

                // Chart Data
                const kraData = filteredData.map(item => JSON.parse(item.kra_data));
                const kraLabels = kraData[0].map(item => item.name);
                const kraScores = kraData[0].map(item => item.get_score != null ? Number(item.get_score) :
                    item.kpiData.reduce((sum, kpi) => sum + Number(kpi.get_score || 0), 0));
                const kraMaxScores = kraData[0].map(item => item.max_score != null ? Number(item.max_score) :
                    item.kpiData.reduce((sum, kpi) => sum + Number(kpi.max_score || 0), 0));

                // Destroy previous chart instances
                if (kraChart) kraChart.destroy();
                if (kraDoughnutChart) kraDoughnutChart.destroy();

                // Bar Chart — single series (achieved only)
                const kraCtx = document.getElementById('kraChart').getContext('2d');
                kraChart = new Chart(kraCtx, {
                    type: 'bar',
                    data: {
                        labels: kraLabels,
                        datasets: [{
                            label: 'Achieved Score',
                            data: kraScores,
                            backgroundColor: '#4CAF50',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            x: { grid: { display: false } },
                            y: { beginAtZero: true, grid: { display: true }, title: { display: true, text: 'Score' } }
                        },
                        plugins: { title: { display: true, text: 'KRA Performance: Achieved Score' } }
                    }
                });

                // Doughnut Chart
                const doughnutColors = ['#FF6384', '#36A2EB', '#FFCE56', '#8C9EFF', '#4CAF50', '#FF9F40', '#9966FF'];
                const kraDoughnutCtx = document.getElementById('kraDoughnutChart').getContext('2d');
                const totalAchieved = kraScores.reduce((sum, n) => sum + (Number(n) || 0), 0) || 1;

                kraDoughnutChart = new Chart(kraDoughnutCtx, {
                    type: 'doughnut',
                    data: {
                        labels: kraLabels,
                        datasets: [{
                            data: kraScores,
                            backgroundColor: doughnutColors,
                            hoverBackgroundColor: doughnutColors,
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            title: { display: true, text: 'KRA Distribution' },
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        var label = context.label || '';
                                        var value = Number(context.raw || 0);
                                        var pct = ((value / totalAchieved) * 100).toFixed(1);
                                        return label + ': ' + value + ' (' + pct + '%)';
                                    }
                                }
                            }
                        },
                        legend: { display: false },
                        tooltips: {
                            callbacks: {
                                label: function (tooltipItem, data) {
                                    var label = data.labels[tooltipItem.index] || '';
                                    var value = Number(data.datasets[0].data[tooltipItem.index] || 0);
                                    var pct = ((value / totalAchieved) * 100).toFixed(1);
                                    return label + ': ' + value + ' (' + pct + '%)';
                                }
                            }
                        }
                    }
                });

                // Explicit legend under pie so KRA names/scores always show clearly
                var legendHtml = '';
                kraLabels.forEach(function (name, idx) {
                    var score = Number(kraScores[idx] || 0);
                    var max = Number(kraMaxScores[idx] || 0);
                    var pct = ((score / totalAchieved) * 100).toFixed(1);
                    var color = doughnutColors[idx % doughnutColors.length];
                    legendHtml +=
                        '<div class="legend-row">' +
                            '<div class="legend-left">' +
                                '<span class="swatch" style="background:' + color + ';"></span>' +
                                '<span class="legend-name" title="' + String(name).replace(/"/g, '&quot;') + '">' + name + '</span>' +
                            '</div>' +
                            '<span class="legend-score">' + score + (max ? '/' + max : '') + ' · ' + pct + '%</span>' +
                        '</div>';
                });
                $('#kraDoughnutLegend').html(legendHtml);
            }
        }

        const startYear = 2024; // Starting year
        const currentYear = new Date().getFullYear(); // Current year
        const $yearDropdown = $('#performance_year');

        // Populate the dropdown with years starting from 2024 up to the current year
        for (let year = startYear; year <= currentYear; year++) {
            if (year === currentYear) {
                $yearDropdown.append(`<option value="${year}" selected>${year}</option>`);
            } else {
                $yearDropdown.append(`<option value="${year}">${year}</option>`);
            }
        }

        // Update selected year display when the dropdown value changes
        $yearDropdown.change(function() {
            const selectedYear = $(this).val();
            if (selectedYear) {
                $('.selected_year').text(selectedYear); // Update the displayed selected year
                loadPerformanceData(selectedYear); // Load data for the selected year
            } else {
                $('.selected_year').text(currentYear); // Fallback to the current year if nothing is selected
            }
        });

        // Initially set the current year in the display
        $('.selected_year').text(currentYear);

        // Function to load and display data based on the selected year
        function loadPerformanceData(year) {
            // PHP JSON-encoded data of KRA (Performance Data)
            let kraData = $("#staffData").val();
            if (!kraData) {
                return;
            }
            kraData = JSON.parse(kraData);
            
            // Check if kraData is an array
            if (!Array.isArray(kraData)) {
                console.error('KRA data is not an array');
                return; // Exit if data is not in the expected format
            }

           // Filter KRA data to include only those with a `date_created` field
           const kraDataWithDate = kraData.filter(kra => {
                // Convert date_created to a Date object
                const kraDate = new Date(kra.date_created);
                const kraYear = kraDate.getFullYear();  // Extract year from the Date object
                return kraYear === parseInt(year); // Compare the year to the selected year
            });

            // Function to extract month from date_created
            function getMonthFromDate(dateString) {
                const date = new Date(dateString);
                return date.toLocaleString('default', {
                    month: 'long'
                }); // Extract the month name
            }

            // Group data by months based on `date_created`
            const kraDataForMonths = kraDataWithDate.reduce((acc, kra) => {
                const month = getMonthFromDate(kra.date_created);
                if (!acc[month]) {
                    acc[month] = [];
                }
                acc[month].push(kra); // Push KRA data for this month
                return acc;
            }, {});

            // Calculate the average score for the month based on KRA data
            function calculateAvgScore(kraData) {
                let totalScore = 0;
                let maxTotalScore = 0;
                kraData.forEach(kra => {
                    var ascore = kra.avg_score;
                    var fscore = kra.fatal_error_score ? (kra.fatal_error_score / 100) * ascore : 0;
                    var addscore = kra.add_on_score ? (kra.add_on_score / 100) * ascore : 0;
                    var nscore = (ascore - fscore + addscore).toFixed(2);
                    totalScore += parseFloat(nscore);
                    maxTotalScore += 100; // Assuming 100 as max score per KRA
                });
                return (totalScore / maxTotalScore * 100).toFixed(2); // Calculate percentage
            }

            function findStaffComment(kraData) {
                let comment = '';
                kraData.forEach(kra => {
                    comment = kra.staff_comment;
                });
                return comment;
            }

            // Function to toggle the visibility of the row
            function toggleContent(index) {
                const contentRow = document.getElementById(`collapse-${index}`);
                const iconElement = document.getElementById(`icon-${index}`);

                if (contentRow.classList.contains('expanded')) {
                    contentRow.classList.remove('expanded'); // Collapse the row
                    iconElement.textContent = '+'; // Change icon back to "+"
                } else {
                    contentRow.classList.add('expanded'); // Expand the row
                    iconElement.textContent = '-'; // Change icon to "-"
                }
            }

            function lineChartByYear(kraDataForMonths) {
                // Line chart removed
            }


            // Function to render the expandable table using the filtered KRA data
            function renderExpandableTable(kraDataForMonths) {
                let tableContent = '';
                let monthIndex = 0;

                // Loop through each month and its KRA data
                for (const [month, kraData] of Object.entries(kraDataForMonths)) {
                    const avgScore = calculateAvgScore(kraData);
                    const addScore = kraData.reduce((total, item) => total + Number(item.add_on_score || 0), 0); // Total add_on_score
                    const fScore = kraData.reduce((total, item) => total + Number(item.fatal_error_score || 0), 0); // Total fatal_error_score
                    const staffComment = findStaffComment(kraData);

                    // Create the row for the month with expandable content
                    tableContent += `
                    <tr style="cursor: pointer;">
                        <td colspan="2" class="toggle-row" data-index="${monthIndex}">
                            <span id="icon-${monthIndex}" class="icon-toggle">+</span><strong>${month}</strong>
                        </td>
                        <td colspan="" class="toggle-row" data-index="${monthIndex}">Avg Score: ${avgScore}% ${(addScore != 0 || fScore != 0) ? `<small>(Add on Score: ${addScore}%, Fatal Error Score:${fScore}%)<small>`:''}</td>
                        <td colspan="">
                            ${staffComment ? 
                                `<button type="button" class="btn btn-primary commentViewbutton" data-comment="${staffComment}">View</button>` : 
                                ``
                            }
                        </td>
                    </tr>
                    <tr id="collapse-${monthIndex}" class="collapse-content">
                        <td colspan="4">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>KRA</th>
                                        <th>KPI</th>
                                        <th>Max Score</th>
                                        <th>Score</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                    // Loop through each KRA and its associated KPIs
                    kraData.forEach(kra => {
                        const kraDataParsed = safeParseKraData(kra.kra_data); // Parse `kra_data` field safely

                        kraDataParsed.forEach(kraItem => {
                            let totalKpiMaxScore = 0;
                            let totalKpiGetScore = 0;

                            // If `kpiData` exists, sum up the `max_score` and `get_score` values
                            if (kraItem.kpiData) {
                                kraItem.kpiData.forEach(kpi => {
                                    totalKpiMaxScore += parseFloat(kpi.max_score);
                                    totalKpiGetScore += parseFloat(kpi.get_score);
                                });
                            }

                            // If kpiData is present, display the sum; otherwise, use kraItem.get_score
                            const scoreToDisplay = kraItem.kpiData ? totalKpiGetScore : kraItem.get_score;

                            tableContent += `
                            <tr>
                                <td rowspan="${kraItem.kpiData ? kraItem.kpiData.length + 1 : 1}">${kraItem.name}</td>
                                <td></td>
                                <td><b>${kraItem.max_score}</b></td>
                                <td><b>${scoreToDisplay}</b></td>
                            </tr>`;

                            // Display KPI details if they exist
                            if (kraItem.kpiData) {
                                kraItem.kpiData.forEach(kpi => {
                                    tableContent += `
                                    <tr>
                                        <td>${kpi.name}</td>
                                        <td>${kpi.max_score}</td>
                                        <td>${kpi.get_score}</td>
                                    </tr>`;
                                });
                            }
                        });
                    });

                    tableContent += `
                                </tbody>
                            </table>
                        </td>
                    </tr>`;
                    monthIndex++;
                }

                // Inject the generated table content into the HTML
                document.getElementById('kraTableBody').innerHTML = tableContent;

                // Attach event listeners for toggling content after rendering the table
                $('.toggle-row').click(function() {
                    const index = $(this).data('index');
                    toggleContent(index);
                });
            }

            //Call the line chart function
            lineChartByYear(kraDataForMonths);
            // Call the function to render the table after the page loads
            renderExpandableTable(kraDataForMonths);
        }

        // Load data for the current year by default when the page loads
        // loadPerformanceData(currentYear);


        function check_comments(val) {
            if (!val) {
                return 'No Comments Given.';
            }
            return val;
        }

        function csvEscape(value) {
            var s = (value === null || value === undefined) ? '' : String(value);
            if (/[",\n\r]/.test(s)) {
                return '"' + s.replace(/"/g, '""') + '"';
            }
            return s;
        }

        function computePedmaFinalScore(row) {
            var ascore = parseFloat(row.avg_score || 0) || 0;
            var fscore = row.fatal_error_score ? (parseFloat(row.fatal_error_score) / 100) * ascore : 0;
            var addscore = row.add_on_score ? (parseFloat(row.add_on_score) / 100) * ascore : 0;
            return (ascore - fscore + addscore).toFixed(2);
        }

        function feedbackAckLabel(row) {
            var v = parseInt(row.feedback_accepted, 10);
            if (v === 1) return 'Accepted';
            if (v === 2) return 'Need Meeting';
            return 'Pending';
        }

        $(document).on('click', '#pedmaExportBtn', function () {
            var staffId = $('#designationFilter').val();
            var staffName = $('#designationFilter option:selected').text().trim();
            var deptName = $('#departmentFilter option:selected').text().trim();
            var range = getPedmaSelectedRange();
            var raw = $('#staffData').val();

            if (!staffId || !raw) {
                alert_float('warning', 'Select an employee with PEDMA data before exporting.');
                return;
            }

            var staffData;
            try {
                staffData = JSON.parse(raw);
            } catch (e) {
                alert_float('danger', 'Unable to read PEDMA data for export.');
                return;
            }

            if (!Array.isArray(staffData) || !staffData.length) {
                alert_float('warning', 'No PEDMA data available to export.');
                return;
            }

            var rows = filterStaffDataByRange(staffData, range.from, range.to);
            if (!rows.length) {
                alert_float('warning', 'No PEDMA data in the selected date range.');
                return;
            }

            var rangeLabel = (range.from === range.to) ? range.from : (range.from + '_to_' + range.to);
            var header = [
                'Employee', 'Department', 'Month', 'KRA', 'Max Score', 'Achieved Score',
                'Manager Comment', 'HR Remarks', 'Overall Score %', 'Overall Feedback', 'Employee Comment',
                'Acknowledgment', 'Acknowledged At'
            ];
            var lines = [header.map(csvEscape).join(',')];

            rows.forEach(function (row) {
                var monthLabel = '';
                if (row.date_created) {
                    var d = new Date(row.date_created);
                    monthLabel = d.toLocaleString('en-US', { month: 'long', year: 'numeric' });
                }
                var finalScore = computePedmaFinalScore(row);
                var kraList = safeParseKraData(row.kra_data);

                if (!kraList.length) {
                    lines.push([
                        staffName, deptName, monthLabel, '', '', '', '', '',
                        finalScore, row.overall_feedback || '', row.staff_comment || '',
                        feedbackAckLabel(row), row.feedback_accepted_at || ''
                    ].map(csvEscape).join(','));
                    return;
                }

                kraList.forEach(function (item) {
                    var achieved = item.get_score;
                    var maxScore = item.max_score;
                    var feedback = item.comment || '';
                    var hrRemarks = item.hr_remarks || '';

                    if (row.type === 'custom' && item.kpiData && item.kpiData.length) {
                        achieved = item.kpiData.reduce(function (sum, kpi) {
                            return sum + (parseFloat(kpi.get_score) || 0);
                        }, 0);
                        maxScore = item.kpiData.reduce(function (sum, kpi) {
                            return sum + (parseFloat(kpi.max_score) || 0);
                        }, 0);
                        feedback = item.kpiData.map(function (kpi) {
                            return (kpi.name || '') + ': ' + (kpi.get_score || 0) + '/' + (kpi.max_score || 0) +
                                (kpi.comment ? ' (' + kpi.comment + ')' : '');
                        }).join(' | ');
                        hrRemarks = item.kpiData.map(function (kpi) {
                            return (kpi.name || '') + ': ' + (kpi.hr_remarks || '-');
                        }).join(' | ');
                    }

                    lines.push([
                        staffName, deptName, monthLabel, item.name || '', maxScore, achieved,
                        feedback, hrRemarks, finalScore, row.overall_feedback || '', row.staff_comment || '',
                        feedbackAckLabel(row), row.feedback_accepted_at || ''
                    ].map(csvEscape).join(','));
                });
            });

            var blob = new Blob([lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            var safeName = (staffName || 'pedma').replace(/[^\w\-]+/g, '_');
            a.href = url;
            a.download = 'PEDMA_' + safeName + '_' + (rangeLabel || 'all') + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        });
    </script>

    </body>

    </html>