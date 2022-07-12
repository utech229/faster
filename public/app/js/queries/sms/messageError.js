"use strict";
const SMSCampaignManagerError = function(){
	const el = document.querySelector("#tb_error"), // el : selecteur de la table html
	columns = [
		{ // Number
			targets: 0,
			responsivePriority: 0,
			render: function(data, type, full, meta) {
				return data;
			},

		},
		{ // Phone
			targets: 1,
			responsivePriority: 1,
			render: function(data, type, full, meta) {
				return data;
			},

		},
		{ // messageError
			targets: 2,
			orderable: !1,
			responsivePriority: 3,
			render: function(data, type, full, meta) {
				return "<p class='fs-7 fw-bolder text-danger'>"+data+"</p>";
			},

		},
		{ // infos contact
			targets: 3,
			orderable: !1,
			responsivePriority: 4,
			render: function(data, type, full, meta) {
				var infos = "";
				if(data[1]) infos += data[1]["code"]
				if(data[1] && data[1]["operator"]) infos += "; "+data[1]["operator"];
				return "<span class='badge badge-light-warning fw-bold me-auto'>"+infos+"</span>";
			},

		},
		{ // message original
			targets: 4,
			orderable: !1,
			visible: !1,
			responsivePriority: 5,
			render: function(data, type, full, meta) {
				return data;
			}
		},
		{ // Actions
			targets: 5,
			orderable: !1,
			responsivePriority: 2,
			render : function (data, type, full, meta) {
				return `<!--begin::Update-->
					<button class="btn btn-icon btn-active-light-primary btn-hover-scale w-30px h-30px" id="update" data-id=`+data[0]+`>
						<span class="indicator-label"><i class="fa fa-edit"></i></span>
						<span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
					</button>
				<!--end::Update-->
				<!--begin::Delete-->
					<button class="btn btn-icon btn-active-light-danger btn-hover-scale w-30px h-30px" id="delete" data-id=`+data[0]+`>
						<span class="indicator-label"><i class="text-danger fa fa-trash-alt"></i></span>
						<span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
					</button>
				<!--end::Delete-->`;
			}
		}
	],
	editElement = document.querySelector("#modal_edit"),
	editModal = new bootstrap.Modal(editElement),
	editModalClose = editElement.querySelector("#close"),
	editForm = editElement.querySelector("#formEdith"),
	editModalCancel = editForm.querySelector("#cancel"),
	submitForm = editForm.querySelector("#submit"),
	messageForm = editForm.querySelector("[name=message]")
	;
	var datatable, data = [];

	const initDataTable = ()=>{
		datatable = $(el).DataTable({ // Initiation du datatable
			destroy: true,
			responsive: true,
			data,
			info: !1,
			order: [[ 5, "asc" ]],
			columnDefs: columns,
			lengthMenu: [10, 25, 100, 250, 500, 1000],
			pageLength: 10,
			language: {
				url: _language_datatables,
			},
			dom: '<"top text-end bt-export d-none"B>rtF<"row"<"col-sm-6"l><"col-sm-6"p>>',
		});
	}

	return {
		init: (dataSrc = [])=>{
			data = dataSrc;

			initDataTable();

			datatable.on('draw', ()=>{
				$(el).off("click", "button#update");
				$(el).on("click", "button#update", ($this)=>{
					const position = $($this.target.closest("button")).attr("data-id");

					if(position) {
						// intl['phone'].setNumber(data[position][3]['number']);
						$(editForm.querySelector("[name=phone]")).val(data[position][3][0]['number']);
						// $(editForm.querySelector("[name=param1]")).val(data[position][3]['param1']);
						// $(editForm.querySelector("[name=param2]")).val(data[position][3]['param2']);
						// $(editForm.querySelector("[name=param3]")).val(data[position][3]['param3']);
						// $(editForm.querySelector("[name=param4]")).val(data[position][3]['param4']);
						// $(editForm.querySelector("[name=param5]")).val(data[position][3]['param5']);
						$(editForm.querySelector("[name=message]")).val(data[position][4]);
						$(editForm.querySelector("[name=campaign]")).val(data[position][5][2]);
						$(editForm.querySelector("[name=position]")).val(data[position][5][1]);

						editModal.show();
					}
				});

				$(el).off("click", "button#delete");
				$(el).on("click", "button#delete", ($this)=>{
					$this.preventDefault();
					const ele = $this.target.closest("button");
					const position = $(ele).attr("data-id");

					btnAnimation(ele, true);
					swalConfirm("warning", deleteConfirm, ()=>{
						$.ajax({
							url: url_delete,
							type: 'post',
							data: {position: data[position][5][1], campaign: data[position][5][2]},
							dataType: 'json',
							success: function (response) {
								btnAnimation(ele);
								swalSimple(response.type, response.message);
								if (response.status === 'warning') {
									SMSCampaignManagerError.init(response.data);
								}else if (response.status === 'success') {
									window.location.replace(url_home);
								}
							},
							error: function (response) {
								swalSimple("error", _Form_Error_Swal);
								btnAnimation(ele);
								console.log(response);
							}
						});
					}, ()=>{btnAnimation(ele);});
				});
			});

			$(document).on("submit", "#formEdith", ($this)=>{
				$this.preventDefault();
				// if($($this.target).attr('id') === "formEdith")
				// {
					btnAnimation(submitForm, true);
					$.ajax({
						url: $(editForm).attr("action"),
						type: 'post',
						data: new FormData(editForm),
						processData: false,
						cache: false,
						contentType: false,
						success: function (response) {
							btnAnimation(submitForm);
							swalSimple(response.type, response.message);
							if (response.status === 'warning') {
								data = response.data;
								initDataTable();
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
				// }
			});

			$(editModalClose).on("click", ()=>{editModal.hide();})
			$(editModalCancel).on("click", ()=>{editModal.hide();})
			$(messageForm).on("keyup change", ()=>{countMessageCaracts(messageForm, "p#countTwo")});
		}
	}
}();

// KTUtil.onDOMContentLoaded((function() {
//     SMSCampaignManagerError.init()
// }));
