"use strict";
var datatableMessage;
const SMSListMessageManager = function(){
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
        { // Montant de la campagne
            targets: 4,
            responsivePriority: 4,
            render: function(data, type, full, meta) {
                return data[0]+" "+data[1];
            },
            className: "text-end",
        },
        { // Message
            targets: 5,
            responsivePriority: 5,
            render: function(data, type, full, meta) {
                return data;
            },
        },
        /*{ // Actions
            targets: 5,
            orderable: !1,
            responsivePriority: 3,
            render : function (data, type, full, meta) {
                var action = `<!--begin::View-->
                    <a href="" class="btn btn-icon btn-active-light-primary btn-hover-scale w-30px h-30px" title="" id="view">
                        <span class="indicator-label"><i class="fa fa-mail-bulk"></i></span>
                        <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </a>
                <!--end::View-->`;

                action += `<!--begin::Update-->
                    <a href="" class="btn btn-icon btn-active-light-primary btn-hover-scale w-30px h-30px" title="" id="update">
                        <span class="indicator-label"><i class="fa fa-edit"></i></span>
                        <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </a>
                <!--end::Update-->`;

                action += `<!--begin::Send-->
                    <a href="" class="btn btn-icon btn-active-light-primary btn-hover-scale w-30px h-30px" title="" id="send">
                        <span class="indicator-label"><i class="fa fa-paper-plane"></i></span>
                        <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </a>
                <!--end::Send-->`;

                // action += `<!--begin::Delete-->
                //     <button class="btn btn-icon btn-active-light-danger btn-hover-scale w-30px h-30px" id="delete" data-id=`+data+`>
                //         <span class="indicator-label"><i class="text-danger fa fa-trash-alt"></i></span>
                //         <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                //     </button>
                // <!--end::Delete-->`;

                return action;
            }
        }*/
    ];

    var e = false, d = false, l = false;

    return {
        init: ()=>{
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

            if(brandInit) $(brand).val(brandInit).trigger("change");
            if(userInit) $(user).val(userInit).trigger("change");

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
                            e = response.data.permission.pEdit;
                            d = response.data.permission.pDelete;
                            l = response.data.permission.pList;

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

            // Action sur bouton export
            $("#export").on('click', ($this)=>{ $this.preventDefault(); return $(".bt-export").hasClass('d-none')?$(".bt-export").removeClass('d-none'):$(".bt-export").addClass('d-none'); });

            $("#search").on('keyup', ($this)=>{ datatableMessage.search($this.target.value).draw(); }); // Recherche dans l'input search

            // Si bouton reset du filtre et cliqué
            $("#menu-filter #reset").on("click", ()=>{
                if(brandInit) $(brand).val(brandInit).trigger("change");
                if(userInit) $(user).val(userInit).trigger("change");
                $(sender).val("").trigger("change");
                $(status).val("").trigger("change");
                $(periode).val("1w").trigger("change");
                loading(true);
                datatableMessage.ajax.reload();
            });

            // Si bouton submit du filtre et cliqué
            $("#menu-filter #submit").on("click", ()=>{loading(true); datatableMessage.ajax.reload();});

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
                            case 8: countInvalid++; break;
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
                loading();
            });
        }
    }
}();

KTUtil.onDOMContentLoaded((function() {
    SMSListMessageManager.init();
}));
