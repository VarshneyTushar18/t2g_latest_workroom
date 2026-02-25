<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$staffData = json_encode($performance_values);
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
                                <!-- JavaScript will populate years here -->
                            </select>
                        </div>
                        <div class="col-md-12 p-0" style="margin-top: 10px;">
                            <!-- Line Chart for One Year Avg PEDMA Score -->

                            <div class="card">
                                <div class="card-body">
                                    <canvas id="pedmaChart" width="400" height="200"></canvas>
                                </div>
                            </div>

                        </div>
                        <div class="col-md-12 p-0">
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
    <?php init_tail(); ?>


    <!-- Bootstrap JS and custom JavaScript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.3/umd/popper.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
        $(document).ready(function() {
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
                let kraData = <?php echo $staffData; ?>

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
                        totalScore += nscore;
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
                    // Extract months and their average PEDMA scores from the dynamic data
                    const labels = [];
                    const data = [];

                    for (const [month, kraData] of Object.entries(kraDataForMonths)) {
                        labels.push(month); // Add the month to labels
                        const avgScore = calculateAvgScore(kraData); // Calculate average PEDMA score for the month
                        data.push(avgScore); // Add the average score to data
                    }

                    // Sort labels and data by month order (optional, depending on how you want to display)
                    const monthOrder = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                    const sortedData = monthOrder.map((month) => {
                        const index = labels.indexOf(month);
                        return index !== -1 ? data[index] : null; // Match data with the month, or set null if no data
                    });

                    // Load the Chart.js script and render the chart
                    // const chartScript = document.createElement('script');
                    // chartScript.src = 'https://cdn.jsdelivr.net/npm/chart.js';
                    // chartScript.onload = () => {
                    const pedmaData = {
                        labels: monthOrder, // Using predefined month order
                        datasets: [{
                            label: 'Average PEDMA Score',
                            data: sortedData, // Use the dynamically sorted data
                            borderColor: 'rgba(75, 192, 192, 1)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderWidth: 2,
                            fill: true
                        }]
                    };

                    // PEDMA Line Chart
                    const pedmaCtx = document.getElementById('pedmaChart').getContext('2d');
                    const pedmaChart = new Chart(pedmaCtx, {
                        type: 'line',
                        data: pedmaData,
                        options: {
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Month'
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Score'
                                    }
                                }
                            },
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'One Year Average PEDMA Score'
                                }
                            }
                        }
                    });
                    // };

                    //document.head.appendChild(chartScript);
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
                                    `<button type="button" class="btn btn-primary commentbutton" data-month="${month}" data-year="${year}" data-score="${avgScore}">Reply</button>`
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
                            const kraDataParsed = JSON.parse(kra.kra_data); // Parse `kra_data` field

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
            loadPerformanceData(currentYear);

        });
    </script>

    <script>
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
            // Set the default value of the performance_month input to the current month
            var currentMonth = new Date().toISOString().slice(0, 7);
            $('#performance_month').val(currentMonth);
            // Set the max attribute of the performance_month input to the current month
            $('#performance_month').attr('max', currentMonth);
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

        let kraChart = null; // Define chart instances outside the change event
        let kraDoughnutChart = null;

        $('#performance_month').on('change', function() {
            var selectedMonth = $(this).val(); // Get selected month in "YYYY-MM" format
            let staffData = <?php echo $staffData; ?>; // Ensure we pass only performance_values
            const kraCardData = $("#kra-card-data");
            kraCardData.empty();

            // Extract year and month from the selected value
            const [year, month] = selectedMonth.split('-').map(Number);

            // Filter staffData for the selected month and year
            const filteredData = staffData.filter(item => {
                const dateCreated = new Date(item.date_created);
                return dateCreated.getFullYear() === year && dateCreated.getMonth() === (month - 1); // Adjust month index
            });

            let cardHTML = '';

            if (filteredData.length === 0) { // Check if there are any entries for the selected month
                kraCardData.html("No Data available for this month");
                $('#avg_score').html('-');
                $('#overall_feedback').html('-');
                $('#twoChart').hide();

            } else {
                $('#twoChart').show();

                filteredData.forEach((data) => {
                    // Parse kra_data from the item
                    let kra_data = JSON.parse(data.kra_data);

                    kra_data.forEach((item) => {
                        // KRA Row
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
                                                <div class="score high">`;

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
                                                ${scoreToDisplay}/${item.max_score}
                                                </div>
                                                <div class="hr"></div>
                        `;

                        if (data.type === "custom" && item.kpiData.length > 0) {
                            cardHTML += `<p class="h4">KPI Details :</p>`;
                            item.kpiData.forEach((kpi) => {
                                cardHTML += `
                                    <p class="mb-0"><b>${kpi.name}: ${kpi.get_score}/${kpi.max_score}</b></p>
                                    <p class="">Feedback: ${kpi.comment}</p>
                                `;
                            });
                        } else {
                            cardHTML += `
                                <div class="comment">
                                    <span style="font-weight: bold;">Feedback : </span>${item.comment}
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
                var ascore = filteredData[0].avg_score;
                var fscore = filteredData[0].fatal_error_score ? (filteredData[0].fatal_error_score / 100) * ascore : 0;
                var addscore = filteredData[0].add_on_score ? (filteredData[0].add_on_score / 100) * ascore : 0;
                var nscore = (ascore - fscore + addscore).toFixed(2);
                $('#avg_score').html(`${nscore}%`);
                $('#overall_feedback').html(filteredData[0].overall_feedback);

                $('[data-toggle="popover"]').popover();

                const kraData = filteredData.map(item => JSON.parse(item.kra_data));

                const kraLabels = kraData[0].map(item => item.name);
                const kraScores = kraData[0].map(item => {
                    return item.get_score != null ? Number(item.get_score) :
                        item.kpiData.reduce((sum, kpi) => sum + Number(kpi.get_score || 0), 0);
                });

                const kraMaxScores = kraData[0].map(item => {
                    return item.max_score != null ? Number(item.max_score) :
                        item.kpiData.reduce((sum, kpi) => sum + Number(kpi.max_score || 0), 0);
                });

                // Destroy the previous chart instances before creating new ones
                if (kraChart) {
                    kraChart.destroy();
                }
                if (kraDoughnutChart) {
                    kraDoughnutChart.destroy();
                }

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
                        }, {
                            label: 'Total Score',
                            data: kraMaxScores,
                            backgroundColor: '#FFCE56',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            x: {
                                stacked: true,
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                grid: {
                                    display: true
                                },
                                title: {
                                    display: true,
                                    text: 'Score'
                                }
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: 'KRA Performance: Achieved vs Max Score'
                            }
                        }
                    }
                });

                const kraDoughnutCtx = document.getElementById('kraDoughnutChart').getContext('2d');
                kraDoughnutChart = new Chart(kraDoughnutCtx, {
                    type: 'doughnut',
                    data: {
                        labels: kraLabels,
                        datasets: [{
                            data: kraScores,
                            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#8C9EFF', '#4CAF50'],
                            hoverBackgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#8C9EFF', '#4CAF50']
                        }]
                    },
                    options: {
                        plugins: {
                            title: {
                                display: true,
                                text: 'KRA Distribution'
                            }
                        }
                    }
                });
            }
        });


        function check_comments(val) {
            if (!val) {
                return 'No Comments Given.';
            }
            return val;
        }
    </script>

    </body>

    </html>