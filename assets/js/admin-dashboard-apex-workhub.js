(function () {
  'use strict';

  var root = document.getElementById('apex-workhub-root');
  if (!root) {
    return;
  }

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

  var searchInput = root.querySelector('input[placeholder*="Search"]');
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
})();
