"use strict";
const SenderManager = function(){
	var datatable, pEdit = false, pDelete = false, pStatus = false, pList = false, saveData = [];
	const el = document.querySelector("#tb_sender"), // el : selecteur de la table html
	elSenderModal = document.querySelector("#modal_sender"),
	senderModal = new bootstrap.Modal(elSenderModal),
	senderForm = elSenderModal.querySelector("#form_sender"),
	titleSenderModal = elSenderModal.querySelector("#title"),
	cancelSenderForm = senderForm.querySelector("#cancel"),
	submitSenderForm = senderForm.querySelector("#submit"),
	filter = document.querySelector("#menu-filter"), // filter : selecteur du div contenant les champs du filtre
	brandFilter = filter.querySelector("#brand"), // brand : selecteur du champ select brand du filtre
	userFilter = filter.querySelector("#user"), // user : selecteur du champ select user du filtre
	statusFilter = filter.querySelector("#status"), // status : selecteur du champ select status du filtre
	resetFilter = filter.querySelector("#reset"), // reset : selecteur du bouton reset du filtre
	submitFilter = filter.querySelector("#submit"),
	brandSender = senderForm.querySelector("#sender_brand"),
	userSender = senderForm.querySelector("#sender_manager"),
	// statusSender = senderForm.querySelector("#sender_status"),
	submitSender = senderForm.querySelector("#submit"),
	cls = [ // cls : colonnes du datatable
		{ // name
			targets: 0,
			responsivePriority: 0,
			render: function(data, type, full, meta) {
				return data;
			},

		},
		{ // status
			targets: 1,
			responsivePriority: 2,
			render: function(data, type, full, meta) {
				return '<span class="badge badge-light-'+data.label+'">' + data.name + '</span>';
			},

		},
		{ // createdAt
			targets: 2,
			responsivePriority: 3,
			render: function(data, type, full, meta) {
				return viewTime(data);
			}
		},
		{ // Marque
			targets: 3,
			visible: userType > 3 ? !1 : 1,
			responsivePriority: 4,
			render: function(data, type, full, meta) {
				return data[0];
			}
		},
		{ // manager
			targets: 4,
			visible: userType > 3 ? !1 : 1,
			responsivePriority: 5,
			render: function(data, type, full, meta) {
				return data[0];
			}
		},
		{ // updatedAt
			targets: 5,
			responsivePriority: 6,
			render: function(data, type, full, meta) {
				return viewTime(data);
			}
		},
		{ // observation
			targets: 6,
			orderable: !1,
			responsivePriority: 7,
			render : function (data,type, full, meta) {
				return "<p class='text'>"+data+"</p>";
			}
		},
		{ // Actions
			targets: 7,
			orderable: !1,
			responsivePriority: 1,
			render : function (data,type, full, meta) {
				saveData[data] = full;
				var icons = (pEdit && (pList || full[1].code === 2)) ? `<!--begin::Update-->
					<button class="btn btn-icon btn-active-light-primary w-30px h-30px" id="update" data-id=`+data+`>
						<span class="indicator-label"><i class="fa fa-edit"></i></span>
						<span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
					</button>
				<!--end::Update-->`:'';

				if(pStatus){
					icons += full[1].code !== 3 ? `<!--begin::Enable-->
						<button class="btn btn-icon btn-active-light-primary w-30px h-30px" id="enable" data-id=`+data+`>
							<span class="indicator-label"><i class="text-success fa fa-unlock"></i></span>
							<span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
						</button>
					<!--end::Enable-->` : `<!--begin::Disable-->
						<button class="btn btn-icon btn-active-light-danger w-30px h-30px" id="disable" data-id=`+data+`>
							<span class="indicator-label"><i class="text-warning fa fa-lock"></i></span>
							<span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
						</button>
					<!--end::Disable-->`;
				}

				if(pDelete){
					icons += `<!--begin::Delete-->
						<button class="btn btn-icon btn-active-light-danger w-30px h-30px" id="delete" data-id=`+data+`>
							<span class="indicator-label"><i class="text-danger fa fa-trash-alt"></i></span>
							<span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
						</button>
					<!--end::Delete-->`;
				}

				return icons;
			}
		}
	]
	;

	const initSelectsFilter = ()=>{
		// Filter
		$(brandFilter).select2({
			templateSelection: select2Format1,
			templateResult: select2Format1,
		});
		// Charge par ajax les utilisateurs sous la marque sélectionnée
		$(userFilter).css("width","100%");
		$(brandFilter).on("change.select2", ($this)=>{
			$(userFilter).select2({data:[{id:'',text:''}]});
			$(userFilter).val("").trigger("change.select2");
			$(userFilter).select2({
				data: dataUsers,
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

		if(brandInit) $(brandFilter).val(brandInit).trigger("change.select2"); else  $(brandFilter).val("").trigger("change.select2");

		if(userInit) $(userFilter).val(userInit).trigger("change.select2"); else  $(userFilter).val("").trigger("change.select2");
	}

	const initSelectsSender = ()=>{
					// Filter
		$(brandSender).select2({
			templateSelection: select2Format1,
			templateResult: select2Format1,
		});
		// Charge par ajax les utilisateurs sous la marque sélectionnée
		$(userSender).css("width","100%");
		$(brandSender).on("change.select2", ($this)=>{
			$(userSender).select2({data:[{id:'',text:''}]});
			$(userSender).val("").trigger("change.select2");
			$(userSender).select2({
				data: dataUsers,
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

		if(brandInit) $(brandSender).val(brandInit).trigger("change.select2");

		if(userInit) $(userSender).val(userInit).trigger("change.select2"); else $(userSender).val("").trigger("change.select2");
	}

	const resetSenderForm = ()=>{
		senderForm.reset();

		initSelectsSender()

		$(senderForm.querySelector("#sender__token")).val(_token);
		btnAnimation(submitSender);
	}

	const fillingSenderForm = (target)=>{ // filling : function pour remplir le formulaire sur click d'un bouton ayant data-id = uid de la ligne
		const $this = target.closest("button");
		if($($this).length == 0) return false;

		const id = $($this).attr("data-id");
		$(senderForm).attr("action",url_edit.replace("_1_", id));

		$(brandSender).val(saveData[id][3][1]).trigger("change")

		$(userSender).select2({
			data: [{id:saveData[id][4][1],text:saveData[id][4][0]}]
		});
		$(userSender).val(saveData[id][4][1]).trigger("change")

		$(senderForm.querySelector("#sender_name")).val(saveData[id][0]);
		$(senderForm.querySelector("#sender_observation")).val(saveData[id][6]);
		$(senderForm.querySelector("#sender_uid")).val(saveData[id][7]);
	}

	const post = (target, action)=>{ // post : function exécutant les changement de status (activation, désactivation et suppression)
		const $this = target.closest("button");
		if($($this).length == 0) return false;
		const id = $($this).attr("data-id");
		var message;
		switch (action) {
			case "1": message = _enabled_data; break;
			case "2": message = _delete_question; break;
			default: message = _disabled_data; break;
		}
		swalConfirm("warning", message, ()=>{
			btnAnimation($this, true);
			$.ajax({
				url: url_action.replace("_1_", id),
				type: 'post',
				data: {_token, action},
				dataType: 'json',
				success: function (response) {
					swalSimple(response.type, response.message);
					if (response.status === 'success') {
						datatable.ajax.reload();
					}
					btnAnimation($this);
				},
				error: function (response) {
					swalSimple("error", _Form_Error_Swal);
					btnAnimation($this);
					console.log(response);
				}
			});
		});
	}

	return {
		init: ()=>{
			datatable = $(el).DataTable({ // Initiation du datatable
				responsive: true,
				ajax: {
					"url": url_get,
					"type": "POST",
					data: {
						_token: function(){ return _token; },
						manager: function(){ return $(userFilter).val(); },
						brand: function(){ return $(brandFilter).val(); },
						status: function(){ return $(statusFilter).val(); },
					},
					dataSrc: function(response){
						if(response.message) swalSimple(response.type, response.message);

						pEdit		= response.data.permission.pEdit;
						pDelete		= response.data.permission.pDelete;
						pStatus		= response.data.permission.pStatus;
						pList		= response.data.permission.pList;

						return response.data.table;
					},
					error: function (response) {
						$(document).trigger('toastr.tableListError');
					}
				},
				info: !1,
				order: [[ 2, "desc" ]],
				columnDefs: cls,
				lengthMenu: [10, 25, 100, 250, 500, 1000],
				pageLength: 10,
				language: {
					url: _language_datatables,
				},
				dom: '<"top text-end bt-export d-none"B>rtF<"row"<"col-sm-6"l><"col-sm-6"p>>',
			});

			datatable.on('draw', ()=>{ // A chaque rafraichissement du tableau
				$(el).off("click", "#update");
				$(el).on("click", "#update", ($this)=>{
					$this.preventDefault();
					$(titleSenderModal).html(updateTitle)
					resetSenderForm();
					fillingSenderForm($this.target);
					senderModal.show();
				});

				$(el).off("click", "#enable");
				$(el).on("click", "#enable", ($this)=>{
					$this.preventDefault();
					post($this.target, "1");
				});

				$(el).off("click", "#disable");
				$(el).on("click", "#disable", ($this)=>{
					$this.preventDefault();
					post($this.target, "0");
				});

				$(el).off("click", "#delete");
				$(el).on("click", "#delete", ($this)=>{
					$this.preventDefault();
					post($this.target, "2");
				});

				loading();
			});

			$("#search").on('keyup', ($this)=>{ datatable.search($this.target.value).draw() })

			// Action sur bouton export
			$("#export").on('click', ($this)=>{
				$this.preventDefault();
				return $(".bt-export").hasClass('d-none')?$(".bt-export").removeClass('d-none'):$(".bt-export").addClass('d-none');
			})

			initSelectsFilter()

			$(submitFilter).on("click", ($this)=>{
				loading(true)
				datatable.ajax.reload()
			})

			$(resetFilter).on("click", ($this)=>{
				initSelectsFilter()
				$(statusFilter).val("").trigger("change.select2");
				loading(true);
				datatable.ajax.reload()
			})

			$(elSenderModal.querySelector("#close")).on("click", ($this)=>{
				$this.preventDefault();
				senderModal.hide()
			})

			$(cancelSenderForm).on("click", ($this)=>{
				$this.preventDefault();
				senderModal.hide()
			})

			initSelectsSender()

			$("#add_sender").on("click", ($this)=>{
				$this.preventDefault();
				$(titleSenderModal).html(addTitle)
				$(senderForm).attr("action",url_new);
				resetSenderForm()
				senderModal.show()
			})

			$(document).on("submit", "#form_sender", ($this)=>{ // soumission du formulaire
				$this.preventDefault();
				btnAnimation(submitSender, true);
				$.ajax({
					url: $(senderForm).attr("action"),
					type: 'post',
					data: new FormData(senderForm),
					processData: false,
					cache: false,
					contentType: false,
					success: function (response) {
						swalSimple(response.type, response.message);
						if (response.status === 'success') {
							datatable.ajax.reload();
							senderModal.hide();
						}
						btnAnimation(submitSender);
					},
					error: function (response) {
						swalSimple("error", _Form_Error_Swal);
						btnAnimation(submitSender);
						console.log(response);
					}
				});
			});
		}
	}
}();

KTUtil.onDOMContentLoaded((function() {
	SenderManager.init()
}));
