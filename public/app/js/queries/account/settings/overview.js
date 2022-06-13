"use strict";
var onChange = false, occupation;
var KTAccountSettingsOverview= {
    init:function() {
        var initer = true;
        const detail_section = $('#kt_profile_details_view');
        const profile_setting_section  = $('#kt_profile_details_setting');
        const desactivate_section              = $('#kt_profile_desactivate_account');
        const signin_method_section            = $('#kt_profile_sign_in_methods');
        const solde_notification_section       = $('#kt_profile_notification');
        const company_section                  = $('#kt_profile_company');
        
        var initButton       = $('#kt_profile_details_edit_button')
        var initNoticeButton = $('#kt_profile_details_edit_button_for_notice')
        var discardButton    = $('#kt_profile_details_discard_button')
        var reviewButton     = $('#kt_profile_details_review_button')
        
        const overviewButton           = $('#overview_link')
        const companyButton            = $('#company_link')
        const soldeNotificationButton  = $('#solde_notification_link')
        const signinMethodButton       = $('#signin_link')
        const desactivateButton        = $('#desactivate_link')

        $(initButton).click(function() {
            loading(true)
            setTimeout(() => {
                profile_setting_section.removeClass('d-none')
                detail_section.addClass('d-none')
                loading()
            }, 100);
        });
        $(initNoticeButton).click(function() {
            loading(true)
            setTimeout(() => {
                profile_setting_section.removeClass('d-none')
                detail_section.addClass('d-none')
                loading()
            }, 100);
        });
        $(discardButton).click(function() {
            loading(true)
            setTimeout(() => {
                profile_setting_section.addClass('d-none')
                detail_section.removeClass('d-none')
                loading()
            }, 100);
            
        });
        $(reviewButton).click(function() {
            loading(true)
            setTimeout(() => {
                profile_setting_section.addClass('d-none')
                detail_section.removeClass('d-none')
                loading()
            }, 100);
        });

        $(overviewButton).click(function() {
            loading(true)
            setTimeout(() => {
                companyButton.removeClass('active');
                soldeNotificationButton.removeClass('active');
                signinMethodButton.removeClass('active');
                desactivateButton.removeClass('active');
                $(this).addClass('active');

                profile_setting_section.addClass('d-none');
                company_section.addClass('d-none');
                detail_section.removeClass('d-none')
                desactivate_section.addClass('d-none')
                loading()
            }, 100);
        });

        $(companyButton).click(function() {
            loading(true)
            overviewButton.removeClass('active');
            $(this).addClass('active');
            setTimeout(() => {
                overviewButton.removeClass('active');
                soldeNotificationButton.removeClass('active')
                signinMethodButton.removeClass('active')
                desactivateButton.removeClass('active')

                company_section.removeClass('d-none')
                profile_setting_section.addClass('d-none')
                detail_section.addClass('d-none')
                desactivate_section.addClass('d-none')
                loading()
            }, 100);
        });

        $(signinMethodButton).click(function() {
            loading(true)
            $(this).addClass('active');
            setTimeout(() => {
                
                overviewButton.removeClass('active');
                companyButton.removeClass('active')
                soldeNotificationButton.removeClass('active')
                desactivateButton.removeClass('active')

                signin_method_section.removeClass('d-none');
                desactivate_section.addClass('d-none')
                company_section.addClass('d-none')
                profile_setting_section.addClass('d-none')
                solde_notification_section.addClass('d-none')
                detail_section.addClass('d-none')
                loading()
            }, 100);
        });

        $(soldeNotificationButton).click(function() {
            loading(true)
            $(this).addClass('active');
            
            setTimeout(() => {
                overviewButton.removeClass('active');
                companyButton.removeClass('active')
                signinMethodButton.removeClass('active')
                companyButton.removeClass('active')
                desactivateButton.removeClass('active')

                solde_notification_section.removeClass('d-none')
                desactivate_section.addClass('d-none')
                signin_method_section.addClass('d-none');
                company_section.addClass('d-none')
                profile_setting_section.addClass('d-none')
                detail_section.addClass('d-none')
                loading()
            }, 100);
        });

        $(desactivateButton).click(function() {
            loading(true)
            $(this).addClass('active');
            
            setTimeout(() => {
                overviewButton.removeClass('active');
                companyButton.removeClass('active')
                signinMethodButton.removeClass('active')
                soldeNotificationButton.removeClass('active')

                desactivate_section.removeClass('d-none')
                signin_method_section.addClass('d-none');
                company_section.addClass('d-none')
                profile_setting_section.addClass('d-none')
                detail_section.addClass('d-none')
                loading()
            }, 100);
        });

        
        $.ajax({
            url:  get_this_user_link,
            type: "post",
            data: {_token : csrfToken},
            dataType: "json",
            success: function(r) {
                $('#user_fname').val(r.data.firstname);
                $('#user_lname').val(r.data.lastname);
                $('#user_email').val(r.data.email);
                $('#user_gender').val(r.data.gender).trigger('change');
                $('#kt_user_add_select2_country').val(r.data.countryCode).trigger('change');;
                $('#user_language').val(r.data.language).trigger('change');;
                $('#user_timezone').val(r.data.timezone).trigger('change');
                $('#user_currency').val(r.data.currency).trigger('change');
            },
            error: function () { 
                $(document).trigger('toastr.onAjaxError');
            }
        });

      
    }
}

;

KTUtil.onDOMContentLoaded((function() {
    KTAccountSettingsOverview.init()
}));
