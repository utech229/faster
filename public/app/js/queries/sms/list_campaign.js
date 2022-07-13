"use strict";
const SMSListCampaignManager = function(){
	var datatable, pEdit = false, pDelete = false, pList = false;

	const brand = document.querySelector("#menu-filter #brand"),
	user = document.querySelector("#menu-filter #user"),
	sender = document.querySelector("#menu-filter #sender"),
	status = document.querySelector("#menu-filter #status"),
	periode = document.querySelector("#menu-filter #periode"),
	el = document.querySelector("#campaign_overview_table"), // el : selecteur de la table html
	columns = [
		{ // Name
			targets: 0,
			responsivePriority: 0,
			render: function(data, type, full, meta) {
				return '<span href="#" class="text-gray-900 text-hover-primary">'+data+'</span>';
			},

		},
		{ // Expéditeur
			targets: 1,
			responsivePriority: 1,
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
		{ // Nombre de page (SMS)
			targets: 3,
			responsivePriority: 6,
			render: function(data, type, full, meta) {
				return "<div class='w-70 text-center'>"+data[1]+"</div>";
			},
			// className: "text-end",
		},
		{ // Status
			targets: 4,
			responsivePriority: 2,
			render: function(data, type, full, meta) {
				return '<span class="badge badge-light-'+data['label']+' fw-bolder me-auto">'+data['name']+'</span>';
			}
		},
		{ // Message
			targets: 5,
			//visible: false,
			responsivePriority: 7,
			render: function(data, type, full, meta) {
				return data;
			}
		},
		{ // Date de création
			targets: 6,
			responsivePriority: 5,
			render: function(data, type, full, meta) {
				return data;//viewTime(data);
			},

		},
		{ // Actions
			targets: 7,
			orderable: !1,
			responsivePriority: 3,
			render : function (data, type, full, meta) {
				return addContentPane(full, false);
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
			// $(sender).select2({data:[{id:'',text:''}]});
			$(sender).val("").change();//.trigger("change.select2");
			$(sender).select2({
				data: dataSenders,
				ajax: {
					url: url_sender_names,
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

	function addContentPane(dataInner, adding = true){
		var statusBar;
		switch (dataInner[4]["code"]) {
			case 1: statusBar = "warning"; break;
			case 9: statusBar = "danger"; break;
			case 5: statusBar = "danger"; break;
			case 8: statusBar = "success"; break;
			case 10: statusBar = "dark"; break;
			default: statusBar = "primary";
		}
		var action = `<!--begin::View-->
			<a href="`+url_home_message.replace('_1_', dataInner[7])+`" class="btn btn-icon btn-active-light-success w-30px h-30px" title="`+titleMessages+`" id="view">
				<span class="indicator-label"><i class="fa fa-mail-bulk"></i></span>
				<span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
			</a>
		<!--end::View-->`;

		if(pEdit && dataInner[4]['code'] != 8 && dataInner[4]['code'] != 1 && dataInner[4]['code'] != 2){
			action += `<!--begin::Update-->
				<a href="`+url_message_create.replace("_1_", dataInner[7])+`" class="btn btn-icon btn-active-light-warning w-30px h-30px" title="`+titleEdit+`" id="edit">
					<span class="indicator-label"><i class="fa fa-edit text-warning"></i></span>
					<span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
				</a>
			<!--end::Update-->`;
		}

		if(pEdit && dataInner[4]['code'] == 0){
			action += `<!--begin::disable-->
				<button class="btn btn-icon btn-active-light-dark w-25px h-25px" data-id="`+dataInner[7]+`" title="`+titleSuspend+`" id="disable">
					<span class="indicator-label"><i class="fa fa-power-off text-dark"></i></span>
					<span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
				</button>
			<!--end::disable-->`;
		}

		if(pEdit && dataInner[4]['code'] == 5){
			action += `<!--begin::Send-->
				<button class="btn btn-icon btn-active-light-primary w-25px h-25px" data-id="`+dataInner[7]+`" title="`+titleSend+`" id="send">
					<span class="indicator-label"><i class="fa fa-paper-plane text-primary"></i></span>
					<span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
				</button>
			<!--end::Send-->`;
		}

		if(pDelete && dataInner[4]['code'] != 1 && dataInner[4]['code'] != 2){
			action += `<!--begin::Delete-->
				<button class="btn btn-icon btn-active-light-danger w-25px h-25px" data-id="`+dataInner[7]+`" title="`+titleDelete+`" id="delete">
					<span class="indicator-label"><i class="fa fa-trash-alt text-danger"></i></span>
					<span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
				</button>
			<!--end::Delete-->`;
		}

		if(adding){
			$("#campaign_create_content").append(`<!--begin::Col-->
				<div class="col-md-4 d-flex align-items-stretch mt-0">
					<!--begin::Card-->
					<div class="card shadow-sm w-100 mb-6 mb-xl-9">
						<!--begin::Card body-->
						<div class="card-body pb-2">
							<!--begin::Header-->
							<div class="d-flex flex-stack mb-3">
								<!--begin::Badge-->
								<div class="badge badge-light">`+dataInner[1]+`</div>
								<!--end::Badge-->
								<!--begin::Menu-->
								<div>`+action+`</div>
								<!--end::Menu-->
							</div>
							<!--end::Header-->
							<!--begin::Title-->
							<div class="mb-2">
								<a href="javascript:void" class="fs-5 fw-bolder mb-1 text-gray-900 text-hover-primary">`+dataInner[0]+`</a>
							</div>
							<!--end::Title-->
							<!--begin::Content-->
							<div class="fs-7 fw-bold text-gray-600 mb-5">`+dataInner[5]+`</div>
							<!--end::Content-->
							<!--begin::Users-->
							<div class="w-100 text-center">
								<span class="badge badge-light-`+dataInner[4]['label']+` fw-bolder me-auto">`+dataInner[4]['name']+`</span>
							</div>
							<!--end::Users-->
							<!--begin::Footer-->
							<div class="d-flex flex-stack flex-wrapr">
								<!--begin::Stat-->
								<div class="border border-dashed border-gray-300 rounded py-2 px-3" title="">
									<span class="ms-1 fs-7 fw-bolder text-gray-600">`+dataInner[3][0]+`</span>
									<!--begin::Svg Icon | path: assets/media/icons/duotune/communication/com014.svg-->
									<span class="svg-icon svg-icon-3">
										<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
											<path d="M16.0173 9H15.3945C14.2833 9 13.263 9.61425 12.7431 10.5963L12.154 11.7091C12.0645 11.8781 12.1072 12.0868 12.2559 12.2071L12.6402 12.5183C13.2631 13.0225 13.7556 13.6691 14.0764 14.4035L14.2321 14.7601C14.2957 14.9058 14.4396 15 14.5987 15H18.6747C19.7297 15 20.4057 13.8774 19.912 12.945L18.6686 10.5963C18.1487 9.61425 17.1285 9 16.0173 9Z" fill="currentColor"/><rect opacity="0.3" x="14" y="4" width="4" height="4" rx="2" fill="currentColor"/><path d="M4.65486 14.8559C5.40389 13.1224 7.11161 12 9 12C10.8884 12 12.5961 13.1224 13.3451 14.8559L14.793 18.2067C15.3636 19.5271 14.3955 21 12.9571 21H5.04292C3.60453 21 2.63644 19.5271 3.20698 18.2067L4.65486 14.8559Z" fill="currentColor"/><rect opacity="0.3" x="6" y="5" width="6" height="6" rx="3" fill="currentColor"/>
										</svg>
									</span>
									<!--end::Svg Icon-->
								</div>
								<!--end::Stat-->
								<!--begin::Stat-->
								<div class="border border-dashed border-gray-300 rounded py-2 px-3 ms-3" title="">
									<span class="ms-1 fs-7 fw-bolder text-gray-600">`+dataInner[3][1]+`</span>
									<!--begin::Svg Icon | path: assets/media/icons/duotune/communication/com011.svg-->
									<span class="svg-icon svg-icon-3">
										<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
											<path opacity="0.3" d="M21 19H3C2.4 19 2 18.6 2 18V6C2 5.4 2.4 5 3 5H21C21.6 5 22 5.4 22 6V18C22 18.6 21.6 19 21 19Z" fill="currentColor"/><path d="M21 5H2.99999C2.69999 5 2.49999 5.10005 2.29999 5.30005L11.2 13.3C11.7 13.7 12.4 13.7 12.8 13.3L21.7 5.30005C21.5 5.10005 21.3 5 21 5Z" fill="currentColor"/>
										</svg>
									</span>
									<!--end::Svg Icon-->
								</div>
								<!--end::Stat-->
							</div>
							<!--end::Footer-->
						</div>
						<!--end::Card body-->
						<!--begin::Card footer-->
						<div class="card-footer py-2">
							<span class="fs-8 text-gray-600"><span class="fw-bold">`+datesend+` :</span> `+dataInner[2]+`</span>
						</div>
						<div class="h-3px w-100 bg-`+statusBar+`"></div>
						<!--end::Card footer-->
					</div>
					<!--end::Card-->
				</div>
				<!--end::Col-->
			`);
		}

		return action;
	}

	return {
		init: ()=>{
			datatable = $(el).DataTable({ // Initiation du datatable
				responsive: true,
				createdRow: function(row, data, key) {
					$(row).attr('data-row-id', key);
				},
				ajax: {
					"url": url_get,
					"type": "POST",
					data: {
						_token: function(){ return _token; },
						manager: function(){ return $(user).val(); },
						brand: function(){ return $(brand).val(); },
						sender: function(){ return $(sender).val(); },
						status: function(){ return $(status).val(); },
					},
					dataSrc: function(response){
						if(response.message) swalSimple(response.type, response.message);
						if(response.type == "success"){
							pEdit = response.data.permission.pEdit;
							pDelete = response.data.permission.pDelete;
							pList = response.data.permission.pList;

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
				// dom: '<"top text-end bt-export d-none"B>rtF<"row"<"col-sm-6"l><"col-sm-6"p>>',
				dom: `<"tab-content"
				<"top text-end bt-export d-none"B>
				<"#targets_card_pane.tab-pane fade row">
				<"#targets_table_pane.tab-pane table-responsive fade show active"rtF>>
				<"row"<"col-sm-6"l><"col-sm-6"p>>`
			});

			datatable.on('draw', ()=>{ // A chaque rafraichissement du tableau
				const view = datatable.data().context[0].aiDisplay;

				const data = datatable.data();

				var countProgramming	= 0;
				var countInProgress		= 0;
				var countSuspend		= 0;
				var countCompleted		= 0;
				var countBrouillon		= 0;

				for(var i = 0; i < data.length; i++)
				{
					if(view.indexOf(i) != -1){
						// console.log(data[i][2][1])
						switch (data[i][4]['code']) {
							case 0: countProgramming++; break;
							case 1: countInProgress++; break;
							case 2: countInProgress++; break;
							case 9: countSuspend++; break;
							case 5: countSuspend++; break;
							case 8: countCompleted++; break;
							case 10: countBrouillon++; break;
							default:
						}
					}
				}
				$("#count_programming").html(countProgramming);
				$("#count_in_progress").html(countInProgress);
				$("#count_suspend").html(countSuspend);
				$("#count_completed").html(countCompleted);
				$("#count_brouillon").html(countBrouillon);
				$("#count_all").html(countProgramming+countInProgress+countSuspend+countCompleted+countBrouillon);

				const alltr = el.querySelectorAll("tr");

				startContentPane();
				alltr.forEach(function(item) {
					const id = $(item).attr("data-row-id");
					if(typeof id !== 'undefined') addContentPane(data[id], true);
				});
				endContentPane();

				btnAnimation();

				$(".tab-content").off("click", "#view");
				$(".tab-content").on("click", "#view", ($this)=>{
					toastr.info(textRedirect)
				});

				$(".tab-content").off("click", "#send");
				$(".tab-content").on("click", "#send", ($this)=>{
					$this.preventDefault();
					const elem = $this.target.closest("#send");
					btnAnimation(elem, true);
					const id = $(elem).attr("data-id");
					swalConfirm("warning", sendConfirm, ()=>{
						$.ajax({
							url: url_enable,
							type: 'post',
							data: {_token, campaign:id},
							dataType: 'json',
							success: function (response) {
								if (response.status === 'success') {
									swalSimple(response.type, response.message);
									datatable.ajax.reload(null, false);
								}else if(response.data){
									swalConfirm("info", response.message+"<br/>"+redirectConfirm, ()=>{
										var url = url_message_create.replace("_1_", id)
										url = url.replace("_2_", "reload")
										window.location.replace(url)
										toastr.info(textRedirect)
									})
								}else{
									swalSimple(response.type, response.message);
								}
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

				$(".tab-content").off("click", "#disable");
				$(".tab-content").on("click", "#disable", ($this)=>{
					$this.preventDefault();
					const elem = $this.target.closest("#disable");
					btnAnimation(elem, true);
					const id = $(elem).attr("data-id");
					swalConfirm("warning", disableConfirm, ()=>{
						$.ajax({
							url: url_disable,
							type: 'post',
							data: {_token, campaign:id},
							dataType: 'json',
							success: function (response) {
								swalSimple(response.type, response.message);
								if (response.status === 'success') datatable.ajax.reload(null, false);
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

				$(".tab-content").off("click", "#edit");
				$(".tab-content").on("click", "#edit", ($this)=>{
					toastr.info(textRedirect)
				});

				$(".tab-content").off("click", "#delete");
				$(".tab-content").on("click", "#delete", ($this)=>{
					$this.preventDefault();
					const elem = $this.target.closest("#delete");
					btnAnimation(elem, true);
					const id = $(elem).attr("data-id");
					swalConfirm("error", deleteConfirm, ()=>{
						$.ajax({
							url: url_delete,
							type: 'post',
							data: {_token, campaign:id},
							dataType: 'json',
							success: function (response) {
								swalSimple(response.type, response.message);
								if (response.status === 'success') datatable.ajax.reload(null, false);
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

				// console.log(datatable.page.info());
			});

			// Action sur bouton export
			$("#export").on('click', ($this)=>{ $this.preventDefault(); return $(".bt-export").hasClass('d-none')?$(".bt-export").removeClass('d-none'):$(".bt-export").addClass('d-none'); });

			$("#search").on('keyup', ($this)=>{ datatable.search($this.target.value).draw(); }); // Recherche dans l'input search

			$("#reload").on('click', ()=>{ loading(true); datatable.ajax.reload(null, false); });

			initSelects()

			// Si bouton reset du filtre et cliqué
			$("#menu-filter #reset").on("click", ()=>{
				initSelects()
				$(status).val("").change();//.trigger("change");
				$(periode).val("1w").change();//.trigger("change");
				loading(true);
				datatable.ajax.reload();
			});

			// Si bouton submit du filtre et cliqué
			$("#menu-filter #submit").on("click", ()=>{loading(true); datatable.ajax.reload();});
		}
	}
}();

KTUtil.onDOMContentLoaded((function() {
	SMSListCampaignManager.init();
}));
