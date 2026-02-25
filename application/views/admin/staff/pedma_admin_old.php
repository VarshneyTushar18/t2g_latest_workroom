<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<!-- Custom CSS -->
<style>
    .table thead th {
        vertical-align: middle;
    }

    .performance-table {
        width: 100%;
        border-collapse: collapse;
    }

    .performance-table th,
    .performance-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    .performance-table th {
        background-color: #fff;
        text-align: center;
    }

    .table {
        background-color: #fff;
        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
    }

    .table th,
    .table td {
        padding: 12px;
        vertical-align: middle;
    }

    .table th {
        background-color: #f8f9fa;
        text-align: center;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #f9f9f9;
    }

    .performance-criteria {
        font-weight: 500;
        color: #333;
    }

    .comment {
        width: 100%;
        min-height: 60px;
        border-radius: 5px;
        padding: 8px;
        resize: none;
    }

    .performance-table th {
        resize: horizontal;
        overflow: auto;
    }

    .performance-table th {
        white-space: nowrap;
    }

    td.performance-criteria b {
        color: #000;
    }

    th {
        color: #000;
    }

    .left-header {
        background-color: #F8FAFC;
        color: #000 !important;
        font-weight: 600;
        width: 50%;
    }

    .table {
        box-shadow: none !important;
    }

    .bg-primary {
        background-color: #172032;
    }

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

    #overall_feedback {
        resize: vertical;
    }

    .form-group {
        margin-bottom: 8px;
    }

    @keyframes fadeOut {
        100% {
            opacity: 0;
            visibility: hidden;
        }
    }

    .ck-editor__editable {
        height: 100px;
    }

    td.performance-criteria>b {
        font-size: 13.5px !important;
    }

    td.performance-criteria {
        font-size: 11px !important;
    }
</style>

<!-- <script src="https://cdn.ckeditor.com/ckeditor5/41.3.1/classic/ckeditor.js"></script> -->
<script src="https://cdn.ckeditor.com/4.8.0/full-all/ckeditor.js"></script>


