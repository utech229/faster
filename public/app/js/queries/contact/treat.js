"use strict";
var KTUsersManagePayment = function() {
    const t = document.getElementById("kt_modal_payment_request_treat"),
        e = t.querySelector("#kt_modal_payment_request_treat_form"),
        n = new bootstrap.Modal(t);
    return {
        init: function() {
            (() => {
                var o = FormValidation.formValidation(e, {
                    fields: {
                        'trid': {
                            validators: {
                                notEmpty: {
                                    message: _Required_Field
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
                t.querySelector('[data-kt-payment-treat-modal-action="close"]').addEventListener("click", (t => {
                    t.preventDefault(), 
                        t.value , n.hide()
                })), t.querySelector('[data-kt-payment-treat-modal-action="cancel"]').addEventListener("click", (t => {
                    t.preventDefault(), (e.reset(), n.hide())
                }));
                const i = t.querySelector('[data-kt-payment-treat-modal-action-r="submit"]');
                const v = t.querySelector('[data-kt-payment-treat-modal-action-v="submit"]');
                i.addEventListener("click", (function(t) {
                    t.preventDefault(), o && o.validate().then((function(t) {
                        console.log("validated!"), "Valid" == t ? (i.setAttribute("data-kt-indicator", "on"), i.disabled = !0, 
                        loading(true),
                        Swal.fire({
                            text: _Reject_request,
                            icon: "warning",
                            showCancelButton: true,
                            buttonsStyling: false,
                            confirmButtonText: _Yes,
                            cancelButtonText: _No,
                            customClass: {
                                confirmButton: "btn fw-bold btn-danger",
                                cancelButton: "btn fw-bold btn-active-light-primary"
                            }
                        }).then(function(result) {
                            if (result.value) {
                                loading(true);
                                $.ajax({
                                    url: window.location.href + '/'+paymentIDInput.val() + '/reject',
                                    type: 'post',
                                    data: {_token : csrfToken},
                                    dataType: 'json',
                                    success: function(response) {
                                            i.removeAttribute("data-kt-indicator"), i.disabled = !1;
                                            loading()
                                            Swal.fire({
                                                title: response.title,
                                                text: response.message,
                                                icon: response.type,
                                                buttonsStyling: false,
                                                confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                                                customClass: {
                                                    confirmButton: "btn btn-primary"
                                                }
                                            })
                                            if (response.type === 'success') {
                                                t.isConfirmed ,e.reset(), n.hide();
                                            }
                                    },
                                    error: function () { 
                                        $(document).trigger('onAjaxError');
                                        i.removeAttribute("data-kt-indicator"), i.disabled = !1;
                                        loading()
                                    },
                                });
                            } else if (result.dismiss === 'cancel') {
                                $(document).trigger('entityUpStop', ['#updatePaymentOption', paymentIDInput.val(), 'fa-eye']),
                                i.removeAttribute("data-kt-indicator"), i.disabled = !1;
                                loading()
                            }
                        })) : 
                        $(document).trigger('onFormError'),
                        loading();
                    }))
                }))

                v.addEventListener("click", (function(t) {
                    t.preventDefault(), o && o.validate().then((function(t) {
                        console.log("validated!"), "Valid" == t ? (v.setAttribute("data-kt-indicator", "on"), v.disabled = !0, 
                        loading(true),
                        Swal.fire({
                            text: _Validate_request,
                            icon: "warning",
                            showCancelButton: true,
                            buttonsStyling: false,
                            confirmButtonText: _Yes,
                            cancelButtonText: _No,
                            customClass: {
                                confirmButton: "btn fw-bold btn-danger",
                                cancelButton: "btn fw-bold btn-active-light-primary"
                            }
                        }).then(function(result) {
                            if (result.value) {
                                var typer = (document.getElementById("kt_modal_payment_type").checked == false) ? 0 : 1;
                                loading(true);
                                $.ajax({
                                    url: window.location.href + '/'+paymentIDInput.val() + '/validate',
                                    type: 'post',
                                    data: {trid : $("#idtransaction").val(), _token : csrfToken, type : typer},
                                    dataType: 'json',
                                    success: function(response) {
                                            v.removeAttribute("data-kt-indicator"), v.disabled = !1;
                                            loading();
                                            Swal.fire({
                                                title: response.title,
                                                text: response.message,
                                                icon: response.type,
                                                buttonsStyling: false,
                                                confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                                                customClass: {
                                                    confirmButton: "btn btn-primary"
                                                }
                                            })
                                            if (response.type === 'success') {
                                                t.isConfirmed ,e.reset(), n.hide();
                                            }

                                    },
                                    error: function () { 
                                        $(document).trigger('onAjaxError');
                                        i.removeAttribute("data-kt-indicator"), i.disabled = !1;
                                        loading()
                                    },
                                });
                            } else if (result.dismiss === 'cancel') {
                                $(document).trigger('entityUpStop', ['#updatePaymentOption', paymentIDInput.val(), 'fa-eye']),
                                v.removeAttribute("data-kt-indicator"), v.disabled = !1;
                                loading()
                            }
                        })) : 
                        $(document).trigger('onFormError'),
                        loading();
                    }))
                }))
            })()
        }
    }
}();
KTUtil.onDOMContentLoaded((function() {
    KTUsersManagePayment.init()
}));