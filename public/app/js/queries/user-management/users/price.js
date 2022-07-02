"use strict";
$(document).on('entityUpBeginP', function(e, identifier, id, icon) {
    $(identifier + id).removeClass("fas");
	$(identifier + id).removeClass("fas");
	$(identifier + id).removeClass(icon).addClass("fa fa-spin fa-circle-notch");
});

$(document).on('entityUpStopP', function(event, identifier, id, icon) {
    $(identifier + id).removeClass("fas");
	$(identifier + id).removeClass("fa-spin");
	$(identifier + id).removeClass("fa-circle-notch").addClass("fas " + icon);
});


$(document).on('click', ".pricer", function(e) {
    var uid = $(this).data('id');
    $(document).trigger('entityUpBegin', ['#priceOption', uid, 'fa-money-bill']);
    if (permissionVerifier(pEditUser) == true){
        window.location.href = user_price_link.replace("_1_", uid);
    }else
        $(document).trigger('entityUpStop', ['#editUserOption', uid, 'fa-money-bill']);
});

$('#kt_modal_add_user').on('hidden.bs.modal', function(e) {
    $(document).trigger('entityUpStop', ['#editUserOption', userUidInput.val(), 'fa-money-bill']);
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

if (!pViewUser) {
    $('#router_input').hide();
}


