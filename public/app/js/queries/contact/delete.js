"use strict";

$(document).on('click', ".contactDeleter", function() {
	let uid = $(this).data('id');
    let tabUid  =  [];
    tabUid.push(uid);
	$(document).trigger('entityUpBegin', ['#deleteC', uid, 'fa-trash-alt']);
	Swal.fire({
		text: _Deletion_request,
		icon: "warning",
		showCancelButton: true,
		buttonsStyling: false,
		confirmButtonText: _Yes,
		cancelButtonText: _No,
		customClass: {
			confirmButton: "btn fw-bold btn-danger",
			cancelButton: "btn fw-bold btn-active-light-primary"
		}
	}).then(function(result) {
		if (result.value) {
			$.ajax({
				url: contact_delete,
				type: "post",
				data: { tabUid: tabUid, _token: function(){ return csrfToken; }},
				dataType: "json",
				success: function(response) {
					$(document).trigger('securityFirewall', [response, '#deleteC', uid, 'fa-trash-alt']);
					Swal.fire({
                        text: response.message,
                        icon: response.type,
                        buttonsStyling: false,
                        confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                    if (response.status === 'success') {

                        $(document).trigger('entityUpStop', ['#deleteC', uid, 'fa-trash-alt']);
                        $('#kt_modal_add_contact_reload_button').click();
                    }
				},
                error:function(response) {
					$(document).trigger('onAjaxError');
					$(document).trigger('entityUpStop', ['#deleteC', uid, 'fa-trash-alt']);
				}
			});	
		} else if (result.dismiss === 'cancel') {
			$(document).trigger('entityUpStop', ['#deleteC', uid, 'fa-trash-alt']);
			
		}
	});
	
});