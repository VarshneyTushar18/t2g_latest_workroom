<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf_token_name" content="<?= $this->security->get_csrf_token_name(); ?>">
    <meta name="csrf_token" content="<?= $this->security->get_csrf_hash(); ?>">
    <title>Interview Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.6.0/css/all.css">
    <link rel="shortcut icon" id="favicon" href="/uploads/company/favicon.png">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap');

        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            font-family: "Montserrat", sans-serif;
        }


        .interview-form-container .form-control,
        .interview-form-container .form-select {
            font-size: 14px !important;
            color: #222222db !important;
        }

        div#swal2-html-container input {
            width: 100%;
            margin: 4px 0 18px 0px;
            font-size: 16px;
        }

        div#swal2-html-container label {
            width: 100%;
            font-size: 16px;
            text-align: left;
        }

        div#swal2-html-container div img {
            border-radius: .1875em;
        }

        .website-details-contact {
            display: flex;
            gap: 4px;
            justify-content: start;
            align-items: center;
            margin-bottom: 4px;
        }

        .header-container {
            background-color: #141e46e6;
            color: #fff;
        }

        .website-details i {
            font-size: 14px;
        }

        label.form-label i {
            font-size: 14px;
            margin-right: 8px;
            color: #676767;
        }



        legend {
            background: #2b3458;
            padding: 5px 10px 5px 14px !important;
            color: #fff;
            font-size: 14px !important;
            position: relative;
            margin-bottom: 0px;
        }

        /* legend:before {
            content: "";
            width: 15px;
            height: 15px;
            background: #ffffff;
            position: absolute;
            left: -7px;
            top: 60%;
            transform: translate(-50%, -50%);
            -webkit-transform: translate(-50%, -50%);
            rotate: 45deg;
            
        } */

        legend::before {
            content: "";
            width: 15px;
            height: 15px;
            background: #ffffff;
            position: absolute;
            left: 0px;
            top: 50%;
            transform: translate(-50%, -50%) rotate(45deg);
            -webkit-transform: translate(-50%, -50%) rotate(45deg);
            /* Safari & older Chrome */
            -moz-transform: translate(-50%, -50%) rotate(45deg);
            /* Firefox */
            -ms-transform: translate(-50%, -50%) rotate(45deg);
            /* IE/Edge */
            box-sizing: border-box;
            /* Ensures size consistency */
        }


        fieldset {
            border: 1px solid #e4e4e4 !important;
        }

        .user-profile-container .row {
            width: 100%;
            height: 155px;
        }

        table th {
            font-size: 14px;
            font-weight: 600;
            line-height: 1.5;
            color: #222222db;
        }

        .user-profile-container {
            right: 0;
            top: 125px;
            width: 200px !important;
            display: flex;
            align-items: center;
            justify-content: end;
        }

        .user-profile-container .profile-pic {
            width: 100%;
            max-height: 100% !important;
            display: inline-block;
            height: 100%;
            object-fit: contain;
            background: #ffffff;
        }

        .user-profile-container .file-upload {
            display: none;
        }

        .tooltipLongShift:hover i {
            color: #fff !important;
        }

        .user-profile-container .circle {
            border-radius: 5px !important;
            overflow: hidden;
            height: 170px;
            border: 1px solid #e1e1e1;
        }

        .user-profile-container img {
            max-width: 100%;
            height: auto;
        }


        .user-profile-container .p-image:hover {
            transition: all .3s cubic-bezier(.175, .885, .32, 1.275);
        }

        .user-profile-container .upload-button {
            font-size: 1.2em;
            color: #222;
        }

        .website-details a {
            color: #fff;
        }

        .user-profile-container .upload-button:hover {
            transition: all .3s cubic-bezier(.175, .885, .32, 1.275);
            color: #999;
        }

        .input-group-container label {
            width: max-content;
            font-size: 14px;
            font-weight: 600;
            color: #222222db;
        }

        .input-group-container {
            margin-block: 10px;
        }

        .label-group {
            display: flex;
            align-items: center;
            margin-right: 40px;
        }

        .submit-btn {
            background-color: #141e46 !important;
            padding: 10px 20px !important;
        }

        /* .btn-outline-success {
            width: 100% !important;
        } */

        .submit-container {
            margin-top: 20px
        }

        .labelWithtooltipContainer1 .label-group {
            margin-right: 4px !important;
        }

        button#mobile_addmore_btn,
        button#mobile_addmore_btn_two {
            margin-top: -40px;
        }

        .mobile_table_inform label {
            font-weight: 600;
        }

        button#add_row,
        button#mobile_addmore_btn,
        #mobile_addmore_btn_two,
        button#add_row_two,
        button#add_row_three,
        .add_mobile_table,
        .delete_row,
        .delete_row_one,
        .delete_row_two,
        .delete_row_three,
        .remove_mobile_table,
        .remove_mobile_table_two,
        button.btn.btn-outline-success,
        button.btn.btn-outline-danger {
            width: 92px;
            padding: 5px 10px;
            font-size: 13px;
            background: #198754d6;
            color: #fff;
            font-weight: 600;
        }

        .delete_row,
        .delete_row_one,
        .delete_row_two,
        .delete_row_three,
        .remove_mobile_table,
        .remove_mobile_table_two,
        button.btn.btn-outline-danger {
            background: #e05d65 !important;
        }


        @media(max-width:992px) {

            .user-profile-container {
                top: 184px !important;
            }

            .website-details i {
                font-size: 12px;
            }

            legend,
            label,
            i {
                font-size: 12px !important;
                font-weight: 600;
            }

            .interview-form-container .form-control,
            .interview-form-container .form-select {
                font-size: 12px !important;
            }

            .user-information-details-container .container {
                margin-top: 55px !important;
            }

            .user-profile-container .circle {
                height: 145px !important;
            }

            .website-details-contact {
                flex-direction: column;
                align-items: flex-start;
                font-size: 14px;
            }

            .website-details-contact div:nth-child(2) {
                display: none;
            }
        }

        @media(max-width:768px) {

            /* .table-responsive {
                overflow-x: hidden;
            } */


            /* .comfieldmob {
        display: block;
    }
    .mobile_table_inform {
        display: none;
    } */


            button#add_row,
            button#add_row_two,
            button#add_row_three,
            .add_mobile_table,
            .delete_row,
            .delete_row_one,
            .delete_row_two,
            .delete_row_three,
            .remove_mobile_table {
                width: max-content !important;

            }

            #add_row,
            #add_row_two,
            button#add_row_three {
                display: none;
            }

            .delete_row,
            .delete_row_two,
            .delete_row_three {
                display: none;
            }



            .mobile_table label {
                font-size: 12px;
                font-weight: 600;
                padding-bottom: 4px;
            }

            .custable table {
                display: flex;
            }

            .comfieldmob,
            .comfieldmob_three,
            .comfieldmob_two {
                margin-top: -15px;
            }

            button.btn.btn-outline-success,
            button.btn.btn-outline-danger {
                width: max-content !important;

            }

         

            .custable thead,
            .custable tbody {
                width: 50%;
                min-width: 50%;
            }

            .custable tr {
                display: flex;
                flex-direction: column;
            }

            .custable th,
            .custable td {
                height: 50px;
                display: flow;
                font-size: 12px !important;
                border-bottom: 0px;
            }

            fieldset {
                border: 0px solid #e4e4e4 !important;
            }

            address,
            p {
                font-size: 14px;
            }

            .px-4 {
                padding-right: 0px !important;
                padding-left: 0px !important;
            }

            .header-container {
                margin-bottom: 55px;
            }

            table {
                overflow-x: scroll;
            }

            .header-container .w-25 {
                width: 50% !important;
            }

            .label-group {
                margin-right: 0px;
            }

            .submit-container {
                margin-top: 0px;
            }

            .user-profile-container {
                top: 228px !important;
            }

            .user-information-details-container .container {
                margin-top: 10px !important;
            }

            .user-profile-container {
                top: 260px;
            }

            legend {

                font-size: 12px !important;
            }

            .user-profile-container {
                width: 157px !important;
            }

            .input-group-container label {
                width: 100%;
                font-size: 14px;
            }
        }

        @media(max-width:475px) {
            .user-profile-container {
                top: 239px !important;
                width: 130px !important;
            }

            .user-information-details-container .container {
                margin-top: 12px !important;
            }

            .user-profile-container .circle {
                height: 108px !important;
            }

            .p-image {
                top: 85px !important;
            }


        }
    </style>
