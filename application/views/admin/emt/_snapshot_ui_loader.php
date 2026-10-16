<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.snapshot-ui-loader {
    position: fixed;
    inset: 0;
    z-index: 10050;
    background: rgba(255, 255, 255, 0.78);
    display: none;
    align-items: center;
    justify-content: center;
}
.snapshot-ui-loader.is-active {
    display: flex;
}
.snapshot-ui-loader-box {
    text-align: center;
    color: #141e46;
    font-size: 14px;
    font-weight: 500;
}
.snapshot-ui-loader-box .fa-spinner {
    display: block;
    font-size: 36px;
    margin: 0 auto 10px;
    color: #141e46;
}
.snapshot-ui-loading-target.is-loading {
    opacity: 0.45;
    pointer-events: none;
    transition: opacity 0.15s ease;
}
.snapshot-stat-card-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 8px;
}
a.snapshot-stat-card .snapshot-stat-label {
    margin-bottom: 0;
    flex: 1;
}
.snapshot-stat-card-eye {
    color: #141e46;
    font-size: 18px;
    line-height: 1;
    flex-shrink: 0;
    opacity: 0.88;
}
a.snapshot-stat-card:hover .snapshot-stat-card-eye,
a.snapshot-stat-card:focus .snapshot-stat-card-eye {
    opacity: 1;
}
</style>
<div id="snapshot-ui-loader" class="snapshot-ui-loader" aria-hidden="true" aria-live="polite" aria-busy="false">
    <div class="snapshot-ui-loader-box">
        <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
        <span>Loading…</span>
    </div>
</div>
