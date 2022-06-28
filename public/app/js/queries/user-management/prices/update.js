"use strict";
var letUid;

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


$(document).on('click', ".priceUpdater", function(e) {
    var code = $(this).data('id');
    $(document).trigger('entityUpBegin', ['#priceOption', code, 'fa-edit']);
    const url = price_get_link.replace("_1_", user_uid);
    $.ajax({
        url: url,
        type: "post",
        data: {code:code, _token : csrfToken},
        dataType: "json",
        success: function(r) {
            $(document).trigger('securityFirewall', [r, '#priceOption', code, 'fa-edit']);
            ispriceUpdating = true;
            $('#price_price').val(r.data.price);
            $("#kt_user_add_select2_country").val(r.data.code).trigger('change');
            $("#price_input").removeClass('col-lg-6');
            $("#price_input").addClass('col-lg-12');
            $("#country_input").hide();
            formModalButton.click();
            letUid = code;
        },
        error: function () { 
            $(document).trigger('entityUpStop', ['#priceOption', code, 'fa-edit']);
            $(document).trigger('toastr.onAjaxError');
        }
    });
    
});

$('#kt_modal_add_price').on('hidden.bs.modal', function(e) {
    $(document).trigger('entityUpStop', ['#priceOption', letUid, 'fa-edit']);
    ispriceUpdating = false;
    $("#price_input").removeClass('col-lg-12');
    $("#price_input").addClass('col-lg-6');
    $("#country_input").hide();
});


$(document).on('securityFirewall', function(e, r, identifier, rowData, icon) {
    if (r.status == 'error')
        toastr.error(r.message),
        $(document).trigger('entityUpStop', [identifier, rowData, icon]);
});
