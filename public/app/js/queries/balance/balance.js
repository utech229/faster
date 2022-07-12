"use strict";
const ListBalanceManager = function(){
	var datatable, pEdit = false, pDelete = false, pList = false;
	const el = document.querySelector("#tb_balance"), // el : selecteur de la table html
	filter = document.querySelector("#menu-filter"), // filter : selecteur du div contenant les champs du filtre
	brandFilter = filter.querySelector("#brand"), // brand : selecteur du champ select brand du filtre
	userFilter = filter.querySelector("#user"), // user : selecteur du champ select user du filtre
	statusFilter = filter.querySelector("#status"), // status : selecteur du champ select status du filtre
	resetFilter = filter.querySelector("#reset"), // reset : selecteur du bouton reset du filtre
	submitFilter = filter.querySelector("#submit"),
	cls = [ // cls : colonnes du datatable
		{ // utilisateur
			targets: 0,
			responsivePriority: 0,
			render: function(data, type, full, meta) {
				return "<a href='javascript:void(0)'>"+data+"</a>";
			},

		},
		{ // before
			targets: 1,
			responsivePriority: 1,
			render: function(data, type, full, meta) {
				return data;
			},

		},
		{ // amount
			targets: 2,
			responsivePriority: 2,
			render: function(data, type, full, meta) {
				return data;
			}
		},
		{ // After
			targets: 3,
			responsivePriority: 3,
			render: function(data, type, full, meta) {
				return data;
			}
		},
		{ // createdAt
			targets: 4,
			responsivePriority: 4,
			render: function(data, type, full, meta) {
				return viewTime(data);
			}
		},
		{ // observation
			targets: 5,
			orderable: !1,
			responsivePriority: 5,
			render : function (data,type, full, meta) {
				return "<p class='text'>"+data+"</p>";
			}
		},
		{ // Identifian
			targets: 6,
			orderable: !1,
			responsivePriority: 6,
			render : function (data,type, full, meta) {
				return "<p class='text'>"+data+"</p>";
			}
		},
		{ // Status
			targets: 7,
			orderable: !1,
			responsivePriority: 7,
			render : function (data,type, full, meta) {
				return '<span class="badge badge-light-'+data.label+'">' + data.name + '</span>';
			}
		}
	];

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
						pList		= response.data.permission.pList;

						return response.data.table;
					},
					error: function (response) {
						$(document).trigger('toastr.tableListError');
					}
				},
				info: !1,
				order: [[ 4, "desc" ]],
				columnDefs: cls,
				lengthMenu: [10, 25, 100, 250, 500, 1000],
				pageLength: 10,
				language: {
					url: _language_datatables,
				},
				dom: '<"top text-end bt-export d-none"B>rtF<"row"<"col-sm-6"l><"col-sm-6"p>>',
			});

			datatable.on('draw', ()=>{ // A chaque rafraichissement du tableau
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
		}
	}
}();

KTUtil.onDOMContentLoaded((function() {
	ListBalanceManager.init()
}));
