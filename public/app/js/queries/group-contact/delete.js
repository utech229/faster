"use strict";

$(document).on('click', ".groupDeleter", function() {
	let uid = $(this).data('id');
    let tabUid  =  [];
    tabUid.push(uid)
	$(document).trigger('entityUpBegin', ['#', uid, 'fa-trash-alt']);
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
				url: group_delete,
				type: "post",
				data: { tabUid: tabUid, _token: function(){ return csrfToken; }},
				dataType: "json",
				success: function(response) {
					$(document).trigger('securityFirewall', [response, '#', uid, 'fa-trash-alt']);
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

                        $(document).trigger('entityUpStop', ['#', uid, 'fa-trash-alt']);
						let user = document.querySelector('[data-kt-contact-user="user"]').value;
                        rechargeGroups(user);
						$('#kt_modal_add_contact_group_reload_button').click();
                    }
				},
                error:function(response) {
					$(document).trigger('onAjaxError');
					$(document).trigger('entityUpStop', ['#', uid, 'fa-trash-alt']);
				}
			});	
		} else if (result.dismiss === 'cancel') {
			$(document).trigger('entityUpStop', ['#', uid, 'fa-trash-alt']);
			
		}
	});
	
});