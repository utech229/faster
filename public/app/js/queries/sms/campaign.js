"use strict";
const SMSCampaignManager = function(){
    const form = document.querySelector("#form"),
    submitForm = form.querySelector("#submit"),
    brand = form.querySelector("[name=brand]"),
    user = form.querySelector("[name=user]"),
    sender = form.querySelector("[name=sender]"),
    input_fileUrl = form.querySelector("[name=fileUrl]"),
    input_phones = form.querySelector("[name=phones]"),
    group_contacts = form.querySelector("[name=groups]"),
    addContacts = form.querySelector("[name=saveContacts]"),
    page1 = 160, page2 = 205, page3 = 305,
    message = form.querySelector("[name=message]")
    ;

    return {
        init: ()=>{
            // Champ date et heure
            const now = moment().add(1,"h");
            var flatpickr = {
                minDate: "today",
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                enableSeconds: true,
                defaultHour: now.format("H"),
                defaultMinute: now.format("m"),
            };
            if(_locale.toUpperCase() == "FR") flatpickr["time_24hr"] = true;
            $("#datetime").flatpickr(flatpickr);
            $("[name=timezone]").val(now.format("Z")).trigger("change");

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
                    if(response.status == "success"){
                        $(input_fileUrl).val(response.data.url);
                        swalConfirm(
                            "warning",
                            saveContacts,
                            ()=>{$(addContacts).val("true")},
                            ()=>{$(addContacts).val("false")},
                        );
                    }else swalSimple(response.type, response.message);
                }
            });

            $(document).on("submit", form, ($this)=>{
                $this.preventDefault();
                btnAnimation(submitForm, true);
                $.ajax({
                    url: $(form).attr("action"),
                    type: 'post',
                    data: new FormData(form),
                    processData: false,
                    cache: false,
                    contentType: false,
                    success: function (response) {
                        btnAnimation(submitForm);
                        swalSimple(response.type, response.message);
                        if (response.status === 'warning') {
                            SMSCampaignManagerError.init(response.data);
                            $(form).trigger("reset");
                        }else if (response.status === 'success') {
                            window.location.replace(url_home);
                        }
                    },
                    error: function (response) {
                        swalSimple("error", _Form_Error_Swal);
                        btnAnimation(submitForm);
                        console.log(response);
                    }
                });
            });

            $(brand).select2({
                templateSelection: select2Format1,
                templateResult: select2Format1
            });

        	$(user).css("width","100%");
            $(brand).on("change", ($this)=>{
                $(user).select2({data:[{id:'',text:''}]});
                $(user).val("").trigger("change");
            	$(user).select2({
            		ajax: {
            			url: url_user,
            			type: "post",
            			data: {
            				token: filter_token,
            				brand: $($this.target).val(),
            			},
            			/*success: function(response){
            				return response;
            			}*/
            		},
            		language: _locale,
            		width: 'resolve'
            	});
            });

        	$(sender).css("width","100%");
            $(user).on("change", ($this)=>{
                $(sender).select2({data:[{id:'',text:''}]});
                $(sender).val("").trigger("change");
        	    $(sender).select2({
            		ajax: {
            			url: url_sender,
            			type: "post",
            			data: {
            				token: filter_token,
            				user: $($this.target).val(),
            			},
            			/*success: function(response){
            				return response;
            			}*/
            		},
            		language: _locale,
            		width: 'resolve'
            	});
            });

        	$(group_contacts).css("width","100%");
            $(user).on("change", ($this)=>{
                $(group_contacts).select2({data:[{id:'',text:''}]});
                $(group_contacts).val("").trigger("change");
        	    $(group_contacts).select2({
            		ajax: {
            			url: url_sender,
            			type: "post",
            			data: {
            				token: filter_token,
            				user: $($this.target).val(),
            			},
            			/*success: function(response){
            				return response;
            			}*/
            		},
            		language: _locale,
            		width: 'resolve'
            	});
            });

            $("#li_group").on("click", ($this)=>{
                checkActiveTab("group", $this)
            });

            $("#li_import").on("click", ($this)=>{
                checkActiveTab("import", $this)
            });

            $("#li_write").on("click", ($this)=>{
                checkActiveTab("write", $this)
            });

            function checkActiveTab(finalPoint, $this){
                const activeId = $("#dest_tab .active").attr("id");
                var valActiveInput;
                switch (activeId) {
                    case "write": valActiveInput = $(input_phones).val(); break;
                    case "import": valActiveInput = $(input_fileUrl).val(); break;
                    default: valActiveInput = $(group_contacts).val(); break;
                }
                if(activeId != finalPoint && valActiveInput != "" && valActiveInput != null) {
                    swalConfirm(
                        "warning",
                        ongletAlert,
                        ()=>{
                            $(group_contacts).val("").trigger("change");
                            $(input_fileUrl).val("");
                            $(input_phones).val("");
                        },
                        ()=>{
                            $("#dest_nav a").removeClass("active");
                            $("#dest_tab .tab-pane").removeClass("show active");

                            $("#li_"+activeId+" a").addClass("active");
                            $("#dest_tab #"+activeId).addClass("show active");
                        }
                    );
                }
            }

            $(message).on("keyup", ()=>{countMessageCaracts();});

            $(document).on("reset", form, ()=>{countMessageCaracts()});

            function countMessageCaracts(){
                var text = $(message).val(),
                nbr = text.length;

                $("#countMessage").text(nbr);
                switch (true) {
                    case (nbr == 0): $("#countPageMessage").text("0"); break;
                    case (nbr <= page1): $("#countPageMessage").text("1"); break;
                    case (nbr <= page2): $("#countPageMessage").text("2"); break;
                    case (nbr <= page3): $("#countPageMessage").text("3"); break;
                    case (nbr > page3): $("#countPageMessage").text("+++"); return false;
                    default: break;
                }
                return true;
            }
        }
    }
}();

KTUtil.onDOMContentLoaded((function() {
    SMSCampaignManager.init();
    SMSCampaignManagerError.init([]);
}));
