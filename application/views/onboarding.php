<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Onboarding Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/all.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400&display=swap');

        * {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        p {
            color: grey
        }

        .sidebar-container {
            background-image: url("/uploads/onboarding-images/viewing-platform-mori-tower-2.jpg");
            height: 100%;
            background-size: cover;
            padding: 20px;
        }

        #msform {
            text-align: center;
            position: relative;
            margin-top: 20px;
            box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
        }


        #msform fieldset {
            background: white;
            border: 0 none;
            border-radius: 0.5rem;
            box-sizing: border-box;
            width: 100%;
            margin: 0;
            padding-bottom: 20px;
            position: relative;
            /* height: 100%; */
            margin-bottom: 75px;
        }

        span.red-tri {
            color: red !important;
        }

        th {
            color: #38475c;
            font-size: 14px;
        }

        .form-card {
            text-align: left;
            margin-bottom: 30px;
        }

        #msform fieldset:not(:first-of-type) {
            display: none
        }

        #msform select {
            padding: 8px 15px 8px 15px;
            border: 1px solid #ccc;
            border-radius: 0px;
            margin-bottom: 25px;
            margin-top: 2px;
            width: 100%;
            box-sizing: border-box;
            color: #2C3E50;
            background-color: #ECEFF1;
            font-size: 16px;
            letter-spacing: 1px;
        }

        #msform select {
            background: none !important;
            padding: 6px 6px !important;
        }

       .form-select-family {
    border: 0px;
    padding: 8px 20px;
    border-radius: 5px;
    margin-left: -10px;
}
        #msform input,
        #msform select {
            border: none;
            border-bottom: 1px solid #d0d0d0;
            border-radius: 0px;
            margin-bottom: 25px;
            width: 100%;
            box-sizing: border-box;
            color: #111111;
            font-size: 14px;
            letter-spacing: 1px;
            height: 100%;
            font-weight: 500;
            padding: 0;
            /* background: #fff !important; */
        }

        #msform input:focus,
        #msform select:focus {
            color: #111111;
        }

        #msform .comment-container textarea {
            border: 1px solid rgb(210, 210, 210);
            padding: 10px;
            border-radius: 3px;
        }

        #msform textarea:focus,
        #msform select:focus {
            box-shadow: none;
        }

        #msform span {
            border: none;
            border-bottom: 1px solid #d0d0d0;
            background-color: transparent;
            border-radius: 0;
            padding-left: 0;
            color: #a9acb0;
            font-size: 12px;
        }

        #msform .form-group-main {
            color: #606B7C;
            margin-bottom: 30px;
        }

        #msform input:focus {
            box-shadow: none;
        }

        #msform .action-button {
            width: 100px;
            background: #cb010d !important;
            font-weight: bold;
            color: white;
            border: 0 none;
            border-radius: 0px;
            cursor: pointer;
            padding: 10px 5px;
            margin: 10px 0px 10px 5px;
            float: right;
            height: 50px;
            position: absolute;
            /* bottom: 25px; */
            right: 14px;
        }

        #msform .action-button:hover,
        #msform .action-button:focus {
            background-color: #000000;
        }

        #msform .action-button-previous {
            width: 100px;
            background: #616161 !important;
            font-weight: bold;
            color: white;
            border: 0 none;
            border-radius: 0px;
            cursor: pointer;
            padding: 10px 5px;
            margin: 10px 5px 10px 0px;
            float: right;
            height: 50px;
            position: absolute;
            /* bottom: 25px; */
            right: 120px;
            z-index: 1;

        }

        #msform .action-button-previous:hover,
        #msform .action-button-previous:focus {
            background-color: #000000
        }

        #msform .action-button-skip {
            width: 100px;
            background: #FFFF00 !important;
            font-weight: bold;
            color: black;
            border: 0 none;
            border-radius: 0px;
            cursor: pointer;
            padding: 10px 5px;
            margin: 10px 5px 10px 0px;
            float: right;
            height: 50px;
            position: absolute;
            /* bottom: 25px; */
            right: 230px;
            z-index: 1;

        }

        input:-internal-autofill-selected {
            background: #fff !important;
        }

        #msform .action-button-skip:hover,
        #msform .action-button-skip:focus {
            background-color: #FFFFFF;
            border: solid black 2px;
        }

        .card {
            z-index: 0;
            border: none;
            position: relative
        }

        .fs-title {
            font-size: 25px;
            color: #000000;
            margin-bottom: 15px;
            font-weight: normal;
            text-align: left
        }

        .purple-text {
            color: #cb010d;
            font-weight: normal
        }

        .steps {
            font-size: 25px;
            color: gray;
            margin-bottom: 10px;
            font-weight: normal;
            text-align: right
        }

        .fieldlabels {
            color: gray;
            text-align: left
        }

        #progressbar {
            margin-bottom: 30px;
            overflow: hidden;
            color: rgb(255, 255, 255);
            display: flex;
            flex-direction: column;
            padding-left: 10px;
            margin-top: 30px;
        }

        #progressbar .active {
            color: #efe3e3
        }

        #progressbar li {
            list-style-type: none;
            font-size: 15px;
            width: 100%;
            position: relative;
            font-weight: 400;
            display: flex;
            align-items: center;
            justify-content: start;
            gap: 10px;
            margin-bottom: 30px;
        }

        #progressbar li .icons-container {
            width: 30px;
            height: 30px;
            display: block;
            font-size: 20px;
            color: #ffffff !important;
            background: #c5c5c56e;
            border-radius: 50%;
            padding: 2px;
            font-size: 16px;
            display: grid;
            place-content: center;
            position: relative;
        }

        .icons-container:after {
            content: "";
            position: absolute;
            left: 50%;
            background: #6e747b;
            width: 1px;
            height: 78px;
            overflow: visible;
        }

        .content-container {
            text-align: left;
        }

        .content-container h5 {
            margin-bottom: 0;
            font-size: 16px;
            color: #60676e;
        }

        .content-container p {
            font-size: 14px;
            color: #6d737b;
        }


        #progressbar li.active .icons-container {
            background: #ffffff;
        }

        #progressbar li.active .icons-container i {
            color: #1E293B;
        }

        .icons-container i {
            z-index: 1;
        }

        .progress {
            height: 20px
        }

        .progress-bar {
            /* width: 100%; */
            height: 100%;
            background-color: #1E293B;
        }

        .fit-image {
            width: 100%;
            object-fit: cover
        }



        .content {
            position: absolute;
            left: 100%;
            margin-left: 75px;
            top: 33%;
            transform: translate(-50%, -50%);
            width: max-content;
        }

        .form-group-main {
            padding-inline: 15px !important;
        }

        .feildset-container {
            padding: 0 25px 0 0;
            position: relative;
        }

        .feildset-container::after {
            content: "";
            position: absolute;
            background: url('/uploads/onboarding-images/fly.png');
            width: 290px;
            height: 256px;
            background-size: cover;
            bottom: 50px;
            right: 89px;
            opacity: 0.3;
        }

        form#msform .row>div {
            padding: 0;
        }

        .form-card .input-group {
            height: 40px;
        }

        .content h5 {
            font-size: 16px;
        }

        .content p {
            font-size: 12px;
            color: #ebebeb;
        }

        p {
            margin-bottom: 0;
        }

        .main-container {
            position: relative;
            height: 100%;
        }

        .checkbox-group input {
            border: 1px solid gray !important;
            height: 16px !important;
            width: 16px !important;
            border-bottom: none !important;
            padding: 0 !important;
        }

        .form-check-input[type=radio] {
            border-radius: 50% !important;
        }

        .form-check-input[type=radio]:checked {
            background-color: #49535f !important;
            border: 2px solid #d6d6d6 !important;
        }

        .feildset-container {
            height: 100%;
        }




        /* 1801 */

        /* hobby css */
        #hobbyInputContainer {
            display: flex;
            flex-direction: column;
            align-items: center;
        }



        #hobbies-display {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }


        .hobby-button {
            background-color: #3e474e;
            color: white;
            border: none;
            border-radius: 0px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background-color 0.3s;
            font-size: 14px;
        }



        .hobby-button .close-button {
            background-color: transparent;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
            padding: 0 8px;
        }

        .hobby-button button:not(:last-child) {
            padding: 8px 10px;
            border: 0;
            background: #2e3844;
            color: #fff;
        }

        button.addHobbyBtn {
            padding: 6px 9px;
            border: 1px solid #464e5933;
            color: #757b84;
        }

        .flexible-timing-input-group .form-check {
            margin-right: 28px;
        }

        .form-check-input {
            width: 18px !important;
            height: 18px !important;
            border: 1px solid #bcbcbc !important;
        }

        #progressbar li.active .content-container h5 {
            color: white;
        }

        #progressbar li.active .content-container p {
            color: #fff;
        }

        #progressbar li.active .icons-container:after {
            background: #fff;
        }

        .form-check-input[type=radio] {
            width: 14px !important;
            height: 14px !important;
        }


        /* hobby css */
        .upload-file-input {
            border: 1px dashed #d4d4d4;


        }

        .upload-file-input input {
            border-bottom: 0px !important;
            margin-bottom: 0px !important;
        }

        /* table */
        table.table.table-bordered.family-varification-table input {
            margin: 0 !important;
            background: #dfdfdf91 !important;
            padding: 6px 5px !important;
            border-bottom: 0px !important;
        }

        table.table.table-bordered.family-varification-table input:hover,
        table.table.table-bordered.family-varification-table input:focus {
            border: 0px !important;
            background-color: #e8e8e8 !important;
            outline: none !important;
        }

        #msform label {
            font-size: 15px;
            color: #38475c;
            font-weight: 600;

        }


        #msform textarea#floatingTextarea {
            height: 40px;
            border-radius: 0;
        }

        #msform .checkbox-container input {
            background: auto !important;
        }

        #msform .progress-container-mobile-view-parent {
            display: none;
        }

        .input-group-error p {
            font-size: 13px;
            margin-top: 9px;
            color: #e00707bf !important;
        }

        /* table */

        @media(max-width:768px) {
            #progressbar {
                display: none;
            }

            .container-fluid {
                padding: 0;
            }

            #msform .progress-container-mobile-view-parent {
                display: block;
            }

            #msform .steps {
                font-size: 18px;
            }

            #msform .fs-title {
                font-size: 20px;
            }

            .main-container {
                padding: 10px 27px;
            }

            .form-group-main {
                padding-inline: 0px !important;
            }

            #msform {
                margin-top: 0px;
                box-shadow: none;
            }

            #progressbar li:before {
                width: 42px;
                height: 42px;
                line-height: 41px;
            }

            .feildset-container {
                padding: 0px;
            }

            .feildset-container {
                height: 100%;
            }

            .feildset-container::after {
                width: 150px;
                height: 131px;
                right: 99px;
            }

            form#msform .row>div {
                margin-bottom: 20px;
            }

            .card {
                margin-top: 0 !important;
            }

        }

        .input-group-error p {
            font-size: 12px;
        }



        /* .loader {
            display: none;
        } */

        .logo>img {
            width: 100%;
            height: auto;
        }

        @media screen and (min-width: 768px) and (max-width: 1200px) {
            .sidebar-container {
                padding: 4px;
            }
        }
    </style>
    <style>
        .loader-wrapper {
            height: 100vh;
            display: flex;
        }

        .loader {
            width: 50px;
            aspect-ratio: 1;
            margin: auto;
            border-radius: 50%;
            border: 8px solid lightblue;
            border-right-color: #C01F29;
            animation: l2 1s infinite linear;
        }

        @keyframes l2 {
            to {
                transform: rotate(1turn)
            }
        }

        th {
            white-space: nowrap;
            min-width: 150px;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="container">
            <div class="card px-0 pb-0 mb-3 mt-5">
                <div id="msform">
                    <!-- progressbar -->
                    <div class="row">
                        <div class="col-xxl-3 col-xl-3 col-lg-3 col-md-3 col-sm-12 col-12">
                            <div class="sidebar-container">
                                <div class="logo">
                                    <img src="/uploads/onboarding-images/white-logo.png" alt="">
                                </div>
                                <ul id="progressbar" class="desktop-progress">
                                    <li class="active">
                                        <div class="icons-container">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                        <div class="content-container">
                                            <h5>Personal Details</h5>
                                            <p>Personal Details of Emp</p>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="icons-container">
                                            <i class="fa-solid fa-calendar-week"></i>
                                        </div>
                                        <div class="content-container">
                                            <h5>Official Details</h5>
                                            <p>Official Details of Emp</p>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="icons-container">
                                            <i class="fa-solid fa-family"></i>
                                        </div>
                                        <div class="content-container">
                                            <h5>Family Member Details </h5>
                                            <p>Family Information</p>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="icons-container">
                                            <i class="fa-solid fa-book"></i>
                                        </div>
                                        <div class="content-container">
                                            <h5>Qualification</h5>
                                            <p>Qualification Details of Emp</p>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="icons-container">
                                            <i class="fa-solid fa-graduation-cap"></i>
                                        </div>
                                        <div class="content-container">
                                            <h5>Graduation Details</h5>
                                            <p>Graduation information</p>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="icons-container">
                                            <i class="fa-solid fa-briefcase"></i>
                                        </div>
                                        <div class="content-container">
                                            <h5>Work Experience</h5>
                                            <p>Work Experience Details</p>
                                        </div>
                                    </li>
                                    <!-- <li>
                                        <div class="icons-container">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </div>
                                        <div class="content-container">
                                            <h5>Declaration</h5>
                                            <p>Professional Summary</p>
                                        </div>
                                    </li> -->
                                    <li>
                                        <div class="icons-container">
                                            <i class="fa-solid fa-badge-check"></i>
                                        </div>
                                        <div class="content-container">
                                            <h5>Background Verification</h5>
                                            <p>Background Verification Details</p>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="icons-container">
                                            <i class="fa-solid fa-handshake"></i>
                                        </div>
                                        <div class="content-container">
                                            <h5>Agreement</h5>
                                            <p>Application’s Certification</p>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="icons-container">
                                            <i class="fa-solid fa-shield-check"></i>
                                        </div>
                                        <div class="content-container">
                                            <h5>Finalize</h5>
                                            <p>Additional Details</p>
                                        </div>
                                    </li>

                                </ul>

                            </div>
                        </div>
                        <div class="col-xxl-9 col-xl-9 col-lg-9 col-md-9 col-sm-12 col-12">
                            <div class="main-container">
                                <div class="progress" style="height: 3px;">
                                    <div class="progress-bar" role="progressbar" style="width: 15%;" aria-valuenow="25"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="feildset-container mt-4">
                                    <div class="loader-wrapper">
                                        <div class="loader"></div>
                                    </div>
                                    <!-- <img src="https://www.icegif.com/wp-content/uploads/2023/07/icegif-1263.gif" class="loader" alt="Loader"> -->
                                    <fieldset>
                                        <!-- <form id="form1"> -->
                                        <?php echo form_open('', ['id' => 'form1']) ?>
                                        <div class="form-card">
                                            <div class="progress-container-mobile-view-parent">
                                                <div class="row d-flex align-items-center justify-content-between">
                                                    <div class="col-7">
                                                        <h3 class="fs-title">Personal Details</h3>
                                                    </div>
                                                    <div class="col-5">
                                                        <h2 class="steps">Step 1 - 10</h2>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">First Name *</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-light fa-id-badge"></i></span>
                                                        <input type="text" name="Fname" id="Fname" class="form-control"
                                                            placeholder="First Name" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $firstname ?? '' ?>" required maxlength="100">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="Fname_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Last Name *</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-light fa-id-badge"></i></span>
                                                        <input type="text" name="Lname" id="Lname" class="form-control"
                                                            placeholder="Last Name" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $lastname ?? '' ?>" required maxlength="100">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="Lname_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Date of Joining *</label>
                                                    <div class="input-group flex-nowrap">
                                                        <!-- <span class="input-group-text h-100" id="addon-wrapping"><i class="fa-solid fa-envelope"></i></span> -->
                                                        <input type="date" onkeypress="return false" name="DOJ" id="DOJ"
                                                            class="form-control" placeholder="DOJ" aria-label=""
                                                            aria-describedby="addon-wrapping" value="<?= $doj ?? '' ?>"
                                                            required
                                                            min="<?php echo date('Y-m-d', strtotime('-2 months')); ?>"
                                                            max="<?php echo date('Y-m-d', strtotime('+2 months')); ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="DOJ_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Date of Birth *</label>
                                                    <div class="input-group flex-nowrap">
                                                        <!-- <span class="input-group-text h-100" id="addon-wrapping"><i class="fa-regular fa-user"></i></span> -->
                                                        <input type="date" onkeypress="return false" name="DOB" id="DOB"
                                                            class="form-control" placeholder="Full Name" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $birthday ?? '' ?>" required
                                                            min="<?php echo date('Y-m-d', strtotime('-90 years')); ?>"
                                                            max="<?php echo date('Y-m-d', strtotime('-17 years')); ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="DOB_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for=""> Mobile Number *
                                                    </label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping">
                                                            <i class="fa-regular fa-mobile"></i>
                                                        </span>
                                                        <input type="text" name="mnumber" id="mnumber"
                                                            class="form-control" placeholder="Mob No" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $phonenumber ?? '' ?>" required>
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="mnumber_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Alternate Number
                                                    </label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping">
                                                            <i class="fa-regular fa-mobile"></i>
                                                        </span>
                                                        <input type="text" name="tnumber" id="tnumber"
                                                            class="form-control" placeholder="Alternate No"
                                                            aria-label="" aria-describedby="addon-wrapping"
                                                            value="<?= $contact_2 ?? '' ?>" required>
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="tnumber_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Present Address *
                                                    </label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping">
                                                            <i class="fa-regular fa-map-location-dot"></i>
                                                        </span>
                                                        <input type="text" name="address" id="address"
                                                            class="form-control" maxlength="256" placeholder="Address"
                                                            aria-label="" aria-describedby="addon-wrapping"
                                                            value="<?= $current_address ?? '' ?>" required>
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="present_address_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">City *</label>
                                                    <div class="input-group flex-nowrap">
                                                        <!-- <span class="input-group-text h-100" id="addon-wrapping">
                                                            <i class="fa-regular fa-city"></i>
                                                            </span> -->
                                                        <select class="form-select" name="city" id="city" required>
                                                            <?php
                                                            $indianStates = array(
                                                                'Please Select',
                                                                'Andhra Pradesh',
                                                                'Arunachal Pradesh',
                                                                'Assam',
                                                                'Bihar',
                                                                'Chhattisgarh',
                                                                'Goa',
                                                                'Gujarat',
                                                                'Haryana',
                                                                'Himachal Pradesh',
                                                                'Jharkhand',
                                                                'Karnataka',
                                                                'Kerala',
                                                                'Madhya Pradesh',
                                                                'Maharashtra',
                                                                'Manipur',
                                                                'Meghalaya',
                                                                'Mizoram',
                                                                'Nagaland',
                                                                'Odisha',
                                                                'Punjab',
                                                                'Rajasthan',
                                                                'Sikkim',
                                                                'Tamil Nadu',
                                                                'Telangana',
                                                                'Tripura',
                                                                'Uttar Pradesh',
                                                                'Uttarakhand',
                                                                'West Bengal',
                                                                'Andaman and Nicobar Islands',
                                                                'Chandigarh',
                                                                'Dadra and Nagar Haveli',
                                                                'Daman and Diu',
                                                                'Lakshadweep',
                                                                'Delhi',
                                                                'Puducherry'
                                                            );
                                                            foreach ($indianStates as $state) {
                                                                if ($state == "Please Select") {
                                                                    echo "<option value=\"$state\" $isSelected disabled selected>$state</option>";
                                                                } else {
                                                                    $isSelected = ($state == $city) ? 'selected' : '';
                                                                    echo "<option value=\"$state\" $isSelected>$state</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="city_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Pin *</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping">
                                                            <i class="fa-regular fa-map-pin"></i>
                                                        </span>
                                                        <input type="text" name="pin" id="pin" class="form-control"
                                                            placeholder="Pin" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $pin_code ?? '' ?>" required>
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="pin_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Sex *</label>
                                                    <div class="d-flex gap-3 align-items-center">
                                                        <div class="form-check mt-3">
                                                            <input class="form-check-input mb-0" type="radio"
                                                                value="Male" name="sex" id="sex1" <?php if (isset($sex) && ($sex == 'Male')) {
                                                                    echo 'checked';
                                                                } ?>>
                                                            <label class="form-check-label" for="sex1">
                                                                Male
                                                            </label>
                                                        </div>
                                                        <div class="form-check mt-3">
                                                            <input class="form-check-input mb-0" type="radio"
                                                                value="Female" name="sex" id="sex2" <?php if (isset($sex) && ($sex == 'Female')) {
                                                                    echo 'checked';
                                                                } ?>>
                                                            <label class="form-check-label" for="sex2">
                                                                Female
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="sex_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Marital Status *</label>
                                                    <div class="input-group flexible-timing-input-group">
                                                        <div class="form-check mt-3 me-2">
                                                            <input class="form-check-input" type="radio" value="Single"
                                                                name="martial_status" id="martial_status1" <?php if (isset($marital_status) && ($marital_status == 'Single')) {
                                                                    echo 'checked';
                                                                } ?>>
                                                            <label class="form-check-label" for="martial_status1">
                                                                Single
                                                            </label>
                                                        </div>
                                                        <div class="form-check mt-3 me-2">
                                                            <input class="form-check-input" type="radio" value="Married"
                                                                name="martial_status" id="martial_status2" <?php if (isset($marital_status) && ($marital_status == 'Married')) {
                                                                    echo 'checked';
                                                                } ?>>
                                                            <label class="form-check-label" for="martial_status2">
                                                                Married
                                                            </label>
                                                        </div>
                                                        <div class="form-check mt-3 me-0">
                                                            <input class="form-check-input" type="radio"
                                                                value="Separated" name="martial_status"
                                                                id="martial_status3" <?php if (isset($marital_status) && ($marital_status == 'Separated')) {
                                                                    echo 'checked';
                                                                } ?>>
                                                            <label class="form-check-label" for="martial_status3">
                                                                Separated
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="maritial_status_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Email ID *</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-regular fa-envelope"></i></span>
                                                        <input type="email" name="email" id="email" class="form-control"
                                                            placeholder="Email" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $personal_email ?? '' ?>" required>
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="email_error"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="form1_submit" value="form1_submit">
                                        <input type="button" class="next1 action-button" value="Next" />

                                        </form>
                                    </fieldset>
                                    <fieldset>

                                        <!-- <form id="form2"> -->
                                        <?php echo form_open_multipart('', ['id' => 'form2']) ?>
                                        <div class="form-card">
                                            <div class="progress-container-mobile-view-parent">
                                                <div class="row d-flex align-items-center justify-content-between">
                                                    <div class="col-7">
                                                        <h3 class="fs-title">Official Details</h3>
                                                    </div>
                                                    <div class="col-5">
                                                        <h2 class="steps">Step 2 - 10</h2>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <!-- <div class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                                    <label for="">Email ID</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i class="fa-regular fa-envelope"></i></span>
                                                        <input type="email" name="email" id="email" class="form-control" placeholder="Email" aria-label="" aria-describedby="addon-wrapping" required>
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="email_error"></p>
                                                    </div>
                                                </div> -->
                                                <div
                                                    class="form-group-main col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                                    <label for="">Permanent Address *</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping">
                                                            <i class="fa-regular fa-location-dot"></i>
                                                        </span>
                                                        <input type="text" name="permanent_address"
                                                            id="permanent_address" class="form-control"
                                                            placeholder="Address" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $permanent_address ?? '' ?>">
                                                    </div>

                                                    <div class="input-group-error">
                                                        <p id="permanentAddress_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Bank A/C Number *</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-regular fa-file-invoice"></i></span>
                                                        <input type="number" name="ac_number" id="ac_number"
                                                            class="form-control" placeholder="A/C No" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $bank_ac_no ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="bankAccount_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Bank Name *</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-regular fa-building-columns"></i></span>
                                                        <input type="text" name="bank_name" id="bank_name"
                                                            class="form-control" placeholder="Bank Name" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $bank_name ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="bankName_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Bank Address *</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-regular fa-building-columns"></i></span>
                                                        <input type="text" name="bank_address" id="bank_address"
                                                            class="form-control" placeholder="Bank Address"
                                                            aria-label="" aria-describedby="addon-wrapping"
                                                            value="<?= $bank_address ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="bankAddress_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">IFSC Code *</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-regular fa-magnifying-glass"></i></span>
                                                        <input type="text" name="IFSC" id="IFSC" class="form-control"
                                                            placeholder="IFSC Code" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $isfc_code ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="IFSC_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">PAN Number *</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-regular fa-file-shield"></i></span>
                                                        <input type="text" name="pan_number" id="pan_number"
                                                            class="form-control" placeholder="PAN No" aria-label=""
                                                            aria-describedby="addon-wrapping" value="<?= $pan ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="pan_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Blood Group*
                                                    </label>
                                                    <div class="input-group flex-nowrap">
                                                        <!-- <span class="input-group-text h-100" id="addon-wrapping"><i class="fa-solid fa-eye-dropper"></i></span> -->
                                                        <div class="input-group flex-nowrap">
                                                            <select class="form-select" name="blood" id="blood">
                                                                <?php
                                                                $bloodGroups = array(
                                                                    'Please select',
                                                                    'A+',
                                                                    'A-',
                                                                    'B+',
                                                                    'B-',
                                                                    'AB+',
                                                                    'AB-',
                                                                    'O+',
                                                                    'O-'
                                                                );
                                                                foreach ($bloodGroups as $blood) {
                                                                    if ($blood == "Please select") {
                                                                        echo "<option value=\"$blood\" $isSelected disabled selected>$blood</option>";
                                                                    } else {
                                                                        $isSelected = ($blood == $blood_group) ? 'selected' : '';
                                                                        echo "<option value=\"$blood\" $isSelected>$blood</option>";
                                                                    }
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="bloodGroup_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Emergency Contact Number *</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-regular fa-phone-plus"></i></span>
                                                        <input type="text" name="emergency_number" id="emergency_number"
                                                            class="form-control" placeholder="EC No" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $e_contact ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="emergency_number_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Emergency Contact Name *</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-regular fa-user-plus"></i></span>
                                                        <input type="text" name="emergency_name" id="emergency_name"
                                                            class="form-control" placeholder="EC Name" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $e_person ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="emergency_name_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Relation *</label>
                                                    <div class="input-group flex-nowrap">
                                                        <!-- <span class="input-group-text h-100" id="addon-wrapping"><i class="fa-regular fa-user-plus"></i></span> -->
                                                        <div class="input-group flex-nowrap">
                                                            <select class="form-select" name="relation" id="relation">
                                                                <?php
                                                                $all_options = array(
                                                                    'Please select',
                                                                    'Father',
                                                                    'Mother',
                                                                    'Sister',
                                                                    'Brother',
                                                                    'Husband',
                                                                    'Wife',
                                                                    'Guardian'
                                                                );
                                                                foreach ($all_options as $option) {
                                                                    if ($option == "Please select") {
                                                                        echo "<option value=\"$option\" $isSelected disabled selected>$option</option>";
                                                                    } else {
                                                                        $isSelected = ($option == $relation) ? 'selected' : '';
                                                                        echo "<option value=\"$option\" $isSelected>$option</option>";
                                                                    }
                                                                }
                                                                ?>

                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="relation_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">How were you referred to us? *</label>
                                                    <div class="input-group">
                                                        <select class="form-select" name="referr_type" id="reference">
                                                            <?php
                                                            $all_options = array(
                                                                'Please select',
                                                                'Walk-In',
                                                                'Referral',
                                                                'Campus Placement',
                                                                'Job Search Site',
                                                                'Social Media',
                                                                'Career Page',
                                                                'Other'
                                                            );
                                                            foreach ($all_options as $option) {
                                                                if ($option == "Please select") {
                                                                    echo "<option value=\"$option\" $isSelected disabled selected>$option</option>";
                                                                } else {
                                                                    $isSelected = ($option == $referr_type) ? 'selected' : '';
                                                                    echo "<option value=\"$option\" $isSelected>$option</option>";
                                                                }
                                                            }
                                                            ?>

                                                        </select>
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="reference_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Upload Aadhar *</label>
                                                    <div class="input-groups flex-nowrap mt-3 upload-file-input"
                                                        style="background-color: #e9ecef;">
                                                        <?php if (isset($identification)) { ?>
                                                            <p>Uploaded File: <a
                                                                    href="<?php echo base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $identification; ?>"
                                                                    target="_blank"><?php echo $identification; ?></a></p>
                                                        <?php } ?>
                                                        <input class="form-control p-2" type="file" name="aadhar"
                                                            id="aadhar">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="aadhar_error"></p>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        <input type="button" class="next2 action-button" value="Next" />
                                        <input type="button" class="next2 action-button-skip" value="Draft" />
                                        <input type="button" name="previous" class="previous action-button-previous"
                                            value="Previous" />
                                        </form>
                                    </fieldset>
                                    <fieldset>
                                        <!-- <form id="form3"> -->
                                        <?php echo form_open('', ['id' => 'form3']) ?>
                                        <div class="form-card">
                                            <div class="progress-container-mobile-view-parent">
                                                <div class="row d-flex align-items-center justify-content-between">
                                                    <div class="col-7">
                                                        <h3 class="fs-title">Family Member Details</h3>
                                                    </div>
                                                    <div class="col-5">
                                                        <h2 class="steps">Step 3 - 10</h2>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-4" style="overflow: auto;">
                                                <table class="table table-bordered family-varification-table">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">Relation</th>
                                                            <th scope="col">Full Name</th>
                                                            <th scope="col">Date of Birth</th>
                                                            <th scope="col">Residing with self</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <th scope="row">Father <span class="red-tri">*</span></th>
                                                            <td><input type="text" name="father_name"
                                                                    placeholder="Father's Name" id="father_name"
                                                                    value="<?= $father_name ?? '' ?>">
                                                                <div class="input-group-error">
                                                                    <p id="father_name_error"></p>
                                                                </div>
                                                            </td>
                                                            <td><input type="date" onkeypress="return false"
                                                                    name="father_DOB" id="father_DOB"
                                                                    value="<?= $father_DOB ?? '' ?>"
                                                                    max="<?php echo date('Y-m-d', strtotime('-20 years')); ?>">
                                                            </td>
                                                            <td>
                                                                <select name="father_residing" id="father_residing">
                                                                    <option value="" disabled selected>Please select
                                                                    </option>
                                                                    <option value="Yes" <?= isset($father_residing) && $father_residing == 'Yes' ? 'selected' : '' ?>>Yes
                                                                    </option>
                                                                    <option value="No" <?= isset($father_residing) && $father_residing == 'No' ? 'selected' : '' ?>>No
                                                                    </option>
                                                                </select>
                                                                <div class="input-group-error">
                                                                    <p id="father_residing_error"></p>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Mother<span class="red-tri"> *</span></th>
                                                            <td><input type="text" name="mother_name" id="mother_name"
                                                                    value="<?= $mother_name ?? '' ?>"
                                                                    placeholder="Mother's Name">
                                                                <div class="input-group-error">
                                                                    <p id="mother_name_error"></p>
                                                                </div>
                                                            </td>
                                                            <td><input type="date" onkeypress="return false"
                                                                    name="mother_DOB" id="mother_DOB"
                                                                    value="<?= $mother_DOB ?? '' ?>"
                                                                    max="<?php echo date('Y-m-d', strtotime('-20 years')); ?>">
                                                            </td>
                                                            <td>
                                                                <select name="mother_residing" id="mother_residing">
                                                                    <option value="" disabled selected>Please select
                                                                    </option>
                                                                    <option value="Yes" <?= isset($mother_residing) && $mother_residing == 'Yes' ? 'selected' : '' ?>>Yes
                                                                    </option>
                                                                    <option value="No" <?= isset($mother_residing) && $mother_residing == 'No' ? 'selected' : '' ?>>No
                                                                    </option>
                                                                </select>
                                                                <div class="input-group-error">
                                                                    <p id="mother_residing_error"></p>
                                                                </div>
                                                            </td>

                                                        </tr>

                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="row mt-4">
                                                <div
                                                    class="form-group-main col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                                    <!-- <label for="">Add Family Member</label> -->
                                                    <div class="input-group flex-nowrap">

                                                        <div class="input-group flex-nowrap">
                                                            <button type="button" class="form-select-family bg-danger text-light">Add Family
                                                                Member</button>

                                                        </div>
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="relation_error"></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mt-2 family-varification-table-row" style="overflow: auto;">
                                                <table
                                                    class="table table-bordered family-varification-table family-varification-table-two">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">Relation</th>
                                                            <th scope="col">Full Name</th>
                                                            <th scope="col">Date of Birth</th>
                                                            <th scope="col">Residing with self</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                            
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="row mt-4">
                                                <div class="col-md-6">
                                                    <label for="">Name of the person who will be nominee of
                                                        above *</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-regular fa-user"></i></span>
                                                        <input type="text" name="nominee" id="nominee"
                                                            class="form-control" placeholder="Nominee Person Name"
                                                            aria-label="" aria-describedby="addon-wrapping"
                                                            value="<?= $nominee ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="nominee_error"></p>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                        <input type="button" class="next3 action-button" value="Next" />
                                        <input type="button" class="next3 action-button-skip" value="Draft" />
                                        <input type="button" name="previous" class="previous action-button-previous"
                                            value="Previous" />
                                        </form>
                                    </fieldset>
                                    <fieldset>
                                        <?php echo form_open('', ['id' => 'form4', 'enctype' => 'multipart/form-data']) ?>

                                        <div class="form-card">
                                            <div class="progress-container-mobile-view-parent">
                                                <div class="row d-flex align-items-center justify-content-between">
                                                    <div class="col-7">
                                                        <h3 class="fs-title">Qualification</h3>
                                                    </div>
                                                    <div class="col-5">
                                                        <h2 class="steps">Step 4 - 10</h2>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">10th Board/Degree *</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-solid fa-school"></i></span>

                                                        <input type="text" name="_10_board" id="10_board"
                                                            class="form-control" placeholder="Board Name" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $_10_board ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="10_board_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">School Name *</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-solid fa-school"></i></span>
                                                        <input type="text" name="_10_school_name" id="10_school_name"
                                                            class="form-control" placeholder="School Name" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $_10_school_name ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="10_school_name_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Passed Year *</label>
                                                    <div class="input-group">
                                                        <select class="form-select" name="_10_passed_year"
                                                            id="10_passed_year">
                                                            <option disabled selected>--Please Select Year--</option>
                                                            <?php
                                                            $date = date('Y');
                                                            for ($i = $date; $i >= $date - 35; $i--) {
                                                                $isSelected = ($i == $_10_passed_year) ? 'selected' : '';
                                                                ?>
                                                                <option <?= $isSelected ?> value="<?php echo $i; ?>">
                                                                    <?php echo $i; ?>
                                                                </option>
                                                                <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="10_passed_year_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">10th Percentage</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-solid fa-percent"></i></span>
                                                        <input type="number" name="_10_percentage" id="10_percentage"
                                                            class="form-control" placeholder="Enter percentage"
                                                            aria-label="" aria-describedby="addon-wrapping"
                                                            value="<?= $_10_percentage ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="10_percentage_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Grade</label>
                                                    <div class="input-group flex-nowrap">
                                                        <select class="form-select" name="_10_grade" id="10_grade">
                                                            <?php
                                                            $all_options = array(
                                                                'Please select',
                                                                'A1',
                                                                'A2',
                                                                'B1',
                                                                'B2',
                                                                'C1',
                                                                'C2',
                                                                'D1',
                                                                'D2',
                                                                'E'
                                                            );
                                                            foreach ($all_options as $option) {
                                                                $isSelected = ($option == $_10_grade) ? 'selected' : '';
                                                                echo "<option value=\"$option\" $isSelected>$option</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="10_grade_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Field of Study *</label>
                                                    <div class="input-group">
                                                        <select class="form-select" name="_10_field" id="10_field">
                                                            <?php
                                                            $all_options = array(
                                                                'Please select',
                                                                'Science',
                                                                'Commerce',
                                                                'Arts',
                                                                'Vocational Courses',
                                                                'Language Studies',
                                                                'Other'
                                                            );
                                                            foreach ($all_options as $option) {
                                                                $isSelected = ($option == $_10_field) ? 'selected' : '';
                                                                echo "<option value=\"$option\" $isSelected>$option</option>";
                                                            }
                                                            ?>

                                                        </select>
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="10_field_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Upload 10th Marksheet *</label>
                                                    <div class="input-groups flex-nowrap mt-3 upload-file-input"
                                                        style="background-color: #e9ecef;">
                                                        <?php if (isset($_10_marksheet)) { ?>
                                                            <p>Uploaded File: <a
                                                                    href="<?php echo base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $_10_marksheet; ?>"
                                                                    target="_blank"><?php echo $_10_marksheet; ?></a></p>
                                                        <?php } ?>
                                                        <input class="form-control p-2" type="file" id="10_marksheet"
                                                            name="_10_marksheet" value="<?= $_10_marksheet ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="10_marksheet_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12"></div>
                                                <div
                                                class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12"></div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">12th Board/Degree *</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-solid fa-school"></i></span>
                                                        <input type="text" name="_12_board" id="12_board"
                                                            class="form-control" placeholder="Board Name" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $_12_board ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="12_board_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Institute/University*
                                                    </label>
                                                    <div class="input-group flex-nowrap">
                                                        <input type="text" class="form-control" name="_12_institute"
                                                            id="12_institute" placeholder="School Name"
                                                            value="<?= $_12_institute ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="12_institute_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Passed Year *</label>
                                                    <div class="input-group">
                                                        <select class="form-select" name="_12_passed_year"
                                                            id="12_passed_year">
                                                            <option disabled selected>--Please Select Year--</option>
                                                            <?php
                                                            $date = date('Y');
                                                            for ($i = $date; $i >= $date - 35; $i--) {
                                                                $isSelected = ($i == $_12_passed_year) ? 'selected' : '';
                                                                ?>
                                                                <option <?= $isSelected ?> value="<?php echo $i; ?>">
                                                                    <?php echo $i; ?>
                                                                </option>
                                                                <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="12_passed_year_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">12th Percentage</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-solid fa-percent"></i></span>
                                                        <input type="number" name="_12_percentage" id="12_percentage"
                                                            class="form-control" placeholder="Enter percentage"
                                                            aria-label="" aria-describedby="addon-wrapping"
                                                            value="<?= $_12_percentage ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="12_percentage_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Grade</label>
                                                    <div class="input-group flex-nowrap">

                                                        <select class="form-select" name="_12_grade" id="12_grade">
                                                            <?php
                                                            $all_options = array(
                                                                'Please select',
                                                                'A1',
                                                                'A2',
                                                                'B1',
                                                                'B2',
                                                                'C1',
                                                                'C2',
                                                                'D1',
                                                                'D2',
                                                                'E'
                                                            );
                                                            foreach ($all_options as $option) {
                                                                $isSelected = ($option == $_12_grade) ? 'selected' : '';
                                                                echo "<option value=\"$option\" $isSelected>$option</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="12_grade_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Field of Study *</label>
                                                    <div class="input-group">
                                                        <select class="form-select" name="_12_field" id="12_field">
                                                            <?php
                                                            $all_options = array(
                                                                'Please select',
                                                                'Science',
                                                                'Commerce',
                                                                'Arts',
                                                                'Vocational Courses',
                                                                'Language Studies',
                                                                'Other'
                                                            );
                                                            foreach ($all_options as $option) {
                                                                $isSelected = ($option == $_12_field) ? 'selected' : '';
                                                                echo "<option value=\"$option\" $isSelected>$option</option>";
                                                            }
                                                            ?>

                                                        </select>
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="12_field_error"></p>
                                                    </div>
                                                </div>
                                              
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                                    <label for="">Upload 12th Marksheet *</label>
                                                    <div class="input-groups flex-nowrap mt-3 upload-file-input"
                                                        style="background-color: #e9ecef;">
                                                        <?php if (isset($_12_marksheet)) { ?>
                                                            <p>Uploaded File: <a
                                                                    href="<?php echo base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $_12_marksheet; ?>"
                                                                    target="_blank"><?php echo $_12_marksheet; ?></a></p>
                                                        <?php } ?>
                                                        <input class="form-control p-2" type="file" id="12_marksheet"
                                                            name="_12_marksheet" value="<?= $_12_marksheet ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="12_marksheet_error"></p>
                                                    </div>
                                                </div>
                                                <p class="text-danger">Uploaded file must be pdf, jpg, png, word, or doc
                                                    and less than 1mb</p>
                                            </div>
                                        </div>

                                        <input type="button" class="next4 action-button" value="Next" />
                                        <input type="button" class="next4 action-button-skip" value="Draft" />
                                        <input type="button" name="previous" class="previous action-button-previous"
                                            value="Previous" />
                                        </form>
                                    </fieldset>
                                    <fieldset>
                                        <?php echo form_open('', ['id' => 'form5', 'enctype' => 'multipart/form-data']) ?>

                                        <div class="form-card">
                                            <div class="progress-container-mobile-view-parent">
                                                <div class="row d-flex align-items-center justify-content-between">
                                                    <div class="col-7">
                                                        <h3 class="fs-title">Graduation Details</h3>
                                                    </div>
                                                    <div class="col-5">
                                                        <h2 class="steps">Step 5 - 10</h2>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-sm-12 col-12">
                                                    <label for="">Graduation Institute/University *</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-solid fa-school"></i></span>
                                                        <input type="text" name="graduation_university_name"
                                                            id="graduation_university_name" class="form-control"
                                                            placeholder="University Name" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $graduation_university_name ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="graduation_university_name_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-sm-12 col-12">

                                                    <label for="">Passed Year *</label>
                                                    <div class="input-group">
                                                        <select class="form-select" name="graduation_passed_year"
                                                            id="graduation_passed_year"
                                                            value="<?= $graduation_passed_year ?? '' ?>">
                                                            <option selected disabled>--Please Select Year--</option>
                                                            <?php
                                                            $date = date('Y', strtotime('+3 years'));
                                                            for ($i = $date; $i > $date - 39; $i--) {
                                                                $isSelected = ($i == $graduation_passed_year) ? 'selected' : '';
                                                                ?>
                                                                <option <?= $isSelected ?> value="<?php echo $i; ?>">
                                                                    <?php echo $i; ?>
                                                                </option>
                                                                <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="graduation_passed_year_error"></p>
                                                    </div>
                                                </div>

                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-sm-12 col-12">
                                                    <label for="">Upload Graduation Marksheet </label>
                                                    <div class="input-groups flex-nowrap mt-3 upload-file-input"
                                                        style="background-color: #e9ecef;">
                                                        <?php if (isset($graduation_marksheet)) { ?>
                                                            <p>Uploaded File: <a
                                                                    href="<?php echo base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $graduation_marksheet; ?>"
                                                                    target="_blank"><?php echo $graduation_marksheet; ?></a>
                                                            </p>
                                                        <?php } ?>
                                                        <input class="form-control p-2" type="file"
                                                            id="graduation_marksheet" name="graduation_marksheet"
                                                            value="<?= $graduation_marksheet ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="graduation_marksheet_error"></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-sm-12 col-12">
                                                    <label for="">Post Graduation Institute/University</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-solid fa-school"></i></span>
                                                        <input type="text" name="post_graduation_university_name"
                                                            id="post_graduation_university_name" class="form-control"
                                                            placeholder="University Name" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $post_graduation_university_name ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="post_graduation_university_name_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-sm-12 col-12">

                                                    <label for="">Passed Year</label>
                                                    <div class="input-group">
                                                        <select class="form-select" name="post_graduation_passed_year"
                                                            id="post_graduation_passed_year">
                                                            <option selected disabled>--Please Select Year--</option>
                                                            <?php
                                                            $date = date('Y', strtotime('+3 years'));
                                                            for ($i = $date; $i > $date - 39; $i--) {
                                                                $isSelected = ($i == $post_graduation_passed_year) ? 'selected' : '';

                                                                ?>
                                                                <option <?= $isSelected ?> value="<?php echo $i; ?>">
                                                                    <?php echo $i; ?>
                                                                </option>
                                                                <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="post_graduation_passed_year_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-sm-12 col-12">
                                                    <label for="">Upload Post Graduation Marksheet</label>
                                                    <div class="input-groups flex-nowrap mt-3 upload-file-input"
                                                        style="background-color: #e9ecef;">
                                                        <?php if (isset($post_graduation_marksheet)) { ?>
                                                            <p>Uploaded File: <a
                                                                    href="<?php echo base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $post_graduation_marksheet; ?>"
                                                                    target="_blank"><?php echo $post_graduation_marksheet; ?></a>
                                                            </p>
                                                        <?php } ?>
                                                        <input class="form-control p-2" type="file"
                                                            id="post_graduation_marksheet"
                                                            name="post_graduation_marksheet"
                                                            value="<?= $post_graduation_marksheet ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="post_graduation_marksheet_error"></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <p class="text-danger">Upload file must be pdf, jpg, png, word, or doc
                                                    and less than 1mb</p>
                                            </div>
                                        </div>

                                        <!-- <input type="button" class="next action-button-skip" value="Skip" /> -->
                                        <input type="button" class="next5 action-button" value="Next" />
                                        <input type="button" class="next5 action-button-skip" value="Draft" />
                                        <input type="button" name="previous" class="previous action-button-previous"
                                            value="Previous" />
                                        <!-- <p class="text-secondary">Note : Skip if you are not a Graduate</p> -->
                                        </form>
                                    </fieldset>
                                    <fieldset>
                                        <?php echo form_open('', ['id' => 'form6']) ?>

                                        <div class="form-card">
                                            <div class="progress-container-mobile-view-parent">
                                                <div class="row d-flex align-items-center justify-content-between">
                                                    <div class="col-7">
                                                        <h3 class="fs-title">Work Experience</h3>
                                                    </div>
                                                    <div class="col-5">
                                                        <h2 class="steps">Step 6 - 10</h2>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <p class="pb-2 fw-bold">Last Organization Details</p>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                                    <label for="">Designation</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-solid fa-user"></i></span>
                                                        <input type="text" name="previous_designation"
                                                            id="previous_designation" class="form-control"
                                                            placeholder="Designation Name" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $previous_designation ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="previous_designation_error"></p>
                                                    </div>

                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">

                                                    <label for="">Select Year</label>
                                                    <div class="input-group">
                                                        <select class="form-select" name="previous_year"
                                                            id="previous_year">
                                                            <option selected="<?= $previous_year ?? '' ?>">--Please
                                                                Select Year--</option>
                                                            <?php
                                                            $date = date('Y');
                                                            for ($i = $date; $i > $date - 60; $i--) {
                                                                $isSelected = ($i == $previous_year) ? 'selected' : '';

                                                                ?>
                                                                <option <?= $isSelected ?> value="<?php echo $i; ?>">
                                                                    <?php echo $i; ?>
                                                                </option>
                                                                <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-2 col-xl-2 col-lg-2 col-md-2 col-sm-12 col-12">
                                                    <label for="">Emp ID</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-light fa-id-badge"></i></span>
                                                        <input type="text" name="previous_emp_id" id="previous_emp_id"
                                                            class="form-control" placeholder="Emp ID" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $previous_emp_id ?? '' ?>">
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-2 col-xl-2 col-lg-2 col-md-2 col-sm-12 col-12">
                                                    <label for="">Net Pay</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-regular fa-hand-holding-circle-dollar"></i></span>
                                                        <input type="text" name="previous_pay" id="previous_pay"
                                                            class="form-control" placeholder="Net Pay" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $previous_pay ?? '' ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                                    <label for="">Reporting Person Name</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-solid fa-user"></i></span>
                                                        <input type="text" name="previous_person_name"
                                                            id="previous_person_name" class="form-control"
                                                            placeholder="Designation Name" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $previous_person_name ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="previous_person_name_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">

                                                    <label for="">Reporting Person Designation</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control"
                                                            name="previous_person_designation"
                                                            id="previous_person_designation" placeholder="Designation"
                                                            value="<?= $previous_person_designation ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="previous_person_designation_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                                    <label for="">Reporting Person Contact</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-solid fa-phone"></i></span>
                                                        <input type="text" name="previous_person_contact"
                                                            id="previous_person_contact" class="form-control"
                                                            placeholder="Number" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $previous_person_contact ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="previous_person_contact_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                                    <label for="">Last Organization Upload Offer Letter, Salary
                                                        Slips</label>
                                                    <div class="input-groups flex-nowrap mt-3 upload-file-input"
                                                        style="background-color: #e9ecef;">
                                                        <?php if (isset($previous_uploads)) { ?>
                                                            <p>Uploaded File: <a
                                                                    href="<?php echo base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $previous_uploads; ?>"
                                                                    target="_blank"><?php echo $previous_uploads; ?></a>
                                                            </p>
                                                        <?php } ?>
                                                        <input class="form-control p-2" type="file"
                                                            id="previous_uploads" name="previous_uploads" multiple
                                                            value="<?= $previous_uploads ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="previous_uploads_error"></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <p class="pb-2 fw-bold">Second Last Organization Details</p>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                                    <label for="">Designation</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-solid fa-user"></i></span>
                                                        <input type="text" name="before_designation"
                                                            id="before_designation" class="form-control"
                                                            placeholder="Designation Name" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $before_designation ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="before_designation_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">

                                                    <label for="">Select Year</label>
                                                    <div class="input-group">
                                                        <select class="form-select" id="before_year" name="before_year">
                                                            <option>--Please Select Year--</option>
                                                            <?php
                                                            $date = date('Y');
                                                            for ($i = $date; $i > $date - 60; $i--) {
                                                                $isSelected = ($i == $previous_year) ? 'selected' : '';
                                                                ?>
                                                                <option <?= $isSelected ?> value="<?php echo $i; ?>">
                                                                    <?php echo $i; ?>
                                                                </option>
                                                                <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="before_year_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-2 col-xl-2 col-lg-2 col-md-2 col-sm-12 col-12">
                                                    <label for="">Emp ID</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-light fa-id-badge"></i></span>
                                                        <input type="text" name="before_emp_id" id="before_emp_id"
                                                            class="form-control" placeholder="Emp ID" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $before_emp_id ?? '' ?>">
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-2 col-xl-2 col-lg-2 col-md-2 col-sm-12 col-12">
                                                    <label for="">Net Pay</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-regular fa-hand-holding-circle-dollar"></i></span>
                                                        <input type="text" name="before_pay" id="before_pay"
                                                            class="form-control" placeholder="Net Pay" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $before_pay ?? '' ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                                    <label for="">Reporting Person Name</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-solid fa-user"></i></span>
                                                        <input type="text" name="before_reporting_person"
                                                            id="before_reporting_person" class="form-control"
                                                            placeholder="Designation Name" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $before_reporting_person ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="before_reporting_person_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">

                                                    <label for="">Reporting Person Designation</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control"
                                                            name="before_reporting_designation"
                                                            id="before_reporting_designation" placeholder="Designation"
                                                            value="<?= $before_reporting_designation ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="before_reporting_designation_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                                    <label for="">Reporting Person Contact</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-solid fa-phone"></i></span>
                                                        <input type="text" class="form-control"
                                                            name="before_reporting_contact"
                                                            id="before_reporting_contact" placeholder="Number"
                                                            aria-label="" aria-describedby="addon-wrapping"
                                                            value="<?= $before_reporting_contact ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="before_reporting_contact_error"></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                                    <label for="">Second Last Organization Offer Letter, Salary
                                                        Slips</label>
                                                    <div class="input-groups flex-nowrap mt-3 upload-file-input"
                                                        style="background-color: #e9ecef;">
                                                        <?php if (isset($before_uploads)) { ?>
                                                            <p>Uploaded File: <a
                                                                    href="<?php echo base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $before_uploads; ?>"
                                                                    target="_blank"><?php echo $before_uploads; ?></a></p>
                                                        <?php } ?>
                                                        <input class="form-control p-2" type="file" id="before_uploads"
                                                            name="before_uploads" multiple
                                                            value="<?= $before_uploads ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="before_uploads_error"></p>
                                                    </div>
                                                </div>
                                                <p class="text-danger">Upload file must be pdf, jpg, png, word, or doc
                                                    and less than 1mb</p>
                                            </div>
                                        </div>
                                        <input type="button" class="next6 action-button" value="Next" />
                                        <input type="button" class="next6 action-button-skip" value="Draft" />
                                        <input type="button" name="previous" class="previous action-button-previous"
                                            value="Previous" />
                                        </form>
                                    </fieldset>
                                    <!-- <fieldset>
                                        <?php echo form_open('', ['id' => 'form7', 'enctype' => 'multipart/form-data']) ?>
                                        <div class="form-card">
                                            <div class="progress-container-mobile-view-parent">
                                                <div class="row d-flex align-items-center justify-content-between">
                                                    <div class="col-7">
                                                        <h3 class="fs-title">Declaration</h3>
                                                    </div>
                                                    <div class="col-5">
                                                        <h2 class="steps">Step 7 - 10</h2>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">

                                                <div class="form-floating form-group-main">

                                                    <div class="checkbox-container">
                                                        <div class="form-check ">
                                                            <input class="form-check-input" type="checkbox" value="ok"
                                                                id="declaration" name="declaration" <?php
                                                                if (isset($declaration) && $declaration == 'ok')
                                                                    echo 'checked';
                                                                ?>>
                                                            <label class="form-check-label" for="flexCheckChecked">
                                                                I declare that the information given in the personal
                                                                data form and the accompanying certification is correct
                                                                and complete to the best of my knowledge and belief. I
                                                                also understand that at any stage, I may be asked to
                                                                provide adequate justification of the facts stated
                                                                above, and I would do so when called for. I accept the
                                                                job and position given to me in all respects and will
                                                                strive to work towards the company's core values and
                                                                help to achieve its vision and mission.
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="input-group-error">
                                                        <p id="declaration_error"></p>
                                                    </div>


                                                </div>
                                            </div>
                                            <div class="row mt-4 justify-content-between">
                                                <div
                                                    class="form-group-main col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                                    <label for="">Date *</label>
                                                    <div class="input-group flex-nowrap">

                                                        <input type="date" onkeypress="return false"
                                                            name="declaration_date" id="declaration_date"
                                                            class="form-control" placeholder="" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $declaration_date ?? '' ?>"
                                                            min="<?php echo date('Y-m-d'); ?>"
                                                            max="<?php echo date('Y-m-d'); ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="declaration_date_error"></p>
                                                    </div>

                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                                    <label for="">Place *</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-regular fa-location-dot"></i></span>
                                                        <input type="text" name="declaration_place"
                                                            id="declaration_place" class="form-control" placeholder=""
                                                            aria-label="" aria-describedby="addon-wrapping"
                                                            value="<?= $declaration_place ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="declaration_place_error"></p>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="row mt-2">
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                                    <label for="">Upload Signature *</label>
                                                    <div class="input-groups flex-nowrap mt-3 upload-file-input"
                                                        style="background-color: #e9ecef;">
                                                        <?php if (isset($declaration_signature)) { ?>
                                                            <p>Uploaded File: <a
                                                                    href="<?php echo base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $declaration_signature; ?>"
                                                                    target="_blank"><?php echo $declaration_signature; ?></a>
                                                            </p>
                                                        <?php } ?>
                                                        <input class="form-control p-2" type="file"
                                                            id="declaration_signature" name="declaration_signature">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="declaration_signature_error"></p>
                                                    </div>

                                                </div>
                                                <p class="text-danger">Upload file must be jpg, png, or Jpeg and less
                                                    than 1mb</p>
                                            </div>
                                        </div>

                                        <input type="button" class="next7 action-button" value="Next" />
                                        <input type="button" class="next7 action-button-skip" value="Draft" />
                                        <input type="button" name="previous" class="previous action-button-previous"
                                            value="Previous" />
                                        </form>
                                    </fieldset> -->
                                    <fieldset>
                                        <?php echo form_open('', ['id' => 'form8']) ?>
                                        <div class="form-card">
                                            <div class="progress-container-mobile-view-parent">
                                                <div class="row d-flex align-items-center justify-content-between">
                                                    <div class="col-7">
                                                        <h3 class="fs-title">Background Verification</h3>
                                                    </div>
                                                    <div class="col-5">
                                                        <h2 class="steps">Step 8 - 10</h2>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-4">

                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                                    <label for="">Emp Name</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-solid fa-user"></i></span>
                                                        <input type="text" name="bgv_emp_name" id="emp_name"
                                                            class="form-control" placeholder="Enter Emp Name"
                                                            aria-label="" aria-describedby="addon-wrapping"
                                                            value="<?= $bgv_emp_name ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="emp_name_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">

                                                    <label for="">Select Your Postion</label>
                                                    <div class="input-group">
                                                        <select class="form-select" name="bgv_emp_position"
                                                            id="emp_position">
                                                            <?php
                                                            $all_options = array(
                                                                'Please select',
                                                                'Team Leader',
                                                                'Manager',
                                                                'CEO',
                                                                'VP',
                                                                'Project Manager',
                                                                'Other'
                                                            );
                                                            foreach ($all_options as $option) {
                                                                $isSelected = ($option == $bgv_emp_position) ? 'selected' : '';
                                                                echo "<option value=\"$option\" $isSelected>$option</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-2 col-xl-2 col-lg-2 col-md-2 col-sm-12 col-12">
                                                    <label for="">Emp Code</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-light fa-id-badge"></i></span>
                                                        <input type="text" name="bgv_emp_code" id="emp_code"
                                                            class="form-control" placeholder="Emp Code" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $bgv_emp_code ?? '' ?>">
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-2 col-xl-2 col-lg-2 col-md-2 col-sm-12 col-12 px-0">
                                                    <label for="">Last working day</label>
                                                    <div class="input-group flex-nowrap">
                                                        <!-- <span class="input-group-text h-100" id="addon-wrapping"><i class="fa-regular fa-hand-holding-circle-dollar"></i></span> -->
                                                        <input type="date" onkeypress="return false" name="bgv_emp_date"
                                                            id="emp_date" class="form-control" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $bgv_emp_date ?? '' ?>"
                                                            max="<?php echo date('Y-m-d', strtotime('-1 day')); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-4">

                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                                    <label for="">Contact Number</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-solid fa-phone"></i></span>
                                                        <input type="number" name="bgv_emp_number" id="emp_number"
                                                            class="form-control" placeholder="Contact Number"
                                                            aria-label="" aria-describedby="addon-wrapping"
                                                            value="<?= $bgv_emp_number ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="emp_number_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">

                                                    <label for="">Address</label>
                                                    <div class="input-group">

                                                        <input type="text" class="form-control" name="bgv_emp_address"
                                                            id="emp_address" placeholder="Address"
                                                            value="<?= $bgv_emp_address ?? '' ?>">
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                                    <label for="">Department</label>
                                                    <div class="input-group flex-nowrap">
                                                        <!-- <span class="input-group-text h-100" id="addon-wrapping"></span> -->
                                                        <input type="text" name="bgv_emp_department" id="emp_department"
                                                            class="form-control" placeholder="Previous Department"
                                                            aria-label="" aria-describedby="addon-wrapping"
                                                            value="<?= $bgv_emp_department ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="emp_department_error"></p>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="row mt-4">

                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                                    <label for="">Select Year</label>
                                                    <div class="input-group">
                                                        <select class="form-select" name="bgv_emp_year" id="emp_year">
                                                            <option>--Please Select Year--</option>
                                                            <?php
                                                            $date = date('Y');
                                                            for ($i = $date; $i > $date - 60; $i--) {
                                                                $isSelected = ($i == $bgv_emp_year) ? 'selected' : '';
                                                                ?>
                                                                <option <?= $isSelected ?> value="<?php echo $i; ?>">
                                                                    <?php echo $i; ?>
                                                                </option>
                                                                <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">

                                                    <label for="">In Hand Salary</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control"
                                                            name="bgv_emp_hand_salary" id="emp_hand_salary"
                                                            placeholder="In Hand Salary"
                                                            value="<?= $bgv_emp_hand_salary ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="emp_hand_salary_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                                    <label for="">Gross Salary</label>
                                                    <div class="input-group flex-nowrap">
                                                        <!-- <span class="input-group-text h-100" id="addon-wrapping"></span> -->
                                                        <input type="text" name="bgv_emp_gross_salary"
                                                            id="emp_gross_salary" class="form-control"
                                                            placeholder="Gross Salary" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $bgv_emp_gross_salary ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="emp_gross_salary_error"></p>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="row mt-4">

                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                                    <label for="">Location</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" name="bgv_emp_location"
                                                            id="emp_location" value="<?= $bgv_emp_location ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="emp_location_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                                    <label for="">Previous Company Documents </label>
                                                    <div class="d-flex gap-3 align-items-center">
                                                        <div class="form-check mt-3">
                                                            <input class="form-check-input" type="radio"
                                                                value="Submitted" name="bgv_emp_previous_document"
                                                                id="emp_previous_document1" <?php if (isset($bgv_emp_gap) && $bgv_emp_previous_document == 'Submitted') {
                                                                    echo 'checked';
                                                                } ?>>
                                                            <label class="form-check-label"
                                                                for="emp_previous_document1">
                                                                Submitted
                                                            </label>
                                                        </div>
                                                        <div class="form-check mt-3">
                                                            <input class="form-check-input" type="radio" value="Pending"
                                                                name="bgv_emp_previous_document"
                                                                id="emp_previous_document2" <?php if (isset($bgv_emp_gap) && $bgv_emp_previous_document == 'Pending') {
                                                                    echo 'checked';
                                                                } ?>>
                                                            <label class="form-check-label"
                                                                for="emp_previous_document2">
                                                                Pending
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                                    <label for="">Supervisor Name</label>
                                                    <div class="input-group flex-nowrap">
                                                        <!-- <span class="input-group-text h-100" id="addon-wrapping"></span> -->
                                                        <input type="text" name="bgv_emp_supervisior"
                                                            id="emp_supervisior" class="form-control"
                                                            placeholder="Supervisor Name" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $bgv_emp_supervisior ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="emp_supervisior_error"></p>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="row mt-2">
                                                <p class="pb-2"><b>Contact Details of HR Department</b></p>
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                                    <label for="">Email Address</label>
                                                    <div class="input-group">
                                                        <input type="email" class="form-control" name="bgv_emp_hr_email"
                                                            id="emp_hr_email" value="<?= $bgv_emp_hr_email ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="emp_hr_email_error"></p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-8 col-xl-8 col-lg-8 col-md-8 col-sm-12 col-12">
                                                    <label for="">Reason for leaving</label>
                                                    <div class="input-group">
                                                        <textarea class="form-control" name="bgv_emp_leaving_reason"
                                                            style="height:25px;" placeholder="type here..."
                                                            id="emp_leaving_reason"
                                                            style="height: 40px;"><?= $bgv_emp_leaving_reason ?? '' ?></textarea>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <p class="pb-2"><b>Explain any gap in work history?</b></p>

                                                <div
                                                    class="form-group-main col-xxl-8 col-xl-8 col-lg-8 col-md-8 col-sm-12 col-12">
                                                    <label for="">Have you ever been discharged or asked to resign from
                                                        a job?</label>

                                                    <div class="d-flex gap-3 align-items-center">
                                                        <div class="form-check mt-3">
                                                            <input class="form-check-input" value="Yes" type="radio"
                                                                name="bgv_emp_gap" id="emp_gap1" <?php if (isset($bgv_emp_gap) && $bgv_emp_gap == 'Yes') {
                                                                    echo 'checked';
                                                                } ?>><label class="form-check-label"
                                                                for="emp_gap1">
                                                                Yes
                                                            </label>
                                                        </div>
                                                        <div class="form-check mt-3">
                                                            <input class="form-check-input" value="No" type="radio"
                                                                name="bgv_emp_gap" id="emp_gap2" <?php if (isset($bgv_emp_gap) && $bgv_emp_gap == 'No') {
                                                                    echo 'checked';
                                                                } ?>>
                                                            <label class="form-check-label" for="emp_gap2">
                                                                No
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="input-group">
                                                        <textarea name="bgv_emp_gap_reason" class="form-control"
                                                            placeholder="If yes, explain"
                                                            id="emp_gap_reason"><?= $bgv_emp_gap_reason ?? '' ?></textarea>
                                                        <div class="input-group-error">
                                                            <p id="gap_error"></p>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>

                                        <input type="button" class="next8 action-button" value="Next" />
                                        <input type="button" class="next8 action-button-skip" value="Draft" />
                                        <input type="button" name="previous" class="previous action-button-previous"
                                            value="Previous" />
                                        </form>
                                    </fieldset>
                                    <fieldset>
                                        <?php echo form_open('', ['id' => 'form9']) ?>
                                        <div class="form-card">
                                            <div class="progress-container-mobile-view-parent">
                                                <div class="row d-flex align-items-center justify-content-between">
                                                    <div class="col-7">
                                                        <h3 class="fs-title">Agreement</h3>
                                                    </div>
                                                    <div class="col-5">
                                                        <h2 class="steps">Step 9 - 10</h2>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">

                                                <div class="form-floating form-group-main">

                                                    <p><input class="form-check-input m-0" type="checkbox" value="ok"
                                                            id="agreement" name="agreement" <?php if (isset($agreement) && $agreement == 'ok') {
                                                                echo 'checked';
                                                            } ?>> I hereby
                                                        certify that the facts set forth in the above employment
                                                        application are true and complete to the best of my knowledge
                                                        and authorize Tech2Globe Web Solutions and its authorized
                                                        representatives to verify its accuracy. I hereby release and
                                                        authorize Tech2Globe Web Solutions, from any/ all liability of
                                                        whatever kind and nature which, at any time, could result from
                                                        obtaining and having an employment decision based on such
                                                        information. </p>
                                                    <p class="mt-2">I understand that if employed, falsified statements
                                                        of any kind or omissions of facts called for on this application
                                                        shall be considered sufficient basis for dismissal. </p>

                                                    <p class="mt-2">I declare that the information given in the personal
                                                        data form and the accompanying certification is correct and
                                                        complete to the best of my knowledge and belief. I also
                                                        understand that at any stage, I may be asked to provide adequate
                                                        justification of the facts stated above, and I would do so when
                                                        called for. I accept the job and position given to me in all
                                                        respects and will strive to work towards the company's core
                                                        values and help to achieve its vision and mission.
                                                    </p>

                                                    <!-- <div class="checkbox-container">
                                                        <div class="form-check ">
                                                          
                                                            <label class="form-check-label" for="flexCheckChecked">
                                                                Check Now
                                                            </label>
                                                        </div>
                                                    </div> -->
                                                    <div class="input-group-error">
                                                        <p id="applicant_checkbox_error"></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- <div class="row mt-4 justify-content-between">
                                                <div
                                                    class="form-group-main col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                                    <label for="">Applicant Name *</label>
                                                    <div class="input-group flex-nowrap">
                                                        <span class="input-group-text h-100" id="addon-wrapping"><i
                                                                class="fa-solid fa-user"></i></span>
                                                        <input type="text" name="applicant_name" id="applicant_name"
                                                            class="form-control" placeholder="Enter Name" aria-label=""
                                                            disabled aria-describedby="addon-wrapping"
                                                            value="<?= $applicant_name ?? '' ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="applicant_name_error"></p>
                                                    </div>

                                                </div>
                                                <div
                                                    class="form-group-main col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                                    <label for="">Date *</label>
                                                    <div class="input-group flex-nowrap">
                                                        
                                                        <input type="date" onkeypress="return false"
                                                            name="agreement_date" id="agreement_date"
                                                            class="form-control" placeholder="" aria-label=""
                                                            aria-describedby="addon-wrapping"
                                                            value="<?= $agreement_date ?? '' ?>"
                                                            min="<?php echo date('Y-m-d') ?>"
                                                            max="<?php echo date('Y-m-d'); ?>">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="applicant_date_error"></p>
                                                    </div>

                                                </div>

                                            </div> -->
                                            <div class="row mt-2">
                                                <div
                                                    class="form-group-main col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                                    <label for="">Signature of Applicant *</label>
                                                    <div class="input-groups flex-nowrap mt-3 upload-file-input"
                                                        style="background-color: #e9ecef;">
                                                        <?php if (isset($agreement_signature)) {
                                                            ?>
                                                            <p>Uploaded File: <a href="<?php echo base_url() . 'uploads/onboarding_data/' . $staffid . '/' . $agreement_signature;
                                                            ?>" target="_blank"><?php echo $agreement_signature;
                                                            ?></a></p>
                                                        <?php }
                                                        ?>
                                                        <input class="form-control p-2" type="file"
                                                            id="agreement_signature" name="agreement_signature">
                                                    </div>
                                                    <div class="input-group-error">
                                                        <p id="agreement_signature_error"></p>
                                                    </div>
                                                </div>
                                                <p class="text-danger">Upload file must be jpg, png, or Jpeg and less
                                                    than 1mb</p>
                                            </div>

                                        </div>


                                        <input type="button" class="next9 action-button" value="Next" />
                                        <input type="button" name="previous" class="previous action-button-previous"
                                            value="Previous" />
                                        </form>
                                    </fieldset>
                                    <fieldset>
                                        <div class="form-card">
                                            <div class="progress-container-mobile-view-parent">
                                                <div class="row d-flex align-items-center justify-content-between">
                                                    <div class="col-7">
                                                        <h3 class="fs-title">Completed</h3>
                                                    </div>
                                                    <div class="col-5">
                                                        <h2 class="steps">Step 10 - 10</h2>
                                                    </div>
                                                </div>
                                            </div>
                                            <h2 class="text-success text-center "><strong>SUCCESS !</strong></h2> <br>
                                            <div class="row justify-content-center">
                                                <div class="col-3">
                                                    <!-- <img src="/images/GwStPmg-1.png" class="fit-image"> -->
                                                    <div class="gif-container w-100 mx-auto">
                                                        <img src="/uploads/onboarding-images/shield-icon.gif"
                                                            alt="sucess" class="w-100 h-100">
                                                    </div>
                                                </div>
                                            </div> <br><br>
                                            <div class="row justify-content-center">
                                                <div class="col-7 text-center">
                                                    <h5 class="text- text-center fw-bold text-danger">Your Onboarding
                                                        form is Completed!</h5>
                                                </div>
                                            </div>
                                        </div>

                                    </fieldset>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    if (!empty($child_relation) && $child_relation != null) {
        // Safely decode JSON with null-checking fallback
        $relations = json_decode($child_relation, true) ?? [];
        $names = json_decode($child_name, true) ?? [];
        $dob = json_decode($child_DOB, true) ?? [];
        $residing = json_decode($child_residing, true) ?? [];
        
        // Ensure the arrays are properly initialized
        if (!is_array($relations)) $relations = [];
        if (!is_array($names)) $names = [];
        if (!is_array($dob)) $dob = [];
        if (!is_array($residing)) $residing = [];
        
        // Combine the arrays into one data structure for JavaScript
        $dataArray = [];
        $maxLength = max(count($relations), count($names), count($dob), count($residing)); // Find the maximum length of the arrays
        for ($i = 0; $i < $maxLength; $i++) {
            $dataArray[] = [
                'relation' => $relations[$i] ?? '',
                'name' => $names[$i] ?? '',
                'dob' => $dob[$i] ?? '',
                'residing' => $residing[$i] ?? ''
            ];
        }
    } else {
        $dataArray = [];
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(".loader-wrapper").hide();
        function isValidIndianMobileNumber(number) {
            if (number.length > 10 && !number.startsWith('+91')) {
                return false;
            }
            const regex = /^(?:(?:\+|0{0,2})91(\s*|[\-])?|[0]?)?([6789]\d{2}([ -]?)\d{3}([ -]?)\d{4})$/;
            return regex.test(number);
        }


        document.addEventListener("DOMContentLoaded", function () {
            const progressBarItems = document.querySelectorAll("#progressbar li");
            const fieldsets = document.querySelectorAll("fieldset");

            // Function to activate a specific fieldset based on the clicked <li>
            function activateFieldset(index) {
                fieldsets.forEach((fieldset, i) => {
                    if (i === index) {
                        fieldset.style.display = "block"; // Show the selected fieldset
                    } else {
                        fieldset.style.display = "none"; // Hide all other fieldsets
                    }
                });

                // Update active class on progress bar items
                progressBarItems.forEach((item, i) => {
                    if (i === index) {
                        item.classList.add("active");
                    } else {
                        item.classList.remove("active");
                    }
                });
            }

            // Attach click event listeners to each <li> in the progress bar
            // progressBarItems.forEach((item, index) => {
            //     item.addEventListener("click", function () {
            //         activateFieldset(index);
            //     });
            // });

            // Initialize: Show the first fieldset and hide others
            activateFieldset(0);
        });



        function styleAsteriskInLabels() {
            // Get all label elements
            const labels = document.querySelectorAll('label');

            labels.forEach(label => {
                // Replace '*' with a styled span, if it exists
                label.innerHTML = label.innerHTML.replace(
                    '*',
                    '<span style="color: red;">*</span>'
                );
            });
        }

        // Call the function
        styleAsteriskInLabels();


   //    1801

        $(document).ready(function () {
            const addFamilyButton = $(".form-select-family");
            const familyTableRow = $(".family-varification-table-row");
            const tbody = familyTableRow.find("tbody");

            // Pass the PHP array as JSON to JavaScript
            const inputData = <?php echo json_encode($dataArray); ?>;

            // Populate the table if data is not null
            if (inputData && inputData.length > 0) {
                familyTableRow.show(); // Show the table
                inputData.forEach(item => {
                    const newRow = `
                        <tr>
                            <td><input type="text" value="${item.relation}" name="child_relation[]" required></td>
                            <td><input type="text" value="${item.name}" name="child_name[]" required></td>
                            <td><input type="date" value="${item.dob}" name="child_DOB[]" required></td>
                            <td>
                                <select name="child_residing[]" required>
                                    <option value="" disabled>Please select</option>
                                    <option value="Yes" ${item.residing === "Yes" ? "selected" : ""}>Yes</option>
                                    <option value="No" ${item.residing === "No" ? "selected" : ""}>No</option>
                                </select>
                            </td>
                        </tr>`;
                    tbody.append(newRow);
                });
            }

            // Add new row functionality
            addFamilyButton.click(function (e) {
                e.preventDefault();
                addFamilyRow();
            });

            function addFamilyRow() {
                familyTableRow.show(); // Show the table

                // Append a new blank row to the table body
                const newRow = `
                    <tr>
                        <td><input type="text" placeholder="Enter Relation" name="child_relation[]" required></td>
                        <td><input type="text" placeholder="Enter Name" name="child_name[]" required></td>
                        <td><input type="date" name="child_DOB[]" required></td>
                        <td>
                            <select name="child_residing[]" required>
                                <option value="" disabled selected>Please select</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </td>
                    </tr>`;
                tbody.append(newRow);
            }
        });

        function isDigit(number) {
            const regex = /^[0-9]*$/;
            return regex.test(number);
        }

        window.onload = function () {
            var urlParams = new URLSearchParams(window.location.search);
            var showAlert = urlParams.has('showAlert');
            if (showAlert) {
                Swal.fire({
                    icon: "warning",
                    title: "Important Notice",
                    text: "If the form closes unexpectedly or you experience internet issues, please use the link sent to your email to continue. The link is valid for 24 hours, and your data is saved.",
                    showConfirmButton: true,
                    confirmButtonText: "OK"
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Click the next button
                        $(".next1")[0].click();

                        // Remove the parameter from the URL
                        urlParams.delete('showAlert');

                        // Construct the new URL
                        var newUrl = window.location.pathname;
                        if (urlParams.toString() !== '') {
                            newUrl += '?' + urlParams.toString();
                        }

                        // Replace the current URL without the parameter
                        history.replaceState({}, '', newUrl);
                    }
                });
            }
        };


        // document.getElementById('sameAsPresentAddress').addEventListener('change', function() {
        //     var presentAddress = document.getElementById('address');
        //     var permanentAddress = document.getElementById('permanent_address');

        //     if (this.checked) {
        //         permanentAddress.value = presentAddress.value;
        //         permanentAddress.disabled = true;
        //     } else {
        //         // permanentAddress.value = '';
        //         permanentAddress.disabled = false;
        //     }
        // });


        // $(document).ready(function() {
        //     // Check for the parameter in the URL
        //     var urlParams = new URLSearchParams(window.location.search);
        //     if (urlParams.has('clickNext')) {
        //         // Trigger the click event on the "next1" button
        //         $(".next1").trigger("click");

        //         // Remove the "clickNext" parameter from the URL
        //         urlParams.delete('clickNext');
        //         var newUrl = window.location.pathname + '?' + urlParams.toString();
        //         history.replaceState({}, document.title, newUrl);
        //     }
        // });

        $(document).ready(function () {

            var current_fs, next_fs, previous_fs; //fieldsets
            var opacity;
            var current = 1;
            var steps = $("fieldset").length;
            setProgressBar(current);

            // $(".next").click(function() {

            //     var current_fs = $(this).closest('fieldset');
            //     var next_fs = current_fs.next();

            //     //Add Class Active
            //     $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");

            //     //show the next fieldset
            //     next_fs.show();
            //     //hide the current fieldset with style
            //     current_fs.animate({
            //         opacity: 0
            //     }, {
            //         step: function(now) {
            //             // for making fielset appear animation
            //             opacity = 1 - now;

            //             current_fs.css({
            //                 'display': 'none',
            //                 'position': 'relative'
            //             });
            //             next_fs.css({
            //                 'opacity': opacity
            //             });
            //         },
            //         duration: 500
            //     });
            //     setProgressBar(++current);
            // });

            $(".previous").click(function () {

                var current_fs = $(this).closest('fieldset');
                var previous_fs = current_fs.prev();

                //Remove class active
                $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");

                //show the previous fieldset
                previous_fs.show();

                //hide the current fieldset with style
                current_fs.animate({
                    opacity: 0
                }, {
                    step: function (now) {
                        // for making fielset appear animation
                        opacity = 1 - now;

                        current_fs.css({
                            'display': 'none',
                            'position': 'relative'
                        });
                        previous_fs.css({
                            'opacity': opacity
                        });
                    },
                    duration: 500
                });
                setProgressBar(--current);
            });

            $(".submit").click(function () {
                return false;
            });


            var staffid = 0;
            // Validation For Form 1

            // validationFormOne();

            $("#Fname, #Lname, #DOJ, #DOB, #mnumber, #tnumber, #address, #city, #pin, #sex, #martial_status, #email").on('keyup change', validationFormOne);

            function requiredValidationFormOne() {
                var Fname = $("#Fname").val().trim();
                var Lname = $("#Lname").val().trim();
                var DOJ = $("#DOJ").val().trim();
                var DOB = $("#DOB").val().trim();
                var mnumber = $("#mnumber").val().trim();
                var tnumber = $("#tnumber").val().trim();
                var address = $("#address").val().trim();
                var city = $("#city").val();
                var pin = $("#pin").val().trim();
                var email = $("#email").val().trim();
                $("#Fname_error").hide();
                $("#Lname_error").hide();
                $("#DOJ_error").hide();
                $("#DOB_error").hide();
                $("#mnumber_error").hide();
                $("#tnumber_error").hide();
                $("#pin_error").hide();
                $("#email_error").hide();
                $("#sex_error").hide();
                $("#maritial_status_error").hide();
                $("#present_address_error").hide();
                $("#city_error").hide();
                var error = true;
                if (Fname == '') {
                    $("#Fname_error").show();
                    $("#Fname_error").html("First name is required.");
                    $("#Fname_error").css("color", "red");
                    error = false;
                }
                if (Lname == '') {
                    $("#Lname_error").show();
                    $("#Lname_error").html("Last name is required.");
                    $("#Lname_error").css("color", "red");
                    error = false;
                }
                if (DOJ == '') {
                    $("#DOJ_error").show();
                    $("#DOJ_error").html("DOJ is required.");
                    $("#DOJ_error").css("color", "red");
                    error = false;
                }
                if (DOB == '') {
                    $("#DOB_error").show();
                    $("#DOB_error").html("DOB is required");
                    $("#DOB_error").css("color", "red");
                    error = false;
                }
                if (address == '') {
                    $("#present_address_error").show();
                    $("#present_address_error").html("Address is required.");
                    $("#present_address_error").css("color", "red");
                    error = false;
                }
                if (city == null) {
                    $("#city_error").show();
                    $("#city_error").html("Please select City.");
                    $("#city_error").css("color", "red");
                    error = false;
                }
                if (mnumber == '') {
                    $("#mnumber_error").show();
                    $("#mnumber_error").html("Mobile Number is required.");
                    $("#mnumber_error").css("color", "red");
                    error = false;
                }
                if (pin == '') {
                    $("#pin_error").show();
                    $("#pin_error").html("Pincode is required");
                    $("#pin_error").css("color", "red");
                    error = false;
                }
                if (email == '') {
                    $("#email_error").show();
                    $("#email_error").html("Email is required");
                    $("#email_error").css("color", "red");
                    error = false;
                }
                if ($("input[name='sex']:checked").length === 0) {
                    $("#sex_error").show();
                    $("#sex_error").html("Please choose Gender.");
                    $("#sex_error").css("color", "red");
                    error = false;
                }
                if ($("input[name='martial_status']:checked").length === 0) {
                    $("#maritial_status_error").show();
                    $("#maritial_status_error").html("Please choose Maritial Status.");
                    $("#maritial_status_error").css("color", "red");
                    error = false;
                }
                return error;
            }

            function validationFormOne() {
                var Fname = $("#Fname").val();
                var Lname = $("#Lname").val();
                var DOJ = $("#DOJ").val();
                var DOB = $("#DOB").val();
                var mnumber = $("#mnumber").val();
                var tnumber = $("#tnumber").val();
                var address = $("#address").val();
                var city = $("#city").val();
                var pin = $("#pin").val();
                var sex1 = $("#sex1").val();
                var sex2 = $("#sex2").val();
                var martial_status1 = $("#martial_status1").val();
                var martial_status2 = $("#martial_status2").val();
                var martial_status3 = $("#martial_status3").val();
                var email = $("#email").val();
                //hide error tags
                $("#Fname_error").hide();
                $("#Lname_error").hide();
                $("#DOJ_error").hide();
                $("#DOB_error").hide();
                $("#mnumber_error").hide();
                $("#tnumber_error").hide();
                $("#pin_error").hide();
                $("#email_error").hide();
                $("#sex_error").hide();
                $("#maritial_status_error").hide();
                $("#present_address_error").hide();
                $("#city_error").hide();
                if (!/^[a-zA-Z\s]+$/.test(Fname) && Fname != '') {
                    $("#Fname_error").show();
                    $("#Fname_error").html("First Name should have only alphabetic characters.");
                    $("#Fname_error").css("color", "red");
                    return false;
                } else if (!/^[a-zA-Z\s]+$/.test(Lname) && Lname != '') {
                    $("#Lname_error").show();
                    $("#Lname_error").html("Last Name should have only alphabetic characters.");
                    $("#Lname_error").css("color", "red");
                    return false;
                } else if (!/^[a-zA-Z0-9\s,.'\-\\\/]{3,}$/.test(address) && address !== '') {
                    $("#present_address_error").show();
                    $("#present_address_error").html("Please enter a valid address.");
                    $("#present_address_error").css("color", "red");
                    return false;
                } else if (!isValidIndianMobileNumber(mnumber) && mnumber != '') {
                    $("#mnumber_error").show();
                    $("#mnumber_error").html("Please enter a valid Mobile Number.");
                    $("#mnumber_error").css("color", "red");
                    return false;
                } else if (!isValidIndianMobileNumber(tnumber) && tnumber != '') {
                    $("#tnumber_error").show();
                    $("#tnumber_error").html("Please enter a valid Alternate Number.");
                    $("#tnumber_error").css("color", "red");
                    return false;
                } else if (!/^[1-9]{1}[0-9]{2}\s{0,1}[0-9]{3}$/.test(pin) && pin != '') {
                    $("#pin_error").show();
                    $("#pin_error").html("Please enter a valid pincode.");
                    $("#pin_error").css("color", "red");
                    return false;
                } else if (!/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email) && email != '') {
                    $("#email_error").show();
                    $("#email_error").html("Please enter a valid email.");
                    $("#email_error").css("color", "red");
                    return false;
                }
                if (tnumber != "" && mnumber != "") {
                    if (tnumber == mnumber) {
                        $("#tnumber_error").show();
                        $("#tnumber_error").html("Please enter different Alternate Number");
                        $("#tnumber_error").css("color", "red");
                        return false;
                    }
                }
                return true;
            }

            $(".next1").click(function (event) {
                // Prevent default form submission
                event.preventDefault();
                if (!requiredValidationFormOne() || !validationFormOne()) {
                    return;
                }
                const fnameInput = document.getElementById('Fname');
                const lnameInput = document.getElementById('Lname');
                // const applicantNameInput = document.getElementById('applicant_name');
                const fname = fnameInput.value.trim();
                const lname = lnameInput.value.trim();
                // applicantNameInput.value = `${fname} ${lname}`.trim();

                // Serialize form data
                var formData1 = $(this).closest('form').serialize(); // Corrected form serialization
                formData1 += '&id=' + staffid;
                $.ajax({
                    type: "post",
                    url: "/onboarding/personal_details/<?= $token ?? '' ?>",
                    data: formData1,
                    beforeSend: function () {
                        $(".next1").closest('fieldset').hide();
                        $(".loader-wrapper").show();
                    },
                    success: function (response) {
                        $(".loader-wrapper").hide();
                        if (response == 'used') {
                            Swal.fire({
                                icon: "error",
                                title: "Oops...",
                                text: "Please Continue with the url provided in your email.",
                                showConfirmButton: false
                            });
                        } else {
                            console.log(response);
                            var data = JSON.parse(response);

                            console.log(data);
                            staffid = data.staffid;

                            // console.log(response);
                            // alert(response);
                            // alert("Personal Details Submitted Successfully.");
                            <?php if (isset($token)) { ?>
                                next("next1");
                            <?php } else { ?>
                                // Swal.fire({
                                //     title: "Success!",
                                //     text: "To continue filling the rest of the form . Please check your email and continue with the url provided.",
                                //     icon: "success",
                                //     showConfirmButton: false
                                // });

                                window.location.href = "/onboarding/staff/" + data.token + "?showAlert=true";
                            <?php } ?>
                        }

                    }
                });
            });


            // Validation For Form 2

            $('#aadhar').change(function () {
                var file = this.files[0];
                var allowedExtensions = /(\.jpg|\.pdf|\.doc|\.docx|\.png|\.jpeg)$/i;
                if (!allowedExtensions.exec(file.name)) {
                    $('#aadhar_error').show();
                    $('#aadhar_error').html('Please select an pdf, jpg, png, word, or doc File.');
                    $('#aadhar_error').css("color", "red");
                    $("#aadhar").value = ''; // Clear the file input
                    $(".next2").prop("disabled", true);
                } else if (file.size > 1 * 1024 * 1024) {
                    $('#aadhar_error').show();
                    $('#aadhar_error').html('Aadhar card should be smaller than 1MB.');
                    $('#aadhar_error').css("color", "red");
                    $("#aadhar").value = ''; // Clear the file input
                    $(".next2").prop("disabled", true);
                } else {
                    $('#aadhar_error').hide();
                    $(".next2").prop("disabled", false);
                }
            });

            $("#ac_number, #bank_name, #bank_address, #IFSC, #pan_number, #blood, #emergency_number, #emergency_name, #relation, #reference").on('keyup change', validationFormTwo);

            function requiredValidationFormTwo() {
                var permanent_address = $("#permanent_address").val().trim();
                var ac_number = $("#ac_number").val().trim();
                var bank_name = $("#bank_name").val().trim();
                var bank_address = $("#bank_address").val().trim();
                var IFSC = $("#IFSC").val().trim();
                var pan_number = $("#pan_number").val().trim();
                var blood = $("#blood").val();
                var emergency_number = $("#emergency_number").val().trim();
                var emergency_name = $("#emergency_name").val().trim();
                var relation = $("#relation").val();
                var reference = $("#reference").val();
                var aadhar = $("#aadhar")[0].files.length;
                //hide error tags
                $("#permanentAddress_error").hide();
                $("#bankName_error").hide();
                $("#bankAccount_error").hide();
                $("#bankAddress_error").hide();
                $("#IFSC_error").hide();
                $("#pan_error").hide();
                $("#emergency_number_error").hide();
                $("#bloodGroup_error").hide();
                $("#emergency_name_error").hide();
                $("#relation_error").hide();
                $("#reference_error").hide();
                var error = true;
                if (permanent_address == '') {
                    $("#permanentAddress_error").show();
                    $("#permanentAddress_error").html("Enter permanent address.");
                    $("#permanentAddress_error").css("color", "red");
                    error = false;
                }
                if (ac_number == '') {
                    $("#bankAccount_error").show();
                    $("#bankAccount_error").html("Enter account number.");
                    $("#bankAccount_error").css("color", "red");
                    error = false;
                }
                if (bank_name == '') {
                    $("#bankName_error").show();
                    $("#bankName_error").html("Enter bank name.");
                    $("#bankName_error").css("color", "red");
                    error = false;
                }
                if (bank_address == '') {
                    $("#bankAddress_error").show();
                    $("#bankAddress_error").html("Enter bank branch.");
                    $("#bankAddress_error").css("color", "red");
                    error = false;
                }
                if (IFSC == '') {
                    $("#IFSC_error").show();
                    $("#IFSC_error").html("Enter IFSC code.");
                    $("#IFSC_error").css("color", "red");
                    error = false;
                }
                if (pan_number == '') {
                    $("#pan_error").show();
                    $("#pan_error").html("Enter PAN card.");
                    $("#pan_error").css("color", "red");
                    error = false;
                }
                if (blood == null) {
                    $("#bloodGroup_error").show();
                    $("#bloodGroup_error").html("Select blood group.");
                    $("#bloodGroup_error").css("color", "red");
                    error = false;
                }
                if (emergency_number == '') {
                    $("#emergency_number_error").show();
                    $("#emergency_number_error").html("Enter emergency number.");
                    $("#emergency_number_error").css("color", "red");
                    error = false;
                }
                if (emergency_name == '') {
                    $("#emergency_name_error").show();
                    $("#emergency_name_error").html("Enter emergency contact name.");
                    $("#emergency_name_error").css("color", "red");
                    error = false;
                }
                if (relation == null) {
                    $("#relation_error").show();
                    $("#relation_error").html("Select relation.");
                    $("#relation_error").css("color", "red");
                    error = false;
                }
                if (reference == null) {
                    $("#reference_error").show();
                    $("#reference_error").html("Select reference.");
                    $("#reference_error").css("color", "red");
                    error = false;
                }
                if (aadhar === 0) {

                    if ($("#aadhar").prev().length == 0) {
                        $("#aadhar_error").show();
                        $("#aadhar_error").html("Please upload your aadhar card.");
                        $("#aadhar_error").css("color", "red");
                        error = false;
                    };

                }

                return error;
            }

            function validationFormTwo() {
                var permanent_address = $("#permanent_address").val().trim();
                var ac_number = $("#ac_number").val().trim();
                var bank_name = $("#bank_name").val().trim();
                var bank_address = $("#bank_address").val().trim();
                var IFSC = $("#IFSC").val().trim();
                var pan_number = $("#pan_number").val().trim();
                var blood = $("#blood").val();
                var emergency_number = $("#emergency_number").val().trim();
                var emergency_name = $("#emergency_name").val().trim();
                var relation = $("#relation").val();
                var reference = $("#reference").val();
                var aadhar = $("#aadhar")[0].files.length;
                var mnumber = $("#mnumber").val().trim();
                var tnumber = $("#tnumber").val().trim();

                //hide error tags
                $("#permanentAddress_error").hide();
                $("#bankName_error").hide();
                $("#bankAccount_error").hide();
                $("#IFSC_error").hide();
                $("#pan_error").hide();
                $("#emergency_number_error").hide();
                $("#emergency_name_error").hide();

                if (!/^[a-zA-Z\s]+$/.test(bank_name) && bank_name != '') {
                    $("#bankName_error").show();
                    $("#bankName_error").html("Bank Name should have only alphabetic characters.");
                    $("#bankName_error").css("color", "red");
                    return false;
                } else if (ac_number.length < 9 || ac_number.length > 18) {
                    $("#bankAccount_error").show();
                    $("#bankAccount_error").html("Invalid account number.");
                    $("#bankAccount_error").css("color", "red");
                    return false;
                } else if (!/^[A-Z]{4}0[A-Z0-9]{6}$/i.test(IFSC) && IFSC != '') {
                    $("#IFSC_error").show();
                    $("#IFSC_error").html("IFSC code is not valid.");
                    $("#IFSC_error").css("color", "red");
                    return false;
                } else if (!/[A-Z]{5}[0-9]{4}[A-Z]{1}/i.test(pan_number) && pan_number != '') {
                    $("#pan_error").show();
                    $("#pan_error").html("PAN number is not valid");
                    $("#pan_error").css("color", "red");
                    return false;
                } else if (!isValidIndianMobileNumber(emergency_number) && emergency_number != '') {
                    $("#emergency_number_error").show();
                    $("#emergency_number_error").html("Enter valid phone number.");
                    $("#emergency_number_error").css("color", "red");
                    return false;
                } else if (!/^[a-zA-Z\s]+$/.test(emergency_name) && emergency_name != '') {
                    $("#emergency_name_error").show();
                    $("#emergency_name_error").html("Emergency name should have only alphabetic characters.");
                    $("#emergency_name_error").css("color", "red");
                    return false;
                }
                if (emergency_name.toLocaleLowerCase() == `${$("#Fname").val().trim()} ${$("#Lname").val().trim()}`.trim().toLocaleLowerCase() && emergency_name != "") {
                    $("#emergency_name_error").show();
                    $("#emergency_name_error").html("Emergency name should be different from applicant name.");
                    $("#emergency_name_error").css("color", "red");
                    return false;
                }
                if (emergency_number == mnumber && emergency_number != "") {
                    $("#emergency_number_error").show();
                    $("#emergency_number_error").html("Emergency number should be different from Mobile Number.");
                    $("#emergency_number_error").css("color", "red");
                    return false;
                }
                if (emergency_number == tnumber && emergency_number != "") {
                    $("#emergency_number_error").show();
                    $("#emergency_number_error").html("Emergency number should be different from Alternate Number.");
                    $("#emergency_number_error").css("color", "red");
                    return false;
                }
                return true;
            }

            $(".next2").click(function (event) {
                // Prevent default form submission
                event.preventDefault();

                var btnEvent = $(this).val();

                if (btnEvent == "Next") {
                    if (!requiredValidationFormTwo() || !validationFormTwo()) {
                        console.log('error')
                        return;
                    }
                }
                // Serialize form data
                // var formData2 = $(this).closest('form').serialize(); // Corrected form serialization

                // formData2 += '&staffid=' + staffid;

                var formData2 = new FormData($("#form2")[0]);
                formData2.append('staffid', staffid);
                // console.log(formData); return false;

                $.ajax({
                    type: "post",
                    url: "/onboarding/official_details/",
                    data: formData2,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $(".next2").closest('fieldset').hide();
                        $(".loader-wrapper").show();
                    },
                    success: function (response) {
                        // alert(response);
                        $(".loader-wrapper").hide();

                        // if (response == staffid) {
                        // alert("Official Details Submitted Successfully.");
                        if (btnEvent == "Next") {
                            next("next2");
                        } else {
                            $(".next2").closest('fieldset').show();
                        }
                        // }
                        // else{
                        //     alert(response);
                        //     console.log(response);
                        // }
                    }
                });
            });

            // Validation for Form 3

            function requiredValidationFormThree() {
                var father_name = $("#father_name").val().trim();
                var father_DOB = $("#father_DOB").val();
                var father_residing = $("#father_residing").val();
                var mother_name = $("#mother_name").val().trim();
                var mother_DOB = $("#mother_DOB").val();
                var mother_residing = $("#mother_residing").val();
                // var child_name = $("#child_name").val().trim();
                // var child_DOB = $("#child_DOB").val();
                // var child_residing = $("#child_residing").val();
                var nominee = $("#nominee").val().trim();

                //hide error tags
                $("#father_name_error").hide();
                $("#father_residing_error").hide();
                $("#mother_name_error").hide();
                $("#mother_residing_error").hide();
                // $("#child_name_error").hide();
                // $("#child_residing_error").hide();
                $("#nominee_error").hide();

                var error = true;
                if (father_name == '') {
                    $("#father_name_error").show();
                    $("#father_name_error").html("Enter father name.");
                    $("#father_name_error").css("color", "red");
                    error = false;
                }
                if (!father_residing) {
                    $("#father_residing_error").show();
                    $("#father_residing_error").html("This field is required.");
                    $("#father_residing_error").css("color", "red");
                    error = false;
                }
                if (mother_name == '') {
                    $("#mother_name_error").show();
                    $("#mother_name_error").html("Enter mother name.");
                    $("#mother_name_error").css("color", "red");
                    error = false;
                }
                if (!mother_residing) {
                    $("#mother_residing_error").show();
                    $("#mother_residing_error").html("This field is required.");
                    $("#mother_residing_error").css("color", "red");
                    error = false;
                }
                if (nominee == '') {
                    $("#nominee_error").show();
                    $("#nominee_error").html("Enter nominee name.");
                    $("#nominee_error").css("color", "red");
                    error = false;
                }
                return error;
            }

            $("#father_name, #father_DOB, #father_residing, #mother_name, #mother_DOB, #mother_residing, #child_name, #child_DOB, #child_residing, #nominee").on('keyup change', validationFormThree);

            function validationFormThree() {

                var father_name = $("#father_name").val().trim();
                var father_DOB = $("#father_DOB").val();
                var father_residing = $("#father_residing").val();
                var mother_name = $("#mother_name").val().trim();
                var mother_DOB = $("#mother_DOB").val();
                var mother_residing = $("#mother_residing");
                 // var child_name = $("#child_name").val().trim();
                // var child_DOB = $("#child_DOB").val();
                // var child_residing = $("#child_residing");
                var nominee = $("#nominee").val().trim();

                //hide error tags
                $("#father_name_error").hide();
                $("#father_residing_error").hide();
                $("#mother_name_error").hide();
                $("#mother_residing_error").hide();
                // $("#child_name_error").hide();
                // $("#child_residing_error").hide();
                $("#nominee_error").hide();

                if (!/^[a-zA-Z\s]+$/.test(father_name) && father_name != '') {
                    $("#father_name_error").show();
                    $("#father_name_error").html("Father Name should have only alphabetic characters.");
                    $("#father_name_error").css("color", "red");
                    return false;
                } else if (!/^[a-zA-Z\s]+$/.test(mother_name) && mother_name != '') {
                    $("#mother_name_error").show();
                    $("#mother_name_error").html("Mother Name should have only alphabetic characters.");
                    $("#mother_name_error").css("color", "red");
                    return false;
                } else if (!/^[a-zA-Z\s]+$/.test(nominee) && nominee != '') {
                    $("#nominee_error").show();
                    $("#nominee_error").html("Nominee Name should have only alphabetic characters.");
                    $("#nominee_error").css("color", "red");
                    return false;
                }
                return true;
            }

            $(".next3").click(function (event) {
                // Prevent default form submission
                event.preventDefault();

                var btnEvent = $(this).val();

                if (btnEvent == "Next") {
                    if (!requiredValidationFormThree() || !validationFormThree()) {
                        return;
                    }
                }
                // Serialize form data
                // var formData3 = $(this).closest('form').serialize(); // Corrected form serialization
                // formData3 += '&staffid=' + staffid;

                var formData3 = new FormData($("#form3")[0]);
                formData3.append('staffid', staffid);

                $.ajax({
                    type: "post",
                    url: "/onboarding/family_members_details/",
                    data: formData3,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $(".next3").closest('fieldset').hide();
                        $(".loader-wrapper").show();
                    },
                    success: function (response) {
                        $(".loader-wrapper").hide();

                        console.log(response);
                        // if (response == 202) {
                        // alert("Family Member Details Submitted Successfully.");
                        if (btnEvent == "Next") {
                            next("next3");
                        } else {
                            $(".next3").closest('fieldset').show();
                        }
                        // }
                    }
                });
            });

            // Validation for Form 4

            validationFormFour();

            $('#10_marksheet').change(function () {
                var file = this.files[0];
                var allowedExtensions = /(\.jpg|\.pdf|\.doc|\.docx|\.png|\.jpeg)$/i;

                if (!allowedExtensions.exec(file.name)) {
                    $('#10_marksheet_error').show();
                    $('#10_marksheet_error').html('Please select an pdf, jpg, png, word, or doc File.');
                    $('#10_marksheet_error').css("color", "red");
                    $("#10_marksheet").value = ''; // Clear the file input
                    $(".next4").prop("disabled", true);
                } else if (file.size > 1 * 1024 * 1024) {
                    $('#10_marksheet_error').show();
                    $('#10_marksheet_error').html('10 Marksheet should be smaller than 1MB.');
                    $('#10_marksheet_error').css("color", "red");
                    $("#10_marksheet").value = ''; // Clear the file input
                    $(".next4").prop("disabled", true);
                } else {
                    $('#10_marksheet_error').hide();
                    $(".next4").prop("disabled", false);
                }
            });

            $('#12_marksheet').change(function () {
                var file = this.files[0];
                var allowedExtensions = /(\.jpg|\.pdf|\.doc|\.docx|\.png|\.jpeg)$/i;

                if (!allowedExtensions.exec(file.name)) {
                    $('#12_marksheet_error').show();
                    $('#12_marksheet_error').html('Please select an pdf, jpg, png, word, or doc File.');
                    $('#12_marksheet_error').css("color", "red");
                    $("#12_marksheet").value = ''; // Clear the file input
                    $(".next4").prop("disabled", true);
                } else if (file.size > 1 * 1024 * 1024) {
                    $('#12_marksheet_error').show();
                    $('#12_marksheet_error').html('12 Marksheet should be smaller than 1MB.');
                    $('#12_marksheet_error').css("color", "red");
                    $("#12_marksheet").value = ''; // Clear the file input
                    $(".next4").prop("disabled", true);
                } else {
                    $('#12_marksheet_error').hide();
                    $(".next4").prop("disabled", false);
                }
            });

            $("#10_board, #10_school_name, #10_percentage, #12_board, #12_percentage, #12_institute").on('keyup change', validationFormFour);
            $("#10_passed_year, #10_grade, #10_field, #12_field, #12_passed_year, #12_grade").on('change', validationFormFour);

            function requiredValidationFormFour() {
                var ten_board = $("#10_board").val().trim();
                var ten_school_name = $("#10_school_name").val().trim();
                var ten_passed_year = $("#10_passed_year").val();
                var ten_percentage = $("#10_percentage").val().trim();
                var ten_grade = $("#10_grade").val();
                var ten_field = $("#10_field").val();

                var twelve_board = $("#12_board").val().trim();
                var twelve_field = $("#12_field").val();
                var twelve_passed_year = $("#12_passed_year").val();
                var twelve_percentage = $("#12_percentage").val().trim();
                var twelve_grade = $("#12_grade").val();
                var twelve_institute = $("#12_institute").val().trim();
                var ten_marksheet = $("#10_marksheet")[0].files.length;
                var twelve_marksheet = $("#12_marksheet")[0].files.length;

                //hide error tags
                $("#10_board_error").hide();
                $("#10_school_name_error").hide();
                $("#10_passed_year_error").hide();
                $("#10_field_error").hide();
                $("#10_marksheet_error").hide();

                $("#12_board_error").hide();
                $("#12_institute_error").hide();
                $("#12_passed_year_error").hide();
                $("#12_field_error").hide();
                $("#12_marksheet_error").hide();

                var error = true;
                if (ten_board == '') {
                    $("#10_board_error").show();
                    $("#10_board_error").html("This field is required.");
                    $("#10_board_error").css("color", "red");
                    error = false;
                }
                if (ten_school_name == '') {
                    $("#10_school_name_error").show();
                    $("#10_school_name_error").html("This field is required.");
                    $("#10_school_name_error").css("color", "red");
                    error = false;
                }
                if (ten_passed_year == null) {
                    $("#10_passed_year_error").show();
                    $("#10_passed_year_error").html("This field is required.");
                    $("#10_passed_year_error").css("color", "red");
                    error = false;
                }
                if (ten_field == 'Please select') {
                    $("#10_field_error").show();
                    $("#10_field_error").html("This field is required.");
                    $("#10_field_error").css("color", "red");
                    error = false;
                }
                if (twelve_board == '') {
                    $("#12_board_error").show();
                    $("#12_board_error").html("This field is required.");
                    $("#12_board_error").css("color", "red");
                    error = false;
                }
                if (twelve_institute == '') {
                    $("#12_institute_error").show();
                    $("#12_institute_error").html("This field is required.");
                    $("#12_institute_error").css("color", "red");
                    error = false;
                }
                if (twelve_passed_year == null) {
                    $("#12_passed_year_error").show();
                    $("#12_passed_year_error").html("This field is required.");
                    $("#12_passed_year_error").css("color", "red");
                    error = false;
                }
                if ((ten_percentage == '' && ten_grade == 'Please select')) {
                    $("#10_percentage_error").show();
                    $("#10_percentage_error").html("Either 10% or 10 grade is required.");
                    $("#10_percentage_error").css("color", "red");
                    error = false;
                }
                if ((twelve_percentage == '' && twelve_grade == 'Please select')) {
                    $("#12_percentage_error").show();
                    $("#12_percentage_error").html("Either 12% or 12 grade is required.");
                    $("#12_percentage_error").css("color", "red");
                    error = false;
                }
                if (twelve_field == 'Please select') {
                    $("#12_field_error").show();
                    $("#12_field_error").html("This field is required.");
                    $("#12_field_error").css("color", "red");
                    error = false;
                }
                if (ten_marksheet === 0) {
                    if ($("#10_marksheet").prev().length == 0) {

                        $("#10_marksheet_error").show();
                        $("#10_marksheet_error").html("10th marksheet is required.");
                        $("#10_marksheet_error").css("color", "red");
                        error = false;
                    }
                }
                if (twelve_marksheet === 0) {
                    if ($("#12_marksheet").prev().length == 0) {

                        $("#12_marksheet_error").show();
                        $("#12_marksheet_error").html("12th marksheet is required.");
                        $("#12_marksheet_error").css("color", "red");
                        error = false;
                    }
                }
                return error;
            }

            function validationFormFour() {
                var ten_board = $("#10_board").val().trim();
                var ten_school_name = $("#10_school_name").val().trim();
                var ten_passed_year = $("#10_passed_year").val();
                var ten_percentage = $("#10_percentage").val().trim();
                var ten_grade = $("#10_grade").val();
                var ten_field = $("#10_field").val();

                var twelve_board = $("#12_board").val().trim();
                var twelve_field = $("#12_field").val();
                var twelve_passed_year = $("#12_passed_year").val();
                var twelve_percentage = $("#12_percentage").val().trim();
                var twelve_grade = $("#12_grade").val();
                var twelve_institute = $("#12_institute").val().trim();

                //hide error tags
                $("#10_board_error").hide();
                $("#10_school_name_error").hide();
                $("#10_passed_year_error").hide();
                $("#10_field_error").hide();
                $("#10_percentage_error").hide();

                $("#12_board_error").hide();
                $("#12_institute_error").hide();
                $("#12_passed_year_error").hide();
                $("#12_field_error").hide();
                $("#12_percentage_error").hide();

                if (!/^[a-zA-Z\s]+$/.test(ten_board) && ten_board != '') {
                    $("#10_board_error").show();
                    $("#10_board_error").html("Board Name should have only alphabetic characters.");
                    $("#10_board_error").css("color", "red");
                    return false;
                } else if (!/^[a-zA-Z\s]+$/.test(ten_school_name) && ten_school_name != '') {
                    $("#10_school_name_error").show();
                    $("#10_school_name_error").html("School Name should have only alphabetic characters.");
                    $("#10_school_name_error").css("color", "red");
                    return false;
                } else if (ten_percentage < 0 || ten_percentage > 100) {
                    $("#10_percentage_error").show();
                    $("#10_percentage_error").html("Enter Correct percentage.");
                    $("#10_percentage_error").css("color", "red");
                    return false;
                } else if (!/^[a-zA-Z\s]+$/.test(twelve_board) && twelve_board != '') {
                    $("#12_board_error").show();
                    $("#12_board_error").html("Board Name should have only alphabetic characters.");
                    $("#12_board_error").css("color", "red");
                    return false;
                } else if (!/^[a-zA-Z\s]+$/.test(twelve_institute) && twelve_institute != '') {
                    $("#12_institute_error").show();
                    $("#12_institute_error").html("Institute Name should have only alphabetic characters.");
                    $("#12_institute_error").css("color", "red");
                    return false;
                } else if (twelve_percentage < 0 || twelve_percentage > 100) {
                    $("#12_percentage_error").show();
                    $("#12_percentage_error").html("Enter Correct percentage.");
                    $("#12_percentage_error").css("color", "red");
                    return false;
                }
                if (ten_passed_year && twelve_passed_year) {
                    ten_passed_year = parseInt(ten_passed_year);
                    twelve_passed_year = parseInt(twelve_passed_year);
                    // Calculate minimum year for passing 12th grade
                    var min_twelve_passed_year = ten_passed_year + 2;
                    if (twelve_passed_year < ten_passed_year) {
                        $("#12_passed_year_error").show();
                        $("#12_passed_year_error").html("Year of 12th pass cannot be less than year of 10th pass.");
                        $("#12_passed_year_error").css("color", "red");
                        return false;
                    }
                    if (twelve_passed_year === ten_passed_year) {
                        $("#12_passed_year_error").show();
                        $("#12_passed_year_error").html("Year of 12th pass cannot be the same as the year of 10th pass ");
                        $("#12_passed_year_error").css("color", "red");
                        return false;
                    }
                    if (twelve_passed_year < min_twelve_passed_year) {
                        $("#12_passed_year_error").show();
                        $("#12_passed_year_error").html("Year of 12th pass must be " + min_twelve_passed_year + " or later.");
                        $("#12_passed_year_error").css("color", "red");
                        return false;
                    }
                }
                return true;

            }

            $(".next4").click(function (event) {
                // Prevent default form submission
                event.preventDefault();

                var btnEvent = $(this).val();

                if (btnEvent == "Next") {
                    if (!requiredValidationFormFour() || !validationFormFour()) {
                        return false;
                    }
                }
                // Create a new FormData object
                // var formData4 = new FormData($(this).closest('form')[0]);

                // var formData4 = $(this).closest('form').serialize(); // Corrected form serialization
                // formData4 += '&staffid=' + staffid;

                var formData4 = new FormData($("#form4")[0]);
                formData4.append('staffid', staffid);

                $.ajax({
                    type: "post",
                    url: "/onboarding/qualification_details/",
                    data: formData4,
                    processData: false, // Important: prevent jQuery from automatically processing the data
                    contentType: false, // Important: prevent jQuery from setting contentType
                    beforeSend: function () {
                        $(".next4").closest('fieldset').hide();
                        $(".loader-wrapper").show();
                    },
                    success: function (response) {
                        console.log(response);
                        $(".loader-wrapper").hide();

                        // if (response == 203) {
                        // alert("Qualification Details Submitted Successfully.");
                        if (btnEvent == "Next") {
                            next("next4");
                        } else {
                            $(".next4").closest('fieldset').show();
                        }
                        // }
                    }
                });
            });

            // Validation for Form 5
            $('#graduation_marksheet').change(function () {
                var file = this.files[0];
                var allowedExtensions = /(\.jpg|\.pdf|\.doc|\.docx|\.png|\.jpeg)$/i;

                if (!allowedExtensions.exec(file.name)) {
                    $('#graduation_marksheet_error').show();
                    $('#graduation_marksheet_error').html('Please select an pdf, jpg, png, word, or doc File.');
                    $('#graduation_marksheet_error').css("color", "red");
                    $("#graduation_marksheet").value = ''; // Clear the file input
                    $(".next5").prop("disabled", true);
                } else if (file.size > 1 * 1024 * 1024) {
                    $('#graduation_marksheet_error').show();
                    $('#graduation_marksheet_error').html('Graduation Marksheet should be smaller than 1MB.');
                    $('#graduation_marksheet_error').css("color", "red");
                    $("#graduation_marksheet_error").value = ''; // Clear the file input
                    $(".next5").prop("disabled", true);
                } else {
                    $('#graduation_marksheet_error').hide();
                    validationFormFive();

                }
            });

            $('#post_graduation_marksheet').change(function () {
                var file = this.files[0];
                var allowedExtensions = /(\.jpg|\.pdf|\.doc|\.docx|\.png|\.jpeg)$/i;

                if (!allowedExtensions.exec(file.name)) {
                    $('#post_graduation_marksheet_error').show();
                    $('#post_graduation_marksheet_error').html('Please select an pdf, jpg, png, word, or doc File.');
                    $('#post_graduation_marksheet_error').css("color", "red");
                    $("#post_graduation_marksheet").value = ''; // Clear the file input
                    $(".next5").prop("disabled", true);
                } else if (file.size > 1 * 1024 * 1024) {
                    $('#post_graduation_marksheet_error').show();
                    $('#post_graduation_marksheet_error').html('Post Graduation Marksheet should be smaller than 1MB.');
                    $('#post_graduation_marksheet_error').css("color", "red");
                    $("#post_graduation_marksheet").value = ''; // Clear the file input
                    $(".next5").prop("disabled", true);
                } else {
                    $('#post_graduation_marksheet_error').hide();
                    validationFormFive();
                }
            });

            $("#graduation_university_name, #post_graduation_university_name").on('keyup change', validationFormFive);
            $("#graduation_passed_year, #post_graduation_passed_year").change(validationFormFive);

            function requiredValidationFormFive() {
                var graduation_university_name = $("#graduation_university_name").val().trim();
                var post_graduation_university_name = $("#post_graduation_university_name").val().trim();
                var graduation_passed_year = $("#graduation_passed_year").val();
                var post_graduation_passed_year = $("#post_graduation_passed_year").val();
                var graduation_marksheet = $("#graduation_marksheet")[0].files.length;
                var post_graduation_marksheet = $("#post_graduation_marksheet")[0].files.length;

                //hide error tags
                $("#graduation_university_name_error").hide();
                $("#graduation_passed_year_error").hide();
                $("#graduation_marksheet_error").hide();
                $("#post_graduation_marksheet_error").hide();
                var error = true;
                if (graduation_university_name == '') {
                    $("#graduation_university_name_error").show();
                    $("#graduation_university_name_error").html("This field is required.");
                    $("#graduation_university_name_error").css("color", "red");
                    error = false;
                }
                // if (graduation_marksheet === 0) {
                //     if ($("#graduation_marksheet").prev().length == 0) {
                //         $("#graduation_marksheet_error").show();
                //         $("#graduation_marksheet_error").html("Graduation marksheet is required.");
                //         $("#graduation_marksheet_error").css("color", "red");
                //         error = false;
                //     }
                // }
                if (graduation_passed_year == null) {
                    $("#graduation_passed_year_error").show();
                    $("#graduation_passed_year_error").html("This field is required.");
                    $("#graduation_passed_year_error").css("color", "red");
                    error = false;
                }
                if (post_graduation_university_name != '' && post_graduation_marksheet === 0) {
                    if ($("#post_graduation_marksheet").prev().length == 0) {
                        $("#post_graduation_marksheet_error").show();
                        $("#post_graduation_marksheet_error").html("Post-graduation marksheet is required.");
                        $("#post_graduation_marksheet_error").css("color", "red");
                        error = false;
                    }
                }
                return error;
            }

            function validationFormFive() {
                var graduation_university_name = $("#graduation_university_name").val().trim();
                var post_graduation_university_name = $("#post_graduation_university_name").val().trim();
                var graduation_passed_year = $("#graduation_passed_year").val();
                var post_graduation_passed_year = $("#post_graduation_passed_year").val();
                var graduation_marksheet = $("#graduation_marksheet")[0].files.length;
                var post_graduation_marksheet = $("#post_graduation_marksheet")[0].files.length;

                //hide error tags
                $("#graduation_university_name_error").hide();
                $("#post_graduation_university_name_error").hide();
                $("#graduation_passed_year_error").hide();
                $("#post_graduation_passed_year_error").hide();
                if (!/^[a-zA-Z\s]+$/.test(graduation_university_name) && graduation_university_name != '') {
                    $("#graduation_university_name_error").show();
                    $("#graduation_university_name_error").html("University Name should have only alphabetic characters.");
                    $("#graduation_university_name_error").css("color", "red");
                    return false;
                } else if (!/^[a-zA-Z\s]+$/.test(post_graduation_university_name) && post_graduation_university_name != '') {
                    $("#post_graduation_university_name_error").show();
                    $("#post_graduation_university_name_error").html("University Name should have only alphabetic characters.");
                    $("#post_graduation_university_name_error").css("color", "red");
                    return false;
                }
                if (graduation_passed_year && post_graduation_passed_year) {
                    graduation_passed_year = parseInt(graduation_passed_year);
                    post_graduation_passed_year = parseInt(post_graduation_passed_year);

                    if (post_graduation_passed_year < graduation_passed_year) {
                        $("#post_graduation_passed_year_error").show();
                        $("#post_graduation_passed_year_error").html("Year of post graduation pass cannot be less than year of graduation pass");
                        $("#post_graduation_passed_year_error").css("color", "red");
                        return false;
                    }
                    if (post_graduation_passed_year === graduation_passed_year) {
                        $("#post_graduation_passed_year_error").show();
                        $("#post_graduation_passed_year_error").html("Year of post graduation pass cannot be the same as the year of graduation pass");
                        $("#post_graduation_passed_year_error").css("color", "red");
                        return false;
                    }
                }
                return true;
            }

            $(".next5").click(function (event) {
                // Prevent default form submission
                event.preventDefault();
                var btnEvent = $(this).val();

                if (btnEvent == "Next") {
                    if (!requiredValidationFormFive() || !validationFormFive()) {
                        return;
                    }
                }
                // Create a new FormData object
                // var formData5 = $(this).closest('form').serialize(); // Corrected form serialization
                // formData5 += '&staffid=' + staffid;


                var formData5 = new FormData($("#form5")[0]);
                formData5.append('staffid', staffid);

                // var formData5 = new FormData($(this).closest('form')[0]);

                $.ajax({
                    type: "post",
                    url: "/onboarding/graduation_details/",
                    data: formData5,
                    processData: false, // Important: prevent jQuery from automatically processing the data
                    contentType: false, // Important: prevent jQuery from setting contentType
                    beforeSend: function () {
                        $(".next5").closest('fieldset').hide();
                        $(".loader-wrapper").show();
                    },
                    success: function (response) {
                        $(".loader-wrapper").hide();
                        // if (response == 204) {
                        // alert("Graduation Details Submitted Successfully.");
                        if (btnEvent == "Next") {
                            next("next5");
                        } else {
                            $(".next5").closest('fieldset').show();
                        }
                        // }
                    }
                });
            });

            // Validation for Form 6

            validationFormSix();

            $('#previous_uploads').change(function () {

                var files = this.files; // Get all selected files

                var allowedExtensions = /(\.jpg|\.pdf|\.doc|\.docx|\.png|\.jpeg)$/i;
                var maxSize = 1 * 1024 * 1024; // 1MB

                var validFiles = true;

                for (var i = 0; i < files.length; i++) {
                    var file = files[i];

                    if (!allowedExtensions.exec(file.name)) {
                        $('#previous_uploads_error').show();
                        $('#previous_uploads_error').html('Please select a PDF, JPG, PNG, Word, or DOC file.');
                        $('#previous_uploads_error').css("color", "red");
                        validFiles = false;
                    } else if (file.size > maxSize) {
                        $('#previous_uploads_error').show();
                        $('#previous_uploads_error').html('Each Document should be smaller than 1MB.');
                        $('#previous_uploads_error').css("color", "red");
                        validFiles = false;
                    }
                }

                if (!validFiles) {
                    $("#previous_uploads").val(''); // Clear the file input
                    $(".next6").prop("disabled", true);
                } else {
                    $('#previous_uploads_error').hide();
                    validationFormSix();
                }
            });


            $('#before_uploads').change(function () {

                var files = this.files; // Get all selected files

                var allowedExtensions = /(\.jpg|\.pdf|\.doc|\.docx|\.png|\.jpeg)$/i;
                var maxSize = 1 * 1024 * 1024; // 1MB

                var validFiles = true;

                for (var i = 0; i < files.length; i++) {
                    var file = files[i];

                    if (!allowedExtensions.exec(file.name)) {
                        $('#before_uploads_error').show();
                        $('#before_uploads_error').html('Please select a PDF, JPG, PNG, Word, or DOC file.');
                        $('#before_uploads_error').css("color", "red");
                        validFiles = false;
                    } else if (file.size > maxSize) {
                        $('#before_uploads_error').show();
                        $('#before_uploads_error').html('Each Document should be smaller than 1MB.');
                        $('#before_uploads_error').css("color", "red");
                        validFiles = false;
                    }
                }

                if (!validFiles) {
                    $("#before_uploads").val(''); // Clear the file input
                    $(".next6").prop("disabled", true);
                } else {
                    $('#before_uploads_error').hide();
                    validationFormSix();
                }
            });

            $("#previous_designation, #previous_emp_id, #previous_pay, #previous_person_name, #previous_person_designation, #previous_person_contact, #before_designation, #before_emp_id, #before_pay, #before_reporting_person, #before_reporting_designation, #before_reporting_contact").on('keyup change', validationFormSix);
            $("#previous_year, #before_year").change(validationFormSix);

            function validationFormSix() {

                var previous_designation = $("#previous_designation").val().trim();
                var previous_year = $("#previous_year").val();
                var previous_emp_id = $("#previous_emp_id").val().trim();
                var previous_pay = $("#previous_pay").val().trim();
                var previous_person_name = $("#previous_person_name").val().trim();
                var previous_person_designation = $("#previous_person_designation").val().trim();
                var previous_person_contact = $("#previous_person_contact").val().trim();
                var before_designation = $("#before_designation").val().trim();
                var before_year = $("#before_year").val();
                var before_emp_id = $("#before_emp_id").val().trim();
                var before_pay = $("#before_pay").val().trim();
                var before_reporting_person = $("#before_reporting_person").val().trim();
                var before_reporting_designation = $("#before_reporting_designation").val().trim();
                var before_reporting_contact = $("#before_reporting_contact").val().trim();
                var previous_uploads = $("#previous_uploads")[0].files.length;
                var before_uploads = $("#before_uploads")[0].files.length;



                //hide error tags
                $("#previous_designation_error").hide();
                $("#previous_person_name_error").hide();
                $("#previous_person_designation_error").hide();
                $("#previous_person_contact_error").hide();
                $("#before_designation_error").hide();
                $("#before_reporting_person_error").hide();
                $("#before_reporting_designation_error").hide();
                $("#before_reporting_contact_error").hide();
                $("#previous_uploads_error").hide();
                $("#before_uploads_error").hide();
                $("#before_year_error").hide();


                if (!/^[a-zA-Z\s]+$/.test(previous_designation) && previous_designation != '') {
                    $("#previous_designation_error").show();
                    $("#previous_designation_error").html("Designation Name should have only alphabetic characters.");
                    $("#previous_designation_error").css("color", "red");
                    $(".next6").prop("disabled", true);
                } else if (!/^[a-zA-Z\s]+$/.test(previous_person_name) && previous_person_name != '') {
                    $("#previous_person_name_error").show();
                    $("#previous_person_name_error").html("Reporting Person Name should have only alphabetic characters.");
                    $("#previous_person_name_error").css("color", "red");
                    $(".next6").prop("disabled", true);
                } else if (!/^[a-zA-Z\s]+$/.test(previous_person_designation) && previous_person_designation != '') {
                    $("#previous_person_designation_error").show();
                    $("#previous_person_designation_error").html("Reporting Person Designation should have only alphabetic characters.");
                    $("#previous_person_designation_error").css("color", "red");
                    $(".next6").prop("disabled", true);
                } else if (!isValidIndianMobileNumber(previous_person_contact) && previous_person_contact != '') {
                    $("#previous_person_contact_error").show();
                    $("#previous_person_contact_error").html("Incorrect mobile number.");
                    $("#previous_person_contact_error").css("color", "red");
                    $(".next6").prop("disabled", true);
                } else if (!/^[a-zA-Z\s]+$/.test(before_designation) && before_designation != '') {
                    $("#before_designation_error").show();
                    $("#before_designation_error").html("Designation Name should have only alphabetic characters.");
                    $("#before_designation_error").css("color", "red");
                    $(".next6").prop("disabled", true);
                } else if (!/^[a-zA-Z\s]+$/.test(before_reporting_person) && before_reporting_person != '') {
                    $("#before_reporting_person_error").show();
                    $("#before_reporting_person_error").html("Reporting Person Name should have only alphabetic characters.");
                    $("#before_reporting_person_error").css("color", "red");
                    $(".next6").prop("disabled", true);
                } else if (!/^[a-zA-Z\s]+$/.test(before_reporting_designation) && before_reporting_designation != '') {
                    $("#before_reporting_designation_error").show();
                    $("#before_reporting_designation_error").html("Reporting Person Designation should have only alphabetic characters.");
                    $("#before_reporting_designation_error").css("color", "red");
                    $(".next6").prop("disabled", true);
                } else if (!isValidIndianMobileNumber(before_reporting_contact) && before_reporting_contact != '') {
                    $("#before_reporting_contact_error").show();
                    $("#before_reporting_contact_error").html("Incorrect mobile number.");
                    $("#before_reporting_contact_error").css("color", "red");
                    $(".next6").prop("disabled", true);
                } else if (before_year > previous_year && before_year != "" && previous_year != "") {
                    $("#before_year_error").show();
                    $("#before_year_error").html("Before year should be less than or equal to the previous year of organization.");
                    $("#before_year_error").css("color", "red");
                    $(".next6").prop("disabled", true);
                } else if (before_reporting_contact === previous_person_contact && before_reporting_contact != "" && previous_person_contact != "") {
                    $("#before_reporting_contact_error").show();
                    $("#before_reporting_contact_error").html("Contact person numbers should be unique for each organization.");
                    $("#before_reporting_contact_error").css("color", "red");
                    $(".next6").prop("disabled", true);
                } else {
                    $(".next6").prop("disabled", false);
                }



            }

            $(".next6").click(function (event) {
                // Prevent default form submission
                event.preventDefault();

                var btnEvent = $(this).val();

                // Create a new FormData object
                // var formData6 = new FormData($(this).closest('form')[0]);
                // var formData6 = $(this).closest('form').serialize();
                // formData6 += '&staffid=' + staffid;


                var formData6 = new FormData($("#form6")[0]);
                formData6.append('staffid', staffid);

                $.ajax({
                    type: "post",
                    url: "/onboarding/work_experience_details/",
                    data: formData6,
                    processData: false, // Important: prevent jQuery from automatically processing the data
                    contentType: false, // Important: prevent jQuery from setting contentType
                    beforeSend: function () {
                        $(".next6").closest('fieldset').hide();
                        $(".loader-wrapper").show();
                    },
                    success: function (response) {
                        // if (response == 205) {
                        $(".loader-wrapper").hide();

                        // alert("Work Experience Details Submitted Successfully.");
                        if (btnEvent == "Next") {
                            next("next6");
                        } else {
                            $(".next6").closest('fieldset').show();
                        }
                        // }
                    }
                });
            });

            // Validation for Form 7




            $('#declaration_signature').change(function () {
                var file = this.files[0];
                var allowedExtensions = /(\.jpg|\.png|\.jpeg)$/i;

                if (!allowedExtensions.exec(file.name)) {
                    $('#declaration_signature_error').show();
                    $('#declaration_signature_error').html('Please select an jpg, png, or jpeg File.');
                    $('#declaration_signature_error').css("color", "red");
                    $("#declaration_signature").value = ''; // Clear the file input
                    $(".next7").prop("disabled", true);
                } else if (file.size > 1 * 1024 * 1024) {
                    $('#declaration_signature_error').show();
                    $('#declaration_signature_error').html('Signature file should be smaller than 1MB.');
                    $('#declaration_signature_error').css("color", "red");
                    $("#declaration_signature_error").value = ''; // Clear the file input
                    $(".next7").prop("disabled", true);
                } else {
                    $('#declaration_signature_error').hide();
                    validationFormSeven();

                }
            });

            $("#declaration, #declaration_date, #declaration_place").on('keyup change', validationFormSeven);

            function requiredValidationFormSeven() {
                var declaration = $("#declaration").val();
                var declaration_date = $("#declaration_date").val();
                var declaration_place = $("#declaration_place").val().trim();
                var declaration_signature = $("#declaration_signature")[0].files.length;

                //hide error tags
                $("#declaration_error").hide();
                $("#declaration_date_error").hide();
                $("#declaration_place_error").hide();
                $('#declaration_signature_error').hide();
                var error = true;
                console.log(declaration)
                if (declaration_date == '') {
                    $("#declaration_date_error").show();
                    $("#declaration_date_error").html("This field is required.");
                    $("#declaration_date_error").css("color", "red");
                    error = false;
                }
                if (declaration_place == '') {
                    $("#declaration_place_error").show();
                    $("#declaration_place_error").html("This field is required.");
                    $("#declaration_place_error").css("color", "red");
                    error = false;
                }
                if (!$('#declaration').is(':checked')) {
                    $("#declaration_error").show();
                    $("#declaration_error").html("This field is required.");
                    $("#declaration_error").css("color", "red");
                    error = false;
                }
                if (declaration_signature === 0) {
                    if ($("#declaration_signature").prev().length == 0) {
                        $("#declaration_signature_error").show();
                        $("#declaration_signature_error").html("Please upload signature.");
                        $("#declaration_signature_error").css("color", "red");
                        error = false;
                    }
                }
                return error;
            }

            function validationFormSeven() {
                var declaration = $("#declaration").val();
                var declaration_date = $("#declaration_date").val();
                var declaration_place = $("#declaration_place").val().trim();
                var declaration_signature = $("#declaration_signature")[0].files.length;

                //hide error tags
                $("#declaration_error").hide();
                $("#declaration_date_error").hide();
                $("#declaration_place_error").hide();

                if (!/^[a-zA-Z\s]+$/.test(declaration_place) && declaration_place != '') {
                    $("#declaration_place_error").show();
                    $("#declaration_place_error").html("Place should have only alphabetic characters.");
                    $("#declaration_place_error").css("color", "red");
                    return false;
                }
                return true;

            }

            $(".next7").click(function (event) {
                // Prevent default form submission
                event.preventDefault();

                var btnEvent = $(this).val();

                if (btnEvent == "Next") {
                    if (!requiredValidationFormSeven() || !validationFormSeven()) {
                        return false;
                    }
                }

                // Create a new FormData object
                // var formData7 = new FormData($(this).closest('form')[0]);
                // var formData7 = $(this).closest('form').serialize();
                // formData7 += '&staffid=' + staffid;

                var formData7 = new FormData($("#form7")[0]);
                formData7.append('staffid', staffid);

                $.ajax({
                    type: "post",
                    url: "/onboarding/declaration_details/",
                    data: formData7,
                    processData: false, // Important: prevent jQuery from automatically processing the data
                    contentType: false, // Important: prevent jQuery from setting contentType
                    beforeSend: function () {
                        $(".next7").closest('fieldset').hide();
                        $(".loader-wrapper").show();
                    },
                    success: function (response) {
                        $(".loader-wrapper").hide();

                        // if (response == 206) {
                        // alert("Declaration Submitted Successfully.");
                        if (btnEvent == "Next") {
                            next("next7");
                        } else {
                            $(".next7").closest('fieldset').show();
                        }
                        // }
                    }
                });
            });

            // Validation for Form 8

            validationFormEight();

            $("#emp_name, #emp_position, #emp_code, #emp_date, #emp_number, #emp_address, #emp_department, #emp_year, #emp_hand_salary, #emp_gross_salary, #emp_location, #emp_previous_document1, #emp_previous_document2, #emp_supervisior, #emp_hr_email, #emp_leaving_reason, #emp_gap1, #emp_gap2, #emp_gap_reason").on('keyup change', validationFormEight);

            function validationFormEight() {

                var emp_name = $("#emp_name").val().trim();
                var emp_position = $("#emp_position").val().trim();
                var emp_code = $("#emp_code").val().trim();
                var emp_date = $("#emp_date").val();
                var emp_number = $("#emp_number").val().trim();
                var emp_address = $("#emp_address").val().trim();
                var emp_department = $("#emp_department").val().trim();
                var emp_year = $("#emp_year").val();
                var emp_hand_salary = $("#emp_hand_salary").val().trim();
                var emp_gross_salary = $("#emp_gross_salary").val().trim();
                var emp_location = $("#emp_location").val().trim();
                var emp_previous_document1 = $("#emp_previous_document1").val().trim();
                var emp_previous_document2 = $("#emp_previous_document2").val().trim();
                var emp_supervisior = $("#emp_supervisior").val().trim();
                var emp_hr_email = $("#emp_hr_email").val().trim();
                var emp_leaving_reason = $("#emp_leaving_reason").val().trim();
                var emp_gap1 = $("#emp_gap1").is(":checked");
                var emp_gap2 = $("#emp_gap2").is(":checked");
                var emp_gap_reason = $("#emp_gap_reason").val().trim();


                //hide error tags
                $("#emp_name_error").hide();
                $("#emp_number_error").hide();
                $("#emp_department_error").hide();
                $("#emp_supervisior_error").hide();
                $("#emp_hr_email_error").hide();
                $("#gap_error").hide();
                $("#emp_gross_salary_error").hide();
                $("#emp_hand_salary_error").hide();


                if (!/^[a-zA-Z\s]+$/.test(emp_name) && emp_name != '') {
                    $("#emp_name_error").show();
                    $("#emp_name_error").html("Employee Name should have only alphabetic characters.");
                    $("#emp_name_error").css("color", "red");
                    $(".next8").prop("disabled", true);
                } else if (!isValidIndianMobileNumber(emp_number) && emp_number != '') {
                    $("#emp_number_error").show();
                    $("#emp_number_error").html("Invalid contact number.");
                    $("#emp_number_error").css("color", "red");
                    $(".next8").prop("disabled", true);
                } else if (!/^[a-zA-Z\s]+$/.test(emp_department) && emp_department != '') {
                    $("#emp_department_error").show();
                    $("#emp_department_error").html("Department Name should have only alphabetic characters.");
                    $("#emp_department_error").css("color", "red");
                    $(".next8").prop("disabled", true);
                } else if (!isDigit(emp_gross_salary) && emp_gross_salary != '') {
                    $("#emp_gross_salary_error").show();
                    $("#emp_gross_salary_error").html("Gross Salary should have only digits.");
                    $("#emp_gross_salary_error").css("color", "red");
                    $(".next8").prop("disabled", true);
                } else if (!isDigit(emp_hand_salary) && emp_hand_salary != '') {
                    $("#emp_hand_salary_error").show();
                    $("#emp_hand_salary_error").html("Hand Salary should have only digits.");
                    $("#emp_hand_salary_error").css("color", "red");
                    $(".next8").prop("disabled", true);
                } else if ((!/([A-Z0-9a-z_-][^@])+?@[^$#<>?]+?\.[\w]{2,4}/.test(emp_hr_email) || $("#email").val().trim() === emp_hr_email) && emp_hr_email != '') {
                    $("#emp_hr_email_error").show();
                    $("#emp_hr_email_error").html("Please Enter a valid email.");
                    $("#emp_hr_email_error").css("color", "red");
                    $(".next8").prop("disabled", true);
                } else if (emp_gap1 && emp_gap_reason === '') {
                    $("#gap_error").show();
                    $("#gap_error").html("Please type reason");
                    $("#gap_error").css("color", "red");
                    $(".next8").prop("disabled", true);
                } else if (!/^[a-zA-Z\s]+$/.test(emp_supervisior) && emp_supervisior != '') {
                    $("#emp_supervisior_error").show();
                    $("#emp_supervisior_error").html("Supervisior Name should have only alphabetic characters.");
                    $("#emp_supervisior_error").css("color", "red");
                    $(".next8").prop("disabled", true);
                } else {
                    $(".next8").prop("disabled", false);
                }
            }

            $(".next8").click(function (event) {
                // Prevent default form submission
                event.preventDefault();

                var btnEvent = $(this).val();

                // Create a new FormData object
                // var formData8 = new FormData($(this).closest('form')[0]);
                // var formData8 = $(this).closest('form').serialize();
                // formData8 += '&staffid=' + staffid;

                var formData8 = new FormData($("#form8")[0]);
                formData8.append('staffid', staffid);

                $.ajax({
                    type: "post",
                    url: "/onboarding/background_verification_details/",
                    data: formData8,
                    processData: false, // Important: prevent jQuery from automatically processing the data
                    contentType: false, // Important: prevent jQuery from setting contentType
                    beforeSend: function () {
                        $(".next8").closest('fieldset').hide();
                        $(".loader-wrapper").show();
                    },
                    success: function (response) {
                        $(".loader-wrapper").hide();

                        // if (response == 207) {
                        // alert("Background Verification Details Submitted Successfully.");
                        if (btnEvent == "Next") {
                            next("next8");
                        } else {
                            $(".next8").closest('fieldset').show();
                        }
                        // }
                    }
                });
            });

            // Validation for Form 9

            validationFormNine();

            $('#agreement_signature').change(function () {
                var file = this.files[0];
                var allowedExtensions = /(\.jpg|\.png|\.jpeg)$/i;

                if (!allowedExtensions.exec(file.name)) {
                    $('#agreement_signature_error').show();
                    $('#agreement_signature_error').html('Please select an jpg, png, or jpeg File.');
                    $('#agreement_signature_error').css("color", "red");
                    $("#agreement_signature_error").value = ''; // Clear the file input
                    $(".next9").prop("disabled", true);
                } else if (file.size > 1 * 1024 * 1024) {
                    $('#agreement_signature_error').show();
                    $('#agreement_signature_error').html('Signature file should be smaller than 1MB.');
                    $('#agreement_signature_error').css("color", "red");
                    $("#agreement_signature_error").value = ''; // Clear the file input
                    $(".next9").prop("disabled", true);
                } else {
                    $('#agreement_signature_error').hide();
                    validationFormNine();

                }
            });

            $("#agreement").on('keyup change', validationFormNine);

            function requiredValidationFormNine() {
                var agreement = !$("#agreement").is(":checked");
                // var applicant_name = $("#applicant_name").val().trim();
                // var agreement_date = $("#agreement_date").val();
                var agreement_signature = $("#agreement_signature")[0].files.length;
                //hide error tags
                $("#applicant_name_error").hide();
                $("#applicant_checkbox_error").hide();
                $("#applicant_date_error").hide();
                $("#agreement_signature_error").hide();
                var error = true;
                // if (agreement_date == '') {
                //     $("#applicant_date_error").show();
                //     $("#applicant_date_error").html("This field is required.");
                //     $("#applicant_date_error").css("color", "red");
                //     error = false;
                // }
                // if (applicant_name == '') {
                //     $("#applicant_name_error").show();
                //     $("#applicant_name_error").html("This field is required.");
                //     $("#applicant_name_error").css("color", "red");
                //     error = false;
                // }
                if (agreement) {
                    $("#applicant_checkbox_error").show();
                    $("#applicant_checkbox_error").html("This field is required.");
                    $("#applicant_checkbox_error").css("color", "red");
                    error = false;
                }
                if (agreement_signature === 0) {
                    if ($("#agreement_signature").prev().length == 0) {
                        $("#agreement_signature_error").show();
                        $("#agreement_signature_error").html("Upload your signature.");
                        $("#agreement_signature_error").css("color", "red");
                        error = false;
                    }
                }
                return error;
            }

            function validationFormNine() {
                var agreement = !$("#agreement").is(":checked");
                // var applicant_name = $("#applicant_name").val().trim();
                // var agreement_date = $("#agreement_date").val();
                var agreement_signature = $("#agreement_signature")[0].files.length;

                //hide error tags
                $("#applicant_name_error").hide();
                // if (!/^[a-zA-Z\s]+$/.test(applicant_name) && applicant_name != '') {
                //     $("#applicant_name_error").show();
                //     $("#applicant_name_error").html("Applicant Name should have only alphabetic characters.");
                //     $("#applicant_name_error").css("color", "red");
                //     return false;
                // }
                return true;
            }

            $(".next9").click(function (event) {
                // Prevent default form submission
                event.preventDefault();

                if (!requiredValidationFormNine() || !validationFormNine()) {
                    return;
                }
                // Create a new FormData object
                // var formData9 = new FormData($(this).closest('form')[0]);
                // var formData9 = $(this).closest('form').serialize();
                // formData9 += '&staffid=' + staffid;

                var formData9 = new FormData($("#form9")[0]);
                formData9.append('staffid', staffid);


                $.ajax({
                    type: "post",
                    url: "/onboarding/agreement_details/",
                    data: formData9,
                    processData: false, // Important: prevent jQuery from automatically processing the data
                    contentType: false, // Important: prevent jQuery from setting contentType
                    beforeSend: function () {
                        $(".next9").closest('fieldset').hide();
                        $(".loader-wrapper").show();
                    },
                    success: function (response) {
                        $(".loader-wrapper").hide();

                        // if (response == 208) {
                        // alert("Agreement Submitted Successfully.");
                        next("next9");
                        // }
                    }
                });
            });


            function next(form) {
                var form_number = "." + form;
                var current_fs = $(form_number).closest('fieldset');
                var next_fs = current_fs.next();

                // Add Class Active to progress bar
                $("#progressbar li").eq(current).addClass("active");



                // Show the next fieldset
                next_fs.show();

                // Hide the current fieldset with animation
                current_fs.animate({
                    opacity: 0
                }, {
                    step: function (now) {
                        opacity = 1 - now;
                        current_fs.css({
                            'display': 'none',
                            'position': 'relative'
                        });
                        next_fs.css({
                            'opacity': opacity
                        });
                    },
                    duration: 500
                });

                setProgressBar(++current);

            }


            function setProgressBar(curStep) {
                var percent = parseFloat(100 / steps) * curStep;
                percent = percent.toFixed();
                $(".progress-bar")
                    .css("width", percent + "%")
            }







        });




    </script>


</body>

</html>