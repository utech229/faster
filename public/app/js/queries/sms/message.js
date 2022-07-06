"use strict";
const SMSMewMessageManager = function(){
	const btn_new = document.querySelector("#message_sms_create"),
	modalEl = document.querySelector("#modal_message"),
	modal = new bootstrap.Modal(modalEl),
	modalClose = modalEl.querySelector("#close"),
	btnClose = modalEl.querySelector("#cancel"),
	btnSubmit = modalEl.querySelector("#submit"),
	form_message = modalEl.querySelector("#message_form"),
	brand = form_message.querySelector("#brand_message"),
	user = form_message.querySelector("#user_message"),
	sender = form_message.querySelector("#sender_message"),
	message_phone = form_message.querySelector("#message_phone"),
	message = form_message.querySelector("#message_message"),
	true_message = form_message.querySelector("#true_message_message"),
	datetime_message = form_message.querySelector("#datetime_message")
	;
	var intlTel = null;

	return {
		init: ()=>{
			intlTel = intlPhone(message_phone);

			$(brand).select2({
				templateSelection: select2Format1,
				templateResult: select2Format1
			});

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
			$(datetime_message).flatpickr(flatpickr);
			$("[name=timezone]").val(now.format("Z")).trigger("change");

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

			if(brandInit) $(brand).val(brandInit).trigger("change");

			$(btnClose).on("click", ()=>{modal.hide()})
			$(modalClose).on("click", ()=>{modal.hide()})

			$(btn_new).on("click", (e)=>{
				$(message_phone).val("").trigger("change");
				$(datetime_message).val("").trigger("change");
				$(message).val("").trigger("change");
				modal.show();
			})

			$(message).on("keyup change", ()=>{
				$(true_message).val(countMessageCaracts(message, "#countThree"))
			})

			$("#message_form #clear").on("click", ()=>{$("#message_form #datetime_message").val("")})

			$(document).on("submit", "#message_form", ($this)=>{
				$this.preventDefault();
				btnAnimation(btnSubmit, true);
				$.ajax({
					url: $(form_message).attr("action"),
					type: 'post',
					data: new FormData(form_message),
					processData: false,
					cache: false,
					contentType: false,
					success: function (response) {
						btnAnimation(btnSubmit);
						if (response.status === 'success') {
							if(typeof datatableMessage === 'undefined'){
								swalConfirm("success", (response.message)+"<br/>"+loadPage, ()=>{window.location.replace(url_message)});
							}else{
								datatableMessage.ajax.reload();
							}
						}else{
							swalSimple(response.type, response.message);
						}
					},
					error: function (response) {
						swalSimple("error", _Form_Error_Swal);
						btnAnimation(btnSubmit);
						console.log(response);
					}
				});
			})
		}
	}
}();

KTUtil.onDOMContentLoaded((function() {
	SMSMewMessageManager.init();
}));

function countMessageCaracts(message, idp){
	const page1 = 160, page2 = 205, page3 = 305;
	var text = $(message).val();
	text = text.toString();

	//text = text.replace(/<br>|<br\/>|<br \/>|\\n|\\r/gi, (x)=>{return " ";});
	text = text.replace(/<[^>]*>/g, '');
	// text = text.replace(/&([A-za-z])(?:acute|grave|cedil|circ|orn|ring|slash|th|tilde|uml);/g, '');
	// text = text.replace(/<[^>]*>/g, '');
	// text = text.replace(/<[^>]*>/g, '');
	text = sansAccents(text);
	// $str = htmlentities($str, ENT_NOQUOTES, 'utf-8');
	// $str = preg_replace('#&([A-za-z])(?:acute|grave|cedil|circ|orn|ring|slash|th|tilde|uml);#', '\1', $str);
	// $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
	// $str = preg_replace('#&[^;]+;#', '', $str);
	//console.log(text);
	const nbr = text.length;
	$(idp+" #countMessage").text(nbr);
	switch (true) {
		case (nbr == 0): $(idp+" #countPageMessage").text("0"); break;
		case (nbr <= page1): $(idp+" #countPageMessage").text("1"); break;
		case (nbr <= page2): $(idp+" #countPageMessage").text("2"); break;
		case (nbr <= page3): $(idp+" #countPageMessage").text("3"); break;
		case (nbr > page3): $(idp+" #countPageMessage").text("+++"); return false;
		default: break;
	}

	function sansAccents(str){
		var accent = [
			/[\300-\306]/g, /[\340-\346]/g, // A, a
			/[\310-\313]/g, /[\350-\353]/g, // E, e
			/[\314-\317]/g, /[\354-\357]/g, // I, i
			/[\322-\330]/g, /[\362-\370]/g, // O, o
			/[\331-\334]/g, /[\371-\374]/g, // U, u
			/[\321]/g, /[\361]/g, // N, n
			/[\307]/g, /[\347]/g, // C, c
		];
		var noaccent = ['A','a','E','e','I','i','O','o','U','u','N','n','C','c'];

		//var str = this;
		for(var i = 0; i < accent.length; i++){
			str = str.replace(accent[i], noaccent[i]);
		}

		return str;
	}

	return text;
}
