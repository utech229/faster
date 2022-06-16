"use strict";
var KTUsersAddPermission = function() {
    const t = document.getElementById("kt_modal_add_permission"),
        e = t.querySelector("#kt_modal_add_permission_form"),
        n = new bootstrap.Modal(t);
    return {
        init: function() {
            (() => {
                var o = FormValidation.formValidation(e, {
                    fields: {
                        'permission[name]': {
                            validators: {
                                notEmpty: {
                                    message: _PermissionName_Required 
                                }
                            }
                        },
                        'permission[code]': {
                            validators: {
                                notEmpty: {
                                    message: _PermissionCode_Required 
                                }
                            }
                        },
                        description: {
                            validators: {
                                notEmpty: {
                                    message: _PermissionDesc_Required 
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
                t.querySelector('[data-kt-permissions-modal-action="close"]').addEventListener("click", (t => {
                    t.preventDefault(), t.value, n.hide()
                })), t.querySelector('[data-kt-permissions-modal-action="cancel"]').addEventListener("click", (t => {
                    t.preventDefault(), e.reset(), n.hide()
                }));
                const i = t.querySelector('[data-kt-permissions-modal-action="submit"]');
                i.addEventListener("click", (function(t) {
                    t.preventDefault(), o && o.validate().then((function(t) {
                        console.log("validated!"), "Valid" == t ? (i.setAttribute("data-kt-indicator", "on"), i.disabled = !0, 
                        loading(true),
                        $.ajax({
                            url: (isPermissionUpdating == true) ? update_link.replace("_1_", permissionIDInput.val()) : add_permission,
                            type: 'post',
                            data: new FormData(e),
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            cache: false,
                            success: function(response) {
                                    i.removeAttribute("data-kt-indicator"), i.disabled = !1;
                                    loading()
                                    swalSimple(response.type, response.message, ()=> {
                                        if (response.type === 'success') {
                                            t.isConfirmed && e.reset();
                                            e.reset(),tableReloadButton.click();
                                            (isPermissionUpdating == true) ? n.hide() : null;
                                        }
                                    });
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
            })()
        }
    }
}();
KTUtil.onDOMContentLoaded((function() {
    KTUsersAddPermission.init()
}));