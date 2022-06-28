"use strict";
var spanStatAll =	document.getElementById("stat_all"),spanStatValidated = document.getElementById("stat_validated"), spanStatPending	= document.getElementById("stat_pending");

var KTUsersPaymentList = function() {
    var t, e, n, r, o;
    return {
        init: function() {
            (e = document.querySelector("#kt_payments_table")) && (e.querySelectorAll("tbody tr").forEach((t => {
                const e = t.querySelectorAll("td"), n = moment(e[2].innerHTML, "DD MMM YYYY, LT").format();
                e[2].setAttribute("data-order", n)
            })), t = $(e).DataTable({
                responsive: true,
                ajax: {
                    "url": payments_list_link,
                    "type": "POST",
                    data: {
                        _token: function(){ return csrfToken; }
                    },
                    dataSrc: function(json) {
                        spanStatAll.textContent = json.stats.all, spanStatValidated.textContent = json.stats.valisated, spanStatPending.textContent	= json.stats.pending
                        return json.data;
                    },
                    error: function () { 
                        $(document).trigger('toastr.tableListError');
                    }
                },
                info: !1,
                order: [[ 7, "desc" ]],
                columnDefs: [{
                    orderable: !1,
                    targets: 0, 
                    render: function (data, type, full, meta) {
                        return  `<div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" value="`+data+`" />
                                </div>`;
                    }
                },
                {
                    targets: 4,
                    render: function(data, type, full, meta) {
                        return '<img src="'+window.location.origin+'/app/media/svg/card-logos/'+data+'.svg" class="w-50px me-3" alt="'+data+' eContacts"/>';
                    },

                },{
                    orderable: !1,
                    targets: 3, 
                    render: function (data, type, full, meta) {
                        return `<!--begin::User details=-->
                                <div class="d-flex align-items-center">
                                    <!--begin:: Avatar -->
                                    <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                        <a href="javascript:;">
                                            <div class="symbol-label">
                                                <img src="`+window.location.origin+`/app/uploads/avatars/`+data.photo+`" alt="`+data.name+`" class="w-100" />
                                            </div>
                                        </a>
                                    </div>
                                    <!--end::Avatar-->
                                    <!--begin::User details-->
                                    <div class="d-flex flex-column">
                                        <a href="javascript:;" class="text-gray-800 text-hover-primary mb-1">`+data.name+`</a>
                                        <span>`+data.phone+`</span>
                                    </div>
                                    <!--begin::User details-->
                                </div>`;
                    }
                },
                {
                    orderable: 1,
                    targets: 7,
                    render: function(data, type, full, meta) {
                        return  viewTime(data);
                    }
                }, {
                    targets: 8,
                    render: function(data, type, full, meta) {
                        var status = {
                            8 : { 'title': _Rejected, 'class': 'danger' },
                            2 : { 'title': _Pending, 'class': 'warning' },
                            3 : { 'title': _Actif, 'class': 'success' },
                            4 : { 'title': _Disabled, 'class': 'primary' },
                            6 : { 'title': _Suspended, 'class': 'primary' },
                        };
                        if (typeof status[data] === 'undefined') {
                            return data;
                        }
                        return '<span class="badge badge-light-' + status[data].class + '">' + status[data].title + '</span>';
                    },

                },
                {
                    orderable: !1,
                    targets: 9,
                    visible: (!pEdit && !pDelete) ? false : true,
                    render : function (data,type, full, meta) {
                        var updaterIcon =  `<!--begin::Update-->
                        <button class="btn btn-icon btn-active-light-primary w-30px h-30px me-3 paymentUpdater" 
                        data-id=`+data[0]+`>
                        <i id="editPaymentOption`+data[0]+`" class="fa fa-eye"></i>
                        </button>
                        <!--end::Update-->`;
                        var deleterIcon =  `<!--begin::Delete-->
                        <button class="btn btn-icon btn-active-light-primary w-30px h-30px paymentDeleter" 
                            data-id=`+data[0]+` data-kt-payment-table-filter="delete_row">
                          <i id="deletePaymentOption`+data[0]+`" class="text-danger fa fa-trash-alt"></i>
                        </button>
                        <!--end::Delete-->`;
                        updaterIcon = (pEdit) ?  updaterIcon : '' ;
                        deleterIcon = (pDelete && data[1] == 0) ? deleterIcon : '' ;
                        return updaterIcon + deleterIcon;
                    }
                }],
                columns: [

                    { data: 'orderId' },

                    { data: 'reference' },

                    { data: 'transactionId' },

                    { data: 'user', responsivePriority: -10},

                    { data: 'method' ,responsivePriority: -7  },

                    { data: 'amount',responsivePriority: -8  },

                    { data: 'treatedby'  },

                    { data: 'createdAt' , responsivePriority: 0},

                    { data: 'status',responsivePriority: -8 },

                    { data: 'action',responsivePriority: -8 },
                ],
                lengthMenu: [10, 25, 100, 250, 500, 1000],
                pageLength: 10,
                lengthChange: !1,
                language: {
                    url: _language_datatables,
                },
                dom: '<"top text-end bt-export d-none"B>rtF<"row"<"col-sm-6"l><"col-sm-6"p>>',
            }), 
            // Action sur bouton export
            $(x).on('click', ($this)=>{ $this.preventDefault(); return $(y).hasClass('d-none')?$(y).removeClass('d-none'):$(y).addClass('d-none'); }),
            $('#kt_modal_add_payment_reload_button').on('click', function() {
                t.ajax.reload(null, false);
            }),
            
            document.querySelector('[data-kt-payment-table-filter="search"]').addEventListener("keyup", (function(e) {
                t.search(e.target.value).draw()
                UpdateStat(e)   
            })),
            document.querySelector('[data-kt-payment-table-filter="reset"]').addEventListener("click", (function() {
                document.querySelector('[data-kt-payment-table-filter="form"]').querySelectorAll("select").forEach((e => {
                        $(e).val("").trigger("change")
                    })),
                    e.search("").draw()
                    UpdateStat(e)
            })),

            c(), (() => {
                const t = document.querySelector('[data-kt-payment-table-filter="form"]'),
                    n = t.querySelector('[data-kt-payment-table-filter="filter"]'),
                    r = t.querySelectorAll("select");
                n.addEventListener("click", (function() {
                    loading(true);
                    setTimeout(() => {
                        loading()
                    }, 300);
                    var t = "";
                    r.forEach(((e, n) => {
                            e.value && "" !== e.value && (0 !== n && (t += " "),
                                t += e.value)
                        })),
                        e.search(t).draw()
                        UpdateStat(e)
                }));
            })
            ()  )//end

            
        }
    }
}();
KTUtil.onDOMContentLoaded((function() {
    KTUsersPaymentList.init()
}));

function UpdateStat(e) {
    // console.log(e['context'][0])
    let filtre_tab = e['context'][0]['aoData']
    let sum_tr_all = 0, sum_tr_pending = 0, sum_tr_validated = 0
    e['context'][0]['aiDisplay'].forEach(function(item){
    sum_tr_all      +=  1;

    if (parseInt(filtre_tab[item]['_aData'][6]) == 3) {
        sum_tr_validated        +=  1;

    }
    else if(parseInt(filtre_tab[item]['_aData'][6]) == 2){
        sum_tr_pending          +=  1;
        
    }
    });
    spanStatAll.textContent = sum_tr_all, spanStatPending.textContent = sum_tr_pending, spanStatValidated.textContent = sum_tr_actif   
}