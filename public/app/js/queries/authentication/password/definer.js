"use strict";

// Class definition
var KTDefinePasswordGeneral = function() {
    // Elements
    var form;
    var submitButton;
    var validator;
    var passwordMeter;

    // Handle form
    var handleForm = function(e) {
        validator = FormValidation.formValidation(
            form, {
                fields: {
                    'form[plainPassword]': {
                        validators: {
                            notEmpty: {
                                message: _Password_Required
                            },
                            callback: {
                                message: _Password_Valid,
                                callback: function(input) {
                                    if (input.value.length > 1) {
                                        return validatePassword();
                                    }
                                }
                            }
                        }
                    },
                    'cpassword': {
                        validators: {
                            notEmpty: {
                                message: _Password_Confirm
                            },
                            identical: {
                                compare: function() {
                                    return form.querySelector('[name="form[plainPassword]"]').value;
                                },
                                message: _Password_Confirm
                            }
                        }
                    },
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger({
                        event: {
                            password: false
                        }
                    }),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        // Handle form submit
        submitButton.addEventListener('click', function(e) {
            e.preventDefault();
            validator.revalidateField('form[plainPassword]');
            validator.validate().then(function(status) {
                if (status == 'Valid') {
                    // Show loading indication
                    submitButton.setAttribute('data-kt-indicator', 'on');
                    // Disable button to avoid multiple click 
                    submitButton.disabled = true;
                    // Simulate ajax request
                    console.log(intl)
                    var data = $('#kt_password_define_form').serializeArray();
                    $.ajax({
                        url:  password_resetting_new,
                        type: 'post',
                        data: data,
                        dataType: 'json',
                        success: function(response) {
                                // Hide loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                // Enable button
                                submitButton.disabled = false;
                                Swal.fire({
                                    title: response.title,
                                    text: response.message,
                                    icon: response.status,
                                    buttonsStyling: false,
                                    allowOutsideClick: false,
                                    confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                }).then(function(result) {
                                    if (result.isConfirmed && response.status === 'success') {
                                        form.reset(); // reset form                    
                                        passwordMeter.reset(); // reset password meter
                                        window.location.href = login_url;
                                    }
                                });
                        },
                        error: function(response) {
                            $(document).trigger('ajaxError');
                            // Hide loading indication
                            submitButton.removeAttribute('data-kt-indicator');
                            // Enable button
                            submitButton.disabled = false;
                        }
                    });
                } else {
                    // Show error popup. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                    swalSimple('error', _Form_Error_Swal_Notification)
                    // Hide loading indication
                    submitButton.removeAttribute('data-kt-indicator');

                    // Enable button
                    submitButton.disabled = false;
                }
            });
        });

        // Handle password input
        form.querySelector('input[name="password_setting_form[plainPassword]"]').addEventListener('input', function() {
            if (this.value.length > 0) {
                validator.updateFieldStatus('password_setting_form[plainPassword]', 'NotValidated');
            }
        });
    }

    // Password input validation
    var validatePassword = function() {
        return (passwordMeter.getScore() == 100);
    }

    // Public functions
    return {
        // Initialization
        init: function() {
            // Elements
            form = document.querySelector('#kt_password_define_form');
            submitButton = document.querySelector('#kt_password_define_submit');
            passwordMeter = KTPasswordMeter.getInstance(form.querySelector('[data-kt-password-meter="true"]'));

            handleForm();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function() {
    KTDefinePasswordGeneral.init();
});