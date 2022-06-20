"use strict";
var KTUsersDeleteMethod= function() {
    return {
        
        init: function() {
                $(document).on('click', ".delete-payment-method-momo-button", function(e) {
                e.preventDefault();
				var method = $(this).data('id');
				console.log($(this).data('id'));
                Swal.fire({
                    text: _delete_question ,
                    icon: "warning",
                    showCancelButton: !0,
                    buttonsStyling: !1,
                    confirmButtonText: _confirm_delete,
                    cancelButtonText: _No,
                    customClass: {
                        confirmButton: "btn btn-primary",
                        cancelButton: "btn btn-active-light"
                    }
                }).then((function(t) {
                    if (t.value) {
                        load.removeClass('sr-only');
                        $.ajax({
                            url: delete_method_link, 
                            type: 'POST',
                            data: {method : method, _token : csrfToken },
                            dataType: 'json',
                            cache: false,
                            success: function(response) {
                                switch (response.type) {
                                    case 'success':
                                        load.addClass('sr-only');
                                        toastr.success(response.message);
                                        $('#'+contactUid).hide();
                                        break;
                                    case 'error':
                                        toastr.error(response.message);
                                        break;
                                    case 'info':
                                        toastr.info(response.message);
                                        break;
                                    default:
                                        toastr.warning(response.message);
                                        break;
                                }
                            },
                            error: function () { 
                                load.addClass('sr-only');
                                $(document).trigger('onAjaxError');
                            },
                        })
                    }
                    else {
                    Swal.fire({
                        text: _not_delete_confirm,
                        icon: "error",
                        buttonsStyling: !1,
                        confirmButtonText: _confirm_success,
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    })
					load.addClass('sr-only');}
                }))
             });
        }
    }
}();
KTUtil.onDOMContentLoaded((function() {
    KTUsersDeleteMethod.init()
}));
