"use strict";
var mdHTMLTitle      = $("#kt_modal_add_user_title")
const userUidInput   = $('#user_uid');
const avatarPath     = window.location.origin+'/app/uploads/avatars/';
//const userNotice     = $('#kt_modal_add_user_notice');


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
                $('#user_firstName').val(r.data.firstname);
                $('#user_lastName').val(r.data.lastname);
                $('#user_email').val(r.data.email);
                $('#user_phone').val(r.data.phone);
                $('#kt_user_add_select2_country').val(r.data.countryCode).trigger('change');
                $('#city_select').val(r.data.city);
                $('#user_status').val(r.data.status).trigger('change');
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
    mdHTMLTitle.html(_Add);
    isUserUpdating = false;
    userUidInput.val(0);
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
