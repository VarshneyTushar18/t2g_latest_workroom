/**
 * Perfex admin uses Bootstrap 3 (jQuery plugins). Some pages loaded Bootstrap 5 from CDN,
 * which breaks tooltips, modals, and Dropzone upload previews (duplicate bs.tooltip instances).
 */
(function (window) {
  'use strict';

  function patchBootstrap5Tooltip() {
    var bs = window.bootstrap;
    if (!bs || !bs.Tooltip) {
      return;
    }

    var Tooltip = bs.Tooltip;
    if (Tooltip.__perfexPatched) {
      return;
    }
    Tooltip.__perfexPatched = true;

    var origGetOrCreate = Tooltip.getOrCreateInstance;
    if (typeof origGetOrCreate === 'function') {
      Tooltip.getOrCreateInstance = function (element, config) {
        var existing = Tooltip.getInstance(element);
        if (existing) {
          return existing;
        }
        return origGetOrCreate.call(Tooltip, element, config);
      };
    }

    var OrigTooltip = Tooltip;
    function SafeTooltip(element, config) {
      var existing = OrigTooltip.getInstance(element);
      if (existing) {
        return existing;
      }
      return new OrigTooltip(element, config);
    }
    SafeTooltip.getInstance = OrigTooltip.getInstance;
    SafeTooltip.getOrCreateInstance = Tooltip.getOrCreateInstance;
    SafeTooltip.NAME = OrigTooltip.NAME;
    SafeTooltip.EVENT_KEY = OrigTooltip.EVENT_KEY;
    SafeTooltip.DATA_KEY = OrigTooltip.DATA_KEY;
    SafeTooltip.Default = OrigTooltip.Default;
    bs.Tooltip = SafeTooltip;
  }

  patchBootstrap5Tooltip();

  if (typeof window.jQuery !== 'undefined') {
    window.jQuery(function () {
      patchBootstrap5Tooltip();
    });
  } else {
    document.addEventListener('DOMContentLoaded', patchBootstrap5Tooltip);
  }
})(window);