<div id="wrapper">
    <div class="content col-md-12">
        <div class="panel-body">
            <div class="">

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
                <?php echo form_open('admin/staff/staff_performance', array('id' => 'pedma-form')); ?>
                <div class="row">
                    <div class="col-md-6">
                        <!-- <form action=""> -->

                        <?php echo render_select('departments', $departments, array('departmentid', 'name'), 'department'); ?>
                        <?php echo render_select('staffid', $staffs, array('staffid', array('firstname', 'lastname')), 'Select Employee');
                        ?>
                        <div class="form-group" app-field-wrapper="performance_month">
                            <label class="control-label" for="performance_month">Please select a month</label>
                            <div class="dropdown bootstrap-select bs3" style="width: 100%;">
                                <input type="month" class="selectpicker form-control" id="performance_month" name="performance_month" style="width: 100%; padding:6px 12px;" required />
                            </div>
                        </div>


                    </div>
                    <div class="col-md-6">


                        <table class="table table-bordered">

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
                </div>
                <div class="loader hidden">
                    <img src="https://www.icegif.com/wp-content/uploads/2023/07/icegif-1263.gif" alt="Loading...">
                </div>
                <table class="table performance-table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 3%;">S. NO.</th>
                            <th style="width: 36%;">Performance Criteria</th>
                            <th style="width: 25%">Add Score Here (out of 10)</th>
                            <th style="width: 36%;">Comment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td class="performance-criteria">
                                <b>Reporting</b><br>
                                Timely and accurate reporting of work progress. Demonstrates consistent adherence to deadlines
                                and provides detailed updates on tasks and projects.
                            </td>
                            <td>
                                <select name="reporting" id="scoreSelect" class="selectpicker form-control rating" data-live-search="true">
                                    <option value="">-</option>
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                            </td>
                            <td><textarea name="reporting_comment" class="form-control comment" rows="3" placeholder="Enter comment"></textarea></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td class="performance-criteria">
                                <b>Attendance/Leaves</b><br>
                                - Adherence to Office timings.
                                -Planned leaves to be applied and pre-approved
                                -Unplanned leaves to be applied on the very first date of reporting back to office.
                                -Minimum planned/unplanned leaves during the month end.
                                -Ensure full attendance on Meetings, Huddles, trainings , Town hall, fun Fridays etc.
                            </td>
                            <td>
                                <select name="attendance" id="scoreSelect" class="selectpicker form-control rating" data-live-search="true">
                                    <option value="">-</option>
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                            </td>
                            <td><textarea name="attendance_comment" class="form-control comment" rows="3" placeholder="Enter comment"></textarea></td>
                        </tr>

                        <tr>
                            <td>3</td>
                            <td class="performance-criteria">
                                <b>Engagement Event</b><br>
                                Ensure to participate in engagement activities through nomination / self - nomination
                            </td>
                            <td>
                                <select name="engagement" id="scoreSelect" class="selectpicker form-control rating" data-live-search="true">
                                    <option value="">-</option>
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                            </td>
                            <td><textarea name="engagement_comment" class="form-control comment" rows="3" placeholder="Enter comment"></textarea></td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td class="performance-criteria">
                                <b>Process Documentation</b><br>
                                Timely updating of Learning log & SOP.
                                Ensure regular maintenance of Version Control for SOP(s) to facilitate accommodation of changes/updates
                            </td>
                            <td>
                                <select name="documentation" id="scoreSelect" class="selectpicker form-control rating" data-live-search="true">
                                    <option value="">-</option>
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                            </td>
                            <td><textarea name="documentation_comment" class="form-control comment" rows="3" placeholder="Enter comment"></textarea></td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td class="performance-criteria">
                                <b>Continues Learning</b><br>
                                Continuously acquire new skills and knowledge relevant to the role. Actively seek feedback and incorporate it to improve performance.
                            </td>
                            <td>
                                <select name="learning" id="scoreSelect" class="selectpicker form-control rating" data-live-search="true">
                                    <option value="">-</option>
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                            </td>
                            <td><textarea name="learning_comment" class="form-control comment" rows="3" placeholder="Enter comment"></textarea></td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td class="performance-criteria">
                                <b>Task Execution and Completion</b><br>
                                Successfully completing assigned tasks and projects within the specified deadlines while maintaining quality standards.
                            </td>
                            <td>
                                <select name="task" id="scoreSelect" class="selectpicker form-control rating" data-live-search="true">
                                    <option value="">-</option>
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                            </td>
                            <td><textarea name="task_comment" class="form-control comment" rows="3" placeholder="Enter comment"></textarea></td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td class="performance-criteria">
                                <b>Team Collaboration</b><br>
                                Actively participating in team activities, sharing knowledge and ideas, and collaborating with team members to achieve collective goals.
                            </td>
                            <td>
                                <select name="team" id="scoreSelect" class="selectpicker form-control rating" data-live-search="true">
                                    <option value="">-</option>
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                            </td>
                            <td><textarea name="team_comment" class="form-control comment" rows="3" placeholder="Enter comment"></textarea></td>
                        </tr>
                        <tr>
                            <td>8</td>
                            <td class="performance-criteria">
                                <b>Professional Development</b><br>
                                Continuously enhancing skills and knowledge through training, self-study, and seeking opportunities for growth within the organization.
                            </td>
                            <td>
                                <select name="professional" id="scoreSelect" class="selectpicker form-control rating" data-live-search="true">
                                    <option value="">-</option>
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                            </td>
                            <td><textarea name="professional_comment" class="form-control comment" rows="3" placeholder="Enter comment"></textarea></td>
                        </tr>
                        <tr>
                            <td>9</td>
                            <td class="performance-criteria">
                                <b>Quality</b><br>
                                Contributing to the improvement of processes and procedures by identifying areas for enhancement, suggesting innovative solutions, and implementing best practices.
                            </td>
                            <td>
                                <select name="quality" id="scoreSelect" class="selectpicker form-control rating" data-live-search="true">
                                    <option value="">-</option>
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                            </td>
                            <td><textarea name="quality_comment" class="form-control comment" rows="3" placeholder="Enter comment"></textarea></td>
                        </tr>
                        <tr>
                            <td>10</td>
                            <td class="performance-criteria">
                                <b>Initiative and Innovation</b><br>
                                Demonstrating initiative by proactively identifying opportunities for improvement, proposing new ideas or initiatives, and taking ownership of assigned responsibilities.
                            </td>
                            <td>
                                <select name="initiative" id="scoreSelect" class="selectpicker form-control rating" data-live-search="true">
                                    <option value="">-</option>
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                            </td>
                            <td><textarea name="initiative_comment" class="form-control comment" rows="3" placeholder="Enter comment"></textarea></td>
                        </tr>
                        <tr>
                            <td>11</td>
                            <td class="performance-criteria">
                                <b>Communication Skills</b><br>
                                Effectively communicating with colleagues and managers through verbal and written channels, ensuring clarity, professionalism, and understanding.
                            </td>
                            <td>
                                <select name="communication" id="scoreSelect" class="selectpicker form-control rating" data-live-search="true">
                                    <option value="">-</option>
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                            </td>
                            <td><textarea name="communication_comment" class="form-control comment" rows="3" placeholder="Enter comment"></textarea></td>
                        </tr>
                        <tr>
                            <td>12</td>
                            <td class="performance-criteria">
                                <b>Problem-Solving</b><br>
                                Analyzing problems, identifying root causes, and implementing effective solutions to overcome challenges and achieve desired outcomes.
                            </td>
                            <td>
                                <select name="problem" id="scoreSelect" class="selectpicker form-control rating" data-live-search="true">
                                    <option value="">-</option>
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                            </td>
                            <td><textarea name="problem_comment" class="form-control comment" rows="3" placeholder="Enter comment"></textarea></td>
                        </tr>
                        <tr>
                            <td>13</td>
                            <td class="performance-criteria">
                                <b>Ownership and Accountability</b><br>
                                Being answerable for the outcomes of one's actions or decisions, taking ownership of mistakes or shortcomings, and working to rectify them.
                            </td>
                            <td>
                                <select name="ownership" id="scoreSelect" class="selectpicker form-control rating" data-live-search="true">
                                    <option value="">-</option>
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                            </td>
                            <td><textarea name="ownership_comment" class="form-control comment" rows="3" placeholder="Enter comment"></textarea></td>
                        </tr>

                    </tbody>
                </table>

                <div class="row justify-content-between align-items-center mb-4" style="margin-bottom:15px;">
                    <div class="col-md-3">
                        <div class="bg-primary text-white px-4 py-2 text-end rounded" style="padding: 10px; border-radius:10px">
                            <span>OVERALL PERFORMANCE SCORE : </span>
                            <h4 id="avg_score_display" style="margin: 0; margin-top:px"></h4>
                            <input type="hidden" name="avg_score" id="avg_score" />
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="editor" class="control-label">Overall Feedback</label>
                    <textarea name="overall_feedback" id="editor"></textarea>
                </div>

                <?php
                // echo render_textarea('overall_feedback', 'Overall Feedback');
                ?>


                <!-- <p id="editor-error" class="text-danger">This field is required</p> -->
                <!-- <input id="submitBtn" type="submit" class="btn btn-primary" value="Submit"> -->

                </form>

            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
    var editor;
    CKEDITOR.replace('editor');


    CKEDITOR.on('instanceReady', function(ev) {
        editor = ev.editor;

    });
