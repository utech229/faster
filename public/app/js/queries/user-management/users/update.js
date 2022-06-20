"use strict";
var mdHTMLTitle      = $("#kt_modal_add_user_title")
const userUidInput   = $('#user_uid');
const avatarPath     = window.location.origin+'/app/uploads/avatars/';

$('#modalbrand').select2({
    templateSelection: select2Format1,
    templateResult: select2Format1
});
$(document).on('entityUpBegin', function(e, identifier, id, icon) {
    $(identifier + id).removeClass("fa");
	$(identifier + id).removeClass("fa");
	$(identifier + id).removeClass(icon).addClass("fa fa-spin fa-circle-notch");
});

$(document).on('entityUpStop', function(event, identifier, id, icon) {
    $(identifier + id).removeClass("fa");
	$(identifier + id).removeClass("fa-spin");
	$(identifier + id).removeClass("fa-circle-notch").addClass("fa " + icon);
});


$(document).on('click', ".userUpdater", function(e) {
    var uid = $(this).data('id');
    $(document).trigger('entityUpBegin', ['#editUserOption', uid, 'fa-edit']);
    if (permissionVerifier(pEditUser) == true){
        userUidInput.val(uid);
        const url = window.location.href +'/'+ uid + '/get';
        $.ajax({
            url: url,
            type: "post",
            data: {uid : uid, _token : csrfToken},
            dataType: "json",
            success: function(r) {
                $(document).trigger('securityFirewall', [r, '#editUserOption', uid, 'fa-edit']);
                mdHTMLTitle.html(_Edit);
                isUserUpdating = true;
                var phone = r.data.phone;
                $('#modalbrand').val(r.data.brand.uid).trigger('change');
                $("#modalbrand").prop('disabled', true);
                $("#brand_input").hide()
                $('#user_firstname').val(r.data.user.firstname);
                $('#user_lastname').val(r.data.user.lastname);
                $('#user_email').val(r.data.email);
                $('#user_phone').val(phone.substring(4, 20));
                $('#user_is_dlr').val(r.data.isDlr).trigger('change');
                $('#user_post_pay').val(r.data.isPostPay).trigger('change');
                $('#user_role').val(r.data.role.code).trigger('change');
                $('#kt_user_add_select2_country').val(r.data.countryCode).trigger('change');
                $('#user_status').val(r.data.status).trigger('change');
                var cover = avatarPath + r.data.photo;
                $("#avatar_input").css("background-image", "url(" + cover + ")");
                formModalButton.click();
            },
            error: function () { 
                $(document).trigger('entityUpStop', ['#editUserOption', uid, 'fa-edit']);
                $(document).trigger('toastr.onAjaxError');
            }
        });
    }else
        $(document).trigger('entityUpStop', ['#editUserOption', uid, 'fa-edit']);
});

$('#kt_modal_add_user').on('hidden.bs.modal', function(e) {
    $(document).trigger('entityUpStop', ['#editUserOption', userUidInput.val(), 'fa-edit']);
    mdHTMLTitle.html(_Add);
    isUserUpdating = false;
    userUidInput.val(0);
    $('#user_is_dlr').val('false').trigger('change');
    $('#user_post_pay').val('false').trigger('change');
    $("#modalbrand").prop('disabled', false);
    $("#brand_input").show()
});


$(document).on('securityFirewall', function(e, r, identifier, rowData, icon) {
    if (r.status == 'error')
        toastr.error(r.message),
        $(document).trigger('entityUpStop', [identifier, rowData, icon]);
});

function statisticsReload(){
    $.ajax({
        url:  statistic_link,
        type: "post",
        data: {_token : csrfToken},
        dataType: "json",
        success: function(r) {
            $('#stat_all').html(r.data.all);
            $('#stat_pending').html(r.data.pending);
            $('#stat_desactivated').html(r.data.desactivated);
            $('#stat_active').html(r.data.active);
            $('#stat_suspending').html(r.data.suspended);
        },
        error: function () { 
            $(document).trigger('toastr.onAjaxError');
        }
    });
};
