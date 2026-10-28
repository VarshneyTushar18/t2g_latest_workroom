<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
var user_dashboard_visibility = <?php echo $user_dashboard_visibility; ?>;
$(function() {
    $("[data-container]").sortable({
        connectWith: "[data-container]",
        helper: 'clone',
        handle: '.widget-dragger',
        tolerance: 'pointer',
        forcePlaceholderSize: true,
        placeholder: 'placeholder-dashboard-widgets',
        start: function(event, ui) {
            $("body,#wrapper").addClass('noscroll');
            $('body').find('[data-container]').css('min-height', '20px');
        },
        stop: function(event, ui) {
            $("body,#wrapper").removeClass('noscroll');
            $('body').find('[data-container]').removeAttr('style');
        },
        update: function(event, ui) {
            if (this === ui.item.parent()[0]) {
                var data = {};
                $.each($("[data-container]"), function() {
                    var cId = $(this).attr('data-container');
                    data[cId] = $(this).sortable('toArray');
                    if (data[cId].length == 0) {
                        data[cId] = 'empty';
                    }
                });
                $.post(admin_url + 'staff/save_dashboard_widgets_order', data, "json");
            }
        }
    });

    // Read more for dashboard todo items
    $('.read-more').readmore({
        collapsedHeight: 150,
        moreLink: "<a href=\"#\"><?php echo _l('read_more'); ?></a>",
        lessLink: "<a href=\"#\"><?php echo _l('show_less'); ?></a>",
    });

    $('body').on('click', '#viewWidgetableArea', function(e) {
        e.preventDefault();

        if (!$(this).hasClass('preview')) {
            $(this).html("<?php echo _l('hide_widgetable_area'); ?>");
            $('[data-container]').append(
                '<div class="placeholder-dashboard-widgets pl-preview"></div>');
        } else {
            $(this).html("<?php echo _l('view_widgetable_area'); ?>");
            $('[data-container]').find('.pl-preview').remove();
        }

        $('[data-container]').toggleClass('preview-widgets');
        $(this).toggleClass('preview');
    });

    var $widgets = $('.widget');
    var widgetsOptionsHTML = '';
    widgetsOptionsHTML += '<div id="dashboard-options">';
    widgetsOptionsHTML +=
        "<div class=\"tw-flex tw-space-x-4 tw-items-center\"><h4 class='tw-font-medium tw-text-neutral-600 tw-text-lg'><i class='fa-regular fa-circle-question' data-toggle='tooltip' data-placement=\"bottom\" data-title=\"<?php echo _l('widgets_visibility_help_text'); ?>\"></i> <?php echo _l('widgets'); ?></h4><a href=\"<?php echo admin_url('staff/reset_dashboard'); ?>\" class=\"tw-text-sm\"><?php echo _l('reset_dashboard'); ?></a>";

    widgetsOptionsHTML +=
        ' <a href=\"#\" id="viewWidgetableArea" class=\"tw-text-sm\"><?php echo _l('view_widgetable_area'); ?></a></div>';

    $.each($widgets, function() {
        var widget = $(this);
        var widgetOptionsHTML = '';
        if (widget.data('name') && widget.html().trim().length > 0) {
            widgetOptionsHTML += '<div class="checkbox">';
            var wID = widget.attr('id');
            wID = wID.split('widget-');
            wID = wID[wID.length - 1];
            var checked = ' ';
            var db_result = $.grep(user_dashboard_visibility, function(e) {
                return e.id == wID;
            });
            if (db_result.length >= 0) {
                // no options saved or really visible
                if (typeof(db_result[0]) == 'undefined' || db_result[0]['visible'] == 1) {
                    checked = ' checked ';
                }
            }
            widgetOptionsHTML += '<input type="checkbox" class="widget-visibility" value="' + wID +
                '"' + checked + 'id="widget_option_' + wID + '" name="dashboard_widgets[' + wID + ']">';
            widgetOptionsHTML += '<label for="widget_option_' + wID + '">' + widget.data('name') +
                '</label>';
            widgetOptionsHTML += '</div>';
        }
        widgetsOptionsHTML += widgetOptionsHTML;
    });

    $('.screen-options-area').append(widgetsOptionsHTML);
    $('body').find('#dashboard-options input.widget-visibility').on('change', function() {
        if ($(this).prop('checked') == false) {
            $('#widget-' + $(this).val()).addClass('hide');
        } else {
            $('#widget-' + $(this).val()).removeClass('hide');
        }

        var data = {};
        var options = $('#dashboard-options input[type="checkbox"]').map(function() {
            return {
                id: this.value,
                visible: this.checked ? 1 : 0
            };
        }).get();

        data.widgets = options;
        /*
                if (typeof(csrfData) !== 'undefined') {
                    data[csrfData['token_name']] = csrfData['hash'];
                }
        */
        $.post(admin_url + 'staff/save_dashboard_widgets_visibility', data).fail(function(data) {
            // Demo usage, prevent multiple alerts
            if ($('body').find('.float-alert').length == 0) {
                alert_float('danger', data.responseText);
            }
        });
    });

    var leads_chart = $('#leads_status_stats');
    var projects_chart = $('#projects_status_stats');

    if (leads_chart.length > 0) {
        // Leads overview status
        new Chart(leads_chart, {
            type: 'doughnut',
            data: <?php echo $leads_status_stats; ?>,
            options: {
                maintainAspectRatio: false,
                onClick: function(evt) {
                    onChartClickRedirect(evt, this);
                }
            }
        });
    }
    if (projects_chart.length > 0) {
        // Projects statuses
        new Chart(projects_chart, {
            type: 'doughnut',
            data: <?php echo $projects_status_stats; ?>,
            options: {
                maintainAspectRatio: false,
                onClick: function(evt) {
                    onChartClickRedirect(evt, this);
                }
            }
        });
    }

    fix_user_data_widget_tabs();
    $(window).on('resize', function() {
        $('.horizontal-scrollable-tabs ul.nav-tabs-horizontal').removeAttr('style');
        fix_user_data_widget_tabs();
    });
});

