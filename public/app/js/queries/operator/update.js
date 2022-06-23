"use strict";
var mdHTMLTitle        = $("#kt_modal_add_operator_text")
const operatorIDInput    = $('#operator_id');
var isUpdating         = false;
const operatorNotice     = $('#kt_modal_add_operator_notice');
const tableReloadButton    = $('#kt_modal_add_operator_reload_button');

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


$(document).on('click', ".updater", function(e) {
    operatorNotice.removeClass('d-none');
    var uid = $(this).data('id');
    operatorIDInput.val(uid);
    $(document).trigger('entityUpBegin', ['#editOption', uid, 'fa-edit']);
    const url = window.location.href +'/'+ uid + '/get';
    $.ajax({
        url: url,
        type: "post",
        data: {uid : uid, _token : csrfToken},
        dataType: "json",
        success: function(r) {
            $(document).trigger('securityFirewall', [r, '#editOption', uid, 'fa-edit']);
            mdHTMLTitle.html(_Edit);
            isUpdating = true;
            $('#operator_name').val(r.data.name);
            $('#description').val(r.data.description);
            formModalButton.click();
        },
        error: function () { 
            $(document).trigger('toastr.onAjaxError');
        }
    });
});

$('#kt_modal_add_operator').on('hidden.bs.modal', function(e) {
    $(document).trigger('entityUpStop', ['#editOption', operatorIDInput.val(), 'fa-edit']);
    mdHTMLTitle.html(_Add);
    isUpdating = false;
    operatorIDInput.val(0);
    operatorNotice.addClass('d-none');
});

$(document).on('securityFirewall', function(e, r, identifier, rowData, icon) {
    if (r.status == 'error')
        toastr.error(r.message),
        $(document).trigger('entityUpStop', [identifier, rowData, icon]);
});