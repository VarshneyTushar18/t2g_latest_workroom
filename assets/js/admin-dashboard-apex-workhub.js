(function () {
  'use strict';

  var root = document.getElementById('apex-workhub-root');
  var managerShell = document.querySelector('.apex-manager-dashboard-shell');
  if (!root && !managerShell) {
    return;
  }

  function initApexSortableTables(scope) {
    var parent = scope && scope.querySelectorAll ? scope : document;
    parent.querySelectorAll('table.apex-sortable-table').forEach(function (table) {
      if (table.dataset.apexSortBound === '1') {
        return;
      }
      table.dataset.apexSortBound = '1';
      var thead = table.tHead;
      if (!thead) {
        return;
      }
      Array.prototype.forEach.call(thead.querySelectorAll('th[data-sort-col]'), function (th) {
        th.classList.add('sorting');
        th.addEventListener('click', function () {
          var col = parseInt(th.getAttribute('data-sort-col'), 10);
          if (isNaN(col)) {
            return;
          }
          var dir = th.getAttribute('data-sort-dir') === 'asc' ? 'desc' : 'asc';
          thead.querySelectorAll('th[data-sort-col]').forEach(function (head) {
            head.removeAttribute('data-sort-dir');
            var icon = head.querySelector('.apex-sort-icon');
            if (icon) {
              icon.className = 'fa fa-sort apex-sort-icon text-muted';
            }
          });
          th.setAttribute('data-sort-dir', dir);
          var activeIcon = th.querySelector('.apex-sort-icon');
          if (activeIcon) {
            activeIcon.className =
              'fa ' + (dir === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc') + ' apex-sort-icon text-muted';
          }
          var tbody = table.tBodies[0];
          if (!tbody) {
            return;
          }
          var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr')).filter(function (row) {
            return row.cells.length > 1;
          });
          rows.sort(function (a, b) {
            var at = (a.cells[col] && a.cells[col].textContent) || '';
            var bt = (b.cells[col] && b.cells[col].textContent) || '';
            at = at.replace(/\s+/g, ' ').trim().toLowerCase();
            bt = bt.replace(/\s+/g, ' ').trim().toLowerCase();
            var cmp = at.localeCompare(bt, undefined, { numeric: true, sensitivity: 'base' });
            return dir === 'asc' ? cmp : -cmp;
          });
          rows.forEach(function (row) {
            tbody.appendChild(row);
          });
        });
      });
    });
  }

  initApexSortableTables(document);

  function clickHeaderButton(selector) {
    var btn = document.querySelector(selector);
    if (btn) {
      btn.click();
      return true;
    }
    return false;
  }

  var punchOut = document.getElementById('apex-punch-out');
  if (punchOut) {
    punchOut.addEventListener('click', function () {
      if (!clickHeaderButton('#header .check_out, #header button.check_out')) {
        window.location.href = admin_url + 'timesheets/my_attendance';
      }
    });
  }

  var punchIn = document.getElementById('apex-punch-in');
  if (punchIn) {
    punchIn.addEventListener('click', function () {
      if (!clickHeaderButton('#header .check_in, #header button.check_in')) {
        window.location.href = admin_url + 'timesheets/my_attendance';
      }
    });
  }

  var searchInput = root ? root.querySelector('input[placeholder*="Search"]') : null;
  if (searchInput) {
    searchInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        var globalSearch = document.getElementById('search_input');
        if (globalSearch) {
          globalSearch.value = searchInput.value;
          globalSearch.focus();
          var btn = document.getElementById('top_search_button');
          if (btn) {
            btn.click();
          }
        }
      }
    });
  }

  if (!root) {
    root = document.getElementById('apex-workhub-root');
  }
  if (root) {
    root.querySelectorAll('button[type="button"]').forEach(function (btn) {
    var txt = (btn.textContent || '').trim();
    if (txt.indexOf('HR Snapshot') !== -1) {
      btn.addEventListener('click', function () {
        window.location.href = admin_url + 'emt';
      });
    }
    if (btn.closest('header') && btn.querySelector('svg') && txt === '') {
      var bell = btn.querySelector('.bg-rose-500');
      if (bell) {
        btn.addEventListener('click', function () {
          var n = document.querySelector('.notifications-wrapper a, li.notifications-wrapper a');
          if (n) {
            n.click();
          } else {
            window.location.href = admin_url + 'profile?notifications=1';
          }
        });
      }
    }
    });
  }

  var dataEl = document.getElementById('apex-workhub-data');
  if (dataEl && dataEl.textContent) {
    try {
      window.__apexWorkhub = JSON.parse(dataEl.textContent);
    } catch (e) {
      window.__apexWorkhub = null;
    }
  }

  var myWork = document.getElementById('apex-my-work');
  if (myWork) {
    var tabActive =
      'px-3.5 py-1.5 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/60 shadow-xs';
    var tabIdle =
      'px-3.5 py-1.5 rounded-lg text-xs font-semibold text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-colors';
    var tabs = myWork.querySelectorAll('[data-apex-mywork-tab]');
    var panels = myWork.querySelectorAll('[data-apex-mywork-panel]');
    var footer = document.getElementById('apex-mywork-footer');
    var viewAll = document.getElementById('apex-mywork-view-all');
    var urls = (window.__apexWorkhub && window.__apexWorkhub.urls) || {};

    function setMyWorkTab(name) {
      tabs.forEach(function (tab) {
        var on = tab.getAttribute('data-apex-mywork-tab') === name;
        tab.setAttribute('aria-selected', on ? 'true' : 'false');
        tab.className = on ? tabActive : tabIdle;
      });
      panels.forEach(function (panel) {
        var on = panel.getAttribute('data-apex-mywork-panel') === name;
        panel.hidden = !on;
        panel.classList.toggle('hidden', !on);
      });
      if (footer) {
        var shown = footer.getAttribute('data-' + name + '-shown') || '0';
        var total = footer.getAttribute('data-' + name + '-total') || '0';
        if (name === 'tasks') {
          footer.textContent =
            'Showing ' + shown + ' of ' + total + ' active tasks';
        } else if (name === 'projects') {
          footer.textContent =
            'Showing ' + shown + ' of ' + total + ' active projects';
        } else {
          footer.textContent =
            'Showing ' + shown + ' recent announcement' + (shown === '1' ? '' : 's');
        }
      }
      if (viewAll) {
        if (name === 'projects') {
          viewAll.href = urls.projects || admin_url + 'projects';
        } else if (name === 'announcements') {
          viewAll.href = urls.announcements || admin_url + 'announcements';
        } else {
          viewAll.href = urls['view-all-tasks'] || urls.tasks || admin_url + 'tasks/list_tasks';
        }
      }
    }

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        setMyWorkTab(tab.getAttribute('data-apex-mywork-tab'));
      });
    });
  }

  var kraCanvas = document.getElementById('apexKraChart');
  var kraEmpty = document.getElementById('apexKraChartEmpty');
  var pedma = window.__apexWorkhub && window.__apexWorkhub.pedma;
  var prev = pedma && pedma.previous_month;
  if (kraCanvas && prev && prev.kra_labels && prev.kra_labels.length) {
    if (typeof Chart === 'undefined') {
      if (kraEmpty) {
        kraEmpty.classList.remove('hidden');
        kraEmpty.textContent = 'Chart library not loaded.';
      }
    } else {
      var ratingMax =
        (pedma && pedma.kra_rating_max) ||
        (prev.kra_max && prev.kra_max[0]) ||
        5;
      if (kraEmpty) {
        kraEmpty.classList.add('hidden');
      }
      new Chart(kraCanvas.getContext('2d'), {
        type: 'bar',
        data: {
          labels: prev.kra_labels,
          datasets: [
            {
              label: 'Achieved Score',
              data: prev.kra_achieved,
              backgroundColor: '#4CAF50',
              borderWidth: 1,
            },
            {
              label: 'Total Score',
              data: prev.kra_max,
              backgroundColor: '#FFCE56',
              borderWidth: 1,
            },
          ],
        },
        options: {
          maintainAspectRatio: false,
          scales: {
            xAxes: [{ gridLines: { display: false } }],
            yAxes: [
              {
                ticks: { beginAtZero: true, min: 0, max: ratingMax },
                gridLines: { display: true },
                scaleLabel: { display: true, labelString: 'Score' },
              },
            ],
          },
          title: {
            display: true,
            text: 'KRA Performance: Achieved vs Max Score',
          },
        },
      });
    }
  }

  var bulletinBoard = document.getElementById('apex-bulletin-board');
  if (bulletinBoard) {
    var bulletinSlides = bulletinBoard.querySelectorAll('[data-apex-bulletin-slide]');
    var bulletinBlackout = bulletinBoard.querySelector('.apex-bulletin-blackout');
    var bulletinDots = bulletinBoard.querySelectorAll('[data-apex-bulletin-dot]');
    var bulletinIndex = 0;
    var bulletinInterval = parseInt(bulletinBoard.getAttribute('data-interval') || '4000', 10);
    if (!bulletinInterval || bulletinInterval < 2000) {
      bulletinInterval = 4000;
    }

    function setBulletinDot(activeIdx) {
      bulletinDots.forEach(function (dot) {
        var on = parseInt(dot.getAttribute('data-apex-bulletin-dot'), 10) === activeIdx;
        dot.classList.toggle('is-active', on);
        dot.classList.toggle('bg-indigo-500', on);
        dot.classList.toggle('bg-slate-200', !on);
      });
    }

    function showBulletinSlide(nextIdx) {
      if (!bulletinSlides.length) {
        return;
      }
      if (nextIdx < 0 || nextIdx >= bulletinSlides.length) {
        nextIdx = 0;
      }
      if (bulletinBlackout) {
        bulletinBlackout.classList.add('is-visible');
      }
      window.setTimeout(function () {
        bulletinSlides.forEach(function (slide, idx) {
          slide.classList.toggle('is-active', idx === nextIdx);
        });
        setBulletinDot(nextIdx);
        bulletinIndex = nextIdx;
        window.setTimeout(function () {
          if (bulletinBlackout) {
            bulletinBlackout.classList.remove('is-visible');
          }
        }, 80);
      }, 360);
    }

    if (bulletinSlides.length > 1) {
      window.setInterval(function () {
        showBulletinSlide((bulletinIndex + 1) % bulletinSlides.length);
      }, bulletinInterval);
    }
  }

  if (managerShell) {
    var personalPane = document.getElementById('apex-manager-personal-pane');
    var teamPane = document.getElementById('apex-manager-team-pane');
    var teamChartsReady = false;
    var teamPayloadLoading = false;

    function setTeamPaneLoading(on) {
      if (!teamPane) {
        return;
      }
      var root = teamPane.querySelector('#apex-manager-team-root');
      if (root) {
        root.classList.toggle('apex-team-is-loading', !!on);
      }
    }

    function loadManagerTeamPayload(done) {
      var data = parseTeamData();
      if (data && !data.deferred) {
        if (done) {
          done();
        }
        return;
      }
      if (teamPayloadLoading) {
        return;
      }
      teamPayloadLoading = true;
      setTeamPaneLoading(true);
      if (teamPane) {
        teamPane.setAttribute('aria-busy', 'true');
      }
      fetch(admin_url + 'dashboard/apex_manager_team_data', {
        method: 'GET',
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      })
        .then(function (res) {
          if (!res.ok) {
            throw new Error('Team data request failed (' + res.status + ')');
          }
          return res.json();
        })
        .then(function (payload) {
          if (payload && payload.html && teamPane) {
            teamPane.innerHTML = payload.html;
          }
          var jsonEl = document.getElementById('apex-manager-team-data');
          if (payload && payload.data) {
            if (jsonEl) {
              jsonEl.textContent = JSON.stringify(payload.data);
            }
          }
          teamChartsReady = false;
          initApexTeamDataTables(teamPane || document);
          if (done) {
            done();
          }
        })
        .catch(function (err) {
          if (teamPane) {
            var extra = err && err.message ? ' (' + err.message + ')' : '';
            teamPane.innerHTML =
              '<div class="p-8 text-center text-rose-600 text-sm">Team dashboard could not load. Please refresh and try again.' + extra + '</div>';
          }
        })
        .finally(function () {
          teamPayloadLoading = false;
          setTeamPaneLoading(false);
          if (teamPane) {
            teamPane.removeAttribute('aria-busy');
          }
        });
    }

    function setManagerView(view) {
      var isTeam = view === 'team';
      document.querySelectorAll('.apex-manager-dashboard-shell [data-apex-manager-view]').forEach(function (btn) {
        var on = btn.getAttribute('data-apex-manager-view') === view;
        btn.classList.toggle('is-active', on);
        btn.setAttribute('aria-selected', on ? 'true' : 'false');
      });
      if (personalPane) {
        personalPane.classList.toggle('hidden', isTeam);
        personalPane.hidden = isTeam;
      }
      if (teamPane) {
        teamPane.classList.toggle('hidden', !isTeam);
        teamPane.hidden = !isTeam;
        teamPane.setAttribute('aria-hidden', isTeam ? 'false' : 'true');
      }
      if (isTeam) {
        loadManagerTeamPayload(function () {
          if (!teamChartsReady) {
            initManagerTeamCharts();
            teamChartsReady = true;
          }
          bindTeamLiveStatusFilters();
        });
      }
    }

    var adminTeamOnly = managerShell.classList.contains('apex-admin-team-only');

    if (!adminTeamOnly) {
      document.querySelectorAll('.apex-manager-dashboard-shell [data-apex-manager-view]').forEach(function (btn) {
        if (btn.dataset.apexViewBound === '1') {
          return;
        }
        btn.dataset.apexViewBound = '1';
        btn.addEventListener('click', function () {
          setManagerView(btn.getAttribute('data-apex-manager-view'));
        });
      });
    } else {
      loadManagerTeamPayload(function () {
        if (!teamChartsReady) {
          initManagerTeamCharts();
          teamChartsReady = true;
        }
        bindTeamLiveStatusFilters();
      });
    }

    function parseTeamData() {
      var el = document.getElementById('apex-manager-team-data');
      if (!el || !el.textContent) {
        return null;
      }
      try {
        return JSON.parse(el.textContent);
      } catch (e) {
        return null;
      }
    }

    function initManagerTeamCharts() {
      var data = parseTeamData();
      if (!data || typeof Chart === 'undefined') {
        return;
      }
      var trend = data.charts && data.charts.attendance_trend;
      var trendCanvas = document.getElementById('apexTeamAttendanceTrend');
      if (trendCanvas && trend) {
        new Chart(trendCanvas.getContext('2d'), {
          type: 'bar',
          data: {
            labels: trend.labels || [],
            datasets: [
              {
                label: 'Present',
                data: trend.present || [],
                backgroundColor: '#3b82f6',
              },
              {
                label: 'Absent',
                data: trend.absent || [],
                backgroundColor: '#f43f5e',
              },
              {
                label: 'On leave',
                data: trend.leave || [],
                backgroundColor: '#f59e0b',
              },
            ],
          },
          options: {
            maintainAspectRatio: false,
            scales: {
              xAxes: [{ stacked: false, gridLines: { display: false } }],
              yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }],
            },
            legend: { position: 'bottom', labels: { boxWidth: 10, fontSize: 10 } },
          },
        });
      }

      var leave = data.charts && data.charts.leave_summary;
      var donut = document.getElementById('apexTeamLeaveDonut');
      if (donut && leave) {
        new Chart(donut.getContext('2d'), {
          type: 'doughnut',
          data: {
            labels: ['Approved', 'Pending', 'Rejected'],
            datasets: [
              {
                data: [leave.approved || 0, leave.pending || 0, leave.rejected || 0],
                backgroundColor: ['#10b981', '#f59e0b', '#f43f5e'],
              },
            ],
          },
          options: {
            maintainAspectRatio: false,
            legend: { position: 'bottom', labels: { boxWidth: 10, fontSize: 10 } },
          },
        });
      }

      var pedmaGrades = data.charts && data.charts.pedma_grades;
      var pedmaCanvas = document.getElementById('apexTeamPedmaGradeDonut');
      if (pedmaCanvas && pedmaGrades && pedmaGrades.grades) {
        var g = pedmaGrades.grades;
        var a = parseInt(g.A, 10) || 0;
        var b = parseInt(g.B, 10) || 0;
        var c = parseInt(g.C, 10) || 0;
        if (a + b + c > 0) {
          new Chart(pedmaCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
              labels: ['Grade A', 'Grade B', 'Grade C'],
              datasets: [
                {
                  data: [a, b, c],
                  backgroundColor: ['#10b981', '#3b82f6', '#f59e0b'],
                  borderWidth: 0,
                },
              ],
            },
            options: {
              maintainAspectRatio: false,
              cutoutPercentage: 68,
              legend: { display: false },
              tooltips: {
                callbacks: {
                  label: function (item, chart) {
                    var label = chart.labels[item.index] || '';
                    var val = chart.datasets[0].data[item.index];
                    return label + ': ' + val;
                  },
                },
              },
            },
          });
        }
      }
    }

    function destroyApexTeamDataTables(scope) {
      if (typeof jQuery === 'undefined' || !jQuery.fn.DataTable) {
        return;
      }
      var $scope = scope ? jQuery(scope) : jQuery(document);
      $scope.find('table.apex-team-dt-table').each(function () {
        if (jQuery.fn.DataTable.isDataTable(this)) {
          jQuery(this).DataTable().destroy(false);
        }
        jQuery(this).removeClass('dt-inline');
      });
    }

    function apexTeamDefaultPageLength() {
      var len = 10;
      if (typeof app !== 'undefined' && app.options && app.options.tables_pagination_limit) {
        len = parseFloat(app.options.tables_pagination_limit);
      }
      if (isNaN(len) || len < 1) {
        len = 10;
      }
      return len;
    }

    function apexTeamDataTableOptions(extra) {
      var pageLen = apexTeamDefaultPageLength();
      var lengthOptions = [1, 5, 10, 25, 50, 100];
      var lengthNames = [1, 5, 10, 25, 50, 100];
      if (jQuery.inArray(pageLen, lengthOptions) === -1) {
        lengthOptions.push(pageLen);
        lengthNames.push(pageLen);
      }
      lengthOptions.sort(function (a, b) {
        return a - b;
      });
      lengthNames.sort(function (a, b) {
        return a - b;
      });
      var allLabel =
        typeof app !== 'undefined' && app.lang && app.lang.dt_length_menu_all
          ? app.lang.dt_length_menu_all
          : 'All';
      lengthOptions.push(-1);
      lengthNames.push(allLabel);

      var lang =
        typeof app !== 'undefined' && app.lang && app.lang.datatables ? app.lang.datatables : {};

      var base = {
        language: lang,
        paginate: true,
        pageLength: pageLen,
        lengthMenu: [lengthOptions, lengthNames],
        order: [[0, 'asc']],
        autoWidth: false,
        retrieve: false,
        destroy: true,
        dom: "<'row'><'row'<'col-md-6'l><'col-md-6'f>>rt<'row'<'col-md-4'i><'col-md-8 dataTables_paging'<'#colvis'><'.dt-page-jump'>p>>",
        fnDrawCallback: function (oSettings) {
          var $wrap = jQuery(oSettings.nTableWrapper);
          $wrap.removeClass('table-loading');
          $wrap.find('table.apex-team-dt-table').removeClass('dt-table-loading');
          if (typeof _table_jump_to_page === 'function') {
            _table_jump_to_page(jQuery(oSettings.nTable), oSettings);
          }
          if (oSettings.aoData.length === 0 || oSettings.aiDisplay.length === 0) {
            $wrap.addClass('app_dt_empty');
          } else {
            $wrap.removeClass('app_dt_empty');
          }
        },
        initComplete: function (oSettings) {
          var wrapper = oSettings.nTableWrapper;
          if (!wrapper) {
            return;
          }
          jQuery(wrapper).removeClass('table-loading');
          jQuery(oSettings.nTable).removeClass('dt-table-loading');
          var dtEmpty = wrapper.querySelector('.dataTables_empty');
          if (dtEmpty) {
            dtEmpty.setAttribute('colspan', oSettings.aoColumns.length);
          }
        },
      };
      return jQuery.extend({}, base, extra || {});
    }

  function apexTeamLiveStatusFilter(settings, data, dataIndex) {
    if (!settings.nTable || settings.nTable.id !== 'apexTeamLiveTable') {
      return true;
    }
    var ao = settings.aoData[dataIndex];
    var row = ao && ao.nTr;
    if (!row || !row.classList || !row.classList.contains('apex-team-live-row')) {
      return true;
    }
    var dept = document.getElementById('apexTeamFilterDept');
    var status = document.getElementById('apexTeamFilterStatus');
    var search = document.getElementById('apexTeamSearch');
    var deptVal = dept ? dept.value : '';
    var statusVal = status ? status.value : '';
    var q = search ? (search.value || '').toLowerCase().trim() : '';
    return (
      (!deptVal || row.getAttribute('data-dept') === deptVal) &&
      (!statusVal || row.getAttribute('data-status') === statusVal) &&
      (!q || (row.getAttribute('data-search') || '').indexOf(q) !== -1)
    );
  }

  if (typeof jQuery !== 'undefined' && jQuery.fn.dataTable && !window.__apexTeamLiveDtFilterRegistered) {
    jQuery.fn.dataTable.ext.search.push(apexTeamLiveStatusFilter);
    window.__apexTeamLiveDtFilterRegistered = true;
  }

    function initApexTeamDataTables(scope) {
      if (typeof jQuery === 'undefined' || !jQuery.fn.DataTable) {
        bindTeamLiveStatusFilters();
        return;
      }
      destroyApexTeamDataTables(scope);
      var $scope = scope ? jQuery(scope) : jQuery('#apex-manager-team-root');

      var liveDom =
        "<'row'<'col-md-6'l><'col-md-6 text-right apex-team-live-dt-toolbar'>>rt<'row'<'col-md-4'i><'col-md-8 dataTables_paging'<'#colvis'><'.dt-page-jump'>p>>";

      function tbodyHasDataRows(table) {
        var tb = table.tBodies[0];
        if (!tb || tb.rows.length === 0) {
          return false;
        }
        if (tb.rows.length === 1 && tb.rows[0].cells.length <= 1) {
          return false;
        }
        return true;
      }

      $scope.find('#apexTeamLiveTable').each(function () {
        if (!tbodyHasDataRows(this)) {
          return;
        }
        try {
          jQuery(this).addClass('dt-inline').DataTable(
            apexTeamDataTableOptions({
              dom: liveDom,
            })
          );
        } catch (e) {
          console.error('apexTeamLiveTable DataTable init failed', e);
        }
      });

      $scope.find('#apexTeamLeaveBalanceTable, #apexTeamUpcomingLeaveTable').each(function () {
        if (!tbodyHasDataRows(this)) {
          return;
        }
        try {
          jQuery(this).addClass('dt-inline').DataTable(apexTeamDataTableOptions());
        } catch (e) {
          console.error('apex team DataTable init failed', e);
        }
      });

      bindTeamLiveStatusFilters();
    }

    function bindTeamLiveStatusFilters() {
      var deptSelect = document.getElementById('apexTeamFilterDept');
      var statusSelect = document.getElementById('apexTeamFilterStatus');
      var searchInput = document.getElementById('apexTeamSearch');
      var liveTable = document.getElementById('apexTeamLiveTable');

      if (liveTable && deptSelect && deptSelect.dataset.apexDeptPreset !== '1' && deptSelect.options.length < 2) {
        var depts = {};
        liveTable.querySelectorAll('.apex-team-live-row').forEach(function (row) {
          var d = row.getAttribute('data-dept') || '';
          if (d && d !== '—' && !depts[d]) {
            depts[d] = true;
            var opt = document.createElement('option');
            opt.value = d;
            opt.textContent = d;
            deptSelect.appendChild(opt);
          }
        });
      }

      function redrawLiveDt() {
        if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable('#apexTeamLiveTable')) {
          jQuery('#apexTeamLiveTable').DataTable().draw();
        }
      }

      if (deptSelect && deptSelect.dataset.apexBound !== '1') {
        deptSelect.dataset.apexBound = '1';
        deptSelect.addEventListener('change', redrawLiveDt);
      }
      if (statusSelect && statusSelect.dataset.apexBound !== '1') {
        statusSelect.dataset.apexBound = '1';
        statusSelect.addEventListener('change', redrawLiveDt);
      }
      if (searchInput && searchInput.dataset.apexBound !== '1') {
        searchInput.dataset.apexBound = '1';
        searchInput.addEventListener('input', redrawLiveDt);
      }
    }
  }
})();
