"use strict";
const SMSCampaignManager = function(){
	const form = document.querySelector("#form"),
	submitForm = form.querySelector("#submit"),
	brand = form.querySelector("[name=brand]"),
	user = form.querySelector("[name=user]"),
	sender = form.querySelector("[name=sender]"),
	input_fileUrl = form.querySelector("[name=fileUrl]"),
	input_phones = form.querySelector("[name=phones]"),
	group_contacts = form.querySelector("#group_contacts"),
	addContacts = form.querySelector("[name=saveContacts]"),
	message = form.querySelector("[name=message]"),
	apercuMessage = form.querySelector("[name=true_message]")
	;

	var submitButton = submitForm;

	const initSelects = ()=>{
		// Filter
		$(brand).select2({
			templateSelection: select2Format1,
			templateResult: select2Format1,
		});
		// Charge par ajax les utilisateurs sous la marque sélectionnée
		$(user).css("width","100%");
		$(brand).on("change.select2", ($this)=>{
			const $thisValue = $($this.target).val()
			if(typeof defaultSenders[$thisValue] !== "undefined") viewAlert("#campaign_content", defaultSenders[$thisValue].name);
			$(user).select2({data:[{id:'',text:''}]});
			$(user).val("").trigger("change.select2");
			$(user).select2({
				data: dataUsers,
				ajax: {
					url: url_user,
					type: "post",
					data: {
						token: filter_token,
						brand: $thisValue,
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
			$(sender).val("").trigger("change.select2");
			$(sender).select2({
				data: dataSenders,
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

			// $(group_contacts).select2({data:[{id:'',text:''}]});
			$(group_contacts).val("").trigger("change");
			$(group_contacts).select2({
				ajax: {
					url: url_groups,
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

			$("#template").select2({data:[{id:'',text:''}]});
			$("#template").val("").trigger("change");
			$("#template").select2({
				ajax: {
					url: url_template,
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

		if(brandInit) $(brand).val(brandInit).trigger("change.select2");

		if(userInit) $(user).val(userInit).trigger("change.select2"); else $(user).val("").trigger("change.select2");

		if(senderInit) $(sender).val(senderInit).trigger("change.select2"); else $(sender).val("").trigger("change.select2");

		if(campaignType)	$("[name=type]").val(campaignType).trigger("change");
		if(timezone)		$("[name=timezone]").val(timezone).trigger("change");
		if(groups.length > 0)	$(group_contacts).val(groups).trigger("change");
	}

	const checkActiveTab = (finalPoint, $this)=>{
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

	return {
		init: ()=>{
			// Champ date et heure
			const now = moment();//.add(1,"h");
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
			const dropzoneEl = document.querySelector("#kt_import");
			if(dropzoneEl) var myDropzone = new Dropzone("#kt_import", {
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
							"success",
							saveContacts,
							()=>{$(addContacts).val("true")},
							()=>{$(addContacts).val("false")},
						);
					}else swalSimple(response.type, response.message);
				}
			});

			initSelects()

			$(document).on("submit", "#form", ($this)=>{
				$this.preventDefault();
				btnAnimation();
				$("[name=messageText]").val($(apercuMessage).val())
				btnAnimation(submitButton, true);
				$.ajax({
					url: $(form).attr("action"),
					type: 'post',
					data: new FormData(form),
					processData: false,
					cache: false,
					contentType: false,
					dataType: 'json',
					success: function (response) {
						btnAnimation();
						swalSimple(response.type, response.message);
						if (response.status === 'warning') {
							$("#brouillon").addClass("sr-only");
							$(submitForm).addClass("sr-only");
							SMSCampaignManagerError.init(response.data);
							$(form).trigger("reset");
						}else if (response.status === 'success') {
							window.location.replace(url_home);
						}
					},
					error: function (response) {
						swalSimple("error", _Form_Error_Swal);
						btnAnimation();
						console.log(response);
					}
				});
			});

			$("#template").on("change", ($this)=>{
				var text = $($this.target).val();
				if(text != "") $(message).val(text).trigger("change");
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

			// $("#clear").on("click", ()=>{$("#datetime").val("")})
			$("#form #clear").on("click", ()=>{$("#form #datetime").val("")})

			$("#form #brouillon").on("click", ($this)=>{
				$this.preventDefault()
				$("[name=saveMode]").val("offlive");
				toastr.info(textBrouillon)
				submitButton = ($this.target).closest("#brouillon");
				$("#form").submit()
			})

			$("#form #submit").on("click", ($this)=>{
				$this.preventDefault()
				$("[name=saveMode]").val("live");
				submitButton = ($this.target).closest("#submit");
				$("#form").submit()
			})

			$(message).on("keyup change", ()=>{$(apercuMessage).text(countMessageCaracts(message, "p#countOne"))});

			$(document).on("reset", "#form", ()=>{
				initSelects()
				$(apercuMessage).text(countMessageCaracts(message, "p#countOne"))
			});

			$(apercuMessage).text(countMessageCaracts(message, "p#countOne"));

			if(statusCode != -1 && statusCode != 0 && statusCode != 5){
				loading(true);
				$.ajax({
					url: url_check,
					type: 'post',
					data: {campaign:$("[name=id]").val()},
					dataType: "json",
					success: function (response) {
						loading();
						if (response.type === 'success') {
							SMSCampaignManagerError.init(response.data);
						}else{
							swalSimple(response.type, response.message, ()=>{
								window.location.replace(url_this);
							})
						}
					},
					error: function (response) {
						swalSimple("error", _Form_Error_Swal);
						loading();
						console.log(response);
					}
				});
			}
		}
	}
}();

KTUtil.onDOMContentLoaded((function() {
	SMSCampaignManager.init();
	SMSCampaignManagerError.init();
}));
