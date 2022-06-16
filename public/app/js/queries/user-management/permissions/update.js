"use strict";
var mdHTMLTitle            = $("#kt_modal_add_permission_text")
const permissionIDInput    = $('#permission_id');
var isPermissionUpdating   = false;
const permissionNotice     = $('#kt_modal_add_permission_notice');
const tableReloadButton    = $('#kt_modal_add_permission_reload_button');

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


$(document).on('click', ".permissionUpdater", function(e) {
    permissionNotice.removeClass('d-none');
    var uid = $(this).data('id');
    permissionIDInput.val(uid);
    $(document).trigger('entityUpBegin', ['#editPermissionOption', uid, 'fa-edit']);
    const url = window.location.href + uid + '/get';
    $.ajax({
        url: url,
        type: "post",
        data: {uid : uid, _token : csrfToken},
        dataType: "json",
        success: function(r) {
            $(document).trigger('securityFirewall', [r, '#editPermissionOption', uid, 'fa-edit']);
            mdHTMLTitle.html(_Edit);
            isPermissionUpdating = true;
            $('#permission_code').val(r.data.code);
            $('#permission_name').val(r.data.name);
            $('#description').val(r.data.description);
            formModalButton.click();
        },
        error: function () { 
            $(document).trigger('toastr.onAjaxError');
        }
    });
});

$('#kt_modal_add_permission').on('hidden.bs.modal', function(e) {
    $(document).trigger('entityUpStop', ['#editPermissionOption', permissionIDInput.val(), 'fa-edit']);
    mdHTMLTitle.html(_Add);
    isPermissionUpdating = false;
    permissionIDInput.val(0);
    permissionNotice.addClass('d-none');
});

$(document).on('securityFirewall', function(e, r, identifier, rowData, icon) {
    if (r.status == 'error')
        toastr.error(r.message),
        $(document).trigger('entityUpStop', [identifier, rowData, icon]);
});