function fix_user_data_widget_tabs() {
    if ((app.browser != 'firefox' &&
            isRTL == 'false' && is_mobile()) || (app.browser == 'firefox' &&
            isRTL == 'false' && is_mobile())) {
        $('.horizontal-scrollable-tabs ul.nav-tabs-horizontal').css('margin-bottom', '26px');
    }
}

(function initDashboardAttendanceCalendar() {
    var grid = document.getElementById('dashboardHrmsCalGrid');
    if (!grid) {
        return;
    }

    var attStaffId = <?php echo (int) get_staff_user_id(); ?>;
    var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    var calView = new Date();
    calView.setDate(1);
    var monthLabel = document.getElementById('dashboardHrmsCalMonthLabel');
    var dayMap = {};
    var calLoading = false;

    function pad(n) {
        return (n < 10 ? '0' : '') + n;
    }

    function ymd(d) {
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }

    function monthKey(d) {
        return d.getFullYear() + '-' + pad(d.getMonth() + 1);
    }

    function parseHrs(val) {
        if (!val || val === '—') {
            return null;
        }
        var n = parseFloat(String(val).replace('h', ''));
        return isNaN(n) ? null : n;
    }

    function dayHours(day) {
        if (!day) {
            return 0;
        }
        if (typeof day.hours === 'number' && day.hours > 0) {
            return day.hours;
        }
        var aw = parseHrs(day.actual_work_hrs);
        if (aw !== null) {
            return aw;
        }
        return parseHrs(day.total_work_hrs) || 0;
    }

    function dayHealthTone(day) {
        if (!day || day.status === 'future') {
            return 'neutral';
        }
        var code = day.code || '';
        if (day.status === 'weekend' || code === 'O') {
            return 'off';
        }
        if (day.status === 'holiday' || code === 'HO' || code === 'H') {
            return 'holiday';
        }
        if (day.status === 'leave' || day.status === 'saturday_leave') {
            return 'leave';
        }
        if (['EL', 'PL', 'L', 'SL', 'UL', 'LOP', 'CO', 'MAL', 'PHD', 'UHD', 'PAL', 'SHL'].indexOf(code) >= 0) {
            return 'leave';
        }
        if (day.status === 'regularised') {
            return 'ok';
        }
        if (day.status === 'pending') {
            return 'neutral';
        }
        var presentMin = day.present_min_hours || 8.0;
        var halfMin = day.half_day_min_hours || 5;
        var hrs = dayHours(day);
        if (day.status === 'absent' || day.status === 'night_carryover' || day.status === 'punch_missing' || code === 'AB' || code === 'A' || code === 'NC') {
            return 'bad';
        }
        if (day.status === 'rejected') {
            if (hrs + 0.001 >= presentMin) {
                return 'ok';
            }
            if (hrs + 0.001 >= halfMin) {
                return 'half';
            }
            return 'bad';
        }
        if (day.status === 'half_day' || code === 'HD') {
            return 'half';
        }
        if (day.status === 'ok' || code === 'P') {
            return hrs + 0.001 >= presentMin ? 'ok' : (hrs + 0.001 >= halfMin ? 'half' : (hrs > 0 ? 'bad' : 'neutral'));
        }
        if (day.status === 'short_hours') {
            return hrs + 0.001 >= halfMin ? 'half' : 'bad';
        }
        if (hrs + 0.001 >= presentMin) {
            return 'ok';
        }
        if (hrs + 0.001 >= halfMin) {
            return 'half';
        }
        if (hrs > 0) {
            return 'bad';
        }
        return 'neutral';
    }

    function dayCodeClass(tone) {
        if (tone === 'ok') {
            return 'code-ok';
        }
        if (tone === 'half') {
            return 'code-half';
        }
        if (tone === 'bad') {
            return 'code-bad';
        }
        if (tone === 'leave') {
            return 'code-leave';
        }
        if (tone === 'holiday') {
            return 'code-holiday';
        }
        if (tone === 'off') {
            return 'code-off';
        }
        return '';
    }

    function dayCodeLabel(code) {
        if (code === 'AB' || code === 'NC') {
            return 'A';
        }
        return code || '';
    }

    function renderAttendanceCalendar() {
        var y = calView.getFullYear();
        var m = calView.getMonth();
        if (monthLabel) {
            monthLabel.textContent = months[m] + ' ' + y;
        }
        var firstDow = new Date(y, m, 1).getDay();
        var daysInMonth = new Date(y, m + 1, 0).getDate();
        var prevDays = new Date(y, m, 0).getDate();
        var todayKey = ymd(new Date());
        var html = '';
        var i;
        for (i = 0; i < firstDow; i++) {
            html += '<div class="hrms-cal-day muted"><span class="hrms-cal-num">' + (prevDays - firstDow + i + 1) + '</span></div>';
        }
        for (var day = 1; day <= daysInMonth; day++) {
            var key = y + '-' + pad(m + 1) + '-' + pad(day);
            var d = dayMap[key];
            var tone = d ? dayHealthTone(d) : 'neutral';
            var cls = 'hrms-cal-day tone-' + tone;
            if (key === todayKey) {
                cls += ' today';
            }
            html += '<div class="' + cls + '" data-date="' + key + '">';
            html += '<span class="hrms-cal-num">' + day + '</span>';
            if (d && d.code) {
                html += '<span class="hrms-cal-code ' + dayCodeClass(tone) + '">' + dayCodeLabel(d.code) + '</span>';
            }
            html += '</div>';
        }
        var cells = firstDow + daysInMonth;
        var trailing = (7 - (cells % 7)) % 7;
        for (var t = 1; t <= trailing; t++) {
            html += '<div class="hrms-cal-day muted"><span class="hrms-cal-num">' + t + '</span></div>';
        }
        grid.innerHTML = html;
    }

    function loadAttendanceCalendar() {
        if (calLoading) {
            return;
        }
        calLoading = true;
        grid.innerHTML = '<div style="grid-column:1/-1;padding:14px;text-align:center;color:#64748b;font-size:12px;"><i class="fa fa-spinner fa-spin"></i> Loading…</div>';
        $.getJSON(admin_url + 'timesheets/my_attendance_calendar', {
            month: monthKey(calView),
            staff_id: attStaffId
        }).done(function(res) {
            dayMap = {};
            (res.days || []).forEach(function(d) {
                dayMap[d.date] = d;
            });
            renderAttendanceCalendar();
        }).fail(function() {
            grid.innerHTML = '<div style="grid-column:1/-1;padding:14px;text-align:center;color:#dc2626;font-size:12px;">Could not load attendance calendar.</div>';
        }).always(function() {
            calLoading = false;
        });
    }

    var prevBtn = document.getElementById('dashboardHrmsCalPrev');
    var nextBtn = document.getElementById('dashboardHrmsCalNext');
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            calView.setMonth(calView.getMonth() - 1);
            loadAttendanceCalendar();
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            calView.setMonth(calView.getMonth() + 1);
            loadAttendanceCalendar();
        });
    }
    grid.addEventListener('click', function(e) {
        var cell = e.target.closest('.hrms-cal-day[data-date]');
        if (!cell || cell.classList.contains('muted')) {
            return;
        }
        var ds = cell.getAttribute('data-date');
        var my = ds ? ds.slice(0, 7) : monthKey(calView);
        window.location.href = admin_url + 'timesheets/my_attendance?month=' + encodeURIComponent(my);
    });
    loadAttendanceCalendar();
})();
</script>
