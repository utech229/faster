"use strict";

$(document).on('click', ".userDeleter", function() {
	var uid = $(this).data('id');
	$(document).trigger('entityUpBegin', ['#deleteUserOption', uid, 'fa-trash-alt']);
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
			loadModal(true)
			$.ajax({
				url: window.location.href +'/'+ uid + '/delete',
				type: "post",
				data: {uid : uid, _token : csrfToken},
				dataType: "json",
				success: function(response) {
					loadModal()
					$(document).trigger('securityFirewall', [response, '#deleteUserOption', uid, 'fa-trash-alt']);
					if (response.status === 'success') 
					Swal.fire({
						text: response.message,
						icon: response.status,
						buttonsStyling: false,
						confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
						customClass: {
							confirmButton: "btn btn-primary"
						}
					}),
					statisticsReload(),
					$(document).trigger('entityUpStop', ['#deleteUserOption', uid, 'fa-trash-alt']),
					tableReloadButton.click();	
				},
                error:function(response) {
					$(document).trigger('onAjaxError');
					$(document).trigger('entityUpStop', ['#deleteUserOption', uid, 'fa-trash-alt']);
				}
			});	
		} else if (result.dismiss === 'cancel') {
			loadModal()
			$(document).trigger('entityUpStop', ['#deleteUserOption', uid, 'fa-trash-alt']);
		}
	});
	
});