<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $staff_id = get_staff_user_id(); if ($staff_id == 000): ?>	
<!-- 🎉 Birthday Modal -->
<div id="birthday-celebration">
  <h1>🎉 Happy Birthday Hidayat Hashmi ! 🎉</h1>
</div>

<!-- 🎊 Confetti -->
<script>
function createConfetti() {
  const confetti = document.createElement('div');
  confetti.className = 'confetti';
  confetti.style.left = Math.random() * window.innerWidth + 'px';
  confetti.style.top = '-10px';
  confetti.style.backgroundColor = `hsl(${Math.random() * 360}, 100%, 60%)`;
  confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
  document.body.appendChild(confetti);
  setTimeout(() => confetti.remove(), 5000);
}
setInterval(createConfetti, 100);
</script>

<!-- 🖱️ Mouse Sparkle Trail -->
<script>
document.addEventListener('mousemove', e => {
  const sparkle = document.createElement('div');
  sparkle.innerHTML = '✨';
  sparkle.style.position = 'absolute';
  sparkle.style.left = e.pageX + 'px';
  sparkle.style.top = e.pageY + 'px';
  sparkle.style.zIndex = 9999;
  sparkle.style.pointerEvents = 'none';
  sparkle.style.fontSize = '18px';
  sparkle.style.opacity = 1;
  document.body.appendChild(sparkle);
  setTimeout(() => sparkle.remove(), 600);
});
</script>
<?php endif; ?>
<style>
    h5 {
        margin: 5px;
    }

    .card-header {
        background-color: #1E293B;
        color: white;
        text-align: center;
    }



    .icon {
        font-size: 30px;
        color: #1E293B;
    }

    .event-title {

        font-weight: bold;
    }

    .event-department {
        color: #6c757d;

    }

    .event-message {
        color: #0F172B;

    }

    .event-icon {
        color: #1E293B;
        margin-right: 10px;
    }

    .birthday .event-icon {
        color: #ff4081;
    }

    .anniversary .event-icon {
        color: #1E90FF;
    }

    .newjoinee .event-icon {
        color: MediumSeaGreen;
    }

    .bfull-view,
    .afull-view,
    .nfull-view {
        background-color: #f9fafb;
        padding: 30px;
        border-radius: 10px;
        background-image: url('https://www.transparenttextures.com/patterns/arches.png');
    }

    .birthday-view {
        background-color: #FFE4E1;
        background-image: url('https://www.transparenttextures.com/patterns/arches.png');
    }

    .anniversary-view {
        background-color: #E1F5FE;
        background-image: url('https://www.transparenttextures.com/patterns/my-little-plaid.png');
    }

    .bfull-view img,
    .afull-view img,
    .nfull-view img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #1E293B;
    }

    .bfull-view.birthday-view img .afull-view.birthday-view img,
    .nfull-view.birthday-view img {
        border: 3px solid #ff4081;
    }

    .bfull-view.anniversary-view img,
    .afull-view.anniversary-view img,
    .nfull-view.anniversary-view img {
        border: 3px solid #1E90FF;
    }

    .bfull-view .employee-name,
    .afull-view .employee-name,
    .nfull-view .employee-name {

        font-weight: bold;
        color: #1E293B;
        margin-top: 10px;
    }



    .d-flex {
        display: flex;
    }

    .bfull-view .employee-dept,
    .afull-view .employee-dept,
    .nfull-view .employee-dept {
        color: #6c757d;

    }


    .bfull-view.birthday-view .employee-dept,
    .bfull-view.birthday-view .employee-date,
    .bfull-view.birthday-view .greeting-message,
    .bfull-view.birthday-view .employee-name,
    .afull-view.birthday-view .employee-dept,
    .afull-view.birthday-view .employee-date,
    .afull-view.birthday-view .greeting-message,
    .afull-view.birthday-view .employee-name,
    .nfull-view.birthday-view .employee-dept,
    .nfull-view.birthday-view .employee-date,
    .nfull-view.birthday-view .greeting-message,
    .nfull-view.birthday-view .employee-name {
        color: #ff4081;
    }

    .bfull-view.anniversary-view .employee-dept,
    .bfull-view.anniversary-view .employee-date,
    .bfull-view.anniversary-view .greeting-message,
    .bfull-view.anniversary-view .employee-name,
    .afull-view.anniversary-view .employee-dept,
    .afull-view.anniversary-view .employee-date,
    .afull-view.anniversary-view .greeting-message,
    .afull-view.anniversary-view .employee-name,
    .nfull-view.anniversary-view .employee-dept,
    .nfull-view.anniversary-view .employee-date,
    .nfull-view.anniversary-view .greeting-message,
    .nfull-view.anniversary-view .employee-name {
        color: #1E90FF;
    }

    .bfull-view .greeting-message,
    .afull-view .greeting-message,
    .nfull-view .greeting-message {

        color: #0F172B;
        margin-top: 20px;
        font-style: italic;
    }


    .bfull-view .emoji,
    .afull-view .emoji .nfull-view .emoji {
        font-size: 2rem;
    }

    .list-group-item:hover {
        background-color: #f1f5f9;
        cursor: pointer;
    }

    .list-group-item:active {
        background-color: #f1f5f9;
        cursor: pointer;
    }

    .event-title {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
    }

    .event-date {

        color: #6c757d;
    }

    .list-group-item.current {
        background-color: #f1f5f9
    }


    #birthdayeventsList,
    #anniversaryeventsList,
    #newjoineeeventsList {
        max-height: 300px;
        overflow-y: scroll;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<!-- Fireworks JS -->
