"use strict";
var KTModalMomo = function() {
    const t = document.getElementById("kt_modal_momo"),
        e = t.querySelector("#kt_modal_momo_form"),
        n = new bootstrap.Modal(t);
    return {
        init: function() {
            (() => {
                var o = FormValidation.formValidation(e, {
                    fields: {
                        'owner_name': {
                            validators: {
                                notEmpty: {
                                    message: '' 
                                }
                            }
                        },
                        'phone': {
                            validators: {
                                notEmpty: {
                                    message: _Phone_Required
                                },
                                validePhone: {
                                    message: _Phone_Not_Valid,
                                }
                            }
                        },
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
                t.querySelector('[data-kt-momo-action="close"]').addEventListener("click", (t => {
                    t.preventDefault(),
                        t.value , n.hide()
                })), t.querySelector('[data-kt-momo-action="cancel"]').addEventListener("click", (t => {
                    t.preventDefault(), (e.reset(), n.hide()) 
                }));
                const i = t.querySelector('[data-kt-momo-action="submit"]');
                i.addEventListener("click", (function(t) {
                    t.preventDefault(), o && o.validate().then((function(t) {
                        console.log("validated!"), "Valid" == t ? (i.setAttribute("data-kt-indicator", "on"), i.disabled = !0, 
                        $('#mobile_phone').val(intl['mobile_phone'].getNumber()),
                        $.ajax({
                            url: mobile_payment_link ,
                            type: 'post',
                            data: new FormData(e),
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            cache: false,
                            success: function(response) {
                                    i.removeAttribute("data-kt-indicator"), i.disabled = !1;
                                    Swal.fire({
                                        title: reponse.title,
                                        text: response.message,
                                        icon: response.type,
                                        buttonsStyling: false,
                                        confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        }
                                    }).then((function(t) {
                                        if (response.type === 'success') {
                                            t.isConfirmed && e.reset(), n.hide();
                                        }
                                    }))
                                    setTimeout(() => {
                                        if ((response.data.token !== "undefined")){
                                            window.location.href = response.data.token.url;
                                        }
                                    }, 1000);
                            },
                            error: function () { 
                                $(document).trigger('onAjaxError');
                                i.removeAttribute("data-kt-indicator"), i.disabled = !1;
                            },
                        })) : 
                        $(document).trigger('onFormError');
                    }))
                }))
            })()
        }
    }
}();
KTUtil.onDOMContentLoaded((function() {
    KTModalMomo.init()
}));