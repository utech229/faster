"use strict";
var KTNotificationAddCompany = function() {
    const t = document.getElementById("kt_modal_profile_notification"),
        e = t.querySelector("#kt_modal_profile_notification_form"),
        n = new bootstrap.Modal(t);
    return {
        init: function() {
            (() => {
                var o = FormValidation.formValidation(e, {
                    fields: {
                        'solde_notification[minSolde]': {
                            validators: {
                                notEmpty: {
                                    message: _PermissionName_Required 
                                }
                            }
                        },
                        'solde_notification[email1]': {
                            validators: {
                                emailAddress: {
                                    message: _Email_EmailAddress
                                }
                            }
                        },
                        'solde_notification[email2]': {
                            validators: {
                                emailAddress: {
                                    message: _Email_EmailAddress
                                }
                            }
                        },'solde_notification[email3]': {
                            validators: {
                                emailAddress: {
                                    message: _Email_EmailAddress
                                }
                            }
                        }
                    },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger,
                        bootstrap: new FormValidation.plugins.Bootstrap5({
                            rowSelector: ".fv-row",
                            eleInvalidClass: "",
                            eleValidClass: ""
                        })
                    }
                });
                t.querySelector('[data-kt-notification-modal-action="close"]').addEventListener("click", (t => {
                    t.preventDefault(),
                    t.value && n.hide()
                })), t.querySelector('[data-kt-notification-modal-action="cancel"]').addEventListener("click", (t => {
                    t.preventDefault(), 
                    (e.reset(), n.hide())
                }));
                const i = t.querySelector('[data-kt-notification-modal-action="submit"]');
                i.addEventListener("click", (function(t) {
                    t.preventDefault(), o && o.validate().then((function(t) {
                        console.log("validated!"), "Valid" == t ? (i.setAttribute("data-kt-indicator", "on"), i.disabled = !0, 
                        loading(true),
                        $.ajax({
                            url: notification_add_link,
                            type: 'post',
                            data: new FormData(e),
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            cache: false,
                            success: function(response) {
                                    i.removeAttribute("data-kt-indicator"), i.disabled = !1;
                                    loading()
                                    Swal.fire({
                                        title: _Swal_success,
                                        text: response.message,
                                        icon: response.type,
                                        buttonsStyling: false,
                                        confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        }
                                    })
                                    if (response.type === 'success') {
                                        e.reset(), n.hide()
                                        if(response.data.isAdd == true){
                                            $('#notification_null_section').addClass('d-none')
                                            $('#notification_is_section').removeClass('d-none');
                                        }
                                        $('#n_s_amount').text(response.data.amount)
                                        $('#n_s_email1').text(response.data.email1)
                                        $('#n_s_email2').text(response.data.email2)
                                        $('#n_s_email3').text(response.data.email3)
                                    }
                            },
                            error: function () { 
                                $(document).trigger('onAjaxError');
                                i.removeAttribute("data-kt-indicator"), i.disabled = !1;
                                loading()
                            },
                        })) : 
                        $(document).trigger('onFormError'),
                        loading();
                    }))
                }))

                $("#notificationManageButton").click(function(){
                    reloader()
                });

                function reloader()
                {
                    $.ajax({
                        url: user_notification_link,
                        type: 'post',
                        data: {_token : csrfToken},
                        dataType: 'json',
                        success: function(response) {
                            if (response.data.is == true) {
                                $('#solde_notification_minSolde').val(response.data.amount)
                                $('#solde_notification_email1').val(response.data.email1)
                                $('#solde_notification_email2').val(response.data.email2)
                                $('#solde_notification_email3').vam(response.data.email3)
                            }
                        },
                        error: function () { 
                            $(document).trigger('onAjaxError');
                            i.removeAttribute("data-kt-indicator"), i.disabled = !1;
                            loading()
                        },
                    })
                };
            })()
        }
    }
}();
KTUtil.onDOMContentLoaded((function() {
    KTNotificationAddCompany.init()
}));