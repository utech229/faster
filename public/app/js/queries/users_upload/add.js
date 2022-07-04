"use strict";
const block = document.getElementById("kt_modal_add_user_target");
var KTUsersAddUser = function() {//intl['registration_form_phone'].getSelectedCountryData()['iso2'] 
    const t = document.getElementById("kt_modal_add_user"),
        e = t.querySelector("#kt_modal_add_user_form"), 
        n = new bootstrap.Modal(t)
        ;
    var ajax_url;
    return {
        init: function() {
            (() => {
                var o = FormValidation.formValidation(e, {
                    fields: {
                        'avatar': {
                            validators: {
                                file: {
                                    extension: 'xlsx,xls,csv',
                                    type: 'excel,csv',
                                    message: _File_Required
                                },
                            }
                        },
                       
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
                const i = t.querySelector('[data-kt-users-modal-action="submit"]');
                i.addEventListener("click", (t => {
                    t.preventDefault(), o && o.validate().then((function(t) {
                        console.log("validated!"), "Valid" == t ? (i.setAttribute("data-kt-indicator", "on"), i.disabled = !0, 
                            $.ajax({
                                url: url_import,
                                type: 'post',
                                data:   new FormData(e),
                                processData: false,
                                contentType: false,
                                cache: false,
                                async: true,
                                dataType: 'json',
                                success: function (response) {
                                    i.removeAttribute("data-kt-indicator"), i.disabled = !1
                                    swalSimple(response.type, response.message)
                                    if (response.type === 'success') {
                                        t.isConfirmed , e.reset();
                                        tableReloadButton.click();
                                        (isUserUpdating == true) ? n.hide() : null;
                                        
                                    }
                                },
                                error: function (response) {
                                    i.removeAttribute("data-kt-indicator"), i.disabled = !1
                                    $(document).trigger('onAjaxError');
                                }
                            })) : $(document).trigger('onFormError');
                    }))
                })), 
                t.querySelector('[data-kt-users-modal-action="cancel"]').addEventListener("click", (t => {
                    t.preventDefault(), (e.reset(), n.hide()) 
                })), t.querySelector('[data-kt-users-modal-action="close"]').addEventListener("click", (t => {
                    t.preventDefault(), (e.reset(), n.hide())
                }))
            })()
        }
    }
}();
KTUtil.onDOMContentLoaded((function() {
    KTUsersAddUser.init();
}));