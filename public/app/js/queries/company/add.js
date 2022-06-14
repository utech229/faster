"use strict";
var KTUsersAddCompany = function() {
    const t = document.getElementById("kt_modal_profile_company"),
        e = t.querySelector("#kt_modal_profile_company_form"),
        n = new bootstrap.Modal(t);
    return {
        init: function() {
            (() => {
                var o = FormValidation.formValidation(e, {
                    fields: {
                        'company[name]': {
                            validators: {
                                notEmpty: {
                                    message: _PermissionName_Required 
                                }
                            }
                        },
                        'company[email]': {
                            validators: {
                                notEmpty: {
                                    message: _Required_Field
                                },
                                emailAddress: {
                                    message: _Email_EmailAddress
                                }
                            }
                        },
                        'company[phone]': {
                            validators: {
                                notEmpty: {
                                    message: _Phone_Number_Required
                                }, 
                                 validePhone: {
                                    message: _Phone_Not_Valid,
                                },
                                stringLength: {
                                    //min:0, max:9, message: _Phone_Not_Valid
                                }
                            }
                        },
                        'company[ifu]': {
                            validators: {
                                notEmpty: {
                                    message:_Required_Field
                                }
                            }
                        }
            
                        , 'company[rccm]': {
                            validators: {
                                notEmpty: {
                                    message:_Required_Field
                                }
                            }
                        },
                        adress: {
                            validators: {
                                notEmpty: {
                                    message: _PermissionDesc_Required 
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
                t.querySelector('[data-kt-company-modal-action="close"]').addEventListener("click", (t => {
                    t.preventDefault(),
                    t.value && n.hide()
                })), t.querySelector('[data-kt-company-modal-action="cancel"]').addEventListener("click", (t => {
                    t.preventDefault(), 
                    (e.reset(), n.hide())
                }));
                const i = t.querySelector('[data-kt-company-modal-action="submit"]');
                i.addEventListener("click", (function(t) {
                    t.preventDefault(), o && o.validate().then((function(t) {
                        console.log("validated!"), "Valid" == t ? (i.setAttribute("data-kt-indicator", "on"), i.disabled = !0, 
                        loading(true),
                        $.ajax({
                            url: company_manage_link,
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
                                        title: _Swal_success,
                                        text: response.message,
                                        icon: response.type,
                                        buttonsStyling: false,
                                        confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        }
                                    })

                                    if (response.type === 'success') {
                                        t.isConfirmed && e.reset();
                                        e.reset(), n.hide()
                                        if(response.data.isAdd == true){
                                            $('#company_unconfigurated').addClass('d-none')
                                            $('#company_is_section').removeClass('d-none');
                                        }
                                        $('#c_email').text(response.data.email)
                                        $('#c_phone').text(response.data.phone)
                                        $('#c_ifu').text(response.data.ifu)
                                        $('#c_rccm').text(response.data.rccm)
                                        $('#c_name').text(response.data.name)
                                    }
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

                $("#companyManageButton").click(function(){
                    reloader()
                });

                function reloader()
                {
                    $.ajax({
                        url: user_company_link,
                        type: 'post',
                        data: {_token : csrfToken},
                        dataType: 'json',
                        success: function(response) {
                            if (response.data.is == true) {
                                $('#company_name').val(response.data.name)
                                $('#company_email').val(response.data.email)
                                $('#company_phone').val(response.data.phone)
                                $('#company_ifu').val(response.data.ifu)
                                $('#company_rccm').val(response.data.rccm)
                                $('#address').val(response.data.address)
                            }
                        },
                        error: function () { 
                            $(document).trigger('onAjaxError');
                            i.removeAttribute("data-kt-indicator"), i.disabled = !1;
                            loading()
                        },
                    })
                };

            })()
        }
    }
}();
KTUtil.onDOMContentLoaded((function() {
    KTUsersAddCompany.init()
}));