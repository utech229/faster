"use strict";

var KTAccountSettingsDeactivateAccount=function() {
    var t,
    n,
    e;

    return {
        init:function() {

            t=document.querySelector("#kt_account_deactivate_form"),
            e=document.querySelector("#kt_account_deactivate_account_submit"),
            n=FormValidation.formValidation(t, {
                fields: {
                    deactivate: {
                        validators: {
                            notEmpty: {
                                message: _Disable_Checker
                            }
                        }
                    }
                }

                , plugins: {
                    trigger:new FormValidation.plugins.Trigger, submitButton:new FormValidation.plugins.SubmitButton, bootstrap:new FormValidation.plugins.Bootstrap5({
                        rowSelector:".fv-row", eleInvalidClass:"", eleValidClass:""
                    })
            }

        }),
    e.addEventListener("click", (function(t) {
                t.preventDefault(), 
                n.validate().then((function(t) {
                "Valid"==t?swal.fire({
                    text: _Disable_Confirm, 
                    icon:"warning", buttonsStyling: !1, showDenyButton: !0, 
                    confirmButtonText:_Yes, 
                    denyButtonText:_No, 
                    customClass: {
                        confirmButton:"btn btn-light-primary", denyButton:"btn btn-danger"
                    }
                }).then((t=> {
                        if(t.isConfirmed){ 
                            load.removeClass('sr-only'); 
                            $.ajax({
                                url:  account_disable_link,
                                type: 'post',
                                data: $('#kt_account_deactivate_form').serializeArray(),
                                dataType: 'json',
                                success: function (response) {
                                    Swal.fire({
                                        text: response.message,
                                        icon: response.type,
                                        buttonsStyling: !1,
                                        confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        }
                                        
                                    }).then((function(t) {
                                        if (response.status == 'success'){
                                            $('#kt_account_deactivate_form')[0].reset();
                                            setTimeout(() => {
                                                window.location.href = response.data; //avatar here is redirect link 
                                            }, 1000);
                                        }
                                    }));
                                },
                                error: function (response) {
                                    $(document).trigger('onAjaxError');
                                }
                            })}else t.isDenied&&Swal.fire({
                            text: _Not_Disable_Account, 
                            icon:"info", 
                            confirmButtonText:"Ok", 
                            buttonsStyling: !1, customClass: {
                                confirmButton:"btn btn-light-primary"
                            }
                        })

                })): $(document).trigger('onFormError');
            }))
    }))
}
}
}();

KTUtil.onDOMContentLoaded((function() {
    KTAccountSettingsDeactivateAccount.init()
}));
