"use strict";

$(document).on('click', ".deleter", function() {
	var uid = $(this).data('id');
	$(document).trigger('entityUpBegin', ['#deleteOption', uid, 'fa-trash-alt']);
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
			load.removeClass('sr-only');
			$.ajax({
				url: window.location.href + uid + '/delete',
				type: "post",
				data: {uid : uid, _token : csrfToken},
				dataType: "json",
				success: function(response) {
					$(document).trigger('securityFirewall', [response, '#deleteOption', uid, 'fa-trash-alt']);
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
					load.addClass('sr-only')
					$(document).trigger('entityUpStop', ['#deleteOption', uid, 'fa-trash-alt']),
					tableReloadButton.click();
				},
                error:function(response) {
					$(document).trigger('onAjaxError');
					load.addClass('sr-only')
				}
			});	
		} else if (result.dismiss === 'cancel') {
			$(document).trigger('entityUpStop', ['#deleteOption', uid, 'fa-trash-alt']),
			$(document).trigger('onAjaxInfo');
			load.addClass('sr-only')
		}
	});
	
});