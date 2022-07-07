"use strict";

// Class definition
var KTSignupGeneral = function() {
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
                    
                    'form[email]': {
                        validators: {
                            notEmpty: {
                                message: _Email_NotEmpty_Connexion
                            },
                            emailAddress: {
                                message: _Email_EmailAddress
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
            validator.validate().then(function(status) {
                if (status == 'Valid') {
                    // Show loading indication
                    submitButton.setAttribute('data-kt-indicator', 'on');
                    // Disable button to avoid multiple click 
                    submitButton.disabled = true;
                    // Simulate ajax request
                    var data = $('#kt_password_resetting_form').serializeArray();
                    $.ajax({
                        url:  password_reset_url,
                        type: 'post',
                        data:   data,
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
                                    if (result.isConfirmed && response.type == 'success') { 
                                        form.reset();            
                                        window.location.href = login_url;
                                    }
                                });
                                /*setTimeout(() => {
                                    window.location.href = login_url;
                                }, 5000);*/
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
    }

    // Public functions
    return {
        // Initialization
        init: function() {
            // Elements
            form = document.querySelector('#kt_password_resetting_form');
            submitButton = document.querySelector('#kt_password_resetting_submit');
            passwordMeter = KTPasswordMeter.getInstance(form.querySelector('[data-kt-password-meter="true"]'));

            handleForm();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function() {
    KTSignupGeneral.init();
});