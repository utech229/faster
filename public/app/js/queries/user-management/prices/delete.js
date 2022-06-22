"use strict";

$(document).on('click', ".priceDeleter", function() {
	var uid = $(this).data('id');
	$(document).trigger('entityUpBegin', ['#deletepriceOption', uid, 'fa-trash-alt']);
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
				url: window.location.href +'/'+ uid + '/delete',
				type: "post",
				data: {uid : uid, _token : csrfToken},
				dataType: "json",
				success: function(response) {
					$(document).trigger('securityFirewall', [response, '#deletepriceOption', uid, 'fa-trash-alt']);
					$(document).trigger('entityUpStop', ['#deletepriceOption', uid, 'fa-trash-alt']),
					Swal.fire({
						text: response.message,
						icon: response.status,
						buttonsStyling: false,
						confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
						customClass: {
							confirmButton: "btn btn-primary"
						}
					});
					if (response.status === 'success'){
						statisticsReload(),
						tableReloadButton.click();	
					}
				},
                error:function(response) {
					$(document).trigger('onAjaxError');
					$(document).trigger('entityUpStop', ['#deletepriceOption', uid, 'fa-trash-alt']);
				}
			});	
		}
		$(document).trigger('entityUpStop', ['#deletepriceOption', uid, 'fa-trash-alt']);
		
	});
	
});