<script type="module">
    import {
        Fireworks
    } from 'https://cdn.skypack.dev/fireworks-js@2';
    window.Fireworks = Fireworks;
</script>

<style>
    /* Fireworks container should cover the whole page */
    #fireworks-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        pointer-events: none;
        /* Ensures the content below is still clickable */
        z-index: 9999;
        /* Ensures it's on top of all other content */
    }

    /* Example content styling */
    #content {
        position: relative;
        z-index: 1;
        /* Keeps it below the fireworks container */
    }
</style>

<div id="wrapper">
    <div class="screen-options-area"></div>
    <div class="screen-options-btn">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="tw-w-5 tw-h-5 tw-mr-1">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>

        <?php echo _l('dashboard_options'); ?>
    </div>


    <div class="content">
        <div class="row">

            

            <?php hooks()->do_action('before_start_render_dashboard_content'); ?>

            <div class="widget<?php if (!is_staff_member()) {
                                    echo ' hide';
                                } ?>" id="widget-<?php echo create_widget_id(); ?>" data-name="<?php echo 'Announcement' ?>">
                
            </div>


            <div class="clearfix"></div>

            <div class="col-md-12 mtop20" data-container="top-12">
                <?php render_dashboard_widgets('top-12'); ?>
            </div> 

            <?php hooks()->do_action('after_dashboard_top_container'); ?>

            <!-- <div class="col-md-6" data-container="middle-left-6">
                <?php render_dashboard_widgets('middle-left-6'); ?>
            </div> -->
            <!-- <div class="col-md-6" data-container="middle-right-6">
                <?php render_dashboard_widgets('middle-right-6'); ?>
            </div> -->

            <?php hooks()->do_action('after_dashboard_half_container'); ?>

            <div class="col-md-12" data-container="left-8">
                <?php render_dashboard_widgets('left-8'); ?>
            </div>
            <!-- <div class="col-md-4" data-container="right-4">
                <?php render_dashboard_widgets('right-4'); ?>
            </div> -->

            <div class="clearfix"></div>

            <!-- <div class="col-md-4" data-container="bottom-left-4">
                <?php render_dashboard_widgets('bottom-left-4'); ?>
            </div>
            <div class="col-md-4" data-container="bottom-middle-4">
                <?php render_dashboard_widgets('bottom-middle-4'); ?>
            </div>
            <div class="col-md-4" data-container="bottom-right-4">
                <?php render_dashboard_widgets('bottom-right-4'); ?>
            </div> -->
            <?php if (is_staff_member()) { ?>
                    <div class="row col-md-12" id="celebrantWidget" style="display: none;">
                        <div class="col-md-12">
                            <div class="panel_s">
                                <div class="panel-body padding-10">

                                    <p
                                        class="tw-font-medium tw-flex tw-items-center tw-mb-0 tw-space-x-1.5 rtl:tw-space-x-reverse tw-p-1.5">
                                        <i id="spotlighticon" class="fa fa-birthday-cake" aria-hidden="true"></i>

                                        <span class="tw-text-neutral-700" id="spotlight">
                                            <?php echo 'Spotlight on this month\'s birthdays'; ?>
                                        </span>
                                    </p>

                                    <!-- @DATE: 2024-OCT-15 -->
                                    <!-- Tab Navigation -->
                                    <ul class="nav nav-tabs" id="eventTabs" role="tablist">
                                        <li class="nav-item active">
                                            <a class="nav-link active" id="birthday-tab" data-toggle="tab" href="#birthday" role="tab" aria-controls="birthday" aria-selected="true">Birthday</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="work-anniversary-tab" data-toggle="tab" href="#workAnniversary" role="tab" aria-controls="workAnniversary" aria-selected="false">Work Anniversary</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="new-joinee-tab" data-toggle="tab" href="#newJoinee" role="tab" aria-controls="newJoinee" aria-selected="false">New Joinee</a>
                                        </li>
                                    </ul>
                                    <!-- Tab Content -->
                                    <div class="tab-content" id="eventTabContent">
                                        <!-- Birthday Tab -->
                                        <div class="tab-pane fade active in" id="birthday" role="tabpanel" aria-labelledby="birthday-tab">
                                            <div class="">
                                                <!-- Left column for the list of announcements -->
                                                <div class="card col-md-5 px-0">
                                                    <div class="card-body">
                                                        <div id="birthdayeventsList" class="list-group"></div>
                                                    </div>
                                                </div>

                                                <!-- Right column for detailed greeting message -->
                                                <div class="col-md-7 bfull-view d-flex align-items-center justify-content-center">
                                                    <div id="bgreetingDisplay" class="text-center">
                                                        <img id="bemployeeImg" src="https://t2gworkroom.com/assets/images/user-placeholder.jpg"
                                                            alt="Employee Image">
                                                        <div id="bemployeeName" class="employee-name">Employee Name</div>
                                                        <div id="bemployeeDept" class="employee-dept">Department</div>
                                                        <div id="bemployeeDate" class="employee-date">Date</div>
                                                        <div id="bgreetingMessage" class="greeting-message">Wishing you a very Happy Birthday 🎉🥳! May your
                                                            day be filled with joy and laughter.</div>
                                                        <div class="emoji">🎂🎁🎈</div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        <!-- Work Anniversary Tab -->
                                        <div class="tab-pane fade" id="workAnniversary" role="tabpanel" aria-labelledby="work-anniversary-tab">
                                            <div class="">
                                                <!-- Left column for the list of announcements -->
                                                <div class="card col-md-5 px-0">
                                                    <div class="card-body">
                                                        <div id="anniversaryeventsList" class="list-group"></div>
                                                    </div>
                                                </div>

                                                <!-- Right column for detailed greeting message -->
                                                <div class="col-md-7 afull-view d-flex align-items-center justify-content-center">
                                                    <div id="agreetingDisplay" class="text-center">
                                                        <img id="aemployeeImg" src="https://t2gworkroom.com/assets/images/user-placeholder.jpg"
                                                            alt="Employee Image">
                                                        <div id="aemployeeName" class="employee-name">Employee Name</div>
                                                        <div id="aemployeeDept" class="employee-dept">Department</div>
                                                        <div id="aemployeeDate" class="employee-date">Date</div>
                                                        <div id="agreetingMessage" class="greeting-message">Wishing you a very Happy Birthday 🎉🥳! May your
                                                            day be filled with joy and laughter.</div>
                                                        <div class="emoji">🎂🎁🎈</div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        <!-- New Joinee Tab -->
                                        <div class="tab-pane fade" id="newJoinee" role="tabpanel" aria-labelledby="new-joinee-tab">
                                            <div class="">
                                                <!-- Left column for the list of announcements -->
                                                <div class="card col-md-5 px-0">
                                                    <div class="card-body">
                                                        <div id="newjoineeeventsList" class="list-group"></div>
                                                    </div>
                                                </div>

                                                <!-- Right column for detailed greeting message -->
                                                <div class="col-md-7 nfull-view d-flex align-items-center justify-content-center">
                                                    <div id="greetingDisplay" class="text-center">
                                                        <img id="nemployeeImg" src="https://t2gworkroom.com/assets/images/user-placeholder.jpg"
                                                            alt="Employee Image">
                                                        <div id="nemployeeName" class="employee-name">Employee Name</div>
                                                        <div id="nemployeeDept" class="employee-dept">Department</div>
                                                        <div id="nemployeeDate" class="employee-date">Date</div>
                                                        <div id="ngreetingMessage" class="greeting-message">Wishing you a very Happy Birthday 🎉🥳! May your
                                                            day be filled with joy and laughter.</div>
                                                        <div class="emoji">🎂🎁🎈</div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>


                        </div>
                    </div>
                <?php } ?>

                <?php $this->load->view('admin/includes/alerts'); ?>

            <?php hooks()->do_action('after_dashboard'); ?>
        </div>
    </div>
