"use strict";

$(document).on('click', ".permissionDeleter", function() {
	var uid = $(this).data('id');
	$(document).trigger('entityUpBegin', ['#deletePermissionOption', uid, 'fa-trash-alt']);
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
			loading(true);
			$.ajax({
				url: window.location.href + uid + '/delete',
				type: "post",
				data: {uid : uid, _token : csrfToken},
				dataType: "json",
				success: function(response) {
					$(document).trigger('securityFirewall', [response, '#deletePermissionOption', uid, 'fa-trash-alt']);
					if (response.status === 'success') 
					Swal.fire({
						text: response.message,
						icon: response.status,
						buttonsStyling: false,
						confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
						customClass: {
							confirmButton: "btn btn-primary"
						}
					});
					loading()
					$(document).trigger('entityUpStop', ['#deletePermissionOption', uid, 'fa-trash-alt']),
					tableReloadButton.click();
				},
                error:function(response) {
					$(document).trigger('onAjaxError');
					loading()
				}
			});	
		} else if (result.dismiss === 'cancel') {
			$(document).trigger('entityUpStop', ['#deletePermissionOption', uid, 'fa-trash-alt']),
			$(document).trigger('onAjaxInfo');
			loading()
		}
	});
	
});