"use strict";

var KTModalUpgradePlan=function() {

    var t,n,a,i,  licence , method = 'mobile', dismiss = $('#kt_modal_upgrade_plan_dismiss'),
    e=function(n) {
        [].slice.call(t.querySelectorAll("[data-kt-plan-price-month]")).map((function(t) 
        {
            var a=t.getAttribute("data-kt-plan-price-month"), 
            e=t.getAttribute("data-kt-plan-price-annual"); 
            "month"===n?t.innerHTML=a:"annual"===n&&(t.innerHTML=e)
        }
        ))
    };

    return {
        init:function() {
            (t=document.querySelector("#kt_modal_upgrade_plan"))&&(n=t.querySelector('[data-kt-plan="month"]'), 
            a=t.querySelector('[data-kt-plan="annual"]'), 
            n.addEventListener("click", (function(t) {
                method = 'mobile',  e("month")
            })), a.addEventListener("click", (function(t) {
                method = 'card',e("annual")
            })),
            i = t.querySelector('[data-kt-upgrade-plan-modal-action="submit"]'),
                i.addEventListener("click", (t => {
                    t.preventDefault(), 
                        (i.setAttribute("data-kt-indicator", "on"), 
                        i.disabled = !0, licence = $('input[name="plan"]:checked').val(),
                            $.ajax({
                                url: subscription_link,
                                type: 'post',
                                data: {licence : licence, method : method },
                                dataType: 'json',
                                success: function (response) {
                                    i.removeAttribute("data-kt-indicator"), i.disabled = !1,
                                    Swal.fire({
                                        text: response.message,
                                        icon: response.type,
                                        buttonsStyling: !1,
                                        confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        } 
                                    }),
                                    setTimeout(() => {
                                        if ((response.data.token !== "undefined"))
                                        {
                                            //dismiss.click(); 
                                            window.location.href = response.data.token.url;
                                        }
                                    }, 1000);
                                },
                                error: function (response) {
                                    i.removeAttribute("data-kt-indicator"), i.disabled = !1
                                    $(document).trigger('onAjaxError');
                                }
                            }))
                })),
                KTUtil.on(t, '[data-bs-toggle="tab"]', "click", (function(t) {
                this.querySelector('[type="radio"]').checked= !0
            })))
        }
    }
}

();

KTUtil.onDOMContentLoaded((function() {
            KTModalUpgradePlan.init()
        }));
