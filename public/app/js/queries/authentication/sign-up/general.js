"use strict";

// Class definition
var KTSignupGeneral = function() {
    // Elements
    var form;
    var submitButton;
    var validator;
    var passwordMeter;

    const isValidPhone = function() {
        return {
            validate: function(input) {
                const full = intl['registration_form_phone'].getNumber();
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
                    'registration_form[firstName]': {
                        validators: {
                            notEmpty: {
                                message: _FirstName_Required
                            },
                            different: {
                                field: 'registration_form[plainPassword]',
                                message: _Different_FirstName_Password,	
                            }
                        }
                    },
                    'registration_form[lastName]': {
                        validators: {
                            notEmpty: {
                                message: _LastName_Required
                            }
                        }
                    },
                    'registration_form[email]': {
                        validators: {
                            notEmpty: {
                                message: _Email_NotEmpty_Connexion
                            },
                            emailAddress: {
                                message: _Email_EmailAddress
                            }
                        }
                    },
                    'registration_form[phone]': {
                        validators: {
                            notEmpty: {
                                message: _Phone_Required
                            },
                            regexp: {
                                regexp: /^[0-9]+$/,
                                message: _Phone_Numeric
                            },
                            validePhone: {
                                message: _Phone_Not_Valid,
                            }
                        }
                    },
                    'registration_form[plainPassword]': {
                        validators: {
                            notEmpty: {
                                message: _Password_Required
                            },
                            callback: {
                                message: _Password_Valid,
                                callback: function(input) {
                                    if (input.value.length > 0) {
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
                                    return form.querySelector('[name="registration_form[plainPassword]"]').value;
                                },
                                message: _Password_Confirm
                            }
                        }
                    },
                    'toc': {
                        validators: {
                            notEmpty: {
                                message: _Condition_Confirm
                            }
                        }
                    }
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
            validator.revalidateField('registration_form[plainPassword]');
            validator.validate().then(function(status) {
                if (status == 'Valid') {
                    // Show loading indication
                    submitButton.setAttribute('data-kt-indicator', 'on');
                    // Disable button to avoid multiple click 
                    submitButton.disabled = true;
                    // Simulate ajax request
                    console.log(intl)
                    var data = $('#kt_sign_up_form').serializeArray();
                    data.push({ name: 'full_number', value: intl['registration_form_phone'].getNumber() })
                    data.push({ name: 'country', value: intl['registration_form_phone'].getSelectedCountryData()['iso2'] })
                    $('#user_currency_name').val($("#user_currency option:selected" ).text())
                    $.ajax({
                        url: register_url,
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
                                // Show error popup. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                                Swal.fire({
                                    title: response.title,
                                    text: response.message,
                                    icon: response.status,
                                    buttonsStyling: false,
                                    confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                });
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
                    // Show error popup. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                    Swal.fire({
                        text: _Form_Error_Swal_Notification,
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                    // Hide loading indication
                    submitButton.removeAttribute('data-kt-indicator');

                    // Enable button
                    submitButton.disabled = false;
                }
            });
        });

        // Handle password input
        form.querySelector('input[name="registration_form[plainPassword]"]').addEventListener('input', function() {
            if (this.value.length > 0) {
                validator.updateFieldStatus('registration_form[plainPassword]', 'NotValidated');
            }
        });
    }

    // Password input validation
    var validatePassword = function() {
        return (passwordMeter.getScore() >= 50);
    }

    // Public functions
    return {
        // Initialization
        init: function() {
            // Elements
            form = document.querySelector('#kt_sign_up_form');
            submitButton = document.querySelector('#kt_sign_up_submit');
            passwordMeter = KTPasswordMeter.getInstance(form.querySelector('[data-kt-password-meter="true"]'));

            handleForm();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function() {
    KTSignupGeneral.init();
});