</div>
<script>
    app.calendarIDs = '<?php echo json_encode($google_ids_calendars); ?>';
</script>
<?php init_tail(); ?>
<?php $this->load->view('admin/utilities/calendar_template'); ?>
<?php $this->load->view('admin/dashboard/dashboard_js'); ?>



<script>
    var employees = [];

    $(document).ready(function() {
        $.ajax({
            type: "GET",
            url: "/birthdays/get_data",
            dataType: "json",
            success: function(data) {
                employees = data;

                console.log(employees);
                checkForData();
                active_style();

            }

        })


    })

    function checkForData() {
        if (employees.length > 0) {
            // Show the widget if there's data
            document.getElementById('celebrantWidget').style.display = 'block';

            // Populate the eventsList or other elements with employee data
            generateAnnouncements();
        } else {
            // Hide the widget if there's no data
            document.getElementById('celebrantWidget').style.display = 'none';
        }
    }

    function formatDate(dateString) {
        const date = new Date(dateString);

        // Get the day
        const day = date.getDate();

        // Get the month name
        const month = date.toLocaleString('default', {
            month: 'long'
        });

        // Return formatted date
        return `${day}-${month}`;
    }
</script>


<script>
    // const employees = [{
    //         name: 'Navneet Baid',
    //         department: 'Software',
    //         birthday: '1989-09-23',
    //         workAnniversary: '2015-09-15',
    //         img: 'https://t2gworkroom.com/assets/images/user-placeholder.jpg'
    //     },
    //     {
    //         name: 'Yogesh Kumar',
    //         department: 'Software',
    //         birthday: '1992-09-23',
    //         workAnniversary: '2017-09-19',
    //         img: 'https://t2gworkroom.com/assets/images/user-placeholder.jpg'
    //     },
    //     {
    //         name: 'Swati',
    //         department: 'Human Resources',
    //         birthday: '1990-09-19',
    //         workAnniversary: '2018-09-23',
    //         img: 'https://t2gworkroom.com/assets/images/user-placeholder.jpg'
    //     },
    //     {
    //         name: 'Bhavya',
    //         department: 'Software',
    //         birthday: '1985-09-23',
    //         workAnniversary: '2012-09-10',
    //         img: 'https://t2gworkroom.com/assets/images/user-placeholder.jpg'
    //     },
    //     {
    //         name: 'Rahul Kumar',
    //         department: 'Digital Marketing',
    //         birthday: '1988-09-23',
    //         workAnniversary: '2013-09-19',
    //         img: 'https://t2gworkroom.com/assets/images/user-placeholder.jpg'
    //     }
    // ];

    const birthday_eventsList = document.getElementById('birthdayeventsList');
    const anniversary_eventList = document.getElementById('anniversaryeventsList');
    const new_joinee_eventList = document.getElementById('newjoineeeventsList');

    const bfullView = document.getElementsByClassName('bfull-view')[0];
    const afullView = document.getElementsByClassName('afull-view')[0];
    const nfullView = document.getElementsByClassName('nfull-view')[0];

    const bgreetingDisplay = document.getElementById('bgreetingDisplay');
    const agreetingDisplay = document.getElementById('agreetingDisplay');
    const ngreetingDisplay = document.getElementById('ngreetingDisplay');

    const bemployeeImg = document.getElementById('bemployeeImg');
    const aemployeeImg = document.getElementById('aemployeeImg');
    const nemployeeImg = document.getElementById('nemployeeImg');

    const bemployeeName = document.getElementById('bemployeeName');
    const aemployeeName = document.getElementById('aemployeeName');
    const nemployeeName = document.getElementById('nemployeeName');

    const bemployeeDept = document.getElementById('bemployeeDept');
    const aemployeeDept = document.getElementById('aemployeeDept');
    const nemployeeDept = document.getElementById('nemployeeDept');

    const bemployeeDate = document.getElementById('bemployeeDate');
    const aemployeeDate = document.getElementById('aemployeeDate');
    const nemployeeDate = document.getElementById('nemployeeDate');

    const bgreetingMessage = document.getElementById('bgreetingMessage');
    const agreetingMessage = document.getElementById('agreetingMessage');
    const ngreetingMessage = document.getElementById('ngreetingMessage');


    const today = moment().format('MM');

    function createEventCard(type, employee, index, years) {
        const eventDiv = document.createElement('div');
        eventDiv.classList.add(type, 'list-group-item');

        let iconClass = type === 'birthday' ? 'fa-birthday-cake' : (type === 'anniversary' ? 'fa-award' : 'fa-user');
        let title = type === 'birthday' ? 'Birthday' : 'Work Anniversary';
        let message = type === 'birthday' ?
            'Wishing you a very Happy Birthday!' : ((years == 0) ? 'Welcome to Tech2Globe Team! 🎉' :
                `Congratulations on completing ${years} year(s) with us!`);
        let eventDate = type === 'birthday' ? formatDate(employee.birthday) : formatDate(employee.workAnniversary);
        eventDiv.innerHTML = `
        <div class="d-flex">
          <i class="fas ${iconClass} event-icon pt-2"></i>
          <div style="width:-webkit-fill-available">
            <div class="event-title"><div>${employee.name}</div><div class="event-date">${eventDate}</div></div>
            <div class="event-department">${employee.department}</div>
            <div class="event-message">${message}</div>
          </div>
        </div>
      `;

        // default view of first card
        if (index == 0) {
            updateGreetingView(employee, type, years);
        }

        eventDiv.addEventListener('click', function() {
            updateGreetingView(employee, type, years);
        });

        return eventDiv;
    }

    function updateGreetingView(employee, type, years) {

        if (type === 'birthday') {
            bemployeeImg.src = employee.img;
            bemployeeName.textContent = employee.name + " (" + employee.emp_id + ")";
            bemployeeDept.textContent = employee.department;
            bfullView.classList.remove('anniversary-view');
            bfullView.classList.add('birthday-view');
            bgreetingMessage.innerHTML = `🎉 Wishing you a very Happy Birthday, ${employee.name}! 🎂 Enjoy your special day and have a wonderful year ahead! 🥳🎈`;
            bemployeeDate.textContent = formatDate(employee.birthday);
        } else {
            if (years != 0) {
                aemployeeImg.src = employee.img;
                aemployeeName.textContent = employee.name + " (" + employee.emp_id + ")";
                aemployeeDept.textContent = employee.department;
                afullView.classList.remove('birthday-view');
                afullView.classList.add('anniversary-view');
                agreetingMessage.innerHTML = `🎊 Congratulations on your ${years}-year work anniversary, ${employee.name}! 🎉 Thank you for being such a valuable part of our team. 🎖️`;
                aemployeeDate.textContent = formatDate(employee.workAnniversary);
            } else {

                nemployeeImg.src = employee.img;
                nemployeeName.textContent = employee.name + " (" + employee.emp_id + ")";
                nemployeeDept.textContent = employee.department;
                nfullView.classList.remove('birthday-view');
                nfullView.classList.add('anniversary-view');
                ngreetingMessage.innerHTML = `We are thrilled to introduce our newest team member, ${employee.name}! 🎉 Join us in welcoming ${employee.name} to the Tech2Globe Team — we’re excited to have you on board!`;
                nemployeeDate.textContent = formatDate(employee.workAnniversary);

            }
        }
    }

    function generateAnnouncements() {
        const today_date = moment();


        // If the anniversary date hasn't passed yet this year, subtract 1 from the calculated years

        employees.forEach((employee, index) => {
            if (moment(employee.birthday).format('MM') === today) {
                birthday_eventsList.appendChild(createEventCard('birthday', employee, index));
            }

            if (moment(employee.workAnniversary).format('MM') === today) {



                const anniversaryThisYear = moment(employee.workAnniversary).year(today_date.year());

                const workAnniversary = moment(employee.workAnniversary);
                let years = today_date.diff(workAnniversary, 'years');

                if (years === 0 && workAnniversary.isSameOrBefore(today_date, 'year')) {

                    new_joinee_eventList.appendChild(createEventCard('newjoinee', employee, index, years));
                } else if (anniversaryThisYear.isAfter(today_date)) {
                    // If the anniversary is yet to come this year, add +1 year
                    years++;
                    anniversary_eventList.appendChild(createEventCard('anniversary', employee, index, years));
                } else {
                    anniversary_eventList.appendChild(createEventCard('anniversary', employee, index, years));
                }

            }
        });
        const noEventDiv = document.createElement('div');
        noEventDiv.classList.add('text-center');
        noEventDiv.innerHTML = `<p>No events today</p>`;

        if (!birthday_eventsList.hasChildNodes()) {
            birthday_eventsList.appendChild(noEventDiv);
        }
        if (!anniversary_eventList.hasChildNodes()) {

            anniversary_eventList.appendChild(noEventDiv);
        }
        if (!new_joinee_eventList.hasChildNodes()) {

            new_joinee_eventList.appendChild(noEventDiv);
        }
    }
