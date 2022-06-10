"use strict";
var KTUsersAddrouter = function() {
    const t = document.getElementById("kt_modal_add_router"),
        e = t.querySelector("#kt_modal_add_router_form"),
        n = new bootstrap.Modal(t);
    return {
        init: function() {
            (() => {
                var o = FormValidation.formValidation(e, {
                    fields: {
                        'router[name]': {
                            validators: {
                                notEmpty: {
                                    message: _Required_Field
                                }
                            }
                        },
                        description: {
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
                t.querySelector('[data-kt-routers-modal-action="close"]').addEventListener("click", (t => {
                    t.preventDefault(), Swal.fire({
                        text: _modal_close,
                        icon: "warning",
                        showCancelButton: !0,
                        buttonsStyling: !1,
                        confirmButtonText: _Yes,
                        cancelButtonText: _No,
                        customClass: {
                            confirmButton: "btn btn-primary",
                            cancelButton: "btn btn-active-light"
                        }
                    }).then((function(t) {
                        t.value && n.hide()
                    }))
                })), t.querySelector('[data-kt-routers-modal-action="cancel"]').addEventListener("click", (t => {
                    t.preventDefault(), Swal.fire({
                        text:  _Cancel_Question,
                        icon: "warning",
                        showCancelButton: !0,
                        buttonsStyling: !1,
                        confirmButtonText: _Yes,
                        cancelButtonText: _No,
                        customClass: {
                            confirmButton: "btn btn-primary",
                            cancelButton: "btn btn-active-light"
                        }
                    }).then((function(t) {
                        t.value ? (e.reset(), n.hide()) : "cancel" === t.dismiss && Swal.fire({
                            text: _no_cancel_form,
                            icon: "error",
                            buttonsStyling: !1,
                            confirmButtonText:  _Form_Ok_Swal_Button_Text_Notification,
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        })
                    }))
                }));
                const i = t.querySelector('[data-kt-routers-modal-action="submit"]');
                i.addEventListener("click", (function(t) {
                    t.preventDefault(), o && o.validate().then((function(t) {
                        console.log("validated!"), "Valid" == t ? (i.setAttribute("data-kt-indicator", "on"), i.disabled = !0, 
                        load.removeClass('sr-only'),
                        $.ajax({
                            url: (isrouterUpdating == true) ? window.location.href + routerIDInput.val() + '/update_router' : add_router,
                            type: 'post',
                            data: new FormData(e),
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            cache: false,
                            success: function(response) {
                                    i.removeAttribute("data-kt-indicator"), i.disabled = !1;
                                    load.addClass('sr-only')
                                    Swal.fire({
                                        title: _Swal_success,
                                        text: response.message,
                                        icon: response.type,
                                        buttonsStyling: false,
                                        confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        }
                                    }).then((function(t) {
                                        if (response.type === 'success') {
                                            t.isConfirmed && e.reset();
                                            e.reset(),tableReloadButton.click();
                                            (isrouterUpdating == true) ? n.hide() : null;
                                        }
                                    }))
                            },
                            error: function () { 
                                $(document).trigger('onAjaxError');
                                i.removeAttribute("data-kt-indicator"), i.disabled = !1;
                                load.addClass('sr-only')
                            },
                        })) : 
                        $(document).trigger('onFormError'),
                        load.addClass('sr-only');
                    }))
                }))
            })()
        }
    }
}();
KTUtil.onDOMContentLoaded((function() {
    KTUsersAddrouter.init()
}));