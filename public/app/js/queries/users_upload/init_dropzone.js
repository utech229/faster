"use strict";
const SMSCampaignManager = function(){
    const t = document.getElementById("kt_modal_add_user"),
        e = t.querySelector("#kt_modal_add_user_form"), 
        n = new bootstrap.Modal(t)
        ;
    const input_fileUrl = e.querySelector("[name=fileUrl]")
    return {
        init: ()=>{
            // Champ date et heure
            

            // Champ Importation
            var myDropzone = new Dropzone("#kt_import", {
                url: url_import, // Set the url for your upload script location
                paramName: "file", // The name that will be used to transfer the file
                maxFiles: 1,
                maxFilesize: 10, // MB
                addRemoveLinks: true,
                acceptedFiles: ".xlsx,.xls,.csv",
                // autoQueue: false,
                // autoProcessQueue: false,
                accept: function(file, done) {
                    $(input_fileUrl).val("");
                    done();
                },
                success: function(file, response){
                    console.log(response)
                    /*if(response.status == "success"){
                        $(input_fileUrl).val(response.data.url);
                        swalSimple(response.success, response.message)
                        swalConfirm(
                            "warning",
                            saveContacts,
                            ()=>{$(addContacts).val("true")},
                            ()=>{$(addContacts).val("false")},
                        );
                    }else swalSimple(response.type, response.message);*/
                }
            });
        }
    }
}();

KTUtil.onDOMContentLoaded((function() {
    //SMSCampaignManager.init();
}));
