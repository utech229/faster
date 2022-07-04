"use strict";
var KTcontactOverview= {
    init:function() {
        var isInit = false;
    
        var initer = true;
        const contact_group_section         = $('#contact_group_section');
        const contact_group_stat_section    = $('#contact_group_stat_section');
        const contact_section               = $('#contact_section');
        const contact_stat_section          = $('#contact_stat_section');
      
        const btn_one     = $('#contact_button')
        const btn_two     = $('#contact_group_button')

        $(btn_one).click(function() {
            loading(true)
            $(this).addClass('active');
            setTimeout(() => {
                contact_section.removeClass('d-none')
                contact_stat_section.removeClass('d-none')
                contact_group_stat_section.addClass('d-none')
                contact_group_section.addClass('d-none')
                btn_two.removeClass('active');
                loading()
            }, 100);
        });
        $(btn_two).click(function() {
            loading(true);
            $(this).addClass('active');
            setTimeout(() => {
                contact_group_stat_section.removeClass('d-none')
                contact_group_section.removeClass('d-none')
                contact_section.addClass('d-none')
                contact_stat_section.addClass('d-none')
                btn_one.removeClass('active');
                loading()
            }, 100);
            if(!isInit){ 
                KTUtil.onDOMContentLoaded((function() {
                    KTGroupList.init();
                }));
                isInit = true;
            }
        });

    }
}

;

KTUtil.onDOMContentLoaded((function() {
    KTcontactOverview.init()
}));
