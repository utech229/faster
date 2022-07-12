"use strict";
var importContact = 0;
var KTImportContact = function() {
    const t = document.getElementById("kt_modal_import_contacts"),
    x = document.getElementById("kt_modal_create_contact_group"),
    e = t.querySelector("#kt_modal_import_contacts_form");
    var n = new bootstrap.Modal(t);
    return {
        init: function() {
            (() => {
                var o = FormValidation.formValidation(e, {
                    fields: {
                        'group': {
                            validators: {
                                notEmpty: {
                                    message: 'Le groupe est obligatoire' 
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
                    $this.preventDefault(); n.hide();e.reset();$('#id_group_contact_import').val(null).trigger('change');
                });
                $('#open_modal_import_contacts_id').click(function(e) 
                {
                    e.preventDefault();
                    document.querySelector('[data-kt-contact-user="user"]').value == "" ?
                    Swal.fire({
                        html: _selectUser,
                        icon: "warning",
                        buttonsStyling: !1,
                        confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                        customClass: {
                            confirmButton: "btn fw-bold btn-primary"
                        }
                    }): n.show();
                   
                });
                $('#add_group_id').click(function(e) 
                {
                    e.preventDefault();
                    importContact = 1;
                    $("[data-kt-import-contact-modal-action=close]").click();
                    $("#kt_modal_create_contact_group").modal("show");

                });
                t.querySelector('[data-kt-import-contact-modal-action="cancel"]').addEventListener("click", ($this) => {
                    $this.preventDefault();
                    e.reset();n.hide();$('#id_group_contact_import').val(null).trigger('change');
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
                                        $('#id_group_contact_import').val(null).trigger('change');
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