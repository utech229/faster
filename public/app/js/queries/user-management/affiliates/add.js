"use strict";
var KTUsersAddUser = function() {
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
                                    extension: 'jpg,jpeg,png',
                                    type: 'image/jpeg,image/png',
                                    message: _File_Required
                                },
                            }
                        },
                        'user[firstName]': {
                            validators: {
                                notEmpty: {
                                    message: _FirstName_Required
                                },
                                stringLength: {
                                    min: 3,
                                    max: 30,
                                    message:  _Min_Max_Characters_20
                                },
                                regexp: {
                                    regexp: /^[a-zA-Z]+$/,
                                    message: _Only_Alphabetics
                                },
            
                            }
                        },
                        'user[lastName]': {
                            validators: {
                                notEmpty: {
                                    message: _LastName_Required
                                },
                                stringLength: {
                                    min: 3,
                                    max: 30,
                                    message:  _Min_Max_Characters_20
                                },
                                regexp: {
                                    regexp: /^[a-zA-Z]+$/,
                                    message: _Only_Alphabetics
                                },
            
                            }
                        },
                        'uBrand': {
                            validators: {
                                
                            }
                        },
                        'user[email]': {
                            validators: {
                                notEmpty: {
                                    message: _Email_NotEmpty_Connexion
                                },
                                emailAddress: {
                                    message: _Email_EmailAddress
                                }
                            }
                        },
                        'user[phone]': {
                            validators: {
                                notEmpty: {
                                    message: _Phone_Required
                                },
                                validePhone: {
                                    message: _Phone_Not_Valid,
                                }
                            }
                        },
                        'user[role]': {
                            validators: {
                                notEmpty: {
                                    message: _Role_Required
                                }
                            }
                        },
                        'user[is_dlr]': {
                            validators: {
                                notEmpty: {
                                    message: _Required_Field
                                }
                            }
                        },
                        'user[post_pay]': {
                            validators: {
                                notEmpty: {
                                    message: _Required_Field
                                }
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
                            loading(true),
                           ajax_url = (isUserUpdating == false) ? add_user : window.location.href + '/' + userUidInput.val()+ '/edit',
                            $('#user_phone').val(intl['user_phone'].getNumber()),
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
                                        t.isConfirmed,e.reset(),tableReloadButton.click();
                                        (isUserUpdating == true) ? n.hide() : null;
                                        statisticsReload();
                                        $('#user_is_dlr').val('0').trigger('change'), $('#user_post_pay').val('0').trigger('change');
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
                t.querySelector('[data-kt-users-modal-action="cancel"]').addEventListener("click", (t => {
                    t.preventDefault(), e.reset(), n.hide()
                })), t.querySelector('[data-kt-users-modal-action="close"]').addEventListener("click", (t => {
                    t.preventDefault(), e.reset(), n.hide()
                }))
            })()
        }
    }
}();
KTUtil.onDOMContentLoaded((function() {
    KTUsersAddUser.init();
}));