<?php

use app\services\utilities\Date;

defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
    /* adding the css for check out button and checkin/out info */

#timesheets-form-check-in,
#timesheets-form-check-out {
    position: static !important;
    left: auto !important;
    width: auto !important;
    display: inline-block !important;
    margin: 0 !important;
}
    nav .alert {
        padding: 3px 9px;
        margin-top: 4px;
        font-size: 12px;
        margin-right: 26px;
        color: #fffcfc;
        background: #ca8a04;
        border: transparent;
        margin-bottom: 3px;
    }



    /* .sm\:tw-w-\[400px\] {
        width: 350px;
    } */
    .check-time-btn {
        display: none !important;
        /* or any other display property you want */
    }

    /* Only underline Company Policies — not every navbar link */
    .navbar-nav > li > a[href*="company_policies"] {
        text-decoration: underline !important;
        color: white !important;
    }

    @media screen and (min-width: 1100px) {
        .check-time-btn {
            display: inline-flex !important;
            /* or any other display property you want */
        }
    }
	
</style>
<style>
.table-danger td {
    background-color: #f8d7da !important;
    color: #721c24;
}
.body #wrapper th{
		background: #141e46 !important;
    color: #ffffff !important;
	}
</style>

<div id="header">

    <div class="hide-menu tw-ml-1"><i class="fa fa-align-left"></i></div>


    <?php
    if (!function_exists('timesheets_admin_navbar_punch_context')) {
        $CI = &get_instance();
        $CI->load->helper('timesheets/timesheets');
    }

    if (function_exists('get_option')) {
        $tz = (string) get_option('default_timezone');
        if ($tz !== '' && date_default_timezone_get() !== $tz) {
            date_default_timezone_set($tz);
        }
    }

    $cooldown_hours = 13;
    $navbar_punch = function_exists('timesheets_admin_navbar_punch_context')
        ? timesheets_admin_navbar_punch_context($cooldown_hours)
        : [];

    $allows_updating_check_in_time = (int) ($navbar_punch['allows_updating_check_in_time'] ?? 0);
    $html_list = (string) ($navbar_punch['html_list'] ?? '');
    $time_from_checkin = (float) ($navbar_punch['time_from_checkin'] ?? 999);
    $type_check_in_out = $navbar_punch['type_check_in_out'] ?? '';
    $biometric_navbar_active = !empty($navbar_punch['biometric_navbar_active']);
    $biometric_checkin_label = (string) ($navbar_punch['biometric_checkin_label'] ?? '');
    $biometric_is_checked_out = !empty($navbar_punch['biometric_is_checked_out']);
    $biometric_last_out_ts = (int) ($navbar_punch['biometric_last_out_ts'] ?? 0);
    $biometric_break_summary = $navbar_punch['biometric_break_summary'] ?? null;

    // Biometric available → show Biometric pill only. No biometric today → Workroom check in/out.
    ?>

    <nav>

        <div class="tw-flex tw-justify-between">


            <div class="tw-flex tw-flex-1 sm:tw-flex-initial">

                <!-- commenting the search bar -->
                <!-- <div id="top_search" class="tw-inline-flex tw-relative dropdown sm:tw-ml-1.5 sm:tw-mr-3 tw-max-w-xl tw-flex-auto" data-toggle="tooltip" data-placement="bottom" data-title="<?php echo _l('search_by_tags'); ?>">

                    <input type="search" id="search_input" class="tw-px-4 tw-ml-1 tw-mt-2.5 focus:!tw-ring-0 tw-w-full !tw-placeholder-neutral-400 !tw-shadow-none tw-text-neutral-800 focus:!tw-placeholder-neutral-600 hover:!tw-placeholder-neutral-600 sm:tw-w-[400px] tw-h-[40px] tw-bg-neutral-300/30 hover:tw-bg-neutral-300/50 !tw-border-0" placeholder="<?php echo _l('top_search_placeholder'); ?>" autocomplete="off">

                    <div id="top_search_button" class="tw-absolute rtl:tw-left-0 -tw-mt-2 tw-top-1.5 ltr:tw-right-1">

                        <button class="tw-outline-none tw-border-0 tw-text-neutral-600">

                            <i class="fa fa-search"></i>

                        </button>

                    </div>

                    <div id="search_results">

                    </div>

                    <ul class="dropdown-menu search-results animated fadeIn search-history" id="search-history">

                    </ul>



                </div> -->

                <ul class="nav navbar-nav visible-md visible-lg">



                    <?php

                    $quickActions = collect($this->app->get_quick_actions_links())->reject(function ($action) {

                        return isset($action['permission']) && !has_permission($action['permission'], '', 'create');
                    });

                    ?>

                    <!-- commenting the quick create button -->
                    <!-- <?php if ($quickActions->isNotEmpty()) { ?>

                        <li class="icon tw-relative ltr:tw-mr-1.5 rtl:tw-ml-1.5" title="<?php echo _l('quick_create'); ?>" data-toggle="tooltip" data-placement="bottom">

                            <a href="#" class="!tw-px-0 tw-group !tw-text-white" data-toggle="dropdown">

                                <span class="tw-rounded-full tw-bg-primary-600 tw-text-white tw-inline-flex tw-items-center tw-justify-center tw-h-7 tw-w-7 -tw-mt-1 group-hover:!tw-bg-primary-700">

                                    <i class="fa-regular fa-plus fa-lg"></i>

                                </span>

                            </a>

                            <ul class="dropdown-menu dropdown-menu-right animated fadeIn tw-text-base">

                                <li class="dropdown-header tw-mb-1">

                                    <?php echo _l('quick_create'); ?>

                                </li>

                                <?php foreach ($quickActions as $key => $item) {

                                    $url = '';

                                    if (isset($item['permission'])) {

                                        if (!has_permission($item['permission'], '', 'create')) {

                                            continue;
                                        }
                                    }

                                    if (isset($item['custom_url'])) {

                                        $url = $item['url'];
                                    } else {

                                        $url = admin_url('' . $item['url']);
                                    }

                                    $href_attributes = '';

                                    if (isset($item['href_attributes'])) {

                                        foreach ($item['href_attributes'] as $key => $val) {

                                            $href_attributes .= $key . '=' . '"' . $val . '"';
                                        }
                                    } ?>

                                    <li>

                                        <a href="<?php echo $url; ?>" <?php echo $href_attributes; ?> class="tw-group tw-inline-flex tw-space-x-0.5 tw-text-neutral-700">

                                            <?php if (isset($item['icon'])) { ?>

                                                <i class="<?php echo $item['icon']; ?> tw-text-neutral-400 group-hover:tw-text-neutral-600 tw-h-5 tw-w-5"></i>

                                            <?php } ?>

                                            <span>

                                                <?php echo $item['name']; ?>

                                            </span>

                                        </a>

                                    </li>

                                <?php

                                } ?>

                            </ul>

                        </li>

                    <?php } ?> -->


                    <li>
                        <a href="/admin/company_policies">Company Policies</a>
                    </li>

                    <!-- Left: actions + status (Biometric punch when synced, else Workroom check in/out) -->
                    <li id="headerActionButtons" class="header-action-buttons check-time-btn">
                        <?php if ($biometric_navbar_active) { ?>
                            <span class="header-biometric-pill" title="Office Biometric device punch (from sheet)">
                                <span class="header-source-tag">Biometric</span> First check in : <?php echo html_escape($biometric_checkin_label); ?>
                            </span>
                            <?php if (is_array($biometric_break_summary)) { ?>
                            <span class="header-biometric-break-pill header-break-pill"
                                title="<?php echo html_escape($biometric_break_summary['tooltip'] ?? ''); ?>"
                                data-break-completed-secs="<?php echo (int) ($biometric_break_summary['completed_secs'] ?? 0); ?>"
                                data-break-out-ts="<?php echo !empty($biometric_break_summary['on_break']) ? (int) ($biometric_break_summary['current_out_ts'] ?? 0) : 0; ?>">
                                <span class="header-source-tag">Break</span>
                                Total : <span class="header-break-live"><?php echo html_escape($biometric_break_summary['total_label']); ?></span>
                            </span>
                            <?php } ?>
                        <?php } else { ?>
                            <?php
                            if ($type_check_in_out != 1 && ($time_from_checkin >= 13 || $allows_updating_check_in_time == 1)) {
                                echo form_open(admin_url('timesheets/check_in_ts'), array('id' => 'timesheets-form-check-in', 'onsubmit' => 'get_data()', 'class' => 'header-action-form')); ?>
                                <input type="hidden" name="staff_id" value="<?php echo get_staff_user_id(); ?>">
                                <input type="hidden" name="type_check" value="1">
                                <input type="hidden" name="edit_date" value="">
                                <input type="hidden" name="point_id" value="">
                                <input type="hidden" name="location_user" value="">
                                <button type="submit" class="btn btn-success check_in"><?php echo 'Check in'; ?></button>
                            <?php echo form_close();
                            } ?>

                            <?php
                            if ($type_check_in_out == 1) {
                                echo form_open(admin_url('timesheets/check_in_ts'), array('id' => 'timesheets-form-check-out', 'onsubmit' => 'get_data()', 'class' => 'header-action-form')); ?>
                                <input type="hidden" name="staff_id" value="<?php echo get_staff_user_id(); ?>">
                                <input type="hidden" name="type_check" value="2">
                                <input type="hidden" name="edit_date" value="">
                                <input type="hidden" name="point_id" value="">
                                <input type="hidden" name="location_user" value="">
                                <button type="submit" class="btn btn-danger check_out"><?php echo 'Check out'; ?></button>
                            <?php echo form_close();
                            } ?>

                            <?php
                            if (!($time_from_checkin >= 13)) {
                                echo $html_list;
                            }
                            ?>
                        <?php } ?>
                    </li>

                    <style>
                        #headerActionButtons.header-action-buttons {
                            display: inline-flex !important;
                            align-items: center;
                            gap: 6px;
                            padding: 6px 8px !important;
                            width: auto !important;
                            vertical-align: middle;
                            list-style: none;
                            flex-wrap: nowrap;
                        }
                        #headerActionButtons .header-action-form {
                            display: inline-block !important;
                            position: static !important;
                            left: auto !important;
                            width: auto !important;
                            margin: 0 !important;
                        }
                        #headerActionButtons .btn {
                            margin: 0 !important;
                            vertical-align: middle;
                            white-space: nowrap;
                        }
                        #headerActionButtons .btn-suggestion-header {
                            background-color: #0ea5e9 !important;
                            border: 1px solid #38bdf8 !important;
                            color: #fff !important;
                            font-weight: 600;
                            box-shadow: 0 0 0 1px rgba(56, 189, 248, 0.45);
                        }
                        #headerActionButtons .btn-suggestion-header:hover,
                        #headerActionButtons .btn-suggestion-header:focus {
                            background-color: #0284c7 !important;
                            border-color: #7dd3fc !important;
                            color: #fff !important;
                        }
                        .header-source-tag {
                            display: inline-block;
                            padding: 0 4px;
                            border-radius: 3px;
                            font-size: 8px;
                            font-weight: 800;
                            letter-spacing: 0.03em;
                            text-transform: uppercase;
                            line-height: 1.35;
                        }
                        .header-biometric-pill,
                        .header-workroom-pill {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            gap: 4px;
                            padding: 3px 8px;
                            border-radius: 5px;
                            color: #fff !important;
                            font-size: 11px;
                            font-weight: 600;
                            line-height: 1.2;
                            white-space: nowrap;
                            font-variant-numeric: tabular-nums;
                            cursor: default;
                            user-select: none;
                            pointer-events: none;
                            border: none;
                            box-shadow: none;
                            transition: none !important;
                            text-decoration: none !important;
                            margin: 0 !important;
                            height: 24px;
                        }
                        .header-biometric-pill {
                            background-color: #0a1140 !important;
                        }
                        .header-biometric-pill .header-source-tag {
                            background: rgba(96, 165, 250, 0.25);
                            color: #93c5fd;
                        }
                        .header-biometric-break-pill {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            gap: 4px;
                            padding: 3px 8px;
                            border-radius: 5px;
                            color: #fff !important;
                            font-size: 11px;
                            font-weight: 600;
                            line-height: 1.2;
                            white-space: nowrap;
                            font-variant-numeric: tabular-nums;
                            cursor: help;
                            user-select: none;
                            margin: 0 !important;
                            height: 24px;
                            background-color: #b45309 !important;
                        }
                        .header-biometric-break-pill .header-source-tag {
                            background: rgba(0, 0, 0, 0.18);
                            color: #fde68a;
                        }
                        .header-workroom-pill {
                            background-color: #ca8a04 !important;
                        }
                        .header-workroom-pill .header-source-tag {
                            background: rgba(0, 0, 0, 0.18);
                            color: #fff;
                        }
                        @media screen and (max-width: 1099px) {
                            #headerActionButtons.header-action-buttons {
                                display: none !important;
                            }
                        }
                    </style>
                    <script>
                    (function () {
                        function formatBreak(secs) {
                            secs = Math.max(0, parseInt(secs, 10) || 0);
                            if (secs < 60) {
                                return secs + 's';
                            }
                            var mins = Math.floor(secs / 60);
                            if (mins < 60) {
                                return mins + 'm';
                            }
                            var h = Math.floor(mins / 60);
                            var m = mins % 60;
                            return m > 0 ? (h + 'h ' + m + 'm') : (h + 'h');
                        }
                        document.querySelectorAll('.header-break-pill').forEach(function (pill) {
                            var live = pill.querySelector('.header-break-live');
                            if (!live) {
                                return;
                            }
                            var completedSecs = parseInt(pill.getAttribute('data-break-completed-secs') || '0', 10);
                            var outTs = parseInt(pill.getAttribute('data-break-out-ts') || '0', 10);
                            function tick() {
                                var currentSecs = outTs > 0
                                    ? Math.max(0, Math.floor(Date.now() / 1000) - outTs)
                                    : 0;
                                live.textContent = formatBreak(completedSecs + currentSecs);
                            }
                            tick();
                            if (outTs > 0) {
                                setInterval(tick, 1000);
                            }
                        });
                    })();
                    </script>
                </ul>

            </div>



            <div class="mobile-menu tw-shrink-0 ltr:tw-ml-4 rtl:tw-mr-4">

                <button type="button" class="navbar-toggle visible-md visible-sm visible-xs mobile-menu-toggle collapsed tw-ml-1.5" data-toggle="collapse" data-target="#mobile-collapse" aria-expanded="false">

                    <i class="fa fa-chevron-down fa-lg"></i>

                </button>

                <ul class="mobile-icon-menu tw-inline-flex tw-mt-5">

                    <?php

                    // To prevent not loading the timers twice

                    if (is_mobile()) { ?>

                        <li class="dropdown notifications-wrapper header-notifications tw-block ltr:tw-mr-1.5 rtl:tw-ml-1.5">

                            <?php $this->load->view('admin/includes/notifications'); ?>

                        </li>

                        <li class="header-timers ltr:tw-mr-1.5 rtl:tw-ml-1.5">

                            <a href="#" id="top-timers" class="dropdown-toggle top-timers tw-block tw-h-5 tw-w-5" data-toggle="dropdown">

                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="tw-w-5 tw-h-5 tw-shrink-0">

                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />

                                </svg>

                                <span class="tw-leading-none tw-px-1 tw-py-0.5 tw-text-xs bg-success tw-z-10 tw-absolute tw-rounded-full -tw-right-3 -tw-top-2 tw-min-w-[18px] tw-min-h-[18px] tw-inline-flex tw-items-center tw-justify-center icon-started-timers<?php echo $totalTimers = count($startedTimers) == 0 ? ' hide' : ''; ?>"><?php echo count($startedTimers); ?></span>

                            </a>

                            <ul class="dropdown-menu animated fadeIn started-timers-top width300" id="started-timers-top">

                                <?php $this->load->view('admin/tasks/started_timers', ['startedTimers' => $startedTimers]); ?>

                            </ul>

                        </li>

                    <?php } ?>

                </ul>

                <div class="mobile-navbar collapse" id="mobile-collapse" aria-expanded="false" style="height: 0px;" role="navigation">

                    <ul class="nav navbar-nav">

                        <li class="header-my-profile"><a href="<?php echo admin_url('profile'); ?>">

                                <?php echo _l('nav_my_profile'); ?>

                            </a>

                        </li>

                        <li class="header-my-timesheets"><a href="<?php echo admin_url('staff/timesheets'); ?>">

                                <?php echo _l('my_timesheets'); ?>

                            </a>

                        </li>

                        <li class="header-edit-profile"><a href="<?php echo admin_url('staff/edit_profile'); ?>">

                                <?php echo _l('nav_edit_profile'); ?>

                            </a>

                        </li>

                        <?php if (is_staff_member()) { ?>

                            <li class="header-newsfeed">

                                <a href="#" class="open_newsfeed mobile">

                                    <?php echo _l('whats_on_your_mind'); ?>

                                </a>

                            </li>

                        <?php } ?>

                        <li class="header-logout">

                            <a href="#" onclick="logout(); return false;">

                                <?php echo _l('nav_logout'); ?>

                            </a>

                        </li>

                    </ul>

                </div>

            </div>



            <ul class="nav navbar-nav navbar-right right-elements">

                <?php //do_action_deprecated('after_render_top_search', [], '3.0.0', 'admin_navbar_start'); 
                ?>

                <?php //hooks()->do_action('admin_navbar_start'); 
                ?>


                <?php// if (is_staff_member()) { ?>

                    <!--<li class="icon header-newsfeed">

                        <a href="#" class="open_newsfeed desktop" data-toggle="tooltip" title="<?php echo _l('whats_on_your_mind'); ?>" data-placement="bottom">

                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="tw-w-5 tw-h-5">

                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" />

                            </svg>

                        </a>

                    </li>-->

                <?php// } ?>



                <!--<li class="icon header-todo">

                    <a href="<?php// echo admin_url('todo'); ?>" data-toggle="tooltip" title="<?php //echo _l('nav_todo_items'); ?>" data-placement="bottom" class="">

                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="tw-w-5 tw-h-5 tw-shrink-0">

                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />

                        </svg>



                        <span class="tw-leading-none tw-px-1 tw-py-0.5 tw-text-xs bg-warning tw-z-10 tw-absolute tw-rounded-full tw-right-1 tw-top-2.5 tw-min-w-[18px] tw-min-h-[18px] tw-inline-flex tw-items-center tw-justify-center nav-total-todos<?php //echo $current_user->total_unfinished_todos == 0 ? ' hide' : ''; ?>">

                            <?php //echo $current_user->total_unfinished_todos; ?>

                        </span>

                    </a>

                </li>-->
				<li class="icon header-todo">
				<a href="/Snapshot.zip" download style="color:#fff; text-decoration: underline !important;font-size: 14px;"><i class="fa fa-download" aria-hidden="true"></i> Snapshot</a>
				</li>	
                <li class="icon header-user-profile" data-toggle="tooltip" title="<?php echo get_staff_full_name(); ?>" data-placement="bottom">

                    <a href="#" class="dropdown-toggle profile tw-block rtl:!tw-px-0.5 !tw-py-1" data-toggle="dropdown" aria-expanded="false">

                        <?php echo staff_profile_image($current_user->staffid, ['img', 'img-responsive', 'staff-profile-image-small', 'tw-ring-1 tw-ring-offset-2 tw-ring-primary-500 tw-mx-1 tw-mt-2.5']); ?>

                    </a>

                    <ul class="dropdown-menu animated fadeIn">

                        <li class="header-my-profile"><a href="<?php echo admin_url('profile'); ?>"><?php echo _l('nav_my_profile'); ?></a></li>

                        <li class="header-suggestion-box">
                            <a href="#" id="openStaffSuggestionModal" class="open-staff-suggestion" onclick="return window.openStaffSuggestionModal ? window.openStaffSuggestionModal(event) : false;"><?php echo _l('suggestion_box'); ?> Box</a>
                        </li>

                        <!-- <li class="header-my-timesheets"><a href="<?php echo admin_url('staff/timesheets'); ?>"><?php echo _l('my_timesheets'); ?></a>

                        </li>

                        <li class="header-edit-profile"><a href="<?php echo admin_url('staff/edit_profile'); ?>"><?php echo _l('nav_edit_profile'); ?></a>

                        </li> -->

                        <?php if (!is_language_disabled()) { ?>

                            <!-- <li class="dropdown-submenu pull-left header-languages">

                                <a href="#" tabindex="-1"><?php echo _l('language'); ?></a>

                                <ul class="dropdown-menu dropdown-menu">

                                    <li class="<?php echo $current_user->default_language == '' ? 'active' : ''; ?>">

                                        <a href="<?php echo admin_url('staff/change_language'); ?>">

                                            <?php echo _l('system_default_string'); ?>

                                        </a>

                                    </li>

                                    <?php foreach ($this->app->get_available_languages() as $user_lang) { ?>

                                        <li class="<?php echo $current_user->default_language == $user_lang ? 'active' : ''; ?>">

                                            <a href="<?php echo admin_url('staff/change_language/' . $user_lang); ?>">

                                                <?php echo ucfirst($user_lang); ?>

                                            </a>

                                        <?php } ?>

                                </ul>

                            </li> -->

                        <?php } ?>

                        <li class="header-logout">

                            <a href="#" onclick="logout(); return false;"><?php echo _l('nav_logout'); ?></a>

                        </li>

                    </ul>

                </li>



                <li class="icon header-timers timer-button tw-relative ltr:tw-mr-1.5 rtl:tw-ml-1.5" data-placement="bottom" data-toggle="tooltip" data-title="<?php echo _l('my_timesheets'); ?>">

                    <a href="#" id="top-timers" class="top-timers !tw-px-0 tw-group" data-toggle="dropdown">

                        <span class="tw-rounded-md tw-border tw-border-solid tw-border-neutral-200/60 tw-inline-flex tw-items-center tw-justify-center tw-h-8 tw-w-9 -tw-mt-1.5 group-hover:!tw-bg-neutral-100/60">

                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="tw-w-5 tw-h-5 tw-text-neutral-900 tw-shrink-0<?php echo  count($startedTimers) > 0 ? ' tw-animate-spin-slow' : ''; ?>">

                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />

                            </svg>

                        </span>

                        <span class="tw-leading-none tw-px-1 tw-py-0.5 tw-text-xs bg-success tw-z-10 tw-absolute tw-rounded-full -tw-right-1.5 tw-top-2 tw-min-w-[18px] tw-min-h-[18px] tw-inline-flex tw-items-center tw-justify-center icon-started-timers<?php echo $totalTimers = count($startedTimers) == 0 ? ' hide' : ''; ?>">

                            <?php echo count($startedTimers); ?>

                        </span>

                    </a>

                    <ul class="dropdown-menu animated fadeIn started-timers-top width300" id="started-timers-top">

                        <?php $this->load->view('admin/tasks/started_timers', ['startedTimers' => $startedTimers]); ?>

                    </ul>

                </li>



                <li class="icon dropdown tw-relative tw-block notifications-wrapper header-notifications rtl:tw-ml-3" data-toggle="tooltip" title="<?php echo _l('nav_notifications'); ?>" data-placement="bottom">

                    <?php $this->load->view('admin/includes/notifications'); ?>

                </li>







                <?php hooks()->do_action('admin_navbar_end'); ?>

            </ul>

        </div>


    </nav>

