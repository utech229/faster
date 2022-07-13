"use strict";
var datatableMessage;
const SMSListMessageManager = function(){
	var pEdit = false, pDelete = false, pList = false;

	const brand = document.querySelector("#menu-filter #brand"),
	user = document.querySelector("#menu-filter #user"),
	sender = document.querySelector("#menu-filter #sender"),
	status = document.querySelector("#menu-filter #status"),
	periode = document.querySelector("#menu-filter #periode"),
	lfrom = document.querySelector("#menu-filter #lfrom"),
	lof = document.querySelector("#menu-filter #lof"),
	el = document.querySelector("#message_table"), // el : selecteur de la table html
	columns = [
		{ // Téléphone
			targets: 0,
			responsivePriority: 0,
			render: function(data, type, full, meta) {
				return '<span href="#" class="text-gray-900 text-hover-primary">'+data+'</span>';
			},

		},
		{ // Expéditeur
			targets: 1,
			responsivePriority: 2,
			render: function(data, type, full, meta) {
				return "<span class='badge badge-light fw-bold me-auto'>"+data+"</span>";
			},
			className: "fw-bolder",
		},
		{ // Date d'envoi
			targets: 2,
			responsivePriority: 4,
			render: function(data, type, full, meta) {
				return data;//viewTime(data);
			},

		},
		{ // Status
			targets: 3,
			responsivePriority: 1,
			render: function(data, type, full, meta) {
				return '<span class="badge badge-light-'+data['label']+' fw-bolder me-auto">'+data['name']+'</span>';
			}
		},
		{ // Nombre de page
			targets: 4,
			responsivePriority: 6,
			render: function(data, type, full, meta) {
				return "<div class='w-50 text-center'>"+data+"</div>";
			},
			//className: "text-end",
		},
		{ // Infos
			targets: 5,
			responsivePriority: 7,
			render: function(data, type, full, meta) {
				return '<span class="badge badge-light-dark fw-bolder me-auto">'+data['name']+' ('+data['code']+'); '+data['operator']+'</span>';;
			},
		},
		{ // Message
			targets: 6,
			responsivePriority: 8,
			render: function(data, type, full, meta) {
				return data;
			},
		},
		{ // Date de création
			targets: 7,
			responsivePriority: 5,
			render: function(data, type, full, meta) {
				return viewTime(data);
			},
		},
		{ // Actions
			targets: 8,
			orderable: !1,
			responsivePriority: 3,
			render : function (data, type, full, meta) {
				if(!data) return "";

				var action = "";

				if(pEdit && full[3]['code'] == 0){
					action += `<!--begin::disable-->
						<button class="btn btn-icon btn-active-light-dark w-25px h-25px" data-id="`+data+`" title="`+titleSuspend+`" id="disable">
							<span class="indicator-label"><i class="fa fa-power-off text-dark"></i></span>
							<span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
						</button>
					<!--end::disable-->`;
				}

				if(pEdit && full[3]['code'] == 5){
					action += `<!--begin::Send-->
						<button class="btn btn-icon btn-active-light-primary w-25px h-25px" data-id="`+data+`" title="`+titleSend+`" id="send">
							<span class="indicator-label"><i class="fa fa-paper-plane text-primary"></i></span>
							<span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
						</button>
					<!--end::Send-->`;
				}

				if(pDelete && full[3]['code'] != 1 && full[3]['code'] != 2){
					action += `<!--begin::Delete-->
						<button class="btn btn-icon btn-active-light-danger w-25px h-25px" data-id="`+data+`" title="`+titleDelete+`" id="delete">
							<span class="indicator-label"><i class="fa fa-trash-alt text-danger"></i></span>
							<span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
						</button>
					<!--end::Delete-->`;
				}

				return action;
			}
		}
	];

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
			$(user).select2({data:[{id:'',text:''}]});
			$(user).val("").change();//.trigger("change.select2");
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
			$(sender).val("").change();//.trigger("change.select2");
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
		});

		if(brandInit) $(brand).val(brandInit).change();//.trigger("change.select2");

		if(userInit) $(user).val(userInit).change();//.trigger("change.select2");
		else $(user).val("").change();//.trigger("change.select2");

		if(senderInit) $(sender).val(senderInit).change();//.trigger("change.select2");
		else $(sender).val("").change();//.trigger("change.select2");
	}

	return {
		init: ()=>{
			datatableMessage = $(el).DataTable({ // Initiation du datatable
				responsive: true,
				ajax: {
					"url": url_get,
					"type": "POST",
					data: {
						_token: function(){ return _token; },
						brand: function(){ return $(brand).val(); },
						manager: function(){ return $(user).val(); },
						sender: function(){ return $(sender).val(); },
						status: function(){ return $(status).val(); },
						periode: function(){ return $(periode).val(); },
						lfrom: function(){ return $(lfrom).val(); },
						lof: function(){ return $(lof).val(); },
					},
					dataSrc: function(response){
						if(response.message) swalSimple(response.type, response.message);
						if(response.type == "success"){
							pEdit	= response.data.permission.pEdit;
							pDelete	= response.data.permission.pDelete;
							pList	= response.data.permission.pList;

							return response.data.table;
						}
						return [];
					},
					error: function (response) {
						$(document).trigger('toastr.tableListError');
						loading();
						return [];
					}
				},
				info: !1,
				order: [[ 2, "desc" ]],
				columnDefs: columns,
				lengthMenu: [10, 25, 100, 250, 500, 1000],
				pageLength: 10,
				language: {
					url: _language_datatables,
				},
				dom: '<"top text-end bt-export d-none"B>rtF<"row"<"col-sm-6"l><"col-sm-6"p>>',
			});

			datatableMessage.on('draw', ()=>{ // A chaque rafraichissement du tableau
				const view = datatableMessage.data().context[0].aiDisplay;

				const data = datatableMessage.data();

				var countProgramming = 0;
				var countInProgress = 0;
				var countInvalid = 0;
				var countDelivered = 0;

				for(var i = 0; i < data.length; i++)
				{
					if(view.indexOf(i) != -1){
						// console.log(data[i][2][1])
						switch (data[i][3]['code']) {
							case 0: countProgramming++; break;
							case 1: countInProgress++; break;
							case 2: countInProgress++; break;
							case 9: countInvalid++; break;
							case 5: countInvalid++; break;
							case 8: countDelivered++; break;
							default:
						}
					}
				}
				$("#count_programming").html(countProgramming);
				$("#count_in_progress").html(countInProgress);
				$("#count_invalid").html(countInvalid);
				$("#count_delivered").html(countDelivered);

				$(el).off("click", "#disable");
				$(el).on("click", "#disable", ($this)=>{
					$this.preventDefault();
					const elem = $this.target.closest("#disable");
					btnAnimation(elem, true);
					const id = $(elem).attr("data-id");
					swalConfirm("warning", disableConfirm, ()=>{
						$.ajax({
							url: url_disable,
							type: 'post',
							data: {_token, message:id},
							dataType: 'json',
							success: function (response) {
								swalSimple(response.type, response.message);
								if (response.status === 'success') datatableMessage.ajax.reload(null, false);
								btnAnimation(elem);
							},
							error: function (response) {
								swalSimple("error", _Form_Error_Swal);
								btnAnimation(elem);
								console.log(response);
							}
						});
					}, ()=>{btnAnimation(elem);});
				});

				$(el).off("click", "#delete");
				$(el).on("click", "#delete", ($this)=>{
					$this.preventDefault();
					const elem = $this.target.closest("#delete");
					btnAnimation(elem, true);
					const id = $(elem).attr("data-id");
					swalConfirm("error", deleteConfirm, ()=>{
						$.ajax({
							url: url_delete,
							type: 'post',
							data: {_token, message:id},
							dataType: 'json',
							success: function (response) {
								swalSimple(response.type, response.message);
								if (response.status === 'success') datatableMessage.ajax.reload(null, false);
								btnAnimation(elem);
							},
							error: function (response) {
								swalSimple("error", _Form_Error_Swal);
								btnAnimation(elem);
								console.log(response);
							}
						});
					}, ()=>{btnAnimation(elem);});
				});

				$(el).off("click", "#send");
				$(el).on("click", "#send", ($this)=>{
					$this.preventDefault();
					const elem = $this.target.closest("#send");
					btnAnimation(elem, true);
					const id = $(elem).attr("data-id");
					swalConfirm("warning", sendConfirm, ()=>{
						$.ajax({
							url: url_enable,
							type: 'post',
							data: {_token, message:id},
							dataType: 'json',
							success: function (response) {
								swalSimple(response.type, response.message);
								if (response.status === 'success') datatableMessage.ajax.reload(null, false);
								btnAnimation(elem);
							},
							error: function (response) {
								swalSimple("error", _Form_Error_Swal);
								btnAnimation(elem);
								console.log(response);
							}
						});
					}, ()=>{btnAnimation(elem);});
				});
				loading();
			});

			// Action sur bouton export
			$("#export").on('click', ($this)=>{ $this.preventDefault(); return $(".bt-export").hasClass('d-none')?$(".bt-export").removeClass('d-none'):$(".bt-export").addClass('d-none'); });

			$("#search").on('keyup', ($this)=>{ datatableMessage.search($this.target.value).draw(); }); // Recherche dans l'input search

			initSelects()

			// Si bouton reset du filtre et cliqué
			$("#menu-filter #reset").on("click", ()=>{
				initSelects();
				$(status).val("").change();//.trigger("change");
				$(periode).val("1w").change();//.trigger("change");
				loading(true);
				datatableMessage.ajax.reload();
			});

			// Si bouton submit du filtre et cliqué
			$("#menu-filter #submit").on("click", ()=>{loading(true); datatableMessage.ajax.reload(null, false);});
		}
	}
}();

KTUtil.onDOMContentLoaded((function() {
	SMSListMessageManager.init();
}));
