"use strict";
var KTUsersDeleteRoles = function() {
    return {
        
        init: function() {
                $(document).on('click', ".delete-role-button", function(e) {
                e.preventDefault();
                const roleUid = $(this).data('id');
                const page = $(this).data('type');
                Swal.fire({
                    text: _delete_question ,
                    icon: "warning",
                    showCancelButton: !0,
                    buttonsStyling: !1,
                    confirmButtonText: _confirm_delete,
                    cancelButtonText: _No,
                    customClass: {
                        confirmButton: "btn btn-primary",
                        cancelButton: "btn btn-active-light"
                    }
                }).then((function(t) {
                    t.value ? 
                    $.ajax({
                        url: ajaxBaseUrl + '/users/role/' + roleUid + '/delete',
                        type: 'POST',
                        data: {uid : roleUid, _token : csrfToken},
                        dataType: 'json',
                        cache: false,
                        success: function(response) {
                            if (response.type === 'success') {
                                Swal.fire({
                                    title: _Swal_success,
                                    text: response.message,
                                    icon: response.type,
                                    buttonsStyling: false,
                                    confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                }).then((function(t) {
                                    $('#'+roleUid).hide();
                                    (page == 1) ? window.location.href = app_role_view : null;
                                }))
                            } else {
                                Swal.fire({
                                    title: response.title,
                                    text: response.message,
                                    icon: response.type,
                                    buttonsStyling: false,
                                    confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                });
                            }
                        },
                        error: function () { 
                            $(document).trigger('onAjaxError');
                        },
                    })
                    
                    : Swal.fire({
                        text: _not_delete_confirm,
                        icon: "error",
                        buttonsStyling: !1,
                        confirmButtonText: _confirm_success,
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    })
                }))
             });
        }
    }
}();
KTUtil.onDOMContentLoaded((function() {
    KTUsersDeleteRoles.init()
}));
