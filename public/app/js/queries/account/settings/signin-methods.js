"use strict";

var KTAccountSettingsSigninMethods= {
    init:function() {
        var t, e, x, f, passwordMeter, formPassword;
        formPassword = document.querySelector('#kt_signin_change_password');
         // Password input validation
        var validatePassword = function() {
            return (passwordMeter.getScore() >= 30);
        };

        passwordMeter = KTPasswordMeter.getInstance(formPassword.querySelector('[data-kt-password-meter="true"]'));
        //email change function
         !function() {
            var t=document.getElementById("kt_signin_email"),
            e=document.getElementById("kt_signin_email_edit"),
            i=document.getElementById("kt_signin_email_button"),
            s=document.getElementById("kt_signin_cancel"),

            n=document.getElementById("kt_signin_password"),
            o=document.getElementById("kt_signin_password_edit"),
            r=document.getElementById("kt_signin_password_button"),
            a=document.getElementById("kt_password_cancel");

            i.querySelector("button").addEventListener("click", (function() {
                        l()

                    })),
            s.addEventListener("click", (function() {
                        l()

                    })),
            r.querySelector("button").addEventListener("click", (function() {
                        d()

                    })),
            a.addEventListener("click", (function() {
                        d()
                    }));

            var l=function() {
                t.classList.toggle("d-none"),
                i.classList.toggle("d-none"),
                e.classList.toggle("d-none")
            },
            d=function() {
                n.classList.toggle("d-none"),
                r.classList.toggle("d-none"),
                o.classList.toggle("d-none")
            }
        } (),
        //email change form validation
        e=document.getElementById("kt_signin_change_email"), 
        t=FormValidation.formValidation(e, {
            fields: {
                'email': {
                    validators: {
                        notEmpty: {
                            message: _Email_Validation
                        }

                        , emailAddress: {
                            message: _Email_EmailAddress
                        }
                    }
                }

                , 'cpassword': {
                    validators: {
                        notEmpty: {
                            message: _Form_Required_CurrentPassword
                        }
                    }
                }
            }, plugins: {
                trigger:new FormValidation.plugins.Trigger, bootstrap:new FormValidation.plugins.Bootstrap5({
                    rowSelector:".fv-row"
                })
            }
        });

        //email change ajax initer
        const eBtn = document.querySelector('[data-kt-signin-submit-action="submit"]'),
        pBtn = document.querySelector('[data-kt-signin-phone-submit-action="submit"]');
        eBtn.addEventListener("click", (function(n) {
                n.preventDefault(), 
                t.validate().then((function(n) {
                    "Valid"==n?

                    (eBtn.setAttribute("data-kt-indicator", "on"), eBtn.disabled = !0, 
                    $.ajax({
                        url:  email_edit_link,
                        type: 'post',
                        data:   new FormData(e),
                        processData: false,
                        contentType: false,
                        cache: false,
                        async: true,
                        dataType: 'json',
                        success: function (response) {
                            eBtn.removeAttribute("data-kt-indicator"), eBtn.disabled = !1
                            Swal.fire({
                                text: response.message,
                                icon: response.type,
                                buttonsStyling: !1,
                                confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                                
                            }).then((function(t) {
                               if (response.status == 'success'){
                                    e.reset();
                                    $('#kt_signin_cancel').click();
                                }
                            }));
                        },
                        error: function (response) {
                            eBtn.removeAttribute("data-kt-indicator"), eBtn.disabled = !1
                            $(document).trigger('onAjaxError');
                        }
                    })) : $(document).trigger('onFormError');
            }))
        })),
        

    function(t) {
        var e, n=document.getElementById("kt_signin_change_password");
        const passBtn = document.querySelector('[data-kt-signin-password-submit-action="submit"]');
        e=FormValidation.formValidation(n, {
                fields: {
                    currentpassword: {
                        validators: {
                            notEmpty: {
                                message: _Form_Required_CurrentPassword
                            }
                        }
                    }

                    , newpassword: {
                        validators: {
                            notEmpty: {
                                message: _Form_Required_NewPassword
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
                    }

                    , confirmpassword: {
                        validators: {
                            notEmpty: {
                                message: _Password_Confirm
                            },
                            identical: {
                                compare: function() {
                                    return n.querySelector('[name="newpassword"]').value
                                },
                                message: _Form_Error_Confirm
                            }
                        }
                    }
                }
                , plugins: {
                    trigger:new FormValidation.plugins.Trigger, bootstrap:new FormValidation.plugins.Bootstrap5({
                        rowSelector:".fv-row"
                    })
            }

        }),
        n.querySelector("#kt_password_submit").addEventListener("click", (function(t) {
                t.preventDefault(), 
                console.log("click"), 
                e.validate().then((function(t) {
                if ("Valid"==t) 
                {
                    passBtn.setAttribute("data-kt-indicator", "on"), passBtn.disabled = !0;
                    var data = $(n).serializeArray();
                    $.ajax({
                        url:  password_edit_link,
                        type: 'post',
                        data:   data,
                        dataType: 'json',
                        success: function (response) {
                            passBtn.removeAttribute("data-kt-indicator"), passBtn.disabled = !1
                            Swal.fire({
                                text: response.message,
                                icon: response.type,
                                buttonsStyling: !1,
                                confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                                
                            }).then((function(t) {
                                if ( response.status == 'success'){
                                    n.reset(), e.resetForm();
                                    $('#kt_signin_password_cancel').click();
                                    setTimeout(() => {
                                        window.location.href = response.avatar; //avatar here is redirect link 
                                    }, 1000);
                                }
                            }));
                        },
                        error: function (response) {
                            passBtn.removeAttribute("data-kt-indicator"), passBtn.disabled = !1
                            $(document).trigger('onAjaxError');
                        }
                    });
                }else $(document).trigger('onFormError');
            }))
        }))
    }

()}};

KTUtil.onDOMContentLoaded((function() {
            KTAccountSettingsSigninMethods.init()
        }));
