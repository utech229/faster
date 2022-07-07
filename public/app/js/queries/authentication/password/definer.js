

"use strict";

// Class definition
var KTDefinePasswordGeneral = function() {
    // Elements
    var form;
    var submitButton;
    var validator;
    var passwordMeter;

    const isValidPhone = function() {
        return {
            validate: function(input) {
                const full = intl['password_setting_form_phone'].getNumber();
                if (full.length == 12 && full.substr(0, 6) == "+22952") {
                    return {
                        valid: true,
                    }
                }
                return {
                    valid: intl.isValidNumber(),
                }
            }

        };
    };


    // Handle form
    var handleForm = function(e) {
        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        FormValidation.validators.validePhone = isValidPhone;
        validator = FormValidation.formValidation(
            form, {
                fields: {
                    'password_setting_form[plainPassword]': {
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
                    'password_setting_form[confirm_password]': {
                        validators: {
                            notEmpty: {
                                message: _Password_Confirm
                            },
                            identical: {
                                compare: function() {
                                    return form.querySelector('[name="password_setting_form[plainPassword]"]').value;
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
            validator.revalidateField('password_setting_form[plainPassword]');
            validator.validate().then(function(status) {
                if (status == 'Valid') {
                    // Show loading indication
                    submitButton.setAttribute('data-kt-indicator', 'on');
                    // Disable button to avoid multiple click 
                    submitButton.disabled = true;
                    // Simulate ajax request
                    var data = $('#kt_password_define_form').serializeArray();
                    $.ajax({
                        url: password_resetting_new,
                        type: 'post',
                        data: data,
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
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
                                    if (result.isConfirmed) {
                                        form.reset(); // reset form                    
                                        passwordMeter.reset(); // reset password meter
                                        window.location.href = login_url;
                                    }
                                });
                                

                            } else {
                                swalSimple(response.type, response.message)
                                // Hide loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                // Enable button
                                submitButton.disabled = false;
                            }
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