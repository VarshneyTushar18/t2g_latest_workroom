<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
    /* Workroom Ink — local SaaS sidebar (scoped; does not affect live until this file is deployed) */
    :root {
        --wr-rail: #0f172a;
        --wr-rail-mid: #1e293b;
        --wr-text: #94a3b8;
        --wr-text-strong: #f8fafc;
        --wr-accent: #14b8a6;
        --wr-accent-soft: rgba(20, 184, 166, 0.12);
        --wr-accent-soft-strong: rgba(20, 184, 166, 0.18);
        --wr-hairline: rgba(148, 163, 184, 0.16);
        --wr-side-w: 256px;
        --wr-ease: 180ms ease;
    }

    .sidebar.sidebar--saas {
        width: var(--wr-side-w) !important;
        background: linear-gradient(180deg, var(--wr-rail) 0%, var(--wr-rail-mid) 55%, #162032 100%);
        padding: 0 10px 16px;
        border-right: 1px solid var(--wr-hairline);
        box-shadow: 4px 0 24px rgba(15, 23, 42, 0.18);
        position: fixed !important;
        top: 0;
        left: 0;
        bottom: 0;
        height: 100vh !important;
        z-index: 100;
        overflow: hidden;
    }

    /* Setup panel must sit ABOVE the main rail (core z-index is 9). */
    #setup-menu-wrapper {
        width: var(--wr-side-w) !important;
        z-index: 120 !important;
        position: fixed !important;
        top: 0;
        left: 0;
        bottom: 0;
        height: 100vh !important;
        background: linear-gradient(180deg, var(--wr-rail) 0%, var(--wr-rail-mid) 55%, #162032 100%);
        border-right: 1px solid var(--wr-hairline);
        box-shadow: 4px 0 24px rgba(15, 23, 42, 0.18);
        padding: 0 10px 16px;
        overflow-y: auto !important;
        overflow-x: hidden !important;
    }

    #setup-menu-wrapper #setup-menu {
        background: transparent;
        padding-bottom: 24px;
    }

    #setup-menu-wrapper #setup-menu > li > a {
        color: var(--wr-text) !important;
    }

    #setup-menu-wrapper #setup-menu > li > a:hover,
    #setup-menu-wrapper #setup-menu > li.active > a {
        color: var(--wr-text-strong) !important;
    }

    #setup-menu-wrapper .customizer-heading {
        color: var(--wr-text-strong) !important;
    }

    /* Content offset to match wider rail (override core 230px) */
    body.admin #wrapper {
        margin-left: var(--wr-side-w) !important;
    }

    body.admin #header {
        margin-left: var(--wr-side-w) !important;
    }

    body.admin .btn-bottom-toolbar {
        margin-left: var(--wr-side-w) !important;
        width: calc(100% - var(--wr-side-w)) !important;
    }

    body.hide-sidebar:not(.show-sidebar) #menu.sidebar--saas {
        margin-left: calc(var(--wr-side-w) * -1) !important;
    }

    body.hide-sidebar:not(.show-sidebar) #wrapper {
        margin-left: 0 !important;
    }

    body.hide-sidebar:not(.show-sidebar) #header,
    body.hide-sidebar:not(.show-sidebar) .btn-bottom-toolbar {
        margin-left: 0 !important;
        width: 100% !important;
    }

    body.page-small #menu.sidebar--saas {
        margin-left: calc(var(--wr-side-w) * -1) !important;
    }

    body.page-small #wrapper {
        margin-left: 0 !important;
    }

    body.page-small #header,
    body.page-small .btn-bottom-toolbar {
        margin-left: 0 !important;
        width: 100% !important;
    }

    body.page-small.show-sidebar #menu.sidebar--saas {
        margin-left: 0 !important;
    }

    body.page-small.show-sidebar #wrapper {
        margin-left: var(--wr-side-w) !important;
    }

    body.page-small.show-sidebar #header {
        margin-left: var(--wr-side-w) !important;
    }

    body.page-small.show-sidebar .btn-bottom-toolbar {
        margin-left: var(--wr-side-w) !important;
        width: calc(100% - var(--wr-side-w)) !important;
    }

    .sidebar.sidebar--saas #side-menu {
        background: transparent;
        height: 100% !important;
        max-height: 100vh;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        padding-bottom: 24px;
        scrollbar-width: thin;
        scrollbar-color: rgba(148, 163, 184, 0.35) transparent;
        -webkit-overflow-scrolling: touch;
    }

    .sidebar.sidebar--saas #side-menu::-webkit-scrollbar {
        width: 5px;
    }

    .sidebar.sidebar--saas #side-menu::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.35);
        border-radius: 999px;
    }

    .sidebar.sidebar--saas #side-menu::-webkit-scrollbar-track {
        background: transparent;
    }

    /* Brand strip */
    .sidebar.sidebar--saas #side-menu > li:first-child {
        margin: 0 -10px 8px;
        padding: 0 10px;
        border-bottom: 1px solid var(--wr-hairline);
        background: rgba(15, 23, 42, 0.55) !important;
    }

    .sidebar.sidebar--saas #side-menu > li:first-child .sm\:tw-bg-neutral-900\/50,
    .sidebar.sidebar--saas .sm\:tw-bg-neutral-900\/50 {
        background: transparent !important;
    }

    .sidebar.sidebar--saas #logo {
        min-height: 63px;
        padding: 12px 8px !important;
    }

    .sidebar.sidebar--saas #side-menu > li:nth-child(2) > a {
        margin-top: 6px;
    }

    /* Top-level items */
    .sidebar.sidebar--saas #side-menu > li > a {
        position: relative;
        display: flex;
        align-items: center;
        gap: 0;
        color: var(--wr-text) !important;
        background: transparent !important;
        border-radius: 10px;
        padding: 9px 12px 9px 14px;
        margin: 2px 0;
        font-size: 13.5px;
        font-weight: 500;
        letter-spacing: 0.01em;
        border: 0 !important;
        transition: background var(--wr-ease), color var(--wr-ease), box-shadow var(--wr-ease);
    }

    .sidebar.sidebar--saas #side-menu > li > a::before {
        content: '';
        position: absolute;
        left: 0;
        top: 8px;
        bottom: 8px;
        width: 3px;
        border-radius: 0 3px 3px 0;
        background: transparent;
        transition: background var(--wr-ease);
    }

    .sidebar.sidebar--saas #side-menu > li > a .menu-icon {
        width: 18px;
        margin-right: 12px;
        float: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        opacity: 0.75;
        color: inherit;
        transition: opacity var(--wr-ease), color var(--wr-ease);
    }

    .sidebar.sidebar--saas #side-menu > li > a .menu-text {
        flex: 1;
        font-size: 13px;
        line-height: 1.25;
    }

    .sidebar.sidebar--saas #side-menu > li > a:hover,
    .sidebar.sidebar--saas #side-menu > li > a:focus {
        color: var(--wr-text-strong) !important;
        background: rgba(148, 163, 184, 0.08) !important;
    }

    .sidebar.sidebar--saas #side-menu > li > a:hover .menu-icon,
    .sidebar.sidebar--saas #side-menu > li > a:focus .menu-icon {
        opacity: 1;
    }

    .sidebar.sidebar--saas #side-menu > li.active > a,
    .sidebar.sidebar--saas #side-menu > li.active > a:hover,
    .sidebar.sidebar--saas #side-menu > li.active > a:focus {
        color: var(--wr-text-strong) !important;
        background: var(--wr-accent-soft) !important;
        border: none !important;
        box-shadow: none !important;
        outline: none !important;
    }

    .sidebar.sidebar--saas #side-menu > li.active > a::before {
        background: var(--wr-accent);
    }

    .sidebar.sidebar--saas #side-menu > li.active > a .menu-icon {
        opacity: 1;
        color: var(--wr-accent);
    }

    /* Chevrons — hide broken FA squares (glyph tofu). Expand still works by click. */
    .sidebar.sidebar--saas #side-menu .arrow,
    .sidebar.sidebar--saas #side-menu .fa.arrow {
        display: none !important;
    }

    /* Nested levels — quiet rail, no bent-arrow clutter */
    .sidebar.sidebar--saas #side-menu li .nav-second-level,
    .sidebar.sidebar--saas #side-menu li .nav-third-level {
        background: transparent !important;
        padding: 4px 0 8px 8px;
        margin: 0 0 4px 10px;
        border-left: 1px solid var(--wr-hairline);
    }

    .sidebar.sidebar--saas #side-menu li .nav-second-level li,
    .sidebar.sidebar--saas #side-menu li .nav-third-level li {
        background: transparent !important;
    }

    .sidebar.sidebar--saas #side-menu li .nav-second-level li a,
    .sidebar.sidebar--saas #side-menu li .nav-third-level li a {
        display: flex;
        align-items: center;
        color: var(--wr-text) !important;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        outline: none !important;
        border-radius: 8px;
        padding: 7px 10px 7px 12px !important;
        margin: 1px 0 1px 4px !important;
        font-size: 12.5px;
        font-weight: 450;
        transition: background var(--wr-ease), color var(--wr-ease);
    }

    .sidebar.sidebar--saas #side-menu li .nav-third-level li a {
        padding-left: 14px !important;
        font-size: 12px;
    }

    .sidebar.sidebar--saas #side-menu li .nav-second-level li a .menu-icon,
    .sidebar.sidebar--saas #side-menu li .nav-third-level li a .menu-icon {
        width: 14px;
        margin-right: 8px;
        float: none;
        font-size: 12px;
        opacity: 0.65;
    }

    .sidebar.sidebar--saas #side-menu li .nav-second-level li a .sub-menu-text,
    .sidebar.sidebar--saas #side-menu li .nav-third-level li a .sub-menu-text {
        flex: 1;
    }

    .sidebar.sidebar--saas #side-menu li .nav-second-level li:not(.active) > a:hover,
    .sidebar.sidebar--saas #side-menu li .nav-second-level li:not(.active) > a:focus,
    .sidebar.sidebar--saas #side-menu li .nav-third-level li:not(.active) > a:hover,
    .sidebar.sidebar--saas #side-menu li .nav-third-level li:not(.active) > a:focus,
    body .sidebar.sidebar--saas #side-menu li .nav-second-level li:not(.active) a:hover {
        color: var(--wr-text-strong) !important;
        background: rgba(148, 163, 184, 0.08) !important;
    }

    /* Active child — soft teal pill (no border/outline) */
    .sidebar.sidebar--saas #side-menu li .nav-second-level li.active > a,
    .sidebar.sidebar--saas #side-menu li .nav-third-level li.active > a,
    body .sidebar.sidebar--saas #side-menu li .nav-second-level li.active a,
    body .sidebar.sidebar--saas #side-menu li .nav-third-level li.active a {
        color: var(--wr-text-strong) !important;
        background: var(--wr-accent-soft-strong) !important;
        border: none !important;
        box-shadow: none !important;
        outline: none !important;
        border-radius: 8px !important;
        display: flex !important;
        padding: 7px 10px 7px 12px !important;
        margin: 2px 0 2px 4px !important;
    }

    .sidebar.sidebar--saas #side-menu li .nav-second-level li.active > a .menu-icon,
    .sidebar.sidebar--saas #side-menu li .nav-third-level li.active > a .menu-icon {
        color: var(--wr-accent);
        opacity: 1;
    }

    .sidebar.sidebar--saas #side-menu li .nav-second-level li > a .arrow {
        float: none;
        margin-left: auto;
        margin-top: 0;
    }

    /* Badges */
    .sidebar.sidebar--saas #side-menu .badge {
        border-radius: 999px;
        font-weight: 600;
        font-size: 10px;
        padding: 3px 7px;
    }

    /* Nested group parents (Attendance / Leave / Shift) — same style as other items */
    .sidebar.sidebar--saas #side-menu li .nav-second-level > li > a[href="#"] {
        color: var(--wr-text) !important;
        font-weight: 450;
        font-size: 12.5px;
        letter-spacing: normal;
        text-transform: none;
        padding-top: 7px !important;
        padding-bottom: 7px !important;
        opacity: 1;
    }

    .sidebar.sidebar--saas #side-menu li .nav-second-level > li.active > a[href="#"],
    .sidebar.sidebar--saas #side-menu li .nav-second-level > li > a[href="#"]:hover {
        background: rgba(148, 163, 184, 0.06) !important;
        color: var(--wr-text-strong) !important;
        border: none !important;
        box-shadow: none !important;
        outline: none !important;
    }

    .sidebar.sidebar--saas #side-menu li .nav-third-level {
        margin-left: 8px;
        padding-left: 6px;
    }

    /* Setup item */
    .sidebar.sidebar--saas #setup-menu-item > a {
        margin-top: 10px;
        border-top: 1px solid var(--wr-hairline);
        border-radius: 0 0 10px 10px;
        padding-top: 14px;
    }

    /* ── HRMS section (modern card-nav, hide ↳ clutter) ── */
    .sidebar.sidebar--saas #side-menu > li.menu-item-timesheets > a {
        border-radius: 12px;
        margin-top: 6px;
        margin-bottom: 4px;
        font-weight: 650;
    }

    .sidebar.sidebar--saas #side-menu > li.menu-item-timesheets.active > a {
        background: var(--wr-accent-soft-strong) !important;
    }

    /* Kill orange-theme bent arrow under HRMS */
    .sidebar.sidebar--saas #side-menu > li.menu-item-timesheets .nav-second-level li a::before,
    .sidebar.sidebar--saas #side-menu > li.menu-item-timesheets .nav-third-level li a::before,
    body .sidebar.sidebar--saas #side-menu > li.menu-item-timesheets .nav-second-level li a::before {
        content: none !important;
        display: none !important;
    }

    .sidebar.sidebar--saas #side-menu > li.menu-item-timesheets > .nav-second-level {
        margin: 4px 0 10px 6px !important;
        padding: 6px 4px 8px 8px !important;
        border-left: 0 !important;
        background: rgba(15, 23, 42, 0.28) !important;
        border-radius: 14px;
    }

    .sidebar.sidebar--saas #side-menu > li.menu-item-timesheets .nav-second-level > li > a {
        padding: 9px 12px !important;
        margin: 2px 0 !important;
        border-radius: 10px !important;
        font-size: 13px !important;
        font-weight: 550 !important;
        gap: 2px;
        display: flex !important;
        align-items: center;
    }

    /* No expand squares on Attendance / Leave / Reports (broken FA glyph) */
    .sidebar.sidebar--saas #side-menu > li.menu-item-timesheets .nav-second-level > li > a .fa.arrow,
    .sidebar.sidebar--saas #side-menu > li.menu-item-timesheets .nav-second-level > li > a .arrow {
        display: none !important;
        visibility: hidden !important;
    }

    .sidebar.sidebar--saas #side-menu > li.menu-item-timesheets .nav-second-level > li > a .menu-icon {
        width: 28px !important;
        height: 28px;
        margin-right: 10px !important;
        border-radius: 999px;
        background: rgba(20, 184, 166, 0.14);
        color: var(--wr-accent) !important;
        opacity: 1 !important;
        font-size: 13px !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        float: none !important;
    }

    .sidebar.sidebar--saas #side-menu > li.menu-item-timesheets .nav-second-level > li.active > a,
    .sidebar.sidebar--saas #side-menu > li.menu-item-timesheets .nav-second-level > li.active > a:hover {
        background: rgba(20, 184, 166, 0.22) !important;
        color: #f8fafc !important;
        border-radius: 10px !important;
        margin: 2px 0 !important;
        padding: 9px 12px !important;
        display: flex !important;
    }

    .sidebar.sidebar--saas #side-menu > li.menu-item-timesheets .nav-second-level > li.active > a .menu-icon {
        background: rgba(20, 184, 166, 0.28);
        color: #5eead4 !important;
    }

    /* Nested leaves under Attendance / Leave / Shift */
    .sidebar.sidebar--saas #side-menu > li.menu-item-timesheets .nav-third-level {
        margin: 2px 0 6px 12px !important;
        padding: 4px 0 4px 8px !important;
        border-left: 1px solid var(--wr-hairline) !important;
        background: transparent !important;
    }

    .sidebar.sidebar--saas #side-menu > li.menu-item-timesheets .nav-third-level > li > a {
        padding: 7px 10px !important;
        margin: 1px 0 !important;
        border-radius: 8px !important;
        font-size: 12px !important;
    }

    .sidebar.sidebar--saas #side-menu > li.menu-item-timesheets .nav-third-level > li > a .menu-icon {
        width: 22px !important;
        height: 22px;
        margin-right: 8px !important;
        border-radius: 999px;
        background: rgba(148, 163, 184, 0.12);
        color: #94a3b8 !important;
        opacity: 1 !important;
        font-size: 11px !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        float: none !important;
    }

    .sidebar.sidebar--saas #side-menu > li.menu-item-timesheets .nav-third-level > li.active > a {
        background: rgba(20, 184, 166, 0.18) !important;
        color: #f8fafc !important;
        margin: 1px 0 !important;
        padding: 7px 10px !important;
        display: flex !important;
    }
