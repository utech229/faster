"use strict";

$(document).on('click', ".permissionDeleter", function() {
	var uid = $(this).data('id');
	$(document).trigger('entityUpBegin', ['#deletePermissionOption', uid, 'fa-trash-alt']);
	swalConfirm("warning",  _delete_question, ()=>{
		loading(true);
		$.ajax({
			url: delete_link.replace("_1_", uid),
			type: "post",
			data: {uid : uid, _token : csrfToken},
			dataType: "json",
			success: function(response) {
				$(document).trigger('securityFirewall', [response, '#deletePermissionOption', uid, 'fa-trash-alt']);
				swalSimple(response.type, response.message);
				loading()
				$(document).trigger('entityUpStop', ['#deletePermissionOption', uid, 'fa-trash-alt']),
				tableReloadButton.click();
			},
			error:function(response) {
				$(document).trigger('onAjaxError');
				loading()
			}
		});	
	});
	
});