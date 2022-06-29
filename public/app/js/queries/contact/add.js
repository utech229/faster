"use strict";
var KTAddContact = function() {
    const t = document.getElementById("kt_modal_create_contact"),
        e = t.querySelector("#kt_modal_contact_form"),
        n = new bootstrap.Modal(t);
    return {
        init: function() {
            (() => {
                var o = FormValidation.formValidation(e, {
                    fields: {
                        'phone': {
                            validators: {
                                notEmpty: {
                                    message: 'Le numéro est obligatoire' 
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
                t.querySelector('[data-kt-contact-modal-action="close"]').addEventListener("click", ($this) => {
                    $this.preventDefault(); n.hide();
                });
                t.querySelector('[data-kt-contact-modal-action="cancel"]').addEventListener("click", ($this) => {
                    $this.preventDefault();
                    e.reset();n.hide();
                });
                const i = t.querySelector('[data-kt-contact-modal-action="submit"]');
                e.addEventListener("submit", (function(t) {
                    
                    t.preventDefault();
                    var inputs = document.querySelectorAll("[data-name=phone]");

                    inputs.forEach((input, index) => {
                        var hidden = input.closest("div").querySelector("input[type=hidden]");
                        $(hidden).val(allHidden[index].getNumber());
                    });
                    o && o.validate().then((function(t) {
                        console.log("validated!"), "Valid" == t ? (i.setAttribute("data-kt-indicator", "on"), i.disabled = !0, 
                        $.ajax({
                            url: contact_new,
                            type: 'post',
                            data: new FormData(e),
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            cache: false,
                            success: function(response) {
                                    i.removeAttribute("data-kt-indicator"), i.disabled = !1;
                                    Swal.fire({
                                        text: response.message,
                                        icon: response.type,
                                        buttonsStyling: false,
                                        confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        }
                                    })
                                    if (response.type === 'success') {
                                        t.isConfirmed && e.reset(),
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
    KTAddContact.init()
}));