<!-- Suggestion popup: outside nav/ul so it always opens -->
<div id="staffSuggestionModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" style="display:none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" id="closeStaffSuggestionModalX" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('suggestion_box'); ?></h4>
            </div>
            <form id="staff-suggestion-form" method="post" action="<?php echo admin_url('suggestions/submit'); ?>">
                <div class="modal-body">
                    <p class="text-muted"><?php echo _l('suggestion_box_help'); ?></p>
                    <div class="form-group">
                        <label for="suggestion_subject"><?php echo _l('suggestion_subject'); ?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="suggestion_subject" name="subject" maxlength="255" required placeholder="<?php echo _l('suggestion_subject_placeholder'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="suggestion_message"><?php echo _l('suggestion_message'); ?> <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="suggestion_message" name="message" rows="5" required placeholder="<?php echo _l('suggestion_message_placeholder'); ?>"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" id="closeStaffSuggestionModalBtn"><?php echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-primary" id="suggestion-submit-btn"><?php echo _l('suggestion_submit'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<style>
/* Keep profile dropdown items left-aligned like My Profile / Logout */
.header-user-profile > .dropdown-menu > li > a,
.header-user-profile > .dropdown-menu > li.header-suggestion-box > a.open-staff-suggestion {
    text-align: left !important;
    display: block !important;
    justify-content: flex-start !important;
}
#staffSuggestionModal {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    z-index: 99999 !important;
    overflow-x: hidden;
    overflow-y: auto;
    background: rgba(0,0,0,0.45);
}
#staffSuggestionModal .modal-dialog {
    margin: 8vh auto;
    max-width: 560px;
    width: 92%;
    z-index: 100000 !important;
}
#staffSuggestionModal.in,
#staffSuggestionModal.show {
    display: block !important;
    opacity: 1 !important;
}
</style>
<script>
(function () {
    function getSuggestionModal() {
        return document.getElementById('staffSuggestionModal');
    }
    function openStaffSuggestionModal(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        var modal = getSuggestionModal();
        if (!modal) {
            alert('Suggestion popup missing');
            return false;
        }
        if (modal.parentNode !== document.body) {
            document.body.appendChild(modal);
        }
        modal.style.display = 'block';
        modal.classList.add('in');
        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        return false;
    }
    function closeStaffSuggestionModal(e) {
        if (e) {
            e.preventDefault();
        }
        var modal = getSuggestionModal();
        if (!modal) {
            return false;
        }
        modal.style.display = 'none';
        modal.classList.remove('in');
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        return false;
    }
    window.openStaffSuggestionModal = openStaffSuggestionModal;
    window.closeStaffSuggestionModal = closeStaffSuggestionModal;

    // Move modal to body immediately
    var m = getSuggestionModal();
    if (m && m.parentNode !== document.body) {
        document.body.appendChild(m);
    }

    document.addEventListener('click', function (e) {
        var t = e.target;
        while (t && t !== document) {
            if (t.id === 'openStaffSuggestionModal' || (t.classList && t.classList.contains('open-staff-suggestion')) || (t.closest && t.closest('#openStaffSuggestionModal, .open-staff-suggestion'))) {
                openStaffSuggestionModal(e);
                return;
            }
            if (t.id === 'closeStaffSuggestionModalBtn' || t.id === 'closeStaffSuggestionModalX') {
                closeStaffSuggestionModal(e);
                return;
            }
            if (t.id === 'staffSuggestionModal' && e.target === t) {
                closeStaffSuggestionModal(e);
                return;
            }
            t = t.parentNode;
        }
    }, true);

    function bindSuggestionSubmit() {
        if (typeof jQuery === 'undefined') {
            return;
        }
        var $ = jQuery;
        var $form = $('#staff-suggestion-form');
        if (!$form.length || $form.data('suggestion-bound')) {
            return;
        }
        $form.data('suggestion-bound', true);
        $form.on('submit', function (ev) {
            ev.preventDefault();
            var $btn = $('#suggestion-submit-btn');
            $btn.prop('disabled', true).text(<?php echo json_encode(_l('wait_text')); ?>);
            var data = {
                subject: $('#suggestion_subject').val(),
                message: $('#suggestion_message').val()
            };
            if (typeof csrfData !== 'undefined') {
                data[csrfData.token_name] = csrfData.hash;
            }
            $.ajax({
                url: <?php echo json_encode(admin_url('suggestions/submit')); ?>,
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function (response) {
                    if (response && response.success) {
                        if (typeof alert_float === 'function') {
                            alert_float('success', response.message);
                        } else {
                            alert(response.message || 'Submitted');
                        }
                        closeStaffSuggestionModal();
                        $form[0].reset();
                    } else {
                        var fail = (response && response.message) ? response.message : <?php echo json_encode(_l('suggestion_submit_failed')); ?>;
                        if (typeof alert_float === 'function') {
                            alert_float('danger', fail);
                        } else {
                            alert(fail);
                        }
                    }
                },
                error: function () {
                    var msg = <?php echo json_encode(_l('suggestion_submit_failed')); ?>;
                    if (typeof alert_float === 'function') {
                        alert_float('danger', msg);
                    } else {
                        alert(msg);
                    }
                },
                complete: function () {
                    $btn.prop('disabled', false).text(<?php echo json_encode(_l('suggestion_submit')); ?>);
                }
            });
        });
    }
    if (typeof jQuery !== 'undefined') {
        jQuery(bindSuggestionSubmit);
    } else {
        document.addEventListener('DOMContentLoaded', bindSuggestionSubmit);
        window.addEventListener('load', bindSuggestionSubmit);
    }
})();
</script>

</div>
