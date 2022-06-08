"use strict";
var onChange = false, occupation;
var KTAccountSettingsOverview= {
    init:function() {
        var initer = true;
        const detail_section = $('#kt_profile_details_view');
        const param_section  = $('#kt_profile_details_setting');
        const desactive_section       = $('#kt_profile_desactivate_account');
        const signin_method_section   = $('#kt_profile_sign_in_methods');
        
        var initButton       = $('#kt_profile_details_edit_button')
        var initNoticeButton = $('#kt_profile_details_edit_button_for_notice')
        var discardButton    = $('#kt_profile_details_discard_button')
        var reviewButton     = $('#kt_profile_details_review_button')
        $(initButton).click(function() {
            load.removeClass('sr-only'); 
            setTimeout(() => {
                param_section.removeClass('d-none')
                detail_section.addClass('d-none')
                load.addClass('sr-only'); 
            }, 100);
        });
        $(initNoticeButton).click(function() {
            load.removeClass('sr-only'); 
            setTimeout(() => {
                param_section.removeClass('d-none')
                detail_section.addClass('d-none')
                load.addClass('sr-only'); 
            }, 100);
        });
        $(discardButton).click(function() {
            load.removeClass('sr-only'); 
            setTimeout(() => {
                param_section.addClass('d-none')
                detail_section.removeClass('d-none')
                load.addClass('sr-only'); 
            }, 100);
            
        });
        $(reviewButton).click(function() {
            load.removeClass('sr-only'); 
            setTimeout(() => {
                param_section.addClass('d-none')
                detail_section.removeClass('d-none')
                load.addClass('sr-only'); 
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
