"use strict";
var KTPaymentOverview= {
    init:function() {
        var initer = true;
        const payment_section_1 = $('#payment_section_1');
        const payment_section_2 = $('#payment_section_2');
        const payment_methode   = $('#payment_methode');
        
        const btn_one     = $('#payment_button')
        const btn_two     = $('#payment_method_button')

        $(btn_one).click(function() {
            loading(true)
            $(this).addClass('active');
            setTimeout(() => {
                payment_methode.addClass('d-none')
                payment_section_1.removeClass('d-none')
                payment_section_2.removeClass('d-none')

                btn_two.removeClass('active');
                loading()
            }, 100);
        });
        $(btn_two).click(function() {
            loading(true)
            $(this).addClass('active');
            setTimeout(() => {
                payment_methode.removeClass('d-none')
                payment_section_1.addClass('d-none')
                payment_section_2.addClass('d-none')
                
                btn_one.removeClass('active');
                loading()
            }, 100);
        });

    }
}

;

KTUtil.onDOMContentLoaded((function() {
    KTPaymentOverview.init()
}));
