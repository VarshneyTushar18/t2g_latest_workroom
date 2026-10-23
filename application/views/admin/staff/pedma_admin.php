<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<!-- Custom CSS -->
<style>
    .table thead th {
        vertical-align: middle;
    }

    .performance-table {
        width: 100%;
        border-collapse: collapse;
    }

    .performance-table th,
    .performance-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    .performance-table th {
        background-color: #fff;
        text-align: center;
    }

    .table {
        background-color: #fff;
        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
    }

    .table th,
    .table td {
        padding: 12px;
        vertical-align: middle;
    }

    .table th {
        background-color: #f8f9fa;
        text-align: center;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #f9f9f9;
    }

    .performance-criteria {
        font-weight: 500;
        color: #333;
    }

    .comment {
        width: 100%;
        min-height: 60px;
        border-radius: 5px;
        padding: 8px;
        resize: none;
    }

    .performance-table th {
        resize: horizontal;
        overflow: auto;
    }

    .performance-table th {
        white-space: nowrap;
    }

    td.performance-criteria b {
        color: #000;
    }

    th {
        color: #000;
    }

    .left-header {
        background-color: #F8FAFC;
        color: #000 !important;
        font-weight: 600;
        width: 50%;
    }

    .table {
        box-shadow: none !important;
    }

    .bg-primary {
        background-color: #172032;
    }

    .loader {
        position: fixed;
        z-index: 99;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: white;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .loader>img {
        width: 100px;
    }

    .loader .hidden {
        animation: fadeOut 1s;
        animation-fill-mode: forwards;
    }

    #overall_feedback {
        resize: vertical;
    }

    .form-group {
        margin-bottom: 8px;
    }

    @keyframes fadeOut {
        100% {
            opacity: 0;
            visibility: hidden;
        }
    }

    .ck-editor__editable {
        height: 100px;
    }

    td.performance-criteria>b {
        font-size: 13.5px !important;
    }

    td.performance-criteria {
        font-size: 11px !important;
    }

    .form-check {
        display: inline-block;
    }

    .bootstrap-select.show-tick .dropdown-menu li.selected a span.check-mark {
        display: none !important;
    }

    #pedmaKraSelectors .bootstrap-select.show-tick .dropdown-menu {
        padding: 6px 0;
    }

    #pedmaKraSelectors .bootstrap-select.show-tick .dropdown-menu li a {
        position: relative;
        padding: 8px 12px 8px 38px;
        white-space: normal;
    }

    #pedmaKraSelectors .bootstrap-select.show-tick .dropdown-menu li a span.text {
        margin-right: 0;
    }

    #pedmaKraSelectors .bootstrap-select.show-tick .dropdown-menu li a span.check-mark {
        display: none !important;
    }

    #pedmaKraSelectors .bootstrap-select.show-tick .dropdown-menu li a:before {
        content: '';
        position: absolute;
        left: 12px;
        top: 50%;
        width: 16px;
        height: 16px;
        margin-top: -8px;
        border: 1.5px solid #94a3b8;
        border-radius: 3px;
        background: #fff;
        box-sizing: border-box;
    }

    #pedmaKraSelectors .bootstrap-select.show-tick .dropdown-menu li.selected a:before {
        background: #2563eb;
        border-color: #2563eb;
    }

    #pedmaKraSelectors .bootstrap-select.show-tick .dropdown-menu li.selected a:after {
        content: '';
        position: absolute;
        left: 17px;
        top: 50%;
        width: 5px;
        height: 9px;
        margin-top: -6px;
        border: solid #fff;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
        box-sizing: border-box;
    }

    .form-check {
        margin-right: 8px;
    }
