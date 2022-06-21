"use strict";
const SMSCampaignManager = function(){
    const form = document.querySelector("#form"),
    submitForm = form.querySelector("#submit");

    return {
        init: ()=>{
            $(document).on("submit", form, ($this)=>{
                $this.preventDefault();
                btnAnimation(submitForm, true);
                $.ajax({
                    url: g,
                    type: 'post',
                    data: new FormData(f),
                    processData: false,
                    cache: false,
                    contentType: false,
                    success: function (response) {
                        swalSimple(response.type, response.message);
                        if (response.status === 'success') {
                            t.ajax.reload();
                            o.hide();
                        }
                    },
                    error: function (response) {
                        swalSimple("error", _Form_Error_Swal);
                        btnAnimation(i);
                        console.log(response);
                    }
                });
            });
        }
    }
}();

KTUtil.onDOMContentLoaded((function() {
    SMSCampaignManager.init()
}));
