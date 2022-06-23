"use strict";
$.ajax({
    url:  get_methods_link,
    type: "post",
    data: {_token : csrfToken},
    dataType: "json",
    success: function(r) {
        if (r.data.momo.operator) {
            $('#momo_method_operator').val(r.data.momo.operator).trigger('change');
        }
        
    },
    error: function () { 
        $(document).trigger('toastr.onAjaxError');
        loading();
    }
});

     