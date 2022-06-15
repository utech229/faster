"use strict";
var KTUsersDeleteRoles = function() {
    return {
        
        init: function() {
                $(document).on('click', ".delete-role-button", function(e) {
                e.preventDefault();
                const roleUid = $(this).data('id');
                const page = $(this).data('type');
                var message = _delete_question
                swalConfirm("warning", message, ()=>{
                    loading(true)
                    $.ajax({
                        url:  delete_link.replace("_1_", roleUid),
                        type: 'POST',
                        data: {uid : roleUid, _token : csrfToken},
                        dataType: 'json',
                        cache: false,
                        success: function(response) {
                            loading()
                            swalSimple(response.type, response.message, ()=> {
                                if (response.type === 'success') {
                                    $('#'+roleUid).hide();
                                    (page == 1) ? window.location.href = app_role_view : null;
                                }
                            });
                        },
                        error: function () { 
                            loading()
                            $(document).trigger('onAjaxError');
                        },
                    })
                });
             });
        }
    }
}();
KTUtil.onDOMContentLoaded((function() {
    KTUsersDeleteRoles.init()
}));
