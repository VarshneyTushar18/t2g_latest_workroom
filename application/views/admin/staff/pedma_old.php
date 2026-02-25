<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"> -->
<!-- Custom CSS -->
<style>
    .card {
        border: 1px solid #ddd;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
        font-size: 2rem;
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
                            <input type="month" id="performance_month" name="performance_month" class="form-control" style="width: 40%;" />
                        </div>
                        <div class="col-md-3 p-0" style="margin-left: auto;">
                            <div class="bg-primary text-white px-4 py-2 text-end rounded" style="padding:10px ;">
                                <span>OVERALL PERFORMANCE SCORE</span>
                                <h4 id="avg_score"></h4>
                            </div>
                        </div>
                    </div>



                    <div class="row">
                        <div class="col-md-8 p-0">
                            <div class="row g-2" style="row-gap: 10px; margin:0;">
                                <div class="col-sm-6 col-md-4 p-i-4 performance_card">
                                    <div class="card h-100">
                                        <h5 class="card-title">
                                            Reporting
                                            <i class="fa-regular fa-circle-question ml-2 info-icon" data-toggle="popover" data-bs-content="Timely and accurate reporting of work progress. Demonstrates consistent adherence to deadlines and provides detailed updates on tasks and projects."></i>
                                        </h5>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="score high" id="reporting">/10</div>
                                                    <div class="hr"></div>
                                                    <div class="comment ellipsis reporting_comment"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4 p-i-4 performance_card">
                                    <div class="card h-100">
                                        <h5 class="card-title">
                                            Attendance/Leaves
                                            <i class="fa-regular fa-circle-question ml-2 info-icon" data-toggle="popover" data-bs-content="Adherence to Office timings. -Planned leaves to be applied and pre-approved -Unplanned leaves to be applied on the very first date of reporting back to office. -Minimum planned/unplanned leaves during the month end. -Ensure full attendance on Meetings, Huddles, trainings , Town hall, fun Fridays etc."></i>
                                        </h5>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="score high" id="attendance">/10</div>
                                                    <div class="hr"></div>
                                                    <div class="comment ellipsis attendance_comment"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4 p-i-4 performance_card">
                                    <div class="card h-100">
                                        <h5 class="card-title">
                                            Continues Learning
                                            <i class="fa-regular fa-circle-question ml-2 info-icon" data-toggle="popover" data-bs-content="Continuously acquire new skills and knowledge relevant to the role. Actively seek feedback and incorporate it to improve performance."></i>
                                        </h5>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="score high" id="learning">/10</div>
                                                    <div class="hr"></div>
                                                    <div class="comment ellipsis learning_comment"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4 p-i-4 performance_card">
                                    <div class="card h-100">
                                        <h5 class="card-title">
                                            Engagement Event
                                            <i class="fa-regular fa-circle-question ml-2 info-icon" data-toggle="popover" data-bs-content="Ensure to participate in engagement activities through nomination / self - nomination"></i>
                                        </h5>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="score high" id="engagement">/10</div>
                                                    <div class="hr"></div>
                                                    <div class="comment ellipsis engagement_comment"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4 p-i-4 performance_card">
                                    <div class="card h-100">
                                        <h5 class="card-title">
                                            Process Documentation
                                            <i class="fa-regular fa-circle-question ml-2 info-icon" data-toggle="popover" data-bs-content="Timely updating of Learning log & SOP. Ensure regular maintenance of Version Control for SOP(s) to facilitate accommodation of changes/updates"></i>
                                        </h5>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="score high" id="documentation">/10</div>
                                                    <div class="hr"></div>
                                                    <div class="comment ellipsis documentation_comment"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4 p-i-4 performance_card">
                                    <div class="card h-100">
                                        <h5 class="card-title">
                                            Task Execution and Completion
                                            <i class="fa-regular fa-circle-question ml-2 info-icon" data-toggle="popover" data-bs-content="Successfully completing assigned tasks and projects within the specified deadlines while maintaining quality standards."></i>
                                        </h5>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="score high" id="task">/10</div>
                                                    <div class="hr"></div>
                                                    <div class="comment ellipsis task_comment"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4 p-i-4 performance_card">
                                    <div class="card h-100">
                                        <h5 class="card-title">
                                            Team Collaboration
                                            <i class="fa-regular fa-circle-question ml-2 info-icon" data-toggle="popover" data-bs-content="Actively participating in team activities, sharing knowledge and ideas, and collaborating with team members to achieve collective goals."></i>
                                        </h5>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="score high" id="team">/10</div>
                                                    <div class="hr"></div>
                                                    <div class="comment ellipsis team_comment"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4 p-i-4 performance_card">
                                    <div class="card h-100">
                                        <h5 class="card-title">
                                            Professional Development
                                            <i class="fa-regular fa-circle-question ml-2 info-icon" data-toggle="popover" data-bs-content="Continuously enhancing skills and knowledge through training, self-study, and seeking opportunities for growth within the organization."></i>
                                        </h5>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="score high" id="professional">/10</div>
                                                    <div class="hr"></div>
                                                    <div class="comment ellipsis professional_comment"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4 p-i-4 performance_card">
                                    <div class="card h-100">
                                        <h5 class="card-title">
                                            Quality
                                            <i class="fa-regular fa-circle-question ml-2 info-icon" data-toggle="popover" data-bs-content="Contributing to the improvement of processes and procedures by identifying areas for enhancement, suggesting innovative solutions, and implementing best practices."></i>
                                        </h5>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="score high" id="quality">/10</div>
                                                    <div class="hr"></div>
                                                    <div class="comment ellipsis quality_comment"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4 p-i-4 performance_card">
                                    <div class="card h-100">
                                        <h5 class="card-title">
                                            Initiative and Innovation
                                            <i class="fa-regular fa-circle-question ml-2 info-icon" data-toggle="popover" data-bs-content="Demonstrating initiative by proactively identifying opportunities for improvement, proposing new ideas or initiatives, and taking ownership of assigned responsibilities."></i>
                                        </h5>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="score high" id="initiative">/10</div>
                                                    <div class="hr"></div>
                                                    <div class="comment ellipsis initiative_comment"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4 p-i-4 performance_card">
                                    <div class="card h-100">
                                        <h5 class="card-title">
                                            Communication Skills
                                            <i class="fa-regular fa-circle-question ml-2 info-icon" data-toggle="popover" data-bs-content="Effectively communicating with colleagues and managers through verbal and written channels, ensuring clarity, professionalism, and understanding."></i>
                                        </h5>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="score high" id="communication">/10</div>
                                                    <div class="hr"></div>
                                                    <div class="comment ellipsis communication_comment"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4 p-i-4 performance_card">
                                    <div class="card h-100">
                                        <h5 class="card-title">
                                            Problem-Solving
                                            <i class="fa-regular fa-circle-question ml-2 info-icon" data-toggle="popover" data-bs-content="Analyzing problems, identifying root causes, and implementing effective solutions to overcome challenges and achieve desired outcomes."></i>
                                        </h5>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="score high" id="problem">/10</div>
                                                    <div class="hr"></div>
                                                    <div class="comment ellipsis problem_comment"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4 p-i-4 performance_card">
                                    <div class="card h-100">
                                        <h5 class="card-title">
                                            Ownership and Accountability
                                            <i class="fa-regular fa-circle-question ml-2 info-icon" data-toggle="popover" data-bs-content="Being answerable for the outcomes of one's actions or decisions, taking ownership of mistakes or shortcomings, and working to rectify them."></i>
                                        </h5>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="score high" id="ownership">/10</div>
                                                    <div class="hr"></div>
                                                    <div class="comment ellipsis ownership_comment"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-4 p-i-4 performance_card mt-md-0 mt-2">
                            <div class="card h-100">
                                <div class="card-title">
                                    Overall Feedback
                                </div>
                                <div class="card-body pb-3 pt-1 comment" id="overall_feedback"></div>
                            </div>
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
    <?php init_tail(); ?>


    <!-- Bootstrap JS and custom JavaScript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.3/umd/popper.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
        $(window).on('load', function() {
            // Set the default value of the performance_month input to the current month
            var currentMonth = new Date().toISOString().slice(0, 7);
            $('#performance_month').val(currentMonth);
            // Trigger the change event for the performance_month input
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

        var counters = {
            'reporting': 0,
            'attendance': 0,
            'engagement': 0,
            'documentation': 0,
            'learning': 0,
            'task': 0,
            'team': 0,
            'professional': 0,
            'quality': 0,
            'initiative': 0,
            'communication': 0,
            'problem': 0,
            'ownership': 0
        };

        // Function to animate the counter
        function animateCounter(counterName, targetValue, duration) {

            if (targetValue) {
                $('#' + counterName).prop('Counter', 0).animate({
                    Counter: targetValue
                }, {
                    duration: duration,
                    easing: 'linear',
                    step: function(now) {
                        $(this).text(Math.ceil(now) + '/10');
                    }
                });
            } else {
                $('#' + counterName).text('-/10');
            }
        }
        $('#performance_month').on('change', function() {
            $.ajax({
                type: "post",
                url: "/admin/staff/get_staff_performance_month_old",
                data: {
                    date: this.value
                },
                success: function(response) {

                    $('.manager_comments').remove();
                    let performance_obj = JSON.parse(response);

                    if (!performance_obj) {
                        $('#reporting').html('-' + '/10');
                        $('#attendance').html('-' + '/10');
                        $('#engagement').html('-' + '/10');
                        $('#documentation').html('-' + '/10')
                        $('#learning').html('-' + '/10');
                        $('#task').html('-' + '/10');
                        $('#team').html('-' + '/10');
                        $('#professional').html('-' + '/10');
                        $('#quality').html('-' + '/10');
                        $('#initiative').html('-' + '/10');
                        $('#communication').html('-' + '/10');
                        $('#problem').html('-' + '/10');
                        $('#ownership').html('-' + '/10');

                        $('#avg_score').html('-');

                        $('.reporting_comment').html('<span style="font-weight: bold;"> Feedback :  </span>  ' + '-');
                        $('.attendance_comment').html('<span style="font-weight: bold;"> Feedback :  </span>  ' + '-');
                        $('.engagement_comment').html('<span  style="font-weight: bold;"> Feedback :  </span>  ' + '-');
                        $('.documentation_comment').html('<span  style="font-weight: bold;"> Feedback :  </span>  ' + '-');
                        $('.learning_comment').html('<span style="font-weight: bold;"> Feedback :  </span>  ' + '-');
                        $('.task_comment').html('<span  style="font-weight: bold;"> Feedback :  </span>  ' + '-');
                        $('.team_comment').html('<span style="font-weight: bold;"> Feedback :  </span>  ' + '-');
                        $('.professional_comment').html('<span style="font-weight: bold;"> Feedback :  </span>  ' + '-');
                        $('.quality_comment').html('<span style="font-weight: bold;"> Feedback :  </span>  ' + '-');
                        $('.initiative_comment').html('<span  style="font-weight: bold;"> Feedback :  </span>  ' + '-');
                        $('.communication_comment').html('<span  style="font-weight: bold;"> Feedback :  </span>  ' + '-');
                        $('.problem_comment').html('<span  style="font-weight: bold;"> Feedback :  </span>  ' + '-');
                        $('.ownership_comment').html('<span style="font-weight: bold;"> Feedback :  </span>  ' + '-');


                        $('#overall_feedback').html('-');

                    } else {
                        // Loop through each counter and animate it
                        Object.keys(counters).forEach(function(key) {
                            animateCounter(key, performance_obj[key], 1000); // 1000ms duration
                        });
                        $('#avg_score').html(performance_obj.avg_score);
                        $('.reporting_comment').html('<span style="font-weight: bold;"> Feedback :  </span>  ' + check_comments(performance_obj.reporting_comment));
                        $('.attendance_comment').html('<span style="font-weight: bold;"> Feedback :  </span>  ' + check_comments(performance_obj.attendance_comment));
                        $('.engagement_comment').html('<span  style="font-weight: bold;"> Feedback :  </span>  ' + check_comments(performance_obj.engagement_comment));
                        $('.documentation_comment').html('<span  style="font-weight: bold;"> Feedback :  </span>  ' + check_comments(performance_obj.documentation_comment));
                        $('.learning_comment').html('<span style="font-weight: bold;"> Feedback :  </span>  ' + check_comments(performance_obj.learning_comment));
                        $('.task_comment').html('<span  style="font-weight: bold;"> Feedback :  </span>  ' + check_comments(performance_obj.task_comment));
                        $('.team_comment').html('<span style="font-weight: bold;"> Feedback :  </span>  ' + check_comments(performance_obj.team_comment));
                        $('.professional_comment').html('<span style="font-weight: bold;"> Feedback :  </span>  ' + check_comments(performance_obj.professional_comment));
                        $('.quality_comment').html('<span style="font-weight: bold;"> Feedback :  </span>  ' + check_comments(performance_obj.quality_comment));
                        $('.initiative_comment').html('<span  style="font-weight: bold;"> Feedback :  </span>  ' + check_comments(performance_obj.initiative_comment));
                        $('.communication_comment').html('<span  style="font-weight: bold;"> Feedback :  </span>  ' + check_comments(performance_obj.communication_comment));
                        $('.problem_comment').html('<span  style="font-weight: bold;"> Feedback :  </span>  ' + check_comments(performance_obj.problem_comment));
                        $('.ownership_comment').html('<span style="font-weight: bold;"> Feedback :  </span>  ' + check_comments(performance_obj.ownership_comment));


                        $('#overall_feedback').html(performance_obj.overall_feedback);


                    }

                    // $('#reporting').html('' + performance_obj.reporting + '/10');
                    // $('#attendance').html('' + performance_obj.attendance + '/10');
                    // $('#engagement').html('' + performance_obj.engagement + '/10');
                    // $('#documentation').html('' + performance_obj.documentation + '/10')
                    // $('#learning').html('' + performance_obj.learning + '/10');
                    // $('#task').html('' + performance_obj.task + '/10');
                    // $('#team').html('' + performance_obj.team + '/10');
                    // $('#professional').html('' + performance_obj.professional + '/10');
                    // $('#quality').html('' + performance_obj.quality + '/10');
                    // $('#initiative').html('' + performance_obj.initiative + '/10');
                    // $('#communication').html('' + performance_obj.communication + '/10');
                    // $('#problem').html('' + performance_obj.problem + '/10');
                    // $('#ownership').html('' + performance_obj.ownership + '/10');
                    // Assuming performance_obj is an object containing counter values
                    // Initialize the counters










                    var ellipsisElements = document.querySelectorAll('.ellipsis');
                    var linesToShow = 3;
                    ellipsisElements.forEach(function(ellipsisDiv, i) {
                        var lineHeight = parseInt(window.getComputedStyle(ellipsisDiv).lineHeight);
                        var calculatedHeight = linesToShow * lineHeight;
                        console.log(document.getElementsByClassName('ellipsis')[i].scrollHeight)
                        if (ellipsisDiv.scrollHeight > calculatedHeight) {
                            ellipsisDiv.style.cursor = 'pointer';
                            ellipsisDiv.addEventListener('click', function() {
                                var popupTitle = ellipsisDiv.closest(".performance_card").querySelector(".card-title").textContent.trim();
                                var score = ellipsisDiv.closest(".performance_card").querySelector(".score").textContent.trim();
                                var popupContent = ellipsisDiv.textContent;
                                document.getElementById('popup-title').textContent = popupTitle + ' (' + score + ')';
                                document.getElementById('popup-content').textContent = popupContent;
                                document.getElementById('popup').style.display = 'flex';
                            });
                        }
                    });

                    document.querySelector('.close-btn').addEventListener('click', function() {
                        document.getElementById('popup').style.display = 'none';
                    });

                    document.querySelector('.close-btn').addEventListener('click', function() {
                        document.getElementById('popup').style.display = 'none';
                    });
                }
            });
        })

        function check_comments(val) {
            if (!val) {
                return 'No Comments Given.';
            }
            return val;
        }
    </script>
    </body>

    </html>