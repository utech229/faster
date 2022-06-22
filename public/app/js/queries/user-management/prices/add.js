"use strict";
var KTpricesAddprice = function() {
    const t = document.getElementById("kt_modal_add_price"),
        e = t.querySelector("#kt_modal_add_price_form"),
        n = new bootstrap.Modal(t)
        ;
    var ajax_url;
    return {
        init: function() {
            (() => {
                var o = FormValidation.formValidation(e, {
                    fields: {
                        'country': {
                            validators: {
                                validators: {
                                    notEmpty: {
                                        message: _Required_Field
                                    },
                                }
                            }
                        },
                        'price[price]': {
                            validators: {
                                notEmpty: {
                                    message: _Required_Field
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
                const i = t.querySelector('[data-kt-prices-modal-action="submit"]');
                i.addEventListener("click", (t => {
                    t.preventDefault(), o && o.validate().then((function(t) {
                        console.log("validated!"), "Valid" == t ? (i.setAttribute("data-kt-indicator", "on"), i.disabled = !0, 
                            loading(true),
                            ajax_url = price_add_link.replace("_1_", user_uid),
                            $.ajax({
                                url: ajax_url,
                                type: 'post',
                                data:   new FormData(e),
                                processData: false,
                                contentType: false,
                                cache: false,
                                async: true,
                                dataType: 'json',
                                success: function (response) {
                                    i.removeAttribute("data-kt-indicator"), i.disabled = !1,loading();
                                    Swal.fire({
                                        text: response.message,
                                        icon: response.type,
                                        buttonsStyling: !1,
                                        confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        }
                                        
                                    })
                                    if (response.type === 'success') {
                                        t.isConfirmed, e.reset(),tableReloadButton.click();
                                        (ispriceUpdating == true) ? n.hide() : null;
                                    }
                                },
                                error: function (response) {
                                    loading()
                                    i.removeAttribute("data-kt-indicator"), i.disabled = !1
                                    $(document).trigger('onAjaxError');
                                }
                            })) : $(document).trigger('onFormError'),loading();
                    }))
                })), 
                t.querySelector('[data-kt-prices-modal-action="cancel"]').addEventListener("click", (t => {
                    t.preventDefault(), e.reset(), n.hide()
                })), t.querySelector('[data-kt-prices-modal-action="close"]').addEventListener("click", (t => {
                    t.preventDefault(), e.reset(), n.hide()
                }))
            })()
        }
    }
}();
KTUtil.onDOMContentLoaded((function() {
    KTpricesAddprice.init();
}));