</style>
<style>
    .button-container {
        text-align: center;
        margin-top: 20px;
    }

    .modal-content {
        text-align: left;
    }

    .percentage-box {
        display: inline-block;
        width: 100px;
        height: 100px;
        border: 2px solid #ccc;
        border-radius: 8px;
        text-align: center;
        line-height: 100px;
        font-size: 18px;
        cursor: pointer;
        margin: 10px;
        transition: border 0.3s ease;
    }

    .percentage-box.selected {
        border: 2px solid #007bff;
    }

    .custom-input-field {
        display: none;
        margin-top: 10px;
    }

    .custom-input-field input {
        width: 100%;
    }

    .month-checkboxes {
        display: flex;
        flex-wrap: wrap;
    }

    .month-checkboxes label {
        margin-right: 10px;
    }

    .btn-outline-danger {
        color: #dc3545;
        border-color: #dc3545;
        background-color: #fff;
    }

    .btn-outline-success {
        color: #28a745;
        border-color: #28a745;
        background-color: #fff;
    }

    .btn-outline-success:hover {
        color: #fff;
        background-color: #28a745;
        border-color: #28a745;
    }

    .btn-outline-danger:hover {
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
    }
    div.cke_notification.cke_notification_warning{
    display: none !important;
    }

    .fscore{
        font-size: 14px;
    }

    .disabledbtn{
        color: #6c757d;    
        border-color: #6c757d;
    }
    .disabledbtn:hover{
        color:#fff;    
        background-color: #6c757d;
        border-color: #6c757d;      
    }

    #defaultKraSelect {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 12px;
        align-items: center;
    }
    #defaultKraSelect .kra-list-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 8px;
        margin: 0;
        border: none;
        border-radius: 0;
        background: transparent;
        width: auto;
        max-width: none;
    }
    #defaultKraSelect .kra-list-item.unlocked {
        background: transparent;
        border: none;
    }
    #defaultKraSelect .kra-list-item.unlocked .form-check-label {
        color: #15803d;
        font-weight: 600;
    }
    #defaultKraSelect .kra-lock-icon {
        min-width: 14px;
        color: #b45309;
        font-size: 12px;
    }
    #defaultKraSelect .kra-list-item.unlocked .kra-lock-icon {
        color: #15803d;
    }
    #defaultKraSelect .form-check-label {
        margin: 0;
        font-weight: 500;
        cursor: pointer;
        white-space: nowrap;
    }

    .pedma-filters-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 20px 20px 8px;
        margin-bottom: 16px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
    }

    .pedma-filters-card .pedma-filters-title {
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        margin: 0 0 14px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .pedma-filters-hint {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        padding: 28px 20px;
        text-align: center;
        color: #64748b;
        font-size: 14px;
        margin-bottom: 16px;
    }

    .pedma-evaluation-panel {
        margin-top: 8px;
    }

    .pedma-kra-layout {
        display: flex;
        flex-wrap: nowrap;
        align-items: flex-start;
        gap: 16px;
        margin-top: 12px;
    }

    .pedma-kra-col {
        width: 230px;
        flex: 0 0 230px;
    }

    .pedma-kra-main {
        flex: 1 1 auto;
        min-width: 0;
    }

    .pedma-kra-sidebar {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px;
    }

    .pedma-section-toggle {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        font-weight: 600;
        color: #1e293b;
        cursor: pointer;
    }

    .pedma-section-note {
        display: block;
        margin: 2px 0 8px 22px;
        color: #64748b;
        font-size: 12px;
    }

    .pedma-kra-check-list {
        margin: 0 0 12px 8px;
        max-height: 280px;
        overflow: auto;
    }

    .pedma-kra-selectors .form-group {
        margin-bottom: 14px;
    }

    .pedma-kra-selectors .control-label {
        font-weight: 600;
        color: #334155;
        font-size: 13px;
    }

    .pedma-kra-sidebar-title {
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        margin: 0 0 12px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .pedma-kra-sidebar .kra-list-item.pedma-kra-locked {
        opacity: 0.85;
    }

    .pedma-kra-sidebar .kra-list-item.pedma-kra-locked input[type="checkbox"] {
        cursor: not-allowed;
    }

    .pedma-rating-group input[disabled] + *,
    .pedma-rating-group input[disabled] {
        cursor: not-allowed;
        opacity: 0.75;
    }

    .pedma-kra-sidebar .kra-list-item {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
        margin: 0;
    }

    .pedma-kra-sidebar .kra-list-item:last-child {
        border-bottom: 0;
    }

    .pedma-kra-sidebar .form-check-label {
        font-weight: 500;
        color: #1e293b;
        cursor: pointer;
        line-height: 1.35;
    }

    .pedma-evaluation-mount {
        width: 100%;
        min-width: 0;
    }

    .pedma-kra-table-wrap {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px;
        width: 100%;
        max-width: none;
        box-sizing: border-box;
    }

    .pedma-evaluation-mount .pedma-employee-bar,
    .pedma-evaluation-mount #default_performance_score,
    .pedma-evaluation-mount .pedma-form-actions {
        width: 100%;
        max-width: none;
    }

    .pedma-evaluation-mount .performance-table {
        table-layout: auto;
        width: 100%;
    }

    .pedma-evaluation-mount .performance-table .comment {
        min-height: 52px;
    }

    .pedma-rating-group {
        display: flex;
        flex-wrap: nowrap;
        gap: 8px;
        justify-content: flex-start;
        align-items: center;
        white-space: nowrap;
    }

    .pedma-rating-group label {
        margin: 0;
        font-weight: 500;
        font-size: 12px;
        cursor: pointer;
        white-space: nowrap;
    }

    .pedma-employee-bar {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        margin-bottom: 12px;
        font-size: 13px;
        color: #475569;
    }

    .pedma-employee-bar strong {
        color: #0f172a;
    }

    .mleft15 {
        margin-left: 14px;
    }

    .pedma-kra-accordion {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .pedma-kra-panel {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
    }

    .pedma-kra-panel-head {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 12px;
        margin: 0;
        border: 0;
        background: #f8fafc;
        color: #1e293b;
        font-size: 13px;
        font-weight: 600;
        text-align: left;
        cursor: pointer;
    }

    .pedma-kra-panel-head:hover {
        background: #f1f5f9;
    }

    .pedma-kra-chevron {
        display: inline-block;
        width: 12px;
        flex-shrink: 0;
        color: #64748b;
        transition: transform 0.15s ease;
    }

    .pedma-kra-panel.is-open .pedma-kra-chevron {
        transform: rotate(90deg);
    }

    .pedma-kra-panel-title {
        flex: 1 1 auto;
    }

    .pedma-kra-panel-meta {
        font-size: 10px;
        font-weight: 500;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .pedma-kra-panel-body {
        padding: 8px 12px 12px;
        border-top: 1px solid #e2e8f0;
    }

    .pedma-kra-all-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 8px;
        padding-bottom: 8px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
    }

    #pedmaKraSidebar .pedma-kra-check-list {
        margin: 0;
        max-height: 220px;
    }
</style>
<div id="wrapper">
    <div class="content col-md-12">
        <div class="panel-body">
            <div class="">

                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-flex tw-items-center"><svg width="20px" height="20px" class="tw-w-5 tw-h-5 tw-text-neutral-500 tw-mr-1.5" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14.7179 20.9008L11.2187 24.0772L10.0782 22.9367C9.77309 22.6316 9.27894 22.6316 8.97388 22.9367C8.66881 23.2417 8.66881 23.7358 8.97388 24.0409L10.6405 25.7074C10.7927 25.8596 10.9923 25.936 11.1923 25.936C11.3798 25.936 11.5678 25.869 11.7173 25.7332L12.9974 24.5713V26.639H6.05129V20.3353H13.778C14.2089 20.3353 14.5585 19.9857 14.5585 19.5548C14.5585 19.1239 14.2089 18.7743 13.778 18.7743H5.27076C4.83982 18.7743 4.49023 19.1239 4.49023 19.5548V27.4195C4.49023 27.8504 4.83982 28.2 5.27076 28.2H13.778C14.2089 28.2 14.5585 27.8504 14.5585 27.4195V23.1538L15.7672 22.0567C16.0866 21.7671 16.1102 21.2729 15.8205 20.9542C15.5309 20.6348 15.0373 20.6112 14.7179 20.9008Z" fill="#1E293B" />
                        <path d="M21.1171 36.9891H18.5397C18.1088 36.9891 17.7592 37.3387 17.7592 37.7696C17.7592 38.2005 18.1088 38.5501 18.5397 38.5501H21.1171C21.5481 38.5501 21.8977 38.2005 21.8977 37.7696C21.8982 37.3387 21.5486 36.9891 21.1171 36.9891Z" fill="#1E293B" />
                        <path d="M14.7179 6.61922L11.2187 9.79554L10.0782 8.65505C9.77309 8.35001 9.27894 8.35001 8.97388 8.65505C8.66881 8.9601 8.66881 9.45422 8.97388 9.75926L10.6405 11.4257C10.7927 11.578 10.9923 11.6544 11.1923 11.6544C11.3798 11.6544 11.5678 11.5873 11.7173 11.4516L12.9974 10.2897V12.3574H6.05129V6.05419H13.778C14.2089 6.05419 14.5585 5.70463 14.5585 5.27372C14.5585 4.84281 14.2089 4.49324 13.778 4.49324H5.27076C4.83982 4.49324 4.49023 4.84281 4.49023 5.27372V13.1384C4.49023 13.5693 4.83982 13.9189 5.27076 13.9189H13.778C14.2089 13.9189 14.5585 13.5693 14.5585 13.1384V8.87271L15.7672 7.77564C16.0866 7.48599 16.1102 6.99187 15.8205 6.67308C15.5309 6.35319 15.0373 6.32956 14.7179 6.61922Z" fill="#1E293B" />
                        <path d="M30.3087 39.8796C29.8778 39.8796 29.5282 40.2292 29.5282 40.6601V49.2195C29.5282 49.6504 29.8778 50 30.3087 50C30.7396 50 31.0892 49.6504 31.0892 49.2195V40.6601C31.0892 40.2287 30.7396 39.8796 30.3087 39.8796Z" fill="#1E293B" />
                        <path d="M14.7179 35.1819L11.2187 38.3583L10.0782 37.2178C9.77309 36.9127 9.27894 36.9127 8.97388 37.2178C8.66881 37.5228 8.66881 38.0169 8.97388 38.322L10.6405 39.9885C10.7927 40.1407 10.9923 40.2171 11.1923 40.2171C11.3798 40.2171 11.5678 40.1501 11.7173 40.0143L12.9974 38.8524V40.9201H6.05129V34.6169H13.778C14.2089 34.6169 14.5585 34.2673 14.5585 33.8364C14.5585 33.4055 14.2089 33.056 13.778 33.056H5.27076C4.83982 33.056 4.49023 33.4055 4.49023 33.8364V41.7011C4.49023 42.132 4.83982 42.4816 5.27076 42.4816H13.778C14.2089 42.4816 14.5585 42.132 14.5585 41.7011V37.4354L15.7672 36.3384C16.0866 36.0487 16.1102 35.5546 15.8205 35.2358C15.5309 34.9165 15.0373 34.8923 14.7179 35.1819Z" fill="#1E293B" />
                        <path d="M43.6607 39.8796C43.2297 39.8796 42.8802 40.2292 42.8802 40.6601V49.2195C42.8802 49.6504 43.2297 50 43.6607 50C44.0916 50 44.4412 49.6504 44.4412 49.2195V40.6601C44.4412 40.2287 44.0916 39.8796 43.6607 39.8796Z" fill="#1E293B" />
                        <path d="M28.055 22.708H18.5397C18.1088 22.708 17.7592 23.0576 17.7592 23.4885C17.7592 23.9194 18.1088 24.269 18.5397 24.269H28.055C28.486 24.269 28.8356 23.9194 28.8356 23.4885C28.8356 23.0576 28.486 22.708 28.055 22.708Z" fill="#1E293B" />
                        <path d="M44.2252 32.3947C42.3541 31.9309 40.4852 31.645 38.6268 31.5362C41.2988 30.6128 43.2704 27.4931 43.2704 23.7958C43.2704 19.7812 40.8623 17.2876 36.9861 17.2876C35.5492 17.2876 34.3147 17.6305 33.3291 18.2659V0.780477C33.3291 0.349566 32.9795 0 32.5486 0H0.780529C0.349589 0 0 0.349566 0 0.780477V46.1938C0 46.6247 0.349589 46.9743 0.780529 46.9743H23.9688V49.2195C23.9688 49.6504 24.3184 50 24.7493 50C25.1803 50 25.5299 49.6504 25.5299 49.2195V38.1049C25.5299 35.6145 28.0309 34.4482 30.129 33.9084C34.5961 32.7603 39.2122 32.7608 43.8492 33.9101C45.5631 34.3349 48.4389 35.4452 48.4389 38.1049V49.2195C48.4389 49.6504 48.7885 50 49.2195 50C49.6504 50 50 49.6504 50 49.2195V38.1049C50.0005 35.3864 47.8953 33.3049 44.2252 32.3947ZM29.7403 32.3964C26.0185 33.3539 23.9688 35.3809 23.9688 38.1049V45.4133H1.56106V1.56095H31.7675V19.8153C31.0755 20.8662 30.699 22.2117 30.699 23.7958C30.699 25.4161 31.0738 26.9655 31.7675 28.2791V31.9523C31.0887 32.076 30.4131 32.2238 29.7403 32.3964ZM33.3291 31.7127V30.3287C33.9321 30.8789 34.6021 31.2845 35.3151 31.5313C34.6511 31.5686 33.9887 31.6297 33.3291 31.7127ZM32.26 23.7958C32.26 20.6519 33.9827 18.8491 36.9855 18.8491C39.9872 18.8491 41.7083 20.6524 41.7083 23.7958C41.7083 27.3568 39.5898 30.2539 36.9855 30.2539C34.3801 30.2539 32.26 27.3568 32.26 23.7958Z" fill="#1E293B" />
                        <path d="M28.055 4.49269H18.5397C18.1088 4.49269 17.7592 4.84226 17.7592 5.27317C17.7592 5.70408 18.1088 6.05365 18.5397 6.05365H28.055C28.486 6.05365 28.8356 5.70408 28.8356 5.27317C28.8356 4.84226 28.486 4.49269 28.055 4.49269Z" fill="#1E293B" />
                        <path d="M28.055 18.7743H18.5397C18.1088 18.7743 17.7592 19.1239 17.7592 19.5548C17.7592 19.9857 18.1088 20.3353 18.5397 20.3353H28.055C28.486 20.3353 28.8356 19.9857 28.8356 19.5548C28.8356 19.1239 28.486 18.7743 28.055 18.7743Z" fill="#1E293B" />
                        <path d="M28.055 8.42641H18.5397C18.1088 8.42641 17.7592 8.77597 17.7592 9.20688C17.7592 9.6378 18.1088 9.98736 18.5397 9.98736H28.055C28.486 9.98736 28.8356 9.6378 28.8356 9.20688C28.8356 8.77597 28.486 8.42641 28.055 8.42641Z" fill="#1E293B" />
                        <path d="M22.2885 33.0554H18.5403C18.1094 33.0554 17.7598 33.405 17.7598 33.8359C17.7598 34.2668 18.1094 34.6164 18.5403 34.6164H22.2885C22.7194 34.6164 23.069 34.2668 23.069 33.8359C23.069 33.405 22.7194 33.0554 22.2885 33.0554Z" fill="#1E293B" />
                    </svg> Performance Evaluation and Discussion Management (PEDMA)</h4>
                <div class="pedma-filters-card">
                    <p class="pedma-filters-title">Evaluation filters</p>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group" app-field-wrapper="performance_month">
                                <label class="control-label" for="performance_month">Please select a month</label>
                                <input type="month" class="form-control" id="performance_month" name="performance_month" value="<?php echo date('Y-m'); ?>" required />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <?php echo render_select('departments', $departments, array('departmentid', 'name'), 'department'); ?>
                        </div>
                        <div class="col-md-4">
                            <?php echo render_select('staffid', $staffs, array('staffid', array('firstname', 'lastname', 'staff_identifi')), 'Select Employee'); ?>
                        </div>
                    </div>
                </div>

                <?php $this->load->view('admin/staff/pedma_admin_evaluation_workspace', [
                    'pedma_comment_sections' => isset($pedma_comment_sections) ? $pedma_comment_sections : pedma_comment_sections(),
                    'pedma_can_overall_feedback' => isset($pedma_can_overall_feedback) ? $pedma_can_overall_feedback : pedma_can_edit_overall_feedback(),
                    'pedma_can_edit_all_eval' => isset($pedma_can_edit_all_eval) ? $pedma_can_edit_all_eval : pedma_can_edit_all_pedma_eval(),
                ]); ?>

            </div>
        </div>
    </div>
</div>

<!-- The Modal -->
<div class="modal" id="fatalErrorModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Adjust Score for Fatal Error</h4>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <form id="fatalErrorForm">
                    <div class="form-group">
                        <label>Choose Percentage Adjustment:</label>
                        <div class="percentage-selection">
                            <div id="fatalError0" class="percentage-box" data-value="100">-100%</div>
                            <div id="fatalError50" class="percentage-box" data-value="50">-50%</div>
                            <div id="fatalErrorCustom" class="percentage-box" data-value="custom">Custom</div>
                        </div>
                        <!-- Custom Input Field for Custom Percentage -->
                        <div class="custom-input-field" id="customInputField">
                            <label for="customPercentage">Decrease Score by (in %):</label>
                            <input type="number" id="customPercentage" class="form-control" placeholder="Enter custom percentage" max="100" min="0" value=""/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Select Months to Affect:</label>
                        <div class="month-checkboxes lastsixmonths">

                        </div>
                    </div>
                    <div class="form-group">
                        <label>Comment:</label>
                        <textarea class="form-control" name="fatal_comment" id="fatal_comment" required rows="5" placeholder="Please write a reason of giving fatal error..."></textarea>
                    </div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="closeFatalErrorBtn">Close</button>
                <button type="submit" class="btn btn-primary" id="applyFatalErrorBtn">Save</button>
            </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="addOnModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Adjust Score for Add-on</h4>
            </div>
            <!-- Modal body -->
            <div class="modal-body">
                <form id="highAchievementForm">
                    <div class="form-group">
                        <label for="customPercentageIncrease" class="form-label">Increase Score by (in %):</label>
                        <input type="number" class="form-control" id="customPercentageIncrease" placeholder="Enter percentage" required max="100" min="0" value="">
                    </div>
                    <div class="form-group">
                        <label>Select Months to Affect:</label>
                        <div class="month-checkboxes lastsixmonths">

                        </div>
                    </div>
                    <div class="form-group">
                        <label>Comment:</label>
                        <textarea class="form-control" name="addOn_comment" id="addOn_comment" required rows="5" placeholder="Please write a reason of giving add-on..."></textarea>
                    </div>
            </div>


            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="closeHighAchievementBtn">Close</button>
                <button type="submit" class="btn btn-primary" id="applyHighAchievementBtn">Save</button>
            </div>
            </form>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function($) {
    'use strict';

    var PEDMA_KRA_RATING_MAX = <?php echo (int) pedma_kra_rating_max(); ?>;
    var pedmaDefaultKras = [];
    var pedmaCustomKras = [];
    var pedmaScoreMap = {};
    var pedmaPersistedByKey = {};
    var pedmaSelectedDefault = [];
    var pedmaSelectedCustom = [];
    var pedmaExistingPerformance = null;
    var pedmaIsUpdate = false;

    function pedmaScoreKey(kind, index) {
        return kind + ':' + index;
    }

    function pedmaTrimVal(v) {
        return (v === undefined || v === null) ? '' : String(v).trim();
    }

    function pedmaKraHasPersistedFeedback(scored) {
        if (!scored) {
            return false;
        }
        if (pedmaTrimVal(scored.get_score) !== '') {
            return true;
        }
        if (pedmaTrimVal(scored.comment) !== '') {
            return true;
        }
        if (pedmaTrimVal(scored.hod_remarks) !== '') {
            return true;
        }
        if (pedmaTrimVal(scored.hr_remarks) !== '') {
            return true;
        }
        return false;
    }

    function pedmaIsKraDeselectLocked(kind, index) {
        if (window.pedmaCanEditAllFields) {
            return false;
        }
        var persisted = pedmaPersistedByKey[pedmaScoreKey(kind, index)];
        if (!pedmaKraHasPersistedFeedback(persisted)) {
            return false;
        }
        // Each role may change their own saved KRAs; lock sidebar only when another role has weighed in.
        if (window.pedmaCanManagerComment) {
            return pedmaTrimVal(persisted.hod_remarks) !== '' || pedmaTrimVal(persisted.hr_remarks) !== '';
        }
        if (window.pedmaCanHodComment) {
            return pedmaTrimVal(persisted.comment) !== '' || pedmaTrimVal(persisted.hr_remarks) !== '';
        }
        if (window.pedmaCanHrComment) {
            return pedmaTrimVal(persisted.comment) !== '' || pedmaTrimVal(persisted.hod_remarks) !== '';
        }
        return true;
    }

    function pedmaPersistedFieldHasValue(field, persisted) {
        if (!persisted) {
            return false;
        }
        if (field === 'rating') {
            return pedmaTrimVal(persisted.get_score) !== '';
        }
        if (field === 'manager') {
            return pedmaTrimVal(persisted.comment) !== '';
        }
        if (field === 'hod') {
            return pedmaTrimVal(persisted.hod_remarks) !== '';
        }
        if (field === 'hr') {
            return pedmaTrimVal(persisted.hr_remarks) !== '';
        }
        return false;
    }

    function pedmaFieldLockedForCurrentUser(field, persisted) {
        if (!pedmaPersistedFieldHasValue(field, persisted)) {
            return false;
        }
        if (window.pedmaCanEditAllFields) {
            return false;
        }
        if (field === 'manager' && window.pedmaCanManagerComment) {
            return false;
        }
        if (field === 'hod' && window.pedmaCanHodComment) {
            return false;
        }
        if (field === 'hr' && window.pedmaCanHrComment) {
            return false;
        }
        if (field === 'rating') {
            // Rating is owned by managers; HOD/HR cannot change a saved manager rating.
            if (window.pedmaCanManagerComment) {
                return false;
            }
            return true;
        }
        return true;
    }

    function pedmaClearSessionScoreForKra(kind, index) {
        var key = pedmaScoreKey(kind, index);
        var list = kind === 'custom' ? pedmaCustomKras : pedmaDefaultKras;
        var name = list[index] && list[index].name;
        delete pedmaScoreMap[key];
        if (name && pedmaScoreMap[name]) {
            delete pedmaScoreMap[name];
        }
    }

    function pedmaSetOverallField(section, value) {
        value = value || '';
        var canEdit = false;
        if (section === 'manager') {
            canEdit = !!window.pedmaCanEditOverallManager;
        } else if (section === 'hod') {
            canEdit = !!window.pedmaCanEditOverallHod;
        } else if (section === 'hr') {
            canEdit = !!window.pedmaCanEditOverallHr;
        }
        var $ta = $('#pedma_overall_' + section + '_feedback');
        var $hidden = $('#pedma_overall_' + section + '_feedback_hidden');
        var $ro = $('#pedma_overall_' + section + '_feedback_readonly');
        if (canEdit && $ta.length) {
            $ta.val(value);
        } else if ($hidden.length) {
            $hidden.val(value);
            $ro.text(value || '—');
        }
    }

    function pedmaSetOverallFeedbackFromPerf(perf) {
        perf = perf || {};
        var legacy = (perf.overall_feedback || '').trim();
        var manager = (perf.overall_manager_feedback || '').trim();
        var hod = (perf.overall_hod_feedback || '').trim();
        var hr = (perf.overall_hr_feedback || '').trim();
        if (manager === '' && hod === '' && hr === '' && legacy !== '') {
            manager = legacy;
        }
        pedmaSetOverallField('manager', manager);
        pedmaSetOverallField('hod', hod);
        pedmaSetOverallField('hr', hr);
    }

    function pedmaCaptureTableStateToScoreMap() {
        $('#default-kra-table-body tr[data-kra-key]').each(function() {
            var $row = $(this);
            var key = $row.attr('data-kra-key');
            if (!key) {
                return;
            }
            var name = $row.find('[name$="[name]"]').val();
            var scored = {
                name: name,
                description: $row.find('[name$="[description]"]').val() || '',
                max_score: $row.find('[name$="[max_score]"]').val() || '',
                get_score: pedmaRowRatingValue($row),
                comment: $row.find('[name$="[comment]"]').val() || '',
                hr_remarks: $row.find('[name$="[hr_remarks]"]').val() || '',
                hod_remarks: $row.find('[name$="[hod_remarks]"]').val() || ''
            };
            pedmaScoreMap[key] = scored;
            if (name) {
                pedmaScoreMap[name] = scored;
            }
        });
    }

    function hasValue(v) {
        return !(v === undefined || v === null || v === '' || (Array.isArray(v) && v.length === 0));
    }

    function pedmaFiltersReady() {
        return hasValue($('#departments').val())
            && hasValue($('#staffid').val())
            && hasValue($('#performance_month').val());
    }

    function pedmaPostData(extra) {
        var data = $.extend({}, extra || {});
        if (typeof csrfData !== 'undefined') {
            data[csrfData.token_name] = csrfData.hash;
        }
        return data;
    }

    function pedmaShowWorkspace(show) {
        $('#pedmaEvalWorkspace').toggle(!!show);
        $('#pedmaEvalPlaceholder').toggle(!show);
    }

    function pedmaSyncHiddenFilters() {
        $('#pedma_hidden_staffid').val($('#staffid').val());
        $('#pedma_hidden_performance_month').val($('#performance_month').val());
    }

    function pedmaEscapeHtml(text) {
        return $('<div>').text(text == null ? '' : String(text)).html();
    }

    function pedmaRowRatingValue($row) {
        var $checked = $row.find('.pedma-rating-input:checked');
        if ($checked.length) {
            return $checked.val();
        }

        // jQuery .val() on an unchecked radio group returns the first option (1) — not a user choice.
        return '';
    }

    function pedmaRatingInputHtml(fieldPrefix, displayIndex, scoreVal, ratingLocked) {
        scoreVal = (scoreVal === undefined || scoreVal === null) ? '' : String(scoreVal);
        var disabledAttr = ratingLocked ? ' disabled' : '';
        var html = '<div class="pedma-rating-group">';
        for (var r = 1; r <= 5; r++) {
            var checked = scoreVal === String(r) ? ' checked' : '';
            html += '<label><input type="radio" class="pedma-rating-input" name="' + fieldPrefix + '[' + displayIndex + '][get_score]" value="' + r + '"' + checked + disabledAttr + '> ' + r + '</label>';
        }
        html += '</div>';
        return html;
    }

    function pedmaHiddenCommentFields(fieldPrefix, displayIndex, scored) {
        var html = '';
        if (!window.pedmaCanManagerComment) {
            html += '<input type="hidden" name="' + fieldPrefix + '[' + displayIndex + '][comment]" value="' + pedmaEscapeHtml(scored.comment || '') + '">';
        }
        if (!window.pedmaCanHrComment) {
            html += '<input type="hidden" name="' + fieldPrefix + '[' + displayIndex + '][hr_remarks]" value="' + pedmaEscapeHtml(scored.hr_remarks || '') + '">';
        }
        if (!window.pedmaCanHodComment) {
            html += '<input type="hidden" name="' + fieldPrefix + '[' + displayIndex + '][hod_remarks]" value="' + pedmaEscapeHtml(scored.hod_remarks || '') + '">';
        }
        return html;
    }

    function pedmaCommentCell(commentKind, fieldPrefix, displayIndex, scored, persisted) {
        var val = '';
        var fieldKey = commentKind;
        var locked = pedmaFieldLockedForCurrentUser(fieldKey, persisted);
        var readOnlyAttr = locked ? ' readonly' : '';
        if (commentKind === 'manager') {
            val = scored.comment || '';
            if (!window.pedmaCanManagerComment) {
                return '';
            }
            return '<td><textarea class="form-control comment" name="' + fieldPrefix + '[' + displayIndex + '][comment]" placeholder="Manager comment..."' + readOnlyAttr + '>' + pedmaEscapeHtml(val) + '</textarea></td>';
        }
        if (commentKind === 'hod') {
            val = scored.hod_remarks || '';
            if (!window.pedmaCanHodComment) {
                return '';
            }
            return '<td><textarea class="form-control hod-remarks" name="' + fieldPrefix + '[' + displayIndex + '][hod_remarks]" placeholder="HOD comment..."' + readOnlyAttr + '>' + pedmaEscapeHtml(val) + '</textarea></td>';
        }
        val = scored.hr_remarks || '';
        if (!window.pedmaCanHrComment) {
            return '';
        }
        return '<td><textarea class="form-control hr-remarks" name="' + fieldPrefix + '[' + displayIndex + '][hr_remarks]" placeholder="HR comment..."' + readOnlyAttr + '>' + pedmaEscapeHtml(val) + '</textarea></td>';
    }

    function pedmaRecalcScore() {
        var sum = 0;
        var count = 0;
        $('#default-kra-table-body tr[data-kra-key]').each(function() {
            var rating = parseInt(pedmaRowRatingValue($(this)), 10);
            if (!isNaN(rating) && rating >= 1) {
                sum += rating;
                count++;
            }
        });
        if (count > 0) {
            var pct = ((sum / count / PEDMA_KRA_RATING_MAX) * 100).toFixed(2);
            $('#avg_score_display1').text(pct + '%');
            $('#avg_score1').val(pct);
        } else {
            $('#avg_score_display1').text('');
            $('#avg_score1').val('');
        }
    }

    function pedmaBuildEvalTable() {
        pedmaCaptureTableStateToScoreMap();
        var $body = $('#default-kra-table-body');
        $body.empty();
        var hasAny = pedmaSelectedDefault.length || pedmaSelectedCustom.length;

        if (!hasAny) {
            $body.html('<tr class="pedma-kra-empty-row"><td colspan="' + window.pedmaEvalTableColspan + '" class="text-muted text-center" style="padding:18px;">Select one or more KRAs on the left.</td></tr>');
            pedmaRecalcScore();
            return;
        }

        var useCustomPayload = pedmaSelectedCustom.length > 0;
        var fieldPrefix = useCustomPayload ? 'kraData2' : 'kraData1';
        var displayIndex = 0;

        function appendRow(kind, listIndex, item, rowPrefix, rowDisplayIndex) {
            if (!item) {
                return;
            }
            var key = pedmaScoreKey(kind, listIndex);
            var persisted = pedmaPersistedByKey[key] || null;
            var scored = $.extend({}, persisted || {}, pedmaScoreMap[key] || pedmaScoreMap[item.name] || {});
            var scoreVal = (scored.get_score !== undefined && scored.get_score !== null && scored.get_score !== '')
                ? scored.get_score
                : '';
            var ratingLocked = pedmaFieldLockedForCurrentUser('rating', persisted);
            var desc = item.description ? '<br><span class="text-muted">' + pedmaEscapeHtml(item.description) + '</span>' : '';
            var typeBadge = kind === 'custom' ? ' <span class="label label-default">Custom</span>' : '';
            var row = '<tr data-kra-key="' + key + '" data-kra-kind="' + kind + '">' +
                '<td class="performance-criteria"><b>' + pedmaEscapeHtml(item.name) + '</b>' + typeBadge + desc +
                '<input type="hidden" name="' + rowPrefix + '[' + rowDisplayIndex + '][name]" value="' + pedmaEscapeHtml(item.name) + '">' +
                '<input type="hidden" name="' + rowPrefix + '[' + rowDisplayIndex + '][description]" value="' + pedmaEscapeHtml(item.description || '') + '">' +
                '<input type="hidden" name="' + rowPrefix + '[' + rowDisplayIndex + '][max_score]" value="' + PEDMA_KRA_RATING_MAX + '">' +
                pedmaHiddenCommentFields(rowPrefix, rowDisplayIndex, scored) +
                '</td>' +
                '<td>' + pedmaRatingInputHtml(rowPrefix, rowDisplayIndex, scoreVal, ratingLocked) +
                (ratingLocked && scoreVal !== '' ? '<input type="hidden" name="' + rowPrefix + '[' + rowDisplayIndex + '][get_score]" value="' + pedmaEscapeHtml(scoreVal) + '">' : '') +
                '</td>' +
                pedmaCommentCell('manager', rowPrefix, rowDisplayIndex, scored, persisted) +
                pedmaCommentCell('hod', rowPrefix, rowDisplayIndex, scored, persisted) +
                pedmaCommentCell('hr', rowPrefix, rowDisplayIndex, scored, persisted) +
                '</tr>';
            $body.append(row);
        }

        pedmaSelectedDefault.forEach(function(listIndex) {
            appendRow('default', listIndex, pedmaDefaultKras[listIndex], fieldPrefix, displayIndex++);
        });
        pedmaSelectedCustom.forEach(function(listIndex) {
            appendRow('custom', listIndex, pedmaCustomKras[listIndex], fieldPrefix, displayIndex++);
        });

        $('#pedma_kra_option').val(useCustomPayload ? 'custom' : 'default');
        pedmaRecalcScore();
    }

    function pedmaSyncKraAllCheckbox(scope) {
        var list = scope === 'custom' ? pedmaCustomKras : pedmaDefaultKras;
        var selected = scope === 'custom' ? pedmaSelectedCustom : pedmaSelectedDefault;
        var $all = scope === 'custom' ? $('#pedmaCustomKraAll') : $('#pedmaDefaultKraAll');
        if (!$all.length || !list.length) {
            if ($all.length) {
                $all.prop('checked', false).prop('indeterminate', false);
            }
            return;
        }
        $all.prop('checked', selected.length === list.length);
        $all.prop('indeterminate', selected.length > 0 && selected.length < list.length);
    }

    function pedmaRenderKraList(scope, containerSelector, list, selected, pickClass) {
        var html = '';
        $.each(list, function(index, item) {
            var checked = selected.indexOf(index) !== -1;
            var locked = pedmaIsKraDeselectLocked(scope, index);
            html += '<label class="kra-list-item' + (checked ? ' unlocked' : '') + (locked ? ' pedma-kra-locked' : '') + '">' +
                '<input type="checkbox" class="' + pickClass + '" data-scope="' + scope + '" value="' + index + '"' + (checked ? ' checked' : '') + (locked ? ' disabled' : '') + '>' +
                '<span>' + pedmaEscapeHtml(item.name) + (locked ? ' <span class="text-muted">(locked)</span>' : '') + '</span>' +
                '</label>';
        });
        if (!html) {
            html = '<p class="text-muted" style="margin:4px 0 8px;">None for this department.</p>';
        }
        $(containerSelector).html(html);
        pedmaSyncKraAllCheckbox(scope);
    }

    function pedmaSetKraPanelOpen(scope, open) {
        var $panel = scope === 'custom' ? $('#pedmaCustomKraPanel') : $('#pedmaDefaultKraPanel');
        var $body = scope === 'custom' ? $('#pedmaCustomKraPanelBody') : $('#pedmaDefaultKraPanelBody');
        var $head = $panel.find('.pedma-kra-panel-head');
        if (open) {
            $panel.addClass('is-open');
            $body.prop('hidden', false);
            $head.attr('aria-expanded', 'true');
        } else {
            $panel.removeClass('is-open');
            $body.prop('hidden', true);
            $head.attr('aria-expanded', 'false');
        }
    }

    function pedmaSyncKraAccordionOpen() {
        pedmaSetKraPanelOpen('default', pedmaSelectedDefault.length > 0);
        pedmaSetKraPanelOpen('custom', pedmaSelectedCustom.length > 0);
    }

    function pedmaRenderKraSidebar() {
        pedmaRenderKraList('default', '#pedmaDefaultKraList', pedmaDefaultKras, pedmaSelectedDefault, 'pedma-kra-pick');
        pedmaRenderKraList('custom', '#pedmaCustomKraList', pedmaCustomKras, pedmaSelectedCustom, 'pedma-kra-pick');
        pedmaSyncKraAccordionOpen();
    }

    function pedmaApplyPerformanceData(perf) {
        pedmaExistingPerformance = perf;
        pedmaScoreMap = {};
        pedmaPersistedByKey = {};
        pedmaSelectedDefault = [];
        pedmaSelectedCustom = [];
        pedmaIsUpdate = false;
        $('#pedmaPublishBtn, #pedmaDraftBtn').show();
        $('#pedmaUpdateBtn').hide();
        $('#pedmaStaffResponseArea').hide();
        pedmaSetOverallFeedbackFromPerf(null);
        $('#pedmaStaffResp').val('');

        if (!perf) {
            pedmaRenderKraSidebar();
            pedmaBuildEvalTable();
            return;
        }

        var kraRows = [];
        try {
            kraRows = JSON.parse(perf.kra_data || '[]');
        } catch (e) {
            kraRows = [];
        }

        var perfIsCustom = perf.type && perf.type !== 'default';
        kraRows.forEach(function(row) {
            if (!row || !row.name) {
                return;
            }
            var matched = false;
            pedmaDefaultKras.forEach(function(def, idx) {
                if (def.name === row.name) {
                    pedmaSelectedDefault.push(idx);
                    var dKey = pedmaScoreKey('default', idx);
                    pedmaScoreMap[dKey] = row;
                    pedmaPersistedByKey[dKey] = $.extend(true, {}, row);
                    pedmaScoreMap[row.name] = row;
                    matched = true;
                }
            });
            if (!matched || perfIsCustom) {
                pedmaCustomKras.forEach(function(def, idx) {
                    if (def.name === row.name) {
                        if (pedmaSelectedCustom.indexOf(idx) === -1) {
                            pedmaSelectedCustom.push(idx);
                        }
                        var cKey = pedmaScoreKey('custom', idx);
                        pedmaScoreMap[cKey] = row;
                        pedmaPersistedByKey[cKey] = $.extend(true, {}, row);
                        pedmaScoreMap[row.name] = row;
                    }
                });
            }
        });
        pedmaSelectedDefault = pedmaSelectedDefault.filter(function(v, i, a) { return a.indexOf(v) === i; });
        pedmaSelectedCustom = pedmaSelectedCustom.filter(function(v, i, a) { return a.indexOf(v) === i; });

        if (kraRows.length && !pedmaSelectedDefault.length && !pedmaSelectedCustom.length) {
            kraRows.forEach(function(row) {
                if (!row || !row.name) {
                    return;
                }
                pedmaDefaultKras.forEach(function(def, idx) {
                    if (def.name === row.name && pedmaSelectedDefault.indexOf(idx) === -1) {
                        pedmaSelectedDefault.push(idx);
                        var dKey2 = pedmaScoreKey('default', idx);
                        pedmaScoreMap[dKey2] = row;
                        pedmaPersistedByKey[dKey2] = $.extend(true, {}, row);
                    }
                });
                pedmaCustomKras.forEach(function(def, idx) {
                    if (def.name === row.name && pedmaSelectedCustom.indexOf(idx) === -1) {
                        pedmaSelectedCustom.push(idx);
                        var cKey2 = pedmaScoreKey('custom', idx);
                        pedmaScoreMap[cKey2] = row;
                        pedmaPersistedByKey[cKey2] = $.extend(true, {}, row);
                    }
                });
            });
        }

        if (perf.status && String(perf.status) !== '0') {
            pedmaIsUpdate = true;
            $('#pedmaPublishBtn, #pedmaDraftBtn').hide();
            $('#pedmaUpdateBtn').show();
        }

        pedmaSetOverallFeedbackFromPerf(perf);
        if (perf.staff_comment) {
            $('#pedmaStaffResponseArea').show();
            $('#pedmaStaffResp').val(perf.staff_comment);
        }

        if (perf.avg_score) {
            $('#avg_score_display1').text(parseFloat(perf.avg_score).toFixed(2) + '%');
            $('#avg_score1').val(perf.avg_score);
            $('#avg_score2').val(perf.avg_score);
        }

        pedmaRenderKraSidebar();
        pedmaBuildEvalTable();
    }

    function pedmaLoadEmployeeEvaluation() {
        if (!pedmaFiltersReady()) {
            pedmaShowWorkspace(false);
            return;
        }

        pedmaSyncHiddenFilters();
        pedmaShowWorkspace(true);
        $('#pedmaDefaultKraList, #pedmaCustomKraList').html('<p class="text-muted">Loading…</p>');
        $('#default-kra-table-body').html('<tr><td colspan="' + window.pedmaEvalTableColspan + '" class="text-muted text-center">Loading…</td></tr>');

        var department = $('#departments').val();
        var staffid = $('#staffid').val();
        var month = $('#performance_month').val();

        $.when(
            $.ajax({
                url: admin_url + 'staff/get_staff_json',
                type: 'POST',
                data: pedmaPostData({ staffid: staffid }),
                dataType: 'json'
            }),
            $.ajax({
                url: admin_url + 'staff/get_kra_based_on_department_json',
                type: 'POST',
                data: pedmaPostData({ department: department }),
                dataType: 'json'
            }),
            $.ajax({
                url: admin_url + 'staff/get_staff_performance_month_and_id',
                type: 'POST',
                data: pedmaPostData({ staffid: staffid, month: month }),
                dataType: 'json'
            })
        ).done(function(staffRes, kraRes, perfRes) {
            var staffPayload = staffRes[0];
            var kraPayload = kraRes[0];
            var perfPayload = perfRes[0];

            if (staffPayload && staffPayload[0] && staffPayload[0][0]) {
                $('#pedma-full-name').text(staffPayload[0][0].full_name || '');
                $('#pedma-designation').text(staffPayload[0][0].job_name || '');
            }
            if (staffPayload && staffPayload[1]) {
                $('#pedma-department-name').text(staffPayload[1].name || '');
            }

            pedmaDefaultKras = (kraPayload && kraPayload.default_kra) ? kraPayload.default_kra : [];
            pedmaCustomKras = (kraPayload && kraPayload.custom_kra) ? kraPayload.custom_kra : [];
            pedmaApplyPerformanceData(perfPayload || null);
        }).fail(function() {
            alert('Failed to load evaluation data. Please try again.');
            pedmaShowWorkspace(false);
        });
    }

    $(function() {
        $('#departments').prop('required', true);
        $('#staffid').prop('required', true);
        $('#staffid').html('<option value=""></option>');
        if ($('#staffid').hasClass('selectpicker')) {
            $('#staffid').selectpicker('refresh');
        }

        $('#departments').on('change', function() {
            pedmaShowWorkspace(false);
            $('#staffid').selectpicker('val', '');
            var department = $(this).val();
            if (!hasValue(department)) {
                $('#staffid').html('<option value=""></option>').selectpicker('refresh');
                return;
            }
            $.ajax({
                url: admin_url + 'staff/get_staff_department_json',
                type: 'POST',
                data: pedmaPostData({ department: department }),
                dataType: 'json',
                success: function(response) {
                    var staff = '<option value=""></option>';
                    (Array.isArray(response) ? response : []).forEach(function(element) {
                        staff += '<option value="' + element.staffid + '">' +
                            element.firstname + ' ' + element.lastname + ' ' + (element.staff_identifi || '') +
                            '</option>';
                    });
                    $('#staffid').html(staff).selectpicker('refresh');
                }
            });
        });

        $('#staffid, #performance_month').on('change', function() {
            if (!hasValue($('#staffid').val())) {
                pedmaShowWorkspace(false);
                return;
            }
            pedmaLoadEmployeeEvaluation();
        });

        $(document).on('change', '.pedma-kra-pick', function() {
            var scope = $(this).data('scope');
            var idx = parseInt($(this).val(), 10);
            if ($(this).is(':checked')) {
                if (scope === 'custom') {
                    if (pedmaSelectedCustom.indexOf(idx) === -1) {
                        pedmaSelectedCustom.push(idx);
                    }
                } else if (pedmaSelectedDefault.indexOf(idx) === -1) {
                    pedmaSelectedDefault.push(idx);
                }
                if (!pedmaPersistedByKey[pedmaScoreKey(scope, idx)]) {
                    pedmaClearSessionScoreForKra(scope, idx);
                }
            } else {
                if (pedmaIsKraDeselectLocked(scope, idx)) {
                    $(this).prop('checked', true);
                    alert('This KRA has saved feedback and cannot be removed.');
                    return;
                }
                pedmaClearSessionScoreForKra(scope, idx);
                if (scope === 'custom') {
                    pedmaSelectedCustom = pedmaSelectedCustom.filter(function(i) { return i !== idx; });
                } else {
                    pedmaSelectedDefault = pedmaSelectedDefault.filter(function(i) { return i !== idx; });
                }
            }
            pedmaRenderKraSidebar();
            pedmaBuildEvalTable();
        });

        $(document).on('change', '.pedma-kra-all', function() {
            var scope = $(this).data('scope');
            var list = scope === 'custom' ? pedmaCustomKras : pedmaDefaultKras;
            var checked = $(this).is(':checked');
            if (checked) {
                if (scope === 'custom') {
                    pedmaSelectedCustom = list.map(function(_, i) { return i; });
                } else {
                    pedmaSelectedDefault = list.map(function(_, i) { return i; });
                }
            } else {
                var kept = [];
                list.forEach(function(_, i) {
                    if (pedmaIsKraDeselectLocked(scope, i)) {
                        kept.push(i);
                    } else {
                        pedmaClearSessionScoreForKra(scope, i);
                    }
                });
                if (scope === 'custom') {
                    pedmaSelectedCustom = kept;
                } else {
                    pedmaSelectedDefault = kept;
                }
            }
            pedmaSetKraPanelOpen(scope, true);
            pedmaRenderKraSidebar();
            pedmaBuildEvalTable();
        });

        $(document).on('click', '.pedma-kra-panel-head', function() {
            var scope = $(this).closest('.pedma-kra-panel').data('scope');
            var $panel = scope === 'custom' ? $('#pedmaCustomKraPanel') : $('#pedmaDefaultKraPanel');
            var open = !$panel.hasClass('is-open');
            pedmaSetKraPanelOpen(scope, open);
        });

        $(document).on('change', '.pedma-rating-input', function() {
            pedmaCaptureTableStateToScoreMap();
            pedmaRecalcScore();
        });

        $(document).on('change input', '#default-kra-table-body textarea', function() {
            pedmaCaptureTableStateToScoreMap();
        });

        $('#pedmaPublishBtn').on('click', function() {
            $('#pedma_submit_status').val('1');
            $('#pedma-form').trigger('submit');
        });
        $('#pedmaDraftBtn').on('click', function() {
            $('#pedma_submit_status').val('0');
            $('#pedma-form').trigger('submit');
        });
        $('#pedmaUpdateBtn').on('click', function() {
            $('#pedma_submit_status').val('2');
            $('#pedma-form').trigger('submit');
        });

        $('#pedma-form').on('submit', function(e) {
            pedmaCaptureTableStateToScoreMap();
            if (!$('#default-kra-table-body tr[data-kra-key]').length) {
                e.preventDefault();
                alert('Select at least one KRA and enter a rating.');
                return false;
            }
            var missingRating = false;
            var missingName = '';
            $('#default-kra-table-body tr[data-kra-key]').each(function() {
                var $row = $(this);
                var rating = parseInt(pedmaRowRatingValue($row), 10);
                if (isNaN(rating) || rating < 1 || rating > 5) {
                    missingRating = true;
                    if (!missingName) {
                        missingName = $row.find('[name$="[name]"]').val() || 'KRA';
                    }
                }
            });
            if (missingRating) {
                e.preventDefault();
                alert('Each selected KRA must have a rating from 1 to 5 before saving.' + (missingName ? ' Missing: ' + missingName : ''));
                return false;
            }
            if (!$('#avg_score1').val()) {
                e.preventDefault();
                alert('Please set a rating (1–5) for each selected KRA.');
                return false;
            }
            pedmaSyncHiddenFilters();
            var score = $('#avg_score1').val();
            $('#avg_score2').val(score);
            $('#pedma_kra_option').val(pedmaSelectedCustom.length > 0 ? 'custom' : 'default');
        });
    });
})(jQuery);
</script>
