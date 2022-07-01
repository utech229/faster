"use strict";
var KTAddGroup = function() {
    const t = document.getElementById("kt_modal_create_contact_group"),
        e = t.querySelector("#kt_modal_contact_group_form"),
        n = new bootstrap.Modal(t);
    return {
        init: function() {
            (() => {
                var o = FormValidation.formValidation(e, {
                    fields: {
                        'groupName': {
                            validators: {
                                notEmpty: {
                                    message: 'Le nom est obligatoire' 
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
                t.querySelector('[data-kt-contact-group-modal-action="close"]').addEventListener("click", (t => {
                    t.preventDefault(), 
                        t.value , n.hide()
                })), t.querySelector('[data-kt-contact-group-modal-action="cancel"]').addEventListener("click", (t => {
                    t.preventDefault(), (e.reset(), n.hide())
                }));
                const i = t.querySelector('[data-kt-contact-group-modal-action="submit"]');
                i.addEventListener("click", (function(t) {
                    if(document.querySelector('[data-kt-group-contact-user="user"]') !=null)

                    $("#user_group").val(document.querySelector('[data-kt-contact-group="user"]').value);
                    t.preventDefault(), o && o.validate().then((function(t) 
                    {
                        console.log("validated!"), "Valid" == t ?  (i.setAttribute("data-kt-indicator", "on"), i.disabled = !0, 
                        $.ajax({
                            url: group_new,
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
                                    $('#kt_modal_add_contact_group_reload_button').click();
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
    KTAddGroup.init()
}));