"use strict";

var KTAccountAPIKeys= {
    init:function() {
        
        const regenerateBtn = document.querySelector('[data-kt-regenerate-action="submit"]');
        //phone change ajax initer
        regenerateBtn.addEventListener("click", (function(n) {
            n.preventDefault(), 
                regenerateBtn.setAttribute("data-kt-indicator", "on"), regenerateBtn.disabled = !0;

                swal.fire({
                    text: _Regenerate_Apikey_Confirm, 
                    icon:"warning", buttonsStyling: !1, showDenyButton: !0, 
                    confirmButtonText:_Yes, 
                    denyButtonText:_No, 
                    customClass: {
                        confirmButton:"btn btn-light-primary", denyButton:"btn btn-danger"
                    }
                }).then((t=> {
                    t.isConfirmed ? 
                    ajaxCall() : 
                    t.isDenied&&Swal.fire({
                        text: _Not_Regenerate_Apikey, 
                        icon:"info", 
                        confirmButtonText:"Ok", 
                        buttonsStyling: !1, customClass: {
                            confirmButton:"btn btn-light-primary"
                        }
                    })
                }))

               
           
        }));

        function ajaxCall(){
            $.ajax({
                url:  regenerate_apikey_link,
                type: 'post',
                data:   { _token : csrfToken},
                dataType: 'json',
                success: function (response) {
                    regenerateBtn.removeAttribute("data-kt-indicator"), regenerateBtn.disabled = !1
                    Swal.fire({
                        text: response.message,
                        icon: response.type,
                        buttonsStyling: !1,
                        confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                    if (response.status == 'success')
                        $('#kt_referral_link_input').val(response.data)
                },
                error: function (response) {
                    regenerateBtn.removeAttribute("data-kt-indicator"), regenerateBtn.disabled = !1
                    $(document).trigger('onAjaxError');
                }
            });
        }
    } 
};

KTUtil.onDOMContentLoaded((function() {
    KTAccountAPIKeys.init()
}));
