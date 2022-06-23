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
    if (permissionVerifier(pEditAffiliate) == true){
        window.location.href = affiliate_price_link.replace("_1_", uid);
    }else
        $(document).trigger('entityUpStop', ['#editAffiliateOption', uid, 'fa-money-bill']);
});

$('#kt_modal_add_affiliate').on('hidden.bs.modal', function(e) {
    $(document).trigger('entityUpStop', ['#editAffiliateOption', affiliateUidInput.val(), 'fa-money-bill']);
    mdHTMLTitle.html(_Add);
    isAffiliateUpdating = false;
    affiliateUidInput.val(0);
    $('#affiliate_is_dlr').val('false').trigger('change');
    $('#affiliate_post_pay').val('false').trigger('change');
    $("#modalbrand").prop('disabled', false);
    $("#brand_input").show()
});


$(document).on('securityFirewall', function(e, r, identifier, rowData, icon) {
    if (r.status == 'error')
        toastr.error(r.message),
        $(document).trigger('entityUpStop', [identifier, rowData, icon]);
});

if (!pViewAffiliate) {
    $('#router_input').hide();
}



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
