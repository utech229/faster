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
                    t.preventDefault(),  t.value ,n.hide()
                })), t.querySelector('[data-kt-role-modal-action="cancel"]').addEventListener("click", (t => {
                    t.preventDefault(), e.reset(), n.hide()
                }));
                const i = t.querySelector('[data-kt-role-modal-action="submit"]');
                i.addEventListener("click", (function(t) {
                    t.preventDefault(), o && o.validate().then((function(t) {
                         "Valid" == t ? (i.setAttribute("data-kt-indicator", "on"), 
                         i.disabled = !0, loading(true),
                            $.ajax({
                                url: (isRoleUpdating == true) ? update_link.replace("_1_", roleIDInput.val()): add_role,
                                type: 'post',
                                data: new FormData(e),
                                dataType: 'json',
                                processData: false,
                                contentType: false,
                                cache: false,
                                success: function(response) {
                                    loading()
                                    i.removeAttribute("data-kt-indicator"), i.disabled = !1;
                                    swalSimple(response.type, response.message, ()=> {
                                        if (response.type === 'success') {
                                            t.isConfirmed && n.hide(), window.location.reload();
                                        } 
                                    });
                                    
                                },
                                error: function () { 
                                    loading()
                                    $(document).trigger('onAjaxError');
                                    i.removeAttribute("data-kt-indicator"), i.disabled = !1;
                                },
                            })) : $(document).trigger('onAjaxError');
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