</head>

<body>
    <main class="interview-form-container">
        <div class="container-fluid header-container">
            <div class="container">
                <div class="topbar">
                    <header class="position-relative">
                        <div class="col-lg-10 py-4">
                            <div class="logo w-25">
                                <img class="w-100" src="/uploads/interview-form-images//tech2globe-logo.png"
                                    alt="tech2globe">
                            </div>
                            <div class="website-details">
                                <div class="website-details-contact">
                                    <div>
                                        <i class="fa-solid fa-link"></i>&nbsp; Website <a href="https://tech2globe.com/"
                                            target="_blank">www.tech2globe.com</a>
                                    </div>
                                    <div>
                                        &nbsp;||&nbsp;
                                    </div>
                                    <div>
                                        <i class="fa-regular fa-envelope"></i>&nbsp; Email <a
                                            href="mailto:career@tech2globe.com">career@tech2globe.com</a>
                                    </div>
                                </div>
                                <address class="mb-1"><i class="fa-regular fa-building"></i>&nbsp; &nbsp; 606, 6th
                                    Floor, Pearls Omaxe Tower-1, Netaji Subhash Place, New Delhi, 110034
                                </address>
                                <address class="mb-0"><i class="fa-regular fa-building"></i>&nbsp; &nbsp; 701, 7th
                                    Floor, Tower B, Logix Cyber Park, C Block, Phase 2, Sector 62, Noida,
                                    Uttar Pradesh, 201301
                                </address>
                            </div>
                        </div>
                        <?php echo form_open('interview_form/submit_interview_details', ['id' => 'interview_form']) ?>
                        <div class="col-lg-2 position-absolute user-profile-container">
                            <div class="row">
                                <div class="small-12 medium-2 large-2 columns position-relative">
                                    <div class="circle">
                                        <img class="profile-pic" src="/uploads/interview-form-images//user-icon.jpg">

                                    </div>
                                    <div class="p-image position-absolute bottom-0 end-0">
                                        <i class="fa fa-camera upload-button"></i>
                                        <input class="file-upload" type="file" accept="image/*" name="profile-pic" id="profileImg" />
                                        <input type="hidden" id="oldProfileImg" name="old-profile-pic" value="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </header>
                </div>
            </div>
        </div>
        <div class="container-fluid py-4 user-information-details-container my-3">
            <div class="container">
                <h1 class="text-uppercase fw-bold fw-bold fs-3 mb-4 mt-3">Interview Form</h1>
                <fieldset class="px-4 py-3 mb-4">
                    <legend class="text-uppercase float-none w-auto">Personal Information</legend>
                    <div class="row">
                        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-12">
                            <div class="input-group-container">
                                <div class="label-group"><label for="" class="form-label"><i
                                            class="fa-solid fa-user"></i> Full Name <span
                                            style="color: #c01f29;">*</span></label></div>
                                <input type="text" class="form-control" id="userName" name="name" required>
                            </div>
                        </div>
                        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-12">
                            <div class="input-group-container">
                                <div class="label-group"><label for="" class="form-label"><i
                                            class="fa-regular fa-calendar-days"></i> Date <span
                                            style="color: #c01f29;">*</span></label></div>
                                <input type="text" class="form-control" id="date" name="date" required
                                    value="<?= date('d-m-Y') ?>" readonly>
                            </div>
                        </div>

                        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-12">
                            <div class="input-group-container">
                                <div class="label-group"><label for="" class="form-label"><i
                                            class="fa-solid fa-magnifying-glass"></i> Position Applied <span
                                            style="color: #c01f29;">*</span></label>
                                </div>

                                <select name="position" id="positionApplied" class="form-control selectpicker"
                                    data-live-search="true" data-width="100%" required>
                                    <option value="">--Select Job Position--</option>
                                    <?php foreach ($job_positions as $s) { ?>
                                        <option value="<?php echo html_entity_decode(
                                            $s["position_id"]
                                        ); ?>"><?php echo html_entity_decode(
                                             $s["position_name"]
                                         ); ?></option>
                                    <?php } ?>
                                </select>
                                <!-- <input type="text" class="form-control" id="positionApplied" name="position" required> -->
                            </div>

                        </div>

                        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-12">
                            <div class="input-group-container">
                                <div class="label-group"><label for="" class="form-label"><i
                                            class="fa-solid fa-user-group"></i> Referred By</label>
                                </div>
                                <input type="text" class="form-control" id="referredBy" name="refer_by">
                            </div>

                        </div>

                        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-12">
                            <div class="input-group-container">
                                <div class="label-group"><label for="" class="form-label"><i
                                            class="fa-regular fa-calendar-days"></i> Date of Birth <span
                                            style="color: #c01f29;">*</span>
                                    </label></div>
                                <input type="date" class="form-control" id="userDob" name="dob" required>
                            </div>
                        </div>
                        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-12">
                            <div class="input-group-container">
                                <div class="label-group"><label for="" class="form-label"><i
                                            class="fa-solid fa-envelope"></i> Email <span
                                            style="color: #c01f29;">*</span></label></div>
                                <input type="text" class="form-control" id="userEmail" name="email" required readonly>
                            </div>
                        </div>

                        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-12">
                            <div class="input-group-container">
                                <div class="label-group"><label for="" class="form-label"><i
                                            class="fa-solid fa-location-dot"></i> Mode of Travel <span
                                            style="color: #c01f29;">*</span>
                                    </label></div>
                                <select class="form-select" aria-label="Default select example" name="travel_mode"
                                    id="travel_mode" required>
                                    <option value="">--Select Travel Mode--</option>
                                    <option value="Bus">Bus</option>
                                    <option value="Metro">Metro</option>
                                    <option value="Own Vehicle">Own Vehicle</option>
                                    <option value="Other">Other</option>


                                </select>
                            </div>
                        </div>
                        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-12">
                            <div class="input-group-container">
                                <div class="label-group"><label for="" class="form-label"><i
                                            class="fa-solid fa-road"></i> Home Distance (In KM) <span
                                            style="color: #c01f29;">*</span>
                                    </label></div>
                                <input type="number" class="form-control" id="userDistance" name="home_distance"
                                    required min="0">
                            </div>
                        </div>
                        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-12">
                            <div class="input-group-container">
                                <div class="label-group"><label for="" class="form-label"><i
                                            class="fa-solid fa-clock"></i> Travelling Hour <span
                                            style="color: #c01f29;">*</span>
                                    </label></div>
                                <input type="number" class="form-control" id="userTravellingHour" name="travelling_hour"
                                    required min="0">
                            </div>
                        </div>
                        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-12">
                            <div class="input-group-container">
                                <div class="label-group"><label for="" class="form-label"><i
                                            class="fa-solid fa-city"></i> City <span
                                            style="color: #c01f29;">*</span></label></div>
                                <input type="text" class="form-control" id="userCity" name="city" required>
                            </div>
                        </div>
                        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-12">
                            <div class="input-group-container">
                                <div class="label-group"><label for="" class="form-label"><i
                                            class="fa-solid fa-location-dot"></i> Pin <span
                                            style="color: #c01f29;">*</span></label></div>
                                <input type="text" class="form-control" id="userPin" name="pin" required>
                            </div>
                        </div>
                        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-12">
                            <div class="input-group-container">
                                <div class="label-group"><label for="" class="form-label"><i
                                            class="fa-solid fa-user-group"></i> Marital Status <span
                                            style="color: #c01f29;">*</span>
                                    </label></div>
                                <select class="form-select" aria-label="Default select example" name="marital_status"
                                    required>
                                    <option value="">--Select Marital Status--</option>
                                    <option value="Single">Single</option>
                                    <option value="Married">Married</option>
                                    <option value="Separated">Separated</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-12">
                            <div class="input-group-container">
                                <div class="label-group"><label for="" class="form-label"><i
                                            class="fa-solid fa-phone"></i> Mobile No 1 <span
                                            style="color: #c01f29;">*</span></label>
                                </div>
                                <input type="number" class="form-control" id="userMobile1" name="mobile_no_1" required
                                    maxlength="10" readonly>
                            </div>
                        </div>
                        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-12">
                            <div class="input-group-container">
                                <div class="label-group"><label for="" class="form-label"><i
                                            class="fa-solid fa-phone"></i> Mobile No 2 </label></div>
                                <input type="number" class="form-control" id="userMobile2" name="mobile_no_2"
                                    maxlength="10">
                            </div>
                        </div>
                        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-12">
                            <div class="input-group-container">
                                <div class="label-group"><label for="" class="form-label"><i
                                            class="fa-solid fa-phone"></i> Mobile No 3 </label></div>
                                <input type="number" class="form-control" id="userMobile3" name="mobile_no_3"
                                    maxlength="10">
                            </div>
                        </div>

                        <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-12">
                            <div class="input-group-container">
                                <div class="label-group"><label for="" class="form-label"><i
                                            class="fa-solid fa-location-dot"></i> Address <span
                                            style="color: #c01f29;">*</span></label></div>
                                <textarea class="form-control" rows="2" id="userAddress" name="address"
                                    required></textarea>
                            </div>
                        </div>


                    </div>
                </fieldset>

                <div class="row">
                    <fieldset class="px-4 py-3 mb-4 mt-4 custable">
                        <legend class="text-uppercase float-none w-auto">Academic Details (Two Highest Qualifications)
                        </legend>
                        <div class="table-responsive">
                            <table id="add_table" class="table" data-bs-toggle="table" data-mobile-responsive="true">
                                <thead>
                                    <tr>
                                        <th>From <span style="color: #c01f29;">*</span></th>
                                        <th>To <span style="color: #c01f29;">*</span></th>
                                        <th>Name of Degree/Diploma <span style="color: #c01f29;">*</span></th>
                                        <th>University Name<span style="color: #c01f29;">*</span></th>
                                        <th>Subjects <span style="color: #c01f29;">*</span></th>
                                        <th>%Marks/Grade <span style="color: #c01f29;">*</span></th>
                                        <th>Regular/Correspondence <span style="color: #c01f29;">*</span></th>
                                        <th>Status of Degree <span style="color: #c01f29;">*</span></th>
                                        <th>
                                            <button type="button" class="btn btn-outline-success" id="add_row"
                                                class="add"> Add More
                                            </button>
                                        </th>

                                    </tr>
                                </thead>
                                <tbody class="table_body">
                                    <tr>
                                        <td>
                                            <input type="text" class="form-control" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" required>
                                        </td>
                                        <td>
                                            <select class="form-control">
                                                <option value="">Please Select</option>
                                                <option value="Completed">Completed</option>
                                                <option value="Currently Pursuing">Currently Pursuing</option>
                                            </select>
                                        </td>
                                        <td>
                                            <button type="button"
                                                class="btn btn-outline-danger delete_row">Remove</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="comfieldmob">
                            <button type="button" class="btn btn-outline-success" id="mobile_addmore_btn" class="add">
                                Add More
                            </button>
                            <div class="mobile_table_inform mobile_table_one border p-3 mt-3">

                                <label>From <span style="color: #c01f29;">*</span></label>
                                <input type="text" class="form-control mb-2">

                                <label>To <span style="color: #c01f29;">*</span></label>
                                <input type="text" class="form-control mb-2">

                                <label>Name of Degree/Diploma <span style="color: #c01f29;">*</span></label>
                                <input type="text" class="form-control mb-2">

                                <label>University Name <span style="color: #c01f29;">*</span></label>
                                <input type="text" class="form-control mb-2">

                                <label>Subjects <span style="color: #c01f29;">*</span></label>
                                <input type="text" class="form-control mb-2">

                                <label>%Marks/Grade <span style="color: #c01f29;">*</span></label>
                                <input type="text" class="form-control mb-2">

                                <label>Regular/Correspondence <span style="color: #c01f29;">*</span></label>
                                <input type="text" class="form-control mb-2">

                                <label>Status of Degree <span style="color: #c01f29;">*</span></label>
                                <select class="form-control mb-2">
                                    <option value="">Please Select</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Currently Pursuing">Currently Pursuing</option>
                                </select>

                                <button type="button"
                                    class="btn btn-outline-success add_mobile_table add_mobile_table_one"
                                    id="add_mobile_table_one_id">Add More</button>
                                <button type="button" class="btn btn-outline-danger remove_mobile_table"
                                    id="remove_mobile_table_one_id">Remove</button>
                            </div>
                        </div>
                    </fieldset>
                </div>


                <div class="row">
                    <fieldset class="px-4 py-3 mb-4 mt-4 custable">
                        <legend class="text-uppercase float-none w-auto">Professional experience details (Most Recent
                            Three Experiences)
                        </legend>

                        <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-12">
                            <div class="input-group-container">

                                <div class="label-group"><label for="" class="form-label"><i
                                            class="fa-solid fa-lightbulb"></i>Choose your Level of Experience <span
                                            style="color: #c01f29;">*</span></label></div>

                                <div class="row ps-2">
                                    <div class="col-xxl-2 col-xl-6 col-lg-6 col-md-6 col-6 form-check">
                                        <input class="form-check-input" type="checkbox" value="fresher"
                                            id="fresherCheckbox" name="">
                                        <label class="form-check-label" for="">
                                            Fresher
                                        </label>
                                    </div>
                                    <div class="col-xxl-2 col-xl-6 col-lg-6 col-md-6 col-6 form-check">
                                        <input class="form-check-input" type="checkbox" value="experienced"
                                            id="experiencedCheckbox" name="">
                                        <label class="form-check-label" for="">
                                            Experienced
                                        </label>
                                    </div>

                                </div>

                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="add_table_two" class="table" data-bs-toggle="table_two"
                                data-mobile-responsive="true">
                                <thead>
                                    <tr>
                                        <th>Name of the Firm </th>
                                        <th>Designation </th>
                                        <th>From </th>
                                        <th>To </th>
                                        <th>CTC </th>
                                        <th>Incentives </th>

                                        <th>Key Responsibilities </th>
                                        <th>Reason for Leaving </th>
                                        <th>
                                            <button type="button" class="btn btn-outline-success" id="add_row_two"
                                                class="add"> Add More
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="table_body_two">
                                    <tr class="table_row_second">
                                        <td>
                                            <input type="text" class="form-control">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control">
                                        </td>
                                        <td>
                                            <textarea class="form-control" rows="1"></textarea>
                                        </td>
                                        <td>
                                            <button type="button"
                                                class="btn btn-outline-danger delete_row_two">Remove</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="comfieldmob_two">
                                <button type="button" class="btn btn-outline-success" id="mobile_addmore_btn_two"
                                    class="add">
                                    Add More
                                </button>
                                <div class="mobile_table_inform_two mobile_table_onepone border p-3 mt-3">

                                    <label>Name of the Firm </label>
                                    <input type="text" class="form-control mb-2">

                                    <label>Designation </label>
                                    <input type="text" class="form-control mb-2">

                                    <label>From </label>
                                    <input type="text" class="form-control mb-2">

                                    <label>To </label>
                                    <input type="text" class="form-control mb-2">

                                    <label>CTC </label>
                                    <input type="text" class="form-control mb-2">

                                    <label>Incentives </label>
                                    <input type="text" class="form-control mb-2">

                                    <label>Key Responsibilities </label>
                                    <input type="text" class="form-control mb-2">

                                    <label>Reason for Leaving </label>
                                    <textarea class="form-control mb-2" rows="1"></textarea>

                                    <button type="button"
                                        class="btn btn-outline-success add_mobile_table add_mobile_table_two"
                                        id="add_mobile_table_two_id">Add More</button>
                                    <button type="button" class="btn btn-outline-danger remove_mobile_table_two"
                                        id="remove_mobile_table_two_id">Remove</button>
                                </div>
                            </div>
                        </div>

                    </fieldset>
                </div>


                <div class="row">
                    <fieldset class="px-4 py-3 mb-4 mt-4">
                        <legend class="text-uppercase float-none w-auto">About Tech2Globe
                        </legend>

                        <div class="row">
                            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-12">
                                <div class="input-group-container">
                                    <div class="label-group"><label for="" class="form-label"><i
                                                class="fa-solid fa-lightbulb"></i> Reason to Join
                                            Tech2Globe <span style="color: #c01f29;">*</span></label></div>
                                    <textarea class="form-control" rows="2" name="reason_to_join" required></textarea>
                                </div>
                            </div>
                            <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-12">
                                <div class="input-group-container">
                                    <div class="label-group"><label for="" class="form-label"><i
                                                class="fa-solid fa-user-shield"></i> If selected, when can you
                                            join? <span style="color: #c01f29;">*</span></label></div>
                                    <!-- <input type="text" class="form-control" id="" name="when_join" required> -->
                                    <select class="form-select" id="" name="when_join" required>
                                        <option value="">Select Time Period</option>
                                        <option value="Immediately">Immediately</option>
                                        <option value="Within a week">Within a week</option>
                                        <option value="Within two weeks">Within two weeks</option>
                                        <option value="Within one month">Within one month</option>
                                        <option value="Within two months">Within two months</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-12">
                                <div class="input-group-container">
                                    <div class="label-group"><label for="" class="form-label"><i
                                                class="fa-solid fa-laptop"></i> Do you own a laptop? If yes, please
                                            mention the configuration. <span style="color: #c01f29;">*</span></label>
                                    </div>
                                    <input type="text" class="form-control" id="" name="own_system" required>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>



                <div class="row">
                    <fieldset class="px-4 py-3 mb-4 mt-4 custable">
                        <legend class="text-uppercase float-none w-auto">Reference</legend>
                        <div class="table-responsive">
                            <table id="add_table_three" class="table" data-bs-toggle="table"
                                data-mobile-responsive="true">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Contact No</th>
                                        <th>Email ID</th>
                                        <th>Designation</th>
                                        <th>Employee ID</th>
                                        <!-- <th>Education Qualification</th> -->
                                        <th>
                                            <button type="button" class="btn btn-outline-success" id="add_row_three"
                                                class="add"> Add More
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="table_body_three">
                                    <tr>
                                        <td><input type="text" class="form-control"></td>
                                        <td><input type="text" class="form-control"></td>
                                        <td><input type="text" class="form-control"></td>
                                        <td><input type="text" class="form-control"></td>
                                        <td><input type="text" class="form-control"></td>
                                        <!-- <td><input type="text" class="form-control"></td> -->
                                        <td><button type="button"
                                                class="btn btn-outline-danger delete_row_three">Remove</button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="comfieldmob_three">
                            <button type="button" class="btn btn-outline-success" id="mobile_addmore_btn_three"
                                class="add">
                                Add More
                            </button>
                            <div class="mobile_table_inform_three mobile_table_onepthree border p-3 mt-3">
                                <label>Name</label>
                                <input type="text" class="form-control mb-2">

                                <label>Contact No</label>
                                <input type="text" class="form-control mb-2">

                                <label>Email ID</label>
                                <input type="text" class="form-control mb-2">

                                <label>Designation</label>
                                <input type="text" class="form-control mb-2">

                                <label>Employee ID</label>
                                <input type="text" class="form-control mb-2">

                                <button type="button"
                                    class="btn btn-outline-success add_mobile_table add_mobile_table_three"
                                    id="add_mobile_table_three_id">Add More</button>
                                <button type="button" class="btn btn-outline-danger remove_mobile_table_three"
                                    id="remove_mobile_table_three_id">Remove</button>
                            </div>
                        </div>
                    </fieldset>
                </div>


                <div class="row">
                    <fieldset class="px-4 py-3 mb-4 mt-4">
                        <legend class="text-uppercase float-none w-auto">Important Info
                        </legend>

                        <div class="row">

                            <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-12">
                                <div class="input-group-container">
                                    <div class="label-group"><label for="" class="form-label"><i
                                                class="fa-solid fa-user-clock"></i> Last working day ? </label></div>
                                    <input type="text" class="form-control" id="userStatus" name="user_status">
                                </div>

                            </div>
                            <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-12">
                                <div class="input-group-container">
                                    <div class="label-group"><label for="" class="form-label"><i
                                                class="fa-solid fa-brain"></i> Any health problems in last 3 months?
                                            (Please specify)
                                        </label></div>
                                    <input type="text" class="form-control" id="userHealthIssue" name="health_issues">
                                </div>

                            </div>

                            <!--<div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-12">
                                <div class="input-group-container">

                                    <div class="labelWithtooltipContainer labelWithtooltipContainer1 d-flex align-items-center justify-content-start">

                                        <div class="label-group"><label for="" class="form-label me-1"><i
                                                    class="fa-solid fa-user-clock"></i> Agreed to overtime
                                                (Whenever Required)</label>
                                        </div>
                                        <div class="tooltip-maincontainer d-flex align-items-center gap-2">
                                            <span class="tooltipcontainer" style="margin-top: -3px;" class="btn btn-secondary" data-bs-container="body" data-bs-toggle="popover"
                                                data-bs-placement="right" data-bs-content="Lorem ipsum dolor sit amet, consectetur adipisicing elit. Autem, recusandae.">

                                                <i class="fa-solid fa-circle-info"></i>
                                            </span>

                                        </div>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="overtime"
                                            id="inlineRadio1" value="yes" />
                                        <label class="form-check-label" for="inlineRadio1">Yes</label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="overtime"
                                            id="inlineRadio2" value="no" />
                                        <label class="form-check-label" for="inlineRadio2">No</label>
                                    </div>
                                </div>
                            </div> -->


                            <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-12">
                                <div class="input-group-container">
                                    <div
                                        class="labelWithtooltipContainer d-flex align-items-center justify-content-start">

                                        <div class="label-group me-2"><label for="" class="form-label me-1"><i
                                                    class="fa-solid fa-user-clock"></i> Flexible for long
                                                shifts ?</label>
                                        </div>
                                        <div class="tooltip-maincontainer d-flex align-items-center gap-2">
                                            <span class="tooltipcontainer" style="margin-top: -3px;"
                                                class="btn btn-secondary" data-bs-container="body"
                                                data-bs-toggle="popover" data-bs-placement="right"
                                                data-bs-content=" Willingness and ability to work extended hours or non-standard shifts when required. This includes the capacity to adapt to varying schedules based on the needs of the organization.">
                                                <i class="fa-solid fa-circle-info"></i>
                                            </span>

                                        </div>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="long_shift" id="inlineRadio1"
                                            value="yes" />
                                        <label class="form-check-label" for="inlineRadio1">Yes</label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="long_shift" id="inlineRadio2"
                                            value="no" />
                                        <label class="form-check-label" for="inlineRadio2">No</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-12">
                                <div class="input-group-container">

                                    <div class="label-group"><label for="" class="form-label"><i
                                                class="fa-solid fa-lightbulb"></i>Shift Preferences <span
                                                style="color: #c01f29;">*</span></label></div>

                                    <div class="row ps-2">
                                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-12 form-check">
                                            <input class="form-check-input" type="checkbox" value="morning"
                                                id="flexCheckDefaultOne" name="shift_preferences[]">
                                            <label class="form-check-label" for="flexCheckDefault">
                                                Morning (between 9 am-8 pm IST)
                                            </label>
                                        </div>
                                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-12 form-check">
                                            <input class="form-check-input" type="checkbox" value="afternoon"
                                                id="flexCheckDefaultTwo" name="shift_preferences[]">
                                            <label class="form-check-label" for="flexCheckDefault">
                                                Afternoon (between 3 pm-11pm IST)
                                            </label>
                                        </div>
                                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-12 form-check">
                                            <input class="form-check-input" type="checkbox" value="evening"
                                                id="flexCheckDefaultThree" name="shift_preferences[]">
                                            <label class="form-check-label" for="flexCheckDefault">
                                                Evening (between 5 pm-6 am IST)
                                            </label>
                                        </div>
                                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-12 form-check">
                                            <input class="form-check-input" type="checkbox" value="flexible"
                                                id="flexCheckDefaultFour" name="shift_preferences[]">
                                            <label class="form-check-label" for="flexCheckDefault">
                                                Flexible with any shift
                                            </label>
                                        </div>
                                    </div>


                                </div>
                            </div>

                        </div>
                    </fieldset>
                </div>

                <div class="row">
                    <fieldset class="px-4 py-3 mb-4 mt-4">
                        <legend class="text-uppercase float-none w-auto">Upload Document
                        </legend>

                        <div class="row">

                            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-12">
                                <div class="input-group-container">
                                    <div class="label-group"><label for="" class="form-label"><i
                                                class="fa-solid fa-user-clock"></i> Upload Resume <span
                                                style="color: #c01f29;">*</span></label></div>
                                    <input type="file" class="form-control" id="resume" name="resume" required
                                        style="margin-bottom: 10px;">
                                    <span style="color: #c01f29;">Only pdf, jpg, jpeg, and docx of size less than 500kb
                                        are acceptable.</span>
                                </div>
                            </div>

                        </div>
                    </fieldset>
                </div>

                <div class="row submit-container">
                    <div class="col-12 p-0">
                        <input type="hidden" name="candidate_id" id="candidate_id">
                        <input type="hidden" name="table_data_one" id="table_data_one">
                        <input type="hidden" name="table_data_two" id="table_data_two">
                        <input type="hidden" name="table_data_three" id="table_data_three">
                        <button type="submit" class="btn submit-btn text-light" id="formSubmitBtn">Submit Information &nbsp; <i
                                class="fa-solid fa-caret-right text-light"></i> </button>
                    </div>
                </div>

                </form>

            </div>

        </div>


    </main>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>


    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // function generateCaptcha() {
        //     const canvas = document.createElement('canvas');
        //     canvas.width = 150;
        //     canvas.height = 50;
        //     const ctx = canvas.getContext('2d');
        //     ctx.fillStyle = 'lightgray';
        //     ctx.fillRect(0, 0, canvas.width, canvas.height);

        //     // Generate random text for the CAPTCHA
        //     const captchaText = Math.random().toString(36).substring(2, 7);
        //     ctx.font = '20px Arial';
        //     ctx.fillStyle = 'black';
        //     ctx.fillText(captchaText, 30, 30);

        //     return {
        //         imageUrl: canvas.toDataURL(),
        //         text: captchaText
        //     };
        // }

        // const {
        //     imageUrl,
        //     text: generatedCaptchaText
        // } = generateCaptcha();


        function showEmailAlert() {
            Swal.fire({
                title: 'Interview Sign Up',
                html: `
                    <label for="swal-input-email">Email Id:</label>
                    <input type="email" id="swal-input-email" class="swal2-input" placeholder="Enter your email address">
                    
                    <label for="swal-input-phone">Phone No:</label>
                    <input type="tel" id="swal-input-phone" class="swal2-input" placeholder="Enter your phone number">
                    
                    <div id="cf-turnstile-container" data-theme="light"></div>
                `,
                focusConfirm: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    turnstile.render('#cf-turnstile-container', {
                        sitekey: '0x4AAAAAAA-Z9eL9o5fgn_yP', // Replace with your actual site key
                        callback: function (token) {
                            document.getElementById('cf-turnstile-container').setAttribute('data-token', token);
                        }
                    });
                },
                confirmButtonText: 'Proceed',
                showCancelButton: false,
                confirmButtonColor: '#141E46',
                backdrop: `rgba(0,0,0,0.5)`,
                preConfirm: () => {
                    const email = document.getElementById('swal-input-email').value.trim();
                    const phone = document.getElementById('swal-input-phone').value.trim();
                    const captchaToken = document.getElementById('cf-turnstile-container').getAttribute('data-token');

                    if (!email || !validateEmail(email)) {
                        Swal.showValidationMessage('Please enter a valid email address!');
                        return false;
                    }
                    if (!phone || !validatePhone(phone)) {
                        Swal.showValidationMessage('Please enter a valid phone number!');
                        return false;
                    }
                    if (!captchaToken) {
                        Swal.showValidationMessage('Please complete the CAPTCHA!');
                        return false;
                    }

                    return new Promise((resolve, reject) => {
                        const csrfName = $('meta[name="csrf_token_name"]').attr('content'); // CSRF token name
                        const csrfHash = $('meta[name="csrf_token"]').attr('content'); // CSRF token value

                        $.ajax({
                            url: 'interview_form/check_email_or_phone', // Replace with your CI Controller URL
                            type: 'POST',
                            data: {
                                email: email,
                                phone: phone,
                                cf_token: captchaToken, // Sending CAPTCHA token
                                [csrfName]: csrfHash // CSRF protection
                            },
                            success: function (response) {
                                console.log(response);
                                const result = JSON.parse(response);
                                if (result.user) {
                                    resolve({
                                        user: result.user,
                                        exists: true
                                    });
                                } else {
                                    resolve({
                                        email: email,
                                        phone: phone,
                                        exists: false
                                    });
                                }
                            },
                            error: function () {
                                Swal.showValidationMessage('An error occurred while checking the email!');
                                reject();
                            }
                        });
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    if (result.value.exists) {
                        // Populate existing user details
                        const user = result.value.user;
                        console.log(user);
                        document.getElementById('userEmail').value = user.email;
                        document.getElementById('userMobile1').value = user.phonenumber;
                        document.getElementById('userName').value = `${user.candidate_name} ${user.last_name}`;
                        document.getElementById('positionApplied').value = user.job_position;
                        document.getElementById('candidate_id').value = user.id;
                        document.getElementById('referredBy').value = user.refer_by;
                        document.getElementById('userDob').value = user.birthday;
                        document.getElementById('travel_mode').value = user.travel_mode;
                        document.getElementById('date').value = user.date_add;
                        document.getElementById('userDistance').value = user.home_distance;
                        if(user.profile_img != null){
                            $('.profile-pic').attr('src', 'uploads/candidate_profile_images/' + user.profile_img);
                            $("#oldProfileImg").val(user.profile_img);
                        }

                        Swal.fire({
                            title: 'Found in Records!',
                            text: 'Your details have been pre-filled. You can update them if needed.',
                            icon: 'info',
                            confirmButtonText: 'Continue',
                            confirmButtonColor: '#141E46'
                        });
                    } else {
                        // Set new user details
                        document.getElementById('userEmail').value = result.value.email;
                        document.getElementById('userMobile1').value = result.value.phone;
                        Swal.fire({
                            title: 'Success!',
                            text: `Your email (${result.value.email}) and phone number (${result.value.phone}) have been submitted.`,
                            icon: 'success',
                            confirmButtonText: 'Proceed',
                            confirmButtonColor: '#141E46'
                        });
                    }
                }
            });
        }

        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(String(email).toLowerCase());
        }

        function validatePhone(phone) {
            const re = /^[0-9]{10}$/; // Validates 10-digit numbers
            return re.test(String(phone));
        }

        // Show SweetAlert on page load
        window.onload = function () {
            showEmailAlert();
        };

    </script>




    <!-- first module mobile add more -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let counter = 0; // Counter for unique table IDs

            const comField = document.querySelector(".comfieldmob");
            const mobileAddMoreBtn = document.querySelector("#mobile_addmore_btn");
            const mobileTableOne = document.querySelector(".mobile_table_one");

            // Hide the main container and first table by default
            comField.style.display = "none";
            if (mobileTableOne) {
                mobileTableOne.style.display = "none";
            }

            // Check screen size and adjust visibility
            function checkScreenSize() {
                if (window.innerWidth < 769) {
                    comField.style.display = "block"; // Show only button on small screens
                } else {
                    comField.style.display = "none";
                }
            }
            checkScreenSize();
            window.addEventListener("resize", checkScreenSize);

            // Function to create a new table
            function addNewTable(afterElement = null) {
                counter++; // Increment counter for unique IDs

                let newTable = document.createElement("div");
                newTable.className = "mobile_table_inform border p-3 mt-3";
                newTable.setAttribute("id", `mobile_table_${counter}`);
                newTable.style.display = "block"; // Ensure table is visible when added

                newTable.innerHTML = `
            <label>From <span style="color: #c01f29;">*</span></label>
            <input type="text" class="form-control mb-2">

            <label>To <span style="color: #c01f29;">*</span></label>
            <input type="text" class="form-control mb-2">

            <label>Name of Degree/Diploma <span style="color: #c01f29;">*</span></label>
            <input type="text" class="form-control mb-2">

            <label>University Name <span style="color: #c01f29;">*</span></label>
            <input type="text" class="form-control mb-2">

            <label>Subjects <span style="color: #c01f29;">*</span></label>
            <input type="text" class="form-control mb-2">

            <label>%Marks/Grade <span style="color: #c01f29;">*</span></label>
            <input type="text" class="form-control mb-2">

            <label>Regular/Correspondence <span style="color: #c01f29;">*</span></label>
            <input type="text" class="form-control mb-2">

            <label>Status of Degree <span style="color: #c01f29;">*</span></label>
            <select class="form-control mb-2">
                <option value="">Please Select</option>
                <option value="Completed">Completed</option>
                <option value="Currently Pursuing">Currently Pursuing</option>
            </select>

            <button type="button" class="btn btn-outline-success add_mobile_table_one">Add More</button>
            <button type="button" class="btn btn-outline-danger remove_mobile_table">Remove</button>
        `;

                if (afterElement) {
                    afterElement.after(newTable);
                } else {
                    document.querySelector(".comfieldmob").appendChild(newTable);
                }
            }

            // Click event listener
            document.body.addEventListener("click", function (event) {
                // When clicking the #mobile_addmore_btn button
                if (event.target.matches("#mobile_addmore_btn")) {
                    let existingTables = document.querySelectorAll(".mobile_table_inform");

                    if (existingTables.length === 0) {
                        // If no tables exist, create a new one immediately
                        addNewTable();
                    } else if (mobileTableOne.style.display === "none") {
                        // If the default table is hidden, show it
                        mobileTableOne.style.display = "block";
                    } else {
                        // Otherwise, add a new table
                        addNewTable();
                    }
                }

                // When clicking "Add More" inside a table
                else if (event.target.matches(".add_mobile_table_one")) {
                    let currentTable = event.target.closest(".mobile_table_inform");
                    addNewTable(currentTable);
                }

                // When clicking "Remove" inside a table
                else if (event.target.matches(".remove_mobile_table")) {
                    let tableToRemove = event.target.closest(".mobile_table_inform");
                    if (tableToRemove) {
                        tableToRemove.remove();

                        // If all tables are removed, hide the default table
                        if (document.querySelectorAll(".mobile_table_inform").length === 0) {
                            mobileTableOne.style.display = "none";
                        }
                    }
                }
            });
        });

    </script>
    <!-- first module end -->


    <!-- second module mobile add more -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let counterTwo = 0; // Counter for unique table IDs in module two

            const comFieldTwo = document.querySelector(".comfieldmob_two");
            const mobileAddMoreBtnTwo = document.querySelector("#mobile_addmore_btn_two");
            const mobileTableOnePone = document.querySelector(".mobile_table_onepone");

            // Hide the main container and first table by default
            comFieldTwo.style.display = "none";
            if (mobileTableOnePone) {
                mobileTableOnePone.style.display = "none";
            }

            // Check screen size and adjust visibility
            function checkScreenSizeTwo() {
                if (window.innerWidth < 769) {
                    comFieldTwo.style.display = "block"; // Show only button on small screens
                } else {
                    comFieldTwo.style.display = "none";
                }
            }
            checkScreenSizeTwo();
            window.addEventListener("resize", checkScreenSizeTwo);

            // Function to create a new table
            function addNewTableTwo(afterElement = null) {
                counterTwo++; // Increment counter for unique IDs

                let newTableTwo = document.createElement("div");
                newTableTwo.className = "mobile_table_inform_two border p-3 mt-3";
                newTableTwo.setAttribute("id", `mobile_table_two_${counterTwo}`);
                newTableTwo.style.display = "block"; // Ensure table is visible when added

                newTableTwo.innerHTML = `
            <label>Name of the Firm </label>
            <input type="text" class="form-control mb-2">

            <label>Designation </label>
            <input type="text" class="form-control mb-2">

            <label>From </label>
            <input type="text" class="form-control mb-2">

            <label>To </label>
            <input type="text" class="form-control mb-2">

            <label>CTC </label>
            <input type="text" class="form-control mb-2">

            <label>Incentives </label>
            <input type="text" class="form-control mb-2">

            <label>Key Responsibilities </label>
            <input type="text" class="form-control mb-2">

            <label>Reason for Leaving </label>
            <textarea class="form-control mb-2" rows="1"></textarea>

            <button type="button" class="btn btn-outline-success add_mobile_table_two">Add More</button>
            <button type="button" class="btn btn-outline-danger remove_mobile_table_two">Remove</button>
        `;

                if (afterElement) {
                    afterElement.after(newTableTwo);
                } else {
                    document.querySelector(".comfieldmob_two").appendChild(newTableTwo);
                }
            }

            // Click event listener for the second module
            document.body.addEventListener("click", function (event) {
                // When clicking the #mobile_addmore_btn_two button
                if (event.target.matches("#mobile_addmore_btn_two")) {
                    let existingTablesTwo = document.querySelectorAll(".mobile_table_inform_two");

                    if (existingTablesTwo.length === 0) {
                        // If no tables exist, create a new one immediately
                        addNewTableTwo();
                    } else if (mobileTableOnePone.style.display === "none") {
                        // If the default table is hidden, show it
                        mobileTableOnePone.style.display = "block";
                    } else {
                        // Otherwise, add a new table
                        addNewTableTwo();
                    }
                }

                // When clicking "Add More" inside a table
                else if (event.target.matches(".add_mobile_table_two")) {
                    let currentTableTwo = event.target.closest(".mobile_table_inform_two");
                    addNewTableTwo(currentTableTwo);
                }

                // When clicking "Remove" inside a table
                else if (event.target.matches(".remove_mobile_table_two")) {
                    let tableToRemoveTwo = event.target.closest(".mobile_table_inform_two");
                    if (tableToRemoveTwo) {
                        tableToRemoveTwo.remove();

                        // If all tables are removed, hide the default table
                        if (document.querySelectorAll(".mobile_table_inform_two").length === 0) {
                            mobileTableOnePone.style.display = "none";
                        }
                    }
                }
            });
        });

    </script>
    <!-- second module end -->


    <!-- third module mobile add more -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let counterThree = 0; // Counter for unique table IDs

            const comFieldThree = document.querySelector(".comfieldmob_three");
            const mobileAddMoreBtnThree = document.querySelector("#mobile_addmore_btn_three");
            const mobileTableOneThree = document.querySelector(".comfieldmob_three .mobile_table_onepthree");

            // Hide the main container and first table by default
            comFieldThree.style.display = "none";
            if (mobileTableOneThree) {
                mobileTableOneThree.style.display = "none";
            }

            // Function to check screen size and adjust visibility
            function checkScreenSizeThree() {
                if (window.innerWidth < 769) {
                    comFieldThree.style.display = "block"; // Show only the button on small screens
                } else {
                    comFieldThree.style.display = "none";
                }
            }
            checkScreenSizeThree();
            window.addEventListener("resize", checkScreenSizeThree);

            // Function to create a new table
            function addNewTableThree(afterElement = null) {
                counterThree++; // Increment counter for unique IDs

                let newTableThree = document.createElement("div");
                newTableThree.className = "mobile_table_inform_three border p-3 mt-3";
                newTableThree.setAttribute("id", `mobile_table_three_${counterThree}`);
                newTableThree.style.display = "block"; // Ensure table is visible when added

                newTableThree.innerHTML = `
                <label>Name</label>
            <input type="text" class="form-control mb-2">

            <label>Contact No</label>
            <input type="text" class="form-control mb-2">

            <label>Email ID</label>
            <input type="text" class="form-control mb-2">

            <label>Designation</label>
            <input type="text" class="form-control mb-2">

            <label>Employee ID</label>
            <input type="text" class="form-control mb-2">

            <button type="button" class="btn btn-outline-success add_mobile_table_three">Add More</button>
            <button type="button" class="btn btn-outline-danger remove_mobile_table_three">Remove</button>
        `;

                if (afterElement) {
                    afterElement.after(newTableThree);
                } else {
                    document.querySelector(".comfieldmob_three").appendChild(newTableThree);
                }
            }

            // Click event listener for the third module
            document.body.addEventListener("click", function (event) {
                // When clicking the #mobile_addmore_btn_three button
                if (event.target.matches("#mobile_addmore_btn_three")) {
                    let existingTablesThree = document.querySelectorAll(".mobile_table_inform_three");

                    if (existingTablesThree.length === 0) {
                        // If no tables exist, create a new one immediately
                        addNewTableThree();
                    } else if (mobileTableOneThree.style.display === "none") {
                        // If the default table is hidden, show it
                        mobileTableOneThree.style.display = "block";
                    } else {
                        // Otherwise, add a new table
                        addNewTableThree();
                    }
                }

                // When clicking "Add More" inside a table
                else if (event.target.matches(".add_mobile_table_three")) {
                    let currentTableThree = event.target.closest(".mobile_table_inform_three");
                    addNewTableThree(currentTableThree);
                }

                // When clicking "Remove" inside a table
                else if (event.target.matches(".remove_mobile_table_three")) {
                    let tableToRemoveThree = event.target.closest(".mobile_table_inform_three");
                    if (tableToRemoveThree) {
                        tableToRemoveThree.remove();

                        // If all tables are removed, hide the default table
                        if (document.querySelectorAll(".mobile_table_inform_three").length === 0) {
                            mobileTableOneThree.style.display = "none";
                        }
                    }
                }
            });
        });

    </script>
    <!-- third module end -->

    <script>
        $(document).ready(function () {


            var readURL = function (input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        $('.profile-pic').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }


            $(".file-upload").on('change', function () {
                readURL(this);
            });

            $(".upload-button").on('click', function () {
                $(".file-upload").click();
            });
        });
    </script>

    <script>
        $(document).ready(function () {

            $('#add_row').click(function () {
                //Add row
                row = '';
                row += '<tr><td><input type="text" class="form-control"></td><td><input type="text" class="form-control"></td><td><input type="text" class="form-control"></td><td><input type="text" class="form-control"></td><td><input type="text" class="form-control"></td><td><input type="text" class="form-control"></td><td><input type="text" class="form-control"></td>';
                row += '<td><select class="form-control"><option value="">Please select</option><option value="Completed">Completed</option><option value="Currently Pursuing">Currently Pursuing</option></select></td>';
                row += '<td><button class="btn btn-outline-danger delete_row">Remove</button></td></tr>';
                $(".table_body").append(row);
            })

            $("#add_table").on('click', '.delete_row', function () {
                $(this).closest('tr').remove();
            });

        });
    </script>


