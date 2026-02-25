<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('authentication/includes/head.php'); ?>
<style>
    #strengthMessage {
        font-weight: bold;
    }

    .tooweak {
        color: red;
    }

    .weak {
        color: orange;
    }

    .strong {
        color: green;
    }
</style>

<body class="tw-bg-neutral-100 authentication reset-password">
    <div class="tw-max-w-md tw-mx-auto tw-pt-24 authentication-form-wrapper tw-relative tw-z-20">

        <div class="company-logo text-center">
            <?php echo get_dark_company_logo(); ?>
        </div>

        <h1 class="tw-text-2xl tw-text-neutral-800 text-center tw-font-semibold tw-mb-5">
            <?php echo _l('admin_auth_reset_password_heading'); ?>
        </h1>

        
            
        <div class="tw-bg-white tw-mx-2 sm:tw-mx-6 tw-py-6 tw-px-6 sm:tw-px-8 tw-shadow tw-rounded-lg">
            <?php echo form_open($this->uri->uri_string()); ?>
            <?php echo validation_errors('<div class="alert alert-danger text-center">', '</div>'); ?>
            <?php $this->load->view('authentication/includes/alerts'); ?>
            <?php echo render_input('password', 'admin_auth_reset_password', '', 'password'); ?>
            <?php echo render_input('passwordr', 'admin_auth_reset_password_repeat', '', 'password'); ?>
            <p id="strengthMessage"></p>
            <div class="form-group">
                <button type="submit" id='submitbtn' class="btn btn-primary btn-block" disabled>
                    <?php echo _l('auth_reset_password_submit'); ?>
                </button>
            </div>
            <?php echo form_close(); ?>
            
        </div>

        <!-- <div style = "margin-top:10px" class="tw-bg-white tw-mx-2 sm:tw-mx-6 tw-py-6 tw-px-6 sm:tw-px-8 tw-shadow tw-rounded-lg" style="padding-top: -11px;margin-top: 10px;" >

            <h4>Password must contain : </h4>
            <ul>
                <li><b>Minimum Length:</b> At least 8-12 characters.</li>
                <li>Uppercase Letters: At least one uppercase letter.</li>
                <li>Lowercase Letters: At least one lowercase letter.</li>
                <li>Digits: At least one number.</li>
                <li>Special Characters: At least one special character (e.g., !@#$%^&*).</li>
            </ul>            
        </div> -->
    </div>
</body>

<script>
    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        const strengthMessage = document.getElementById('strengthMessage');
        const strength = getPasswordStrength(password);
        const submitButton = document.getElementById('submitbtn');
        strengthMessage.textContent = strength.message;
        strengthMessage.className = strength.className;


        if (strength.className === 'strong') {
            submitButton.disabled = false;
        } else {
            submitButton.disabled = true;
        }

    })


    function getPasswordStrength(password) {
        let strength = {
            message: '',
            className: ''
        }
        if (password.length < 8 && password.length > 1) {
            strength.message = 'Password is too short.';
            strength.className = 'tooweak';
        } else {
            score = 0;
            if (/[A-Z]/.test(password)) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[!@#$%^&*()_+]/.test(password)) score++;

            if (score <= 2 && score >=1) {
                strength.message = 'Too Weak Password';
                strength.className = 'tooweak';
            } else if (score === 3) {
                strength.message = 'Weak Password';
                strength.className = 'weak';
            } else if (score === 4) {
                strength.message = 'Strong Password';
                strength.className = 'strong';
            }
        }

        return strength;
    }
</script>

</html>