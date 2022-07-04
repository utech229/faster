"use strict";
var mdHTMLTitle      = $("#kt_modal_add_user_title")
const userUidInput   = $('#user_uid');
const avatarPath     = window.location.origin+'/app/uploads/avatars/';


$(document).on('click', ".userUpdater", function(e) {
    var uid = $(this).data('id');
    loadingModal($("#editUserOption"+uid), 'fa fa-edit', true)
    if (permissionVerifier(pEditUser) == true){
        userUidInput.val(uid);
        const url = window.location.href +'/'+ uid + '/get';
        $.ajax({
            url: url,
            type: "post",
            data: {uid : uid, _token : csrfToken},
            dataType: "json",
            success: function(r) {
                mdHTMLTitle.html(_Edit);
                isUserUpdating = true;
                $('#user_firstName').val(r.data.firstname);
                $('#user_lastName').val(r.data.lastname);
                $('#user_email').val(r.data.email);
                $('#user_phone').val(r.data.phone);
                $('#kt_user_add_select2_country').val(r.data.countryCode).trigger('change');
                $('#city_select').val(r.data.city);
                $('#user_status').val(r.data.status).trigger('change');
                $('#user_licence').val(r.data.licence).trigger('change');
                $('#user_gender').val(r.data.gender).trigger('change');
                $("input[name=user_role][value=" + r.data.role+ "]").prop('checked', true);
                var cover = avatarPath + r.data.photo;
                $("#avatar_input").css("background-image", "url(" + cover + ")");
                formModalButton.click();
            },
            error: function () { 
                $(document).trigger('toastr.onAjaxError');
            }
        });
    }else
        $(document).trigger('entityUpStop', ['#editUserOption', uid, 'fa-edit']);
});

$('#kt_modal_add_user').on('hidden.bs.modal', function(e) {
    $(document).trigger('entityUpStop', ['#editUserOption', userUidInput.val(), 'fa-edit']);
    loadIcon($("#editUserOption"+userUidInput.val()), 'fa fa-edit')
    mdHTMLTitle.html(_Add);
    isUserUpdating = false;
    userUidInput.val(0);

});


