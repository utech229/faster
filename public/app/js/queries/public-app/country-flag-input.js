
"use strict";
var KTAppCountrySelectWithFlags= {
    init:function() {
        var t=document.getElementById("kt_modal_add_user_form");
        t=function(t) {
            if(!t.id)return t.text;
            var e=document.createElement("span"),
            n="";
            return n+='<img src="'+t.element.getAttribute("data-kt-select2-country")+'" class="rounded-circle me-2" style="height:19px;" alt="image"/>',
            n+=t.text,
            e.innerHTML=n,
            $(e)
        },
        $('[data-kt-user-settings-type="select2_flags"]').select2({
            placeholder:"Select a country", minimumResultsForSearch:1/0, templateSelection:t, templateResult:t
        })
    },

    init2:function(){
        // Format options
        var optionFormat = function(item) {
            if ( !item.id ) {
                return item.text;
            }

            var span = document.createElement('span');
            var imgUrl = item.element.getAttribute('data-kt-select2-country');
            var template = '';

            template += '<img src="' + imgUrl + '" class="rounded-circle h-20px me-2" alt="image"/>';
            template += item.text;

            span.innerHTML = template;

            return $(span);
        }

        // Init Select2 --- more info: https://select2.org/
        $('#user_currency').select2({
            templateSelection: optionFormat,
            templateResult: optionFormat
        });

        $('#kt_user_add_select2_country').select2({
            templateSelection: optionFormat,
            templateResult: optionFormat
        });
    }
};
KTUtil.onDOMContentLoaded((function() {
    KTAppCountrySelectWithFlags.init()
    KTAppCountrySelectWithFlags.init2()
}));

