"use strict";
const paymentIDInput       = $('#payment_id');
const tableReloadButton    = $('#kt_modal_add_payment_reload_button');
const btn1 = $('#kt_modal_payment_request_treat_submit_reject') , 
btn2 = $('#kt_modal_payment_request_treat_submit_validate');

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


$(document).on('click', ".paymentUpdater", function(e) {
    var uid = $(this).data('id');
    paymentIDInput.val(uid);
    $(document).trigger('entityUpBegin', ['#editPaymentOption', uid, 'fa-eye']);
    const url = window.location.href +'/'+ uid + '/get';
    $.ajax({
        url: url,
        type: "post",
        data: {uid : uid, _token : csrfToken},
        dataType: "json",
        success: function(r) {
            var statusColor, statusText;
            switch (r.data.status) {
                case 0:
                    statusColor = 'warning', statusText = 'Waiting';   
                    break;
                case 1:
                    statusColor = 'success', statusText = 'Validated';
                    btn1.hide()
                    btn2.hide()
                    $('#idtransaction').hide()
                    break;
                case 2:
                    statusColor = 'danger', statusText = 'Rejected';
                    btn1.hide()
                    btn2.hide()
                    $('#idtransaction').hide()
                    break;
                case 3:
                    statusColor = 'primary', statusText = 'Waiting';
                    btn1.hide()
                    btn2.hide()
                    $('#idtransaction').hide()
                    break;
                default:
                    break;
            }
            $(document).trigger('securityFirewall', [r, '#editPaymentOption', uid, 'fa-eye']);
            $("#d_user").text(r.data.user[0])
            $("#d_amount").html(r.data.amount)
            $("#d_idtransaction").val(r.data.transactionId)
            $("#d_reference").html(r.data.reference)
            $("#reference").text(r.data.reference)
            $("#d_operator").html(r.data.operator)
            $("#d_moyen").html(r.data.method)
            $("#d_owner").html(r.data.owner)
            $("#d_phone").html(r.data.phone);
            $("#d_validator").html(r.data.treatedby);
            (r.data.type == true) ? 
            document.getElementById("kt_modal_payment_type").checked = true:
            document.getElementById("kt_modal_payment_type").checked = false;
            $("#d_status").html('<span class="badge badge-light-' + statusColor + '">' + statusText + '</span>')
            formMModalButton.click();
        },
        error: function () { 
            $(document).trigger('toastr.onAjaxError');
        }
    });
});

$('#kt_modal_payment_request_treat').on('hidden.bs.modal', function(e) {
    $(document).trigger('entityUpStop', ['#editPaymentOption', paymentIDInput.val(), 'fa-eye']);
    paymentIDInput.val(0);
    btn1.show()
    btn2.show()
    $('#idtransaction').show()
    tableReloadButton.click();
});

$(document).on('securityFirewall', function(e, r, identifier, rowData, icon) {
    if (r.status == 'error')
        toastr.error(r.message),
        $(document).trigger('entityUpStop', [identifier, rowData, icon]);
});