</script>


<script>
    function active_style() {
        document.querySelectorAll('.list-group-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.list-group-item').forEach(i => i.classList.remove('current'));
                this.classList.add('current');
            });
        });
    }

    $('#eventTabs a').on('shown.bs.tab', function(e) {
        var activeTab = $(e.target).attr('id'); // get active tab ID

        if (activeTab == 'work-anniversary-tab') {
            $("#anniversaryeventsList .list-group-item:first").click();
            $('#spotlight').text('Spotlight on this month\'s anniversary');
            $('#spotlighticon').attr('class', 'fa fa-award')
        }
        if (activeTab == 'new-joinee-tab') {
            $("#newjoineeeventsList .list-group-item:first").click();
            $('#spotlight').text('Spotlight on this month\'s new joinees');
            $('#spotlighticon').attr('class', 'fa fa-user')
        }
        if (activeTab == 'birthday-tab') {
            $("#birthdayeventsList .list-group-item:first").click();
            $('#spotlight').text('Spotlight on this month\'s birthdays');
            $('#spotlighticon').attr('class', 'fa fa-birthday-cake')
        }
    });
</script>

<script type="module">
    // Wait for the module to load and define Fireworks
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('fireworks-container');

        // Create Fireworks instance
        const fireworks = new Fireworks(container, {
            speed: 2,
            acceleration: 1.05,
            friction: 0.98,
            gravity: 1.5,
            particles: 150,
            trace: 3
        });

        // Launch fireworks on page load
        fireworks.start();


    });
</script>

</body>

</html>