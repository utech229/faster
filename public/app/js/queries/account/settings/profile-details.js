"use strict";

var KTAccountSettingsProfileDetails=function() {
    var e, t, onChange = false;
    const avatarPath     = window.location.origin+'/app/uploads/avatars/';

    return {
        init:function() {

            e=document.getElementById("kt_account_profile_details_form"),
            e.querySelector("#kt_account_profile_details_submit"),
            t = FormValidation.formValidation(e, {
                fields: {
                    fname: {
                        validators: {
                            notEmpty: {
                                message: _FirstName_Required
                            }
                        }
                    }

                    , lname: {
                        validators: {
                            notEmpty: {
                                message: _LastName_Required
                            }
                        }
                    },
                    country: {
                        validators: {
                            notEmpty: {
                                message: _Birthday_Required
                            }
                        }
                    },
                    timezone: {
                        validators: {
                            notEmpty: {
                                message: _Timezone_Required
                            }
                        }
                    }
                    , language: {
                        validators: {
                            notEmpty: {
                                message:"Please select a language"
                            }
                        }
                    }
                }

                , plugins: {
                    trigger:new FormValidation.plugins.Trigger, submitButton:new FormValidation.plugins.SubmitButton, bootstrap:new FormValidation.plugins.Bootstrap5({
                        rowSelector:".fv-row", eleInvalidClass:"", eleValidClass:""
                    })
                }
            });

            $(e.querySelector('[name="country"]')).on("change", (function() {
                t.revalidateField("country")

            })),
            $(e.querySelector('[name="language"]')).on("change", (function() {
                t.revalidateField("language")

            })),
            $(e.querySelector('[name="timezone"]')).on("change", (function() {
                    t.revalidateField("timezone")
            }));
            const i = document.querySelector('[data-kt-account-profile-details-action="submit"]');
            i.addEventListener("click", (a => {
                a.preventDefault(), t && t.validate().then((function(v) {
                    "Valid" == v ? 
                    (i.setAttribute("data-kt-indicator", "on"), i.disabled = !0, 
                        $('[name=phone]').val(intl["user_phone"].getNumber()),
                        loading(true),
                        $.ajax({
                            url: profile_details_link,
                            type: 'post',
                            data:   new FormData(e),
                            processData: false,
                            contentType: false,
                            cache: false,
                            async: true,
                            dataType: 'json',
                            success: function (response) {
                                i.removeAttribute("data-kt-indicator"), i.disabled = !1
                                Swal.fire({
                                    text: response.message,
                                    icon: response.type,
                                    buttonsStyling: !1,
                                    confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }}).then((function(t) {
                                    const cover = user_avatar_link.replace("_1_", response.data);
                                    if (response.status == 'success'){
                                        $('#profile_avatar').attr('src', cover);
                                        $('#menu_profile_avatar').attr('src', cover);
                                        $('#menu_x_profile_avatar').attr('src', cover);
                                    }
                                }));
                                 loading();
                            },
                            error: function (response) {
                                i.removeAttribute("data-kt-indicator"), i.disabled = !1
                                $(document).trigger('onAjaxError');
                                 loading();
                            }
                        })) : $(document).trigger('onFormError'),  loading();;
                }))
            }));
            
        }
    }
}();

KTUtil.onDOMContentLoaded((function() {
            KTAccountSettingsProfileDetails.init()
}));
