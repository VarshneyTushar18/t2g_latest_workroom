<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
(function ($) {
    'use strict';

    window.snapshotUiLoader = {
        _pending: 0,
        show: function () {
            this._pending++;
            $('#snapshot-ui-loader').addClass('is-active').attr({ 'aria-hidden': 'false', 'aria-busy': 'true' });
        },
        hide: function () {
            this._pending = Math.max(0, this._pending - 1);
            if (this._pending === 0) {
                $('#snapshot-ui-loader').removeClass('is-active').attr({ 'aria-hidden': 'true', 'aria-busy': 'false' });
                $('.snapshot-ui-loading-target.is-loading').removeClass('is-loading');
            }
        },
        setLoadingTarget: function ($el, loading) {
            if (!$el || !$el.length) {
                return;
            }
            if (loading) {
                $el.addClass('is-loading');
            } else {
                $el.removeClass('is-loading');
            }
        },
        navigate: function (url) {
            this.show();
            window.location.href = url;
        },
        bindNavigation: function (selector) {
            $(document).on('click', selector, function (e) {
                var href = ($(this).attr('href') || '').toString();
                if (!href || href === '#' || e.which > 1 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
                    return;
                }
                e.preventDefault();
                snapshotUiLoader.navigate(href);
            });
        }
    };

    $(function () {
        snapshotUiLoader.show();
    });
    $(window).on('load', function () {
        snapshotUiLoader.hide();
    });
})(jQuery);
</script>
