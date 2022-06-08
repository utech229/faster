
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
    }
};
KTUtil.onDOMContentLoaded((function() {
    KTAppCountrySelectWithFlags.init()
}));
