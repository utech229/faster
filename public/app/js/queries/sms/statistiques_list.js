"use strict";
const SMSStatistiquesManager = function(){
	// Define colors
	const primaryColor	= KTUtil.getCssVariableValue('--bs-primary'),
	dangerColor			= KTUtil.getCssVariableValue('--bs-danger'),
	successColor		= KTUtil.getCssVariableValue('--bs-success'),
	warningColor		= KTUtil.getCssVariableValue('--bs-warning'),
	infoColor			= KTUtil.getCssVariableValue('--bs-info');

	const primaryLightColor	= KTUtil.getCssVariableValue('--bs-light-primary'),
	dangerLightColor		= KTUtil.getCssVariableValue('--bs-light-danger'),
	successLightColor		= KTUtil.getCssVariableValue('--bs-light-success'),
	warningLightColor		= KTUtil.getCssVariableValue('--bs-light-warning'),
	infoLightColor			= KTUtil.getCssVariableValue('--bs-light-info');

	// Define fonts
	var fontFamily = KTUtil.getCssVariableValue('--bs-font-sans-serif');

	var graph1		= document.getElementById('graph1'),
	graph2			= document.getElementById('graph2'),
	graph3			= document.getElementById('graph3');

	graph1.getContext('2d');
	graph2.getContext('2d');
	graph3.getContext('2d');

	var chartGraph1 = null, chartGraph2 = null, chartGraph3 = null;

	var responseData = [{"graph1":[], "graph2":[], "graph3":[], "stats":[0,0,0,0]}];

	moment.locale(_locale);
	var tz = moment.tz.guess(true);

	var datatable, pEdit = false, pDelete = false, pList = false;

	const brand = document.querySelector("#menu-filter #brand"),
	user = document.querySelector("#menu-filter #user"),
	sender = document.querySelector("#menu-filter #sender"),
	status = document.querySelector("#menu-filter #status"),
	periode = document.querySelector("#menu-filter #periode"),
	el = document.querySelector("#Stats_table"),
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
			responsivePriority: 3,
			render: function(data, type, full, meta) {
				return "<span class='badge badge-light fw-bold me-auto'>"+data+"</span>";
			},
			className: "fw-bolder",
		},
		{ // Date d'envoi
			targets: 2,
			responsivePriority: 2,
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
			responsivePriority: 4,
			render: function(data, type, full, meta) {
				return "<div class='w-50 text-center'>"+data+"</div>";
			},
			//className: "text-end",
		},
		{ // Infos
			targets: 5,
			responsivePriority: 5,
			render: function(data, type, full, meta) {
				return '<span class="badge badge-light-dark fw-bolder me-auto">'+data['operator']+'-'+data['name']+'</span>';
			},
		},
		{ // Source
			targets: 6,
			responsivePriority: 6,
			render: function(data, type, full, meta) {
				return '<span class="badge badge-light fw-bolder me-auto">'+data[1]+'</span>';
			},
		},
		{ // Message
			targets: 7,
			responsivePriority: 8,
			render: function(data, type, full, meta) {
				return data;
			},
		},
		{ // Date de création
			targets: 8,
			responsivePriority: 7,
			render: function(data, type, full, meta) {
				return viewTime(data);
			},
		},
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

		$(status).val("").change();//.trigger("change");
		$(periode).val("1w").change();//.trigger("change");
	}

	// Init ChartJS -- for more info, please visit: https://www.chartjs.org/docs/latest/
	const initChart = async ()=>{
		var labels = [], labelsOP = [], dataGraph1 = [[],[],[],[]],
		dataGraph2 = [[],[],[]], dataGraph3 = [];

		var colorGraph3 = [warningColor, successColor, dangerColor, primaryColor, infoColor];

		$.each(responseData.graph3, function(index, item) {
			labelsOP.push(index);
			dataGraph3.push(item);
		});

		$.each(responseData.graph2, function(index, item) {
			switch ($(periode).val()) {
				case '1m':
					labels.push(moment(item[1]).tz(tz).format('ddd ll'));
					break;
				case '3m':
					labels.push(moment(item[1][0]).tz(tz).format('DD MMMM')+'-'+moment(item[1][1]).tz(tz).format('ll'));
					break;
				case '1y':
					labels.push(moment(item[1]).tz(tz).format('MMMM YYYY'));
					break;
				default:
					labels.push(moment(item[1]).tz(tz).format('ddd ll'));
					break;
			}

			dataGraph2[0].push(item[0][0]);
			dataGraph2[1].push(item[0][1]);
			dataGraph2[2].push(item[0][2]);
		});

		$.each(responseData.graph1, function(index, item) {
			dataGraph1[0].push(item[0][0]);
			dataGraph1[1].push(item[0][1]);
			dataGraph1[2].push(item[0][2]);
			dataGraph1[3].push(item[0][3]);
		});

		if(colorGraph3.length < labelsOP.length){
			var nbr = labelsOP.length - colorGraph3.length;
			for(var i = 0; i < nbr; i++) colorGraph3.push(infoColor);
		}

		if(chartGraph1) chartGraph1.destroy();
		if(chartGraph2) chartGraph2.destroy();
		if(chartGraph3) chartGraph3.destroy();

		// Chart labels
		chartGraph1 = new Chart(graph1, {
			type: 'bar',
			data: {
				labels: labels,
				datasets: [{
					label: labelProgramming,
					data: dataGraph1[0],
					backgroundColor: primaryColor,//"#1845c8",
					borderColor: primaryLightColor,//"#18457e",
					borderWidth: 1,
					color: "blue",
				},
				{
					label: labelSending,
					data: dataGraph1[1],
					backgroundColor: warningColor,
					borderColor: warningLightColor,
					borderWidth: 1,
					color: "blue",
				},
				{
					label: labelAvailabled,
					data: dataGraph1[2],
					backgroundColor: dangerColor,//'#dd3d3d',
					borderColor: dangerLightColor,//'#bc3434',
					borderWidth: 1,
					color: "blue",
				},
				{
					label: labelDelivred,
					data: dataGraph1[3],
					backgroundColor: successColor,//'#00aa1e',
					borderColor: successLightColor,//'#019a1c',
					borderWidth: 1,
					color: "blue",
				}]
			},
			options: {
				plugins: {
					title: {
						display: false,
					}
				},
				responsive: true,
				interaction: {
					intersect: true,
				},
				scales: {
					y: {
						beginAtZero: true
					}
				},
				transitions: {
					show: {
						animations: {
							x: {
								from: 0
							},
							y: {
								from: 0
							}
						}
					},
					hide: {
						animations: {
							x: {
								to: 0
							},
							y: {
								to: 0
							}
						}
					}
				}
			},
			defaults:{
				global: {
					defaultFont: fontFamily
				}
			}
		});

		chartGraph2 = new Chart(graph2, {
			type: 'radar',
			data: {
				labels: labels,
				datasets: [{
					label: labelCampaign,
					data: dataGraph2[0],
					backgroundColor: primaryColor,//"#1845c8",
					borderColor: primaryLightColor,//"#18457e",
					borderWidth: 1,
					color: "blue",
				},
				{
					label: labelAPI,
					data: dataGraph2[1],
					backgroundColor: successColor,//'#00aa1e',
					borderColor: successLightColor,//'#019a1c',
					borderWidth: 1,
					color: "blue",
				},
				{
					label: labelSMS,
					data: dataGraph2[2],
					backgroundColor: warningColor,//'#dd3d3d',
					borderColor: warningLightColor,//'#bc3434',
					borderWidth: 1,
					color: "blue",
				}]
			},
			options: {
				plugins: {
					title: {
						display: false,
					},
					legend: {
						labels: {
							// This more specific font property overrides the global property
							font: {
								size: 15,
								family: fontFamily
							}
						}
					}
				},
				responsive: true,
			},
			defaults:{
				global: {
					defaultFont: fontFamily
				}
			}
		});

		chartGraph3 = new Chart(graph3, {
			type: 'pie',
			data: {
				labels: labelsOP,
				datasets: [{
					label: "labelCampaign",
					data: dataGraph3,
					backgroundColor: colorGraph3,
					borderColor: colorGraph3,
					borderWidth: 1,
					color: "blue",
				}]
			},
			options: {
				plugins: {
					title: {
						display: false,
					},
					legend: {
						labels: {
							// This more specific font property overrides the global property
							font: {
								size: 15,
								family: fontFamily
							}
						}
					}
				},
				responsive: true,
			},
			defaults:{
				global: {
					defaultFont: fontFamily
				}
			}
		});
	}

	return {
		init: ()=>{
			datatable = $(el).DataTable({ // Initiation du datatable
				responsive: true,
				createdRow: function(row, data, key) {
					$(row).attr('data-row-id', key);
				},
				ajax: {
					"url": url_getList,
					"type": "POST",
					data: {
						_token: function(){ return _token; },
						brand: function(){ return $(brand).val(); },
						manager: function(){ return $(user).val(); },
						sender: function(){ return $(sender).val(); },
						status: function(){ return $(status).val(); },
						periode: function(){ return $(periode).val(); },
					},
					dataSrc: function(response){
						if(response.message) swalSimple(response.type, response.message);
						if(response.type == "success"){
							responseData.graph1 = response.data.graphs.graph1;
							responseData.graph2 = response.data.graphs.graph2;
							responseData.graph3 = response.data.graphs.graph3;
							responseData.stats = response.data.stats;

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
				dom: `<"top text-end bt-export"B>
					<"table-responsive w-100%"rtF>
					<"row"<"col-sm-6"l><"col-sm-6"p>>
				`,
			});

			datatable.on('draw', ()=>{ // A chaque rafraichissement du tableau
				if(responseData.stats){
					$("#count_programming").html(responseData.stats[0]);
					$("#count_in_progress").html(responseData.stats[1]);
					$("#count_invalid").html(responseData.stats[2]);
					$("#count_delivered").html(responseData.stats[3]);
				}

				initChart();
				loading();
			});

			initSelects()

			// Si bouton reset du filtre et cliqué
			$("#menu-filter #reset").on("click", ()=>{
				initSelects();
				loading(true);
				datatable.ajax.reload();
			});

			// Si bouton submit du filtre et cliqué
			$("#menu-filter #submit").on("click", ()=>{ loading(true); datatable.ajax.reload(null, false); });
		}
	}
}();

KTUtil.onDOMContentLoaded((function() {
	SMSStatistiquesManager.init();
}));
