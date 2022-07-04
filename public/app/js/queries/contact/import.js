"use strict";
var KTImportContact = function() {
    const t = document.getElementById("kt_modal_import_contacts"),
        e = t.querySelector("#kt_modal_import_contacts_form"),
        n = new bootstrap.Modal(t);
    return {
        init: function() {
            (() => {
                var o = FormValidation.formValidation(e, {
                    fields: {
                        'group': {
                            validators: {
                                notEmpty: {
                                    message: 'le groupe est obligatoire' 
                                }
                            }
                        }
                    },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger,
                        bootstrap: new FormValidation.plugins.Bootstrap5({
                            rowSelector: ".fv",
                            eleInvalidClass: "",
                            eleValidClass: ""
                        })
                    }
                });
                t.querySelector('[data-kt-import-contact-modal-action="close"]').addEventListener("click", ($this) => {
                    $this.preventDefault(); n.hide();e.reset();
                });
                t.querySelector('[data-kt-import-contact-modal-action="cancel"]').addEventListener("click", ($this) => {
                    $this.preventDefault();
                    e.reset();n.hide();
                });
                const i = t.querySelector('[data-kt-import-contact-modal-action="submit"]');
                e.addEventListener("submit", (function(t) {
                    
                    t.preventDefault();
                    o && o.validate().then((function(t) {
                        console.log("validated!"), "Valid" == t ? (i.setAttribute("data-kt-indicator", "on"), i.disabled = !0, 
                        $.ajax({
                            url: contact_import,
                            type: 'post',
                            data: new FormData(e),
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            cache: false,
                            success: function(response) {
                                    Swal.fire({
                                        text: response.message,
                                        icon: response.type,
                                        buttonsStyling: false,
                                        confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        }
                                    });
                                    i.removeAttribute("data-kt-indicator"), i.disabled = !1;

                                    if (response.type === 'success') {
                                        t.isConfirmed, e.reset(),
                                        $('#kt_modal_add_contact_reload_button').click();
                                        n.hide();
                                    }
                            },
                            error: function () { 
                                $(document).trigger('onAjaxError');
                                i.removeAttribute("data-kt-indicator"), i.disabled = !1;
                                loading()
                            },
                        })
                        ) : 
                        $(document).trigger('onFormError'),
                        loading();
                    }))
                }))
            })()
        }
    }
}();
KTUtil.onDOMContentLoaded((function() {
    KTImportContact.init()
}));