</script>


<script>
    $('#pedma-form').on('submit', function(e) {
        let editor_val = editor.getData();

        // console.log(editor_val);

        // if (editor_val) {
        //     console.log('in if');
        //     $('#editor-error').hide();
        //     $(this).submit();

        // } else {
        //     console.log('in else');
        //     $('#editor-error').show();
        // }

        if (!editor_val) {
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "Overall Feedback is required",

            });

            return false;
        }
    })
</script>

<script>
    // Function to disable submit button
    function disableSubmitButton() {
        document.getElementById('submitBtn').disabled = true;
        $(".comment").each(function() {
            $(this).prop("disabled", true);
        });
        $(".rating").each(function() {
            $(this).prop("disabled", true);
        });
        // $('#overall_feedback').prop("disabled", true);
        // editor.enableReadOnlyMode('overall_feedback');
        editor.setReadOnly(true);

        $('.selectpicker').selectpicker('refresh');
    }

    // Function to enable submit button
    function enableSubmitButton() {
        document.getElementById('submitBtn').disabled = false;
        $(".comment").each(function() {
            $(this).prop("disabled", false);
        });
        $(".rating").each(function() {
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


    $(function() {


        // $("select[name='reporting'],select[name='attendance'],select[name='engagement'],select[name='documentation'],select[name='learning'],select[name='task'],select[name='team'],select[name='professional'],select[name='quality'],select[name='initiative'],select[name='communication'],select[name='problem'],select[name='ownership']").on('change', function() {

        //     console.log(typeof $("select[name='attendance']").find(":selected").val());
        //     let score_array = [
        //         $("select[name='reporting']").find(":selected").val(),
        //         $("select[name='attendance']").find(":selected").val(),
        //         $("select[name='engagement']").find(":selected").val(),
        //         $("select[name='documentation']").find(":selected").val(),
        //         $("select[name='learning']").find(":selected").val(),
        //         $("select[name='task']").find(":selected").val(),
        //         $("select[name='team']").find(":selected").val(),
        //         $("select[name='professional']").find(":selected").val(),
        //         $("select[name='quality']").find(":selected").val(),
        //         $("select[name='communication']").find(":selected").val(),
        //         $("select[name='problem']").find(":selected").val(),
        //         $("select[name='ownership']").find(":selected").val(),
        //         $("select[name='initiative']").find(":selected").val(),
        //     ]

        //     console.log(score_array);

        //     let avg = score_array.reduce((a, b) => Number(a) + Number(b)) / score_array.length;

        //     console.log(avg.toFixed(2));

        //     $('#avg_score').html(avg.toFixed(2));
        // })

        $(".rating").on('change', function() {
            // Loop through each select input
            var sum = 0;
            var count = 0;

            $('.rating').each(function() {
                var value = $(this).val();
                // Check if the value is not empty
                if (value !== "") {
                    sum += parseInt(value);
                    count++;
                }
            });

            if (count > 0) {
                var average = sum / count;
                $('#avg_score_display').html(average.toFixed(2));
                $('#avg_score').val(average.toFixed(2));

            } else {
                $('#avg_score_display').html('No numbers selected.');
            }
        })

        $("#staffid").prop('required', 'required');


        $('#staffid').on('change', function() {

            $('.loader').removeClass('hidden');

            var staffid = $(this).val();

            var month = $('#performance_month').val();

            $.ajax({
                url: "<?php echo base_url('admin/staff/get_staff_json'); ?>",
                type: 'POST',
                data: {
                    staffid
                },
                dataType: 'json',
                success: function(response) {
                    var name = response[0][0].full_name;
                    $('#full-name').empty().append(name);
                    $('#designation').empty().append(response[0][0].job_name);
                    $('#department-name').empty();
                    if (response[1]) {
                        var department = response[1].name;
                        $('#department-name').append(department)

                    }
                }
            })

            $.ajax({
            url: "<?php echo base_url('admin/staff/get_staff_performance_month_and_id_old'); ?>",
            type: 'POST',
            data: {
                staffid,
                month
            },
            dataType: 'json',
            success: function(response) {

                $('.loader').addClass('hidden');


                if (response == null) {

                    $("select[name='reporting']").val('');
                    $("select[name='attendance']").val('');
                    $("select[name='engagement']").val('');
                    $("select[name='documentation']").val('');
                    $("select[name='learning']").val('');
                    $("select[name='task']").val('');
                    $("select[name='team']").val('');
                    $("select[name='professional']").val('');
                    $("select[name='quality']").val('');
                    $("select[name='communication']").val('');
                    $("select[name='problem']").val('');
                    $("select[name='ownership']").val('');
                    $("select[name='initiative']").val('');

                    $("textarea[name='reporting_comment']").val('');
                    $("textarea[name='attendance_comment']").val('');
                    $("textarea[name='engagement_comment']").val('');
                    $("textarea[name='documentation_comment']").val('');
                    $("textarea[name='learning_comment']").val('');
                    $("textarea[name='task_comment']").val('');
                    $("textarea[name='team_comment']").val('');
                    $("textarea[name='professional_comment']").val('');
                    $("textarea[name='quality_comment']").val('');
                    $("textarea[name='communication_comment']").val('');
                    $("textarea[name='problem_comment']").val('');
                    $("textarea[name='ownership_comment']").val('');
                    $("textarea[name='initiative_comment']").val('');
                    // $("textarea[name='overall_feedback']").val('');

                    // editor.setData('');
                    editor.setData('');


                    $("#avg_score_display").html('');
                    $('#avg_score').val('');

                    $('.selectpicker').selectpicker('refresh');

                } else {
                    $("select[name='reporting']").val(response.reporting);
                    $("select[name='attendance']").val(response.attendance);
                    $("select[name='engagement']").val(response.engagement);
                    $("select[name='documentation']").val(response.documentation);
                    $("select[name='learning']").val(response.learning);
                    $("select[name='task']").val(response.task);
                    $("select[name='team']").val(response.team);
                    $("select[name='professional']").val(response.professional);
                    $("select[name='quality']").val(response.quality);
                    $("select[name='communication']").val(response.communication);
                    $("select[name='problem']").val(response.problem);
                    $("select[name='ownership']").val(response.ownership);
                    $("select[name='initiative']").val(response.initiative);

                    $("textarea[name='reporting_comment']").val(response.reporting_comment);
                    $("textarea[name='attendance_comment']").val(response.attendance_comment);
                    $("textarea[name='engagement_comment']").val(response.engagement_comment);
                    $("textarea[name='documentation_comment']").val(response.documentation_comment);
                    $("textarea[name='learning_comment']").val(response.learning_comment);
                    $("textarea[name='task_comment']").val(response.task_comment);
                    $("textarea[name='team_comment']").val(response.team_comment);
                    $("textarea[name='professional_comment']").val(response.professional_comment);
                    $("textarea[name='quality_comment']").val(response.quality_comment);
                    $("textarea[name='communication_comment']").val(response.communication_comment);
                    $("textarea[name='problem_comment']").val(response.problem_comment);
                    $("textarea[name='ownership_comment']").val(response.ownership_comment);
                    $("textarea[name='initiative_comment']").val(response.initiative_comment);
                    // $("textarea[name='overall_feedback']").val(response.overall_feedback);
                    // editor.setData(response.overall_feedback);
                    editor.setData(response.overall_feedback);


                    $("#avg_score_display").html(response.avg_score);
                    $('#avg_score').val(response.avg_score);

                    $('.selectpicker').selectpicker('refresh');
                }
            }
        })
            
        })

        $('#departments').on('change', function() {
            let department = $(this).val();

            $.ajax({
                url: "<?php echo base_url('admin/staff/get_staff_department_json'); ?>",
                type: 'POST',
                data: {
                    department
                },
                dataType: 'json',
                success: function(response) {
                    staff = '<option value=""></option>';
                    response.forEach(element => {
                        staff += '<option value="' + element['staffid'] + '">' + element['firstname'] + ' ' + element['lastname'] + '</option>';
                    });
                    console.log(staff);
                    $('#staffid').html(staff);
                    $('.selectpicker').selectpicker('refresh');

                    // console.log(staff);
                }
            })
        })
    })
    // 
</script>



<script>
    $('#performance_month').on('change', function() {

        $('.loader').removeClass('hidden');
        let staffid = $('#staffid').val();
        let month = $(this).val()
        // checkMonthYear(month);
        // if (!staffid) {
        //     Swal.fire({
        //         icon: "error",
        //         title: "Oops...",
        //         text: "Please select an Employee first",

        //     });
        //     $('.loader').addClass('hidden');

        //     return false;
        // }

        $.ajax({
            url: "<?php echo base_url('admin/staff/get_staff_performance_month_and_id_old'); ?>",
            type: 'POST',
            data: {
                staffid,
                month
            },
            dataType: 'json',
            success: function(response) {

                $('.loader').addClass('hidden');


                if (response == null) {

                    $("select[name='reporting']").val('');
                    $("select[name='attendance']").val('');
                    $("select[name='engagement']").val('');
                    $("select[name='documentation']").val('');
                    $("select[name='learning']").val('');
                    $("select[name='task']").val('');
                    $("select[name='team']").val('');
                    $("select[name='professional']").val('');
                    $("select[name='quality']").val('');
                    $("select[name='communication']").val('');
                    $("select[name='problem']").val('');
                    $("select[name='ownership']").val('');
                    $("select[name='initiative']").val('');

                    $("textarea[name='reporting_comment']").val('');
                    $("textarea[name='attendance_comment']").val('');
                    $("textarea[name='engagement_comment']").val('');
                    $("textarea[name='documentation_comment']").val('');
                    $("textarea[name='learning_comment']").val('');
                    $("textarea[name='task_comment']").val('');
                    $("textarea[name='team_comment']").val('');
                    $("textarea[name='professional_comment']").val('');
                    $("textarea[name='quality_comment']").val('');
                    $("textarea[name='communication_comment']").val('');
                    $("textarea[name='problem_comment']").val('');
                    $("textarea[name='ownership_comment']").val('');
                    $("textarea[name='initiative_comment']").val('');
                    // $("textarea[name='overall_feedback']").val('');

                    // editor.setData('');
                    editor.setData('');


                    $("#avg_score_display").html('');
                    $('#avg_score').val('');

                    $('.selectpicker').selectpicker('refresh');

                } else {
                    $("select[name='reporting']").val(response.reporting);
                    $("select[name='attendance']").val(response.attendance);
                    $("select[name='engagement']").val(response.engagement);
                    $("select[name='documentation']").val(response.documentation);
                    $("select[name='learning']").val(response.learning);
                    $("select[name='task']").val(response.task);
                    $("select[name='team']").val(response.team);
                    $("select[name='professional']").val(response.professional);
                    $("select[name='quality']").val(response.quality);
                    $("select[name='communication']").val(response.communication);
                    $("select[name='problem']").val(response.problem);
                    $("select[name='ownership']").val(response.ownership);
                    $("select[name='initiative']").val(response.initiative);

                    $("textarea[name='reporting_comment']").val(response.reporting_comment);
                    $("textarea[name='attendance_comment']").val(response.attendance_comment);
                    $("textarea[name='engagement_comment']").val(response.engagement_comment);
                    $("textarea[name='documentation_comment']").val(response.documentation_comment);
                    $("textarea[name='learning_comment']").val(response.learning_comment);
                    $("textarea[name='task_comment']").val(response.task_comment);
                    $("textarea[name='team_comment']").val(response.team_comment);
                    $("textarea[name='professional_comment']").val(response.professional_comment);
                    $("textarea[name='quality_comment']").val(response.quality_comment);
                    $("textarea[name='communication_comment']").val(response.communication_comment);
                    $("textarea[name='problem_comment']").val(response.problem_comment);
                    $("textarea[name='ownership_comment']").val(response.ownership_comment);
                    $("textarea[name='initiative_comment']").val(response.initiative_comment);
                    // $("textarea[name='overall_feedback']").val(response.overall_feedback);
                    // editor.setData(response.overall_feedback);
                    editor.setData(response.overall_feedback);


                    $("#avg_score_display").html(response.avg_score);
                    $('#avg_score').val(response.avg_score);

                    $('.selectpicker').selectpicker('refresh');
                }
            }
        })



    })
</script>

<script>
    appValidateForm($('#pedma-form'), {
        performance_month: 'required',
        editor: 'required',
        staffid: 'required'
    });

    $('.selectpicker').selectpicker({});
</script>




</script>

<!-- Bootstrap JS and custom JavaScript -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

</html>