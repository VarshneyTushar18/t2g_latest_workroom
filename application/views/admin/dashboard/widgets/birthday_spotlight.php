<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $staff_placeholder = base_url('assets/images/user-placeholder.jpg'); ?>
<div class="dashboard-birthday-spotlight">
    <p class="dashboard-birthday-spotlight-title">
        <i id="spotlighticon" class="fa fa-birthday-cake" aria-hidden="true"></i>
        <span id="spotlight">Spotlight on this month&apos;s birthdays</span>
    </p>
    <ul class="nav nav-tabs dashboard-birthday-tabs" id="eventTabs" role="tablist">
        <li class="nav-item active">
            <a class="nav-link active" id="birthday-tab" data-toggle="tab" href="#birthday" role="tab" aria-controls="birthday" aria-selected="true">Birthday</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="work-anniversary-tab" data-toggle="tab" href="#workAnniversary" role="tab" aria-controls="workAnniversary" aria-selected="false">Anniversary</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="new-joinee-tab" data-toggle="tab" href="#newJoinee" role="tab" aria-controls="newJoinee" aria-selected="false">New joinee</a>
        </li>
    </ul>
    <div class="tab-content dashboard-birthday-tab-content" id="eventTabContent">
        <div class="tab-pane fade active in" id="birthday" role="tabpanel" aria-labelledby="birthday-tab">
            <div class="dashboard-birthday-stack">
                <div class="dashboard-birthday-list-wrap">
                    <div id="birthdayeventsList" class="list-group dashboard-birthday-list"></div>
                </div>
                <div class="dashboard-birthday-greeting">
                    <div id="bgreetingDisplay" class="dashboard-birthday-greeting-inner">
                        <div class="dashboard-birthday-greeting-head">
                            <img id="bemployeeImg" src="<?php echo html_escape($staff_placeholder); ?>" alt="Employee">
                            <div class="dashboard-birthday-greeting-meta">
                                <div id="bemployeeName" class="employee-name">—</div>
                                <div id="bemployeeDept" class="employee-dept">—</div>
                                <div id="bemployeeDate" class="employee-date">—</div>
                            </div>
                        </div>
                        <div id="bgreetingMessage" class="greeting-message">Select a colleague to see their birthday message.</div>
                        <div class="emoji">🎂🎁🎈</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="workAnniversary" role="tabpanel" aria-labelledby="work-anniversary-tab">
            <div class="dashboard-birthday-stack">
                <div class="dashboard-birthday-list-wrap">
                    <div id="anniversaryeventsList" class="list-group dashboard-birthday-list"></div>
                </div>
                <div class="dashboard-birthday-greeting">
                    <div id="agreetingDisplay" class="dashboard-birthday-greeting-inner">
                        <div class="dashboard-birthday-greeting-head">
                            <img id="aemployeeImg" src="<?php echo html_escape($staff_placeholder); ?>" alt="Employee">
                            <div class="dashboard-birthday-greeting-meta">
                                <div id="aemployeeName" class="employee-name">—</div>
                                <div id="aemployeeDept" class="employee-dept">—</div>
                                <div id="aemployeeDate" class="employee-date">—</div>
                            </div>
                        </div>
                        <div id="agreetingMessage" class="greeting-message">—</div>
                        <div class="emoji">🎖️</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="newJoinee" role="tabpanel" aria-labelledby="new-joinee-tab">
            <div class="dashboard-birthday-stack">
                <div class="dashboard-birthday-list-wrap">
                    <div id="newjoineeeventsList" class="list-group dashboard-birthday-list"></div>
                </div>
                <div class="dashboard-birthday-greeting">
                    <div id="greetingDisplay" class="dashboard-birthday-greeting-inner">
                        <div class="dashboard-birthday-greeting-head">
                            <img id="nemployeeImg" src="<?php echo html_escape($staff_placeholder); ?>" alt="Employee">
                            <div class="dashboard-birthday-greeting-meta">
                                <div id="nemployeeName" class="employee-name">—</div>
                                <div id="nemployeeDept" class="employee-dept">—</div>
                                <div id="nemployeeDate" class="employee-date">—</div>
                            </div>
                        </div>
                        <div id="ngreetingMessage" class="greeting-message">—</div>
                        <div class="emoji">🎉</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
