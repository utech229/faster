"use strict";
var KTUsersAddstatus = function() {
    const t = document.getElementById("kt_modal_add_status"),
        e = t.querySelector("#kt_modal_add_status_form"),
        n = new bootstrap.Modal(t);
    return {
        init: function() {
            (() => {
                var o = FormValidation.formValidation(e, {
                    fields: {
                        'status[name]': {
                            validators: {
                                notEmpty: {
                                    message: _Required_Field
                                }
                            }
                        },
                        'status[code]': {
                            validators: {
                                notEmpty: {
                                    message: _Required_Field
                                },
                                regexp: {
                                    regexp: /^[0-9]+$/,
                                    message: _Required_Number
                                },
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
                t.querySelector('[data-kt-statuss-modal-action="close"]').addEventListener("click", (t => {
                    t.preventDefault(),
                        t.value , n.hide()
                  
                })), t.querySelector('[data-kt-statuss-modal-action="cancel"]').addEventListener("click", (t => {
                    t.preventDefault()
                    (e.reset(), n.hide())
                }));
                const i = t.querySelector('[data-kt-statuss-modal-action="submit"]');
                i.addEventListener("click", (function(t) {
                    t.preventDefault(), o && o.validate().then((function(t) {
                        console.log("validated!"), "Valid" == t ? (i.setAttribute("data-kt-indicator", "on"), i.disabled = !0, 
                        loading(true),
                        $.ajax({
                            url: (isUpdating == true) ? window.location.href +'/'+ statusIDInput.val() + '/update_status' : add_link,
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
                                        title: response.title,
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
                                            (isUpdating == true) ? n.hide() : null;
                                        }
                                    }))
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
    KTUsersAddstatus.init()
}));