<script>
    $('#add_row_two').click(function () {
        let row = '';
        row += '<tr><td><input type="text" class="form-control"></td><td><input type="text" class="form-control"></td><td><input type="text" class="form-control"></td><td><input type="text" class="form-control"></td><td><input type="text" class="form-control"></td><td><input type="text" class="form-control"></td><td><input type="text" class="form-control"></td>';
        row += '<td><textarea class="form-control mb-2" rows="1"></textarea></td>';
        row += '<td><button class="btn btn-outline-danger delete_row_two">Remove</button></td></tr>';
        $(".table_body_two").append(row);
    });

    $(".table_body_two").on('click', '.delete_row_two', function () {
        $(this).closest('tr').remove();
    });
</script>


<!-- 
    <script>
        $(document).ready(function () {

            $('#add_row_two').click(function () {
                
                row = '';
                row += '<tr><td><input type="text" class="form-control"></td><td><input type="text" class="form-control"></td><td><input type="text" class="form-control"></td><td><input type="text" class="form-control"></td><td><input type="text" class="form-control"></td><td><input type="text" class="form-control"></td><td><input type="text" class="form-control"></td>
                    <td> <textarea class="form-control mb-2" rows="1"></textarea></td> ';
                row += '<td><button class="btn btn-outline-danger delete_row_two">Remove</button></td></tr>';
                $(".table_body_two").append(row);
            });

            $("#add_table_two").on('click', '.delete_row_two', function () {
                $(this).closest('tr').remove();
            });

        });
    </script> -->

    <script>
        $(document).ready(function () {

            // Add new row to the table
            $('#add_row_three').click(function () {
                var row = '<tr>' +
                    '<td><input type="text" class="form-control"></td>' +
                    '<td><input type="text" class="form-control"></td>' +
                    '<td><input type="text" class="form-control"></td>' +
                    '<td><input type="text" class="form-control"></td>' +
                    '<td><input type="text" class="form-control"></td>' +
                    '<td><button type="button" class="btn btn-outline-danger delete_row_three">Remove</button></td>' +
                    '</tr>';
                $(".table_body_three").append(row);
            });

            $("#add_table_three").on('click', '.delete_row_three', function () {
                $(this).closest('tr').remove();
            });

        });
    </script>


    <script>
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl)
        })
    </script>

    <script>
        $(document).ready(function () {
            $('#interview_form').submit(function (event) {
                event.preventDefault(); // Prevent the form from submitting immediately

                $("#formSubmitBtn").prop("disabled", true);

                // Collect table data
                var tableData3 = [];
                var tableData2 = [];
                var tableData1 = [];

                $('#add_table_three tbody tr').each(function () {
                    var row = {
                        name: $(this).find('td:eq(0) input').val(),
                        contact: $(this).find('td:eq(1) input').val(),
                        email: $(this).find('td:eq(2) input').val(),
                        designation: $(this).find('td:eq(3) input').val(),
                        experience: $(this).find('td:eq(4) input').val(),
                        education: $(this).find('td:eq(5) input').val()
                    };
                    tableData3.push(row);
                });

                $('#add_table_two tbody tr').each(function () {
                    var row = {
                        firm_name: $(this).find('td:eq(0) input').val(),
                        designation: $(this).find('td:eq(1) input').val(),
                        from: $(this).find('td:eq(2) input').val(),
                        to: $(this).find('td:eq(3) input').val(),
                        ctc: $(this).find('td:eq(4) input').val(),
                        incentives: $(this).find('td:eq(5) input').val(),
                        key_responsibilities: $(this).find('td:eq(6) input').val(),
                        reason_for_leaving: $(this).find('td:eq(7) input').val()
                    };
                    tableData2.push(row);
                });

                $('#add_table tbody tr').each(function () {
                    var row = {
                        from: $(this).find('td:eq(0) input').val(),
                        to: $(this).find('td:eq(1) input').val(),
                        degree_or_diploma_completion: $(this).find('td:eq(2) input').val(),
                        university: $(this).find('td:eq(3) input').val(),
                        subjects: $(this).find('td:eq(4) input').val(),
                        marks_or_grades: $(this).find('td:eq(5) input').val(),
                        regular_or_correspondence: $(this).find('td:eq(6) input').val(),
                        status_of_degree: $(this).find('td:eq(7) select').val()
                    };
                    tableData1.push(row);
                });

                // Convert tableData to JSON
                var tableDataJson3 = JSON.stringify(tableData3);
                var tableDataJson2 = JSON.stringify(tableData2);
                var tableDataJson1 = JSON.stringify(tableData1);


                // Add tableDataJson to a hidden input field
                $('#table_data_three').val(tableDataJson3);
                $('#table_data_two').val(tableDataJson2);
                $('#table_data_one').val(tableDataJson1);

                if ($('input[name="shift_preferences[]"]:checked').length === 0) {
                    event.preventDefault(); // Prevent form submission
                    alert("Please select at least one Shift Preferences.");
                    $("#formSubmitBtn").prop("disabled", false);
                    return;
                }

                // Get the form data
                const formData = new FormData(this);

                const profileInput = $('#profileImg')[0].files.length > 0 ? $('#profileImg')[0].files[0] : null;
                const oldProfileImg = $("#oldProfileImg").val();

                // Check if both new and old profile images are missing
                if (!profileInput && !oldProfileImg) {
                    alert('Please upload a profile picture');
                    $("#formSubmitBtn").prop("disabled", false);
                    return;
                }

                const allowedExtensionsImg = ['jpg', 'jpeg', 'png'];
                const maxImgSize = 500; // in KB

                // Validate only if a new image is uploaded
                if (profileInput) {
                    const imgSizeInKB = profileInput.size / 1024;
                    const imgExtension = profileInput.name.split('.').pop().toLowerCase();

                    if (!allowedExtensionsImg.includes(imgExtension)) {
                        alert(`Invalid profile picture type. Only ${allowedExtensionsImg.join(', ')} are allowed.`);
                        $("#formSubmitBtn").prop("disabled", false);
                        return;
                    }

                    if (imgSizeInKB > maxImgSize) {
                        alert(`Profile picture size exceeds the limit of ${maxImgSize}KB.`);
                        $("#formSubmitBtn").prop("disabled", false);
                        return;
                    }
                }

                // Optional: Validate the file before submitting
                const fileInput = $('#resume')[0].files[0];
                if (!fileInput) {
                    alert('Please upload an resume.');
                    $("#formSubmitBtn").prop("disabled", false);
                    return;
                }

                const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'docx'];
                const maxFileSize = 500; // in KB
                const fileSizeInKB = fileInput.size / 1024;
                const fileExtension = fileInput.name.split('.').pop().toLowerCase();

                if (!allowedExtensions.includes(fileExtension)) {
                    alert(`Invalid resume type. Only ${allowedExtensions.join(', ')} are allowed.`);
                    $("#formSubmitBtn").prop("disabled", false);
                    return;
                }

                if (fileSizeInKB > maxFileSize) {
                    alert(`Resume size exceeds the limit of ${maxFileSize}KB.`);
                    $("#formSubmitBtn").prop("disabled", false);
                    return;
                }

                $.ajax({
                    url: 'interview_form/submit_interview_details', // URL to your CodeIgniter controller method
                    type: 'POST',
                    data: formData,
                    processData: false, // Prevent jQuery from automatically transforming the FormData object
                    contentType: false, // Set content type to false for FormData
                    success: function (response) {
                        $("#formSubmitBtn").prop("disabled", false);
                        if (response) {
                            Swal.fire({
                                title: 'Success!',
                                text: `Your Record has been sucessfully added.`,
                                icon: 'success',
                                showConfirmButton: false,
                            });

                            location.href = 'interview_form/thank_you';
                        } else {
                            Swal.fire({
                                title: 'Fail!',
                                text: `Your Record could not be added`,
                                icon: 'error',
                                showConfirmButton: false,
                            });
                        }
                    },
                    error: function () {
                        $("#formSubmitBtn").prop("disabled", false);

                        Swal.showValidationMessage('An error occurred while checking the email!');
                        reject();
                    }
                });
                // Submit the form (include the serialized table data in the request)

                // console.log($(this).serialize());
                // return false;
                // this.submit();
            });
        })
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const fresherCheckbox = document.getElementById("fresherCheckbox");
            const experiencedCheckbox = document.getElementById("experiencedCheckbox");
            const table = document.getElementById("add_table_two").parentElement; // Getting the table container

            // Hide table by default
            table.style.display = "none";

            function toggleTable() {
                if (experiencedCheckbox.checked) {
                    table.style.display = "block";
                } else {
                    table.style.display = "none";
                }
            }

            // Event listeners for checkboxes
            fresherCheckbox.addEventListener("change", function () {
                if (fresherCheckbox.checked) {
                    experiencedCheckbox.checked = false;
                }
                toggleTable();
            });

            experiencedCheckbox.addEventListener("change", function () {
                if (experiencedCheckbox.checked) {
                    fresherCheckbox.checked = false;
                }
                toggleTable();
            });
        });
    </script>



</body>

</html>