</style>
<aside id="menu" class="sidebar sidebar--saas">

    <ul class="nav metis-menu" id="side-menu">

        <li class="tw-mt-[63px] sm:tw-mt-0 -tw-mx-2 tw-overflow-hidden sm:tw-bg-neutral-900/50">

            <div id="logo" class="tw-py-2 tw-px-2 tw-h-[63px] tw-flex tw-items-center">

                <?php echo get_company_logo(get_admin_uri() . '/', '!tw-mt-0') ?>

            </div>

        </li>

        <?php

        hooks()->do_action('before_render_aside_menu');

        ?>



        <?php foreach ($sidebar_menu as $key => $item) {

            if ((isset($item['collapse']) && $item['collapse']) && count($item['children']) === 0) {

                continue;
            } ?>

            <li class="menu-item-<?php echo $item['slug']; ?>"

                <?php echo _attributes_to_string(isset($item['li_attributes']) ? $item['li_attributes'] : []); ?>>

                <a href="<?php echo count($item['children']) > 0 ? '#' : $item['href']; ?>" aria-expanded="false"

                    <?php echo _attributes_to_string(isset($item['href_attributes']) ? $item['href_attributes'] : []); ?>>

                    <i class="<?php echo $item['icon']; ?> menu-icon"></i>

                    <span class="menu-text">

                        <?php echo _l($item['name'], '', false); ?>

                    </span>

                    <?php if (count($item['children']) > 0) { ?>

                        <span class="fa arrow pleft5"></span>

                    <?php } ?>

                    <?php if (isset($item['badge'], $item['badge']['value']) && !empty($item['badge'])) { ?>

                        <span

                            class="badge pull-right

               <?= isset($item['badge']['type']) && $item['badge']['type'] != '' ? "bg-{$item['badge']['type']}" : 'bg-info' ?>" <?= (isset($item['badge']['type']) && $item['badge']['type'] == '') ||

                                                                                                                                    isset($item['badge']['color']) ? "style='background-color: {$item['badge']['color']}'" : '' ?>>

                            <?= $item['badge']['value'] ?>

                        </span>

                    <?php } ?>

                </a>

                <?php if (count($item['children']) > 0) { ?>

                    <ul class="nav nav-second-level collapse" aria-expanded="false">

                        <?php
                        if (!function_exists('render_aside_submenu_items')) {
                            function render_aside_submenu_items($children, $depth = 2)
                            {
                                foreach ($children as $submenu) {
                                    $has_children = !empty($submenu['children']);
                                    $sub_slug = $submenu['slug'] ?? '';
                                    ?>
                            <li class="sub-menu-item-<?php echo $sub_slug; ?>"
                                <?php echo _attributes_to_string(isset($submenu['li_attributes']) ? $submenu['li_attributes'] : []); ?>>

                                <a href="<?php echo $has_children ? '#' : $submenu['href']; ?>"
                                    <?php echo _attributes_to_string(isset($submenu['href_attributes']) ? $submenu['href_attributes'] : []); ?>>

                                    <?php if (!empty($submenu['icon'])) { ?>
                                        <i class="<?php echo $submenu['icon']; ?> menu-icon"></i>
                                    <?php } ?>

                                    <span class="sub-menu-text">
                                        <?php echo _l($submenu['name'], '', false); ?>
                                    </span>

                                    <?php if ($has_children) { ?>
                                        <span class="fa arrow pleft5"></span>
                                    <?php } ?>
                                </a>

                                <?php if (isset($submenu['badge'], $submenu['badge']['value']) && !empty($submenu['badge'])) { ?>
                                    <span class="badge pull-right <?php echo isset($submenu['badge']['type']) && $submenu['badge']['type'] != '' ? 'bg-' . $submenu['badge']['type'] : 'bg-info'; ?>"
                                        <?php echo (isset($submenu['badge']['type']) && $submenu['badge']['type'] == '' && isset($submenu['badge']['color'])) ? "style='background-color: {$submenu['badge']['color']}'" : ''; ?>>
                                        <?php echo $submenu['badge']['value']; ?>
                                    </span>
                                <?php } ?>

                                <?php if ($has_children) { ?>
                                    <ul class="nav <?php echo $depth >= 2 ? 'nav-third-level' : 'nav-second-level'; ?> collapse" aria-expanded="false">
                                        <?php render_aside_submenu_items($submenu['children'], $depth + 1); ?>
                                    </ul>
                                <?php } ?>

                            </li>
                                    <?php
                                }
                            }
                        }
                        render_aside_submenu_items($item['children']);
                        ?>

                    </ul>

                <?php } ?>

            </li>

            <?php hooks()->do_action('after_render_single_aside_menu', $item); ?>

        <?php

        } ?>

        <?php if ($this->app->show_setup_menu() == true && (is_staff_member() || is_admin())) { ?>
            <li<?php if (get_option('show_setup_menu_item_only_on_hover') == 1) {
                    echo ' style="display:none;"';
                } ?> id="setup-menu-item">
                <a href="#" class="open-customizer"><i class="fa fa-cog menu-icon"></i>
                    <span class="menu-text">
                        <?php echo _l('setting_bar_heading'); ?>
                        <?php
                        if ($modulesNeedsUpgrade = $this->app_modules->number_of_modules_that_require_database_upgrade()) {
                            echo '<span class="badge menu-badge !tw-bg-warning-600">' . $modulesNeedsUpgrade . '</span>';
                        }
                        ?>
                    </span>
                </a>
            </li>
        <?php } ?>

        <?php hooks()->do_action('after_render_aside_menu'); ?>

        <?php $this->load->view('admin/projects/pinned'); ?>

    </ul>

</aside>
