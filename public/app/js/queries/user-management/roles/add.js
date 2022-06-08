"use strict";
var KTUsersAddRoles = function() {
    const t = document.getElementById("kt_modal_add_role"),
        e = t.querySelector("#kt_modal_add_role_form"),
        n = new bootstrap.Modal(t);
    return {
        init: function() {
            (() => {
                var o = FormValidation.formValidation(e, {
                    fields: {
                        'role[name]': {
                            validators: {
                                notEmpty: {
                                    message: _Required_Field
                                }
                            }
                        },
                        'role[code]': {
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
                t.querySelector('[data-kt-role-modal-action="close"]').addEventListener("click", (t => {
                    t.preventDefault(), 
                    Swal.fire({
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
                })), t.querySelector('[data-kt-role-modal-action="cancel"]').addEventListener("click", (t => {
                    t.preventDefault(), Swal.fire({
                        text: _Cancel_Question,
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
                            confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        })
                    }))
                }));
                const i = t.querySelector('[data-kt-role-modal-action="submit"]');
                i.addEventListener("click", (function(t) {
                    t.preventDefault(), o && o.validate().then((function(t) {
                         "Valid" == t ? (i.setAttribute("data-kt-indicator", "on"), 
                         i.disabled = !0, setTimeout((function() {
                            $.ajax({
                                url: (isRoleUpdating == true) ? ajaxBaseUrl + '/users/role/' + roleIDInput.val() + '/update_role' : add_role,
                                type: 'post',
                                data: new FormData(e),
                                dataType: 'json',
                                processData: false,
                                contentType: false,
                                cache: false,
                                success: function(response) {
                                    if (response.type === 'success') {
                                        i.removeAttribute("data-kt-indicator"), i.disabled = !1;
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
                                            t.isConfirmed && n.hide();
                                            window.location.reload();
                                        }))
                                    } else {
                                        Swal.fire({
                                            title: response.title,
                                            text: response.message,
                                            icon: response.type,
                                            buttonsStyling: false,
                                            confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                                            customClass: {
                                                confirmButton: "btn btn-primary"
                                            }
                                        });
                                        i.removeAttribute("data-kt-indicator"), i.disabled = !1;
                                    }
                                },
                                error: function () { 
                                    $(document).trigger('onAjaxError');
                                    i.removeAttribute("data-kt-indicator"), i.disabled = !1;
                                },
                            });
                        }), 2e3)) : 
                        $(document).trigger('onAjaxError');
                    }))
                }))
            })(), (() => {
                const t = e.querySelector("#kt_roles_select_all"),
                    n = e.querySelectorAll('[type="checkbox"]');
                t.addEventListener("change", (t => {
                    n.forEach((e => {
                        e.checked = t.target.checked
                    }))
                }))
            })()
        }
    }
}();
KTUtil.onDOMContentLoaded((function() {
    KTUsersAddRoles.init()
}));