

"use strict";
var spanStatAll =	document.getElementById("stat_all"),spanStatApproved = document.getElementById("stat_approved"), spanStatPending	= document.getElementById("stat_pending");

var KTpaymentsList = function() {
    var e, t, n, r, x = document.querySelector("#export"), y = ".bt-export", o = document.getElementById("kt_payments_table"),
        c = () => {
            
            o.querySelectorAll('[data-kt-payments-table-filter="delete_row"]').forEach((t => {
                t.addEventListener("click", (function(t) {
                    t.preventDefault();
                    const n = t.target.closest("tr"),
                        r = n.querySelectorAll("td")[1].querySelectorAll("a")[1].innerText;
                    Swal.fire({
                        text: "Are you sure you want to delete " + r + "?",
                        icon: "warning",
                        showCancelButton: !0,
                        buttonsStyling: !1,
                        confirmButtonText: _confirm_success,
                        cancelButtonText: _confirm_cancel,
                        customClass: {
                            confirmButton: "btn fw-bold btn-danger",
                            cancelButton: "btn fw-bold btn-active-light-primary"
                        }
                    }).then((function(t) {
                        t.value ? Swal.fire({
                            text: "You have deleted " + r + "!.",
                            icon: "success",
                            buttonsStyling: !1,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary"
                            }
                        }).then((function() {
                            e.row($(n)).remove().draw()
                        })).then((function() {
                            a()
                        })) : "cancel" === t.dismiss && Swal.fire({
                            text: customerName + " was not deleted.",
                            icon: "error",
                            buttonsStyling: !1,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary"
                            }
                        })
                    }))
                }))
            }))
        },
        l = () => {
            const c = o.querySelectorAll('[type="checkbox"]');
            t = document.querySelector('[data-kt-payment-table-toolbar="base"]'),
                n = document.querySelector('[data-kt-payment-table-toolbar="selected"]'),
                r = document.querySelector('[data-kt-payment-table-select="selected_count"]');
            const s = document.querySelector('[data-kt-payment-table-select="delete_selected"]');
            c.forEach((e => {
                    e.addEventListener("click", (function() {
                        setTimeout((function() {
                            a()
                        }), 50)
                    }))
                })),
                s.addEventListener("click", (function() {
                    Swal.fire({
                        text: "Are you sure you want to delete selected customers?",
                        icon: "warning",
                        showCancelButton: !0,
                        buttonsStyling: !1,
                        confirmButtonText: "Yes, delete!",
                        cancelButtonText: "No, cancel",
                        customClass: {
                            confirmButton: "btn fw-bold btn-danger",
                            cancelButton: "btn fw-bold btn-active-light-primary"
                        }
                    }).then((function(t) {
                        t.value ? Swal.fire({
                            text: "You have deleted all selected customers!.",
                            icon: "success",
                            buttonsStyling: !1,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary"
                            }
                        }).then((function() {
                            c.forEach((t => {
                                t.checked && e.row($(t.closest("tbody tr"))).remove().draw()
                            }));
                            o.querySelectorAll('[type="checkbox"]')[0].checked = !1
                        })).then((function() {
                            a(),
                                l()
                        })) : "cancel" === t.dismiss && Swal.fire({
                            text: "Selected customers was not deleted.",
                            icon: "error",
                            buttonsStyling: !1,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary"
                            }
                        })
                    }))
                }))
        };
    const a = () => {
        const e = o.querySelectorAll('tbody [type="checkbox"]');
        let c = !1,
            l = 0;
        e.forEach((e => {
                e.checked && (c = !0, l++)
            })),
            c ? (r.innerHTML = l,
                t.classList.add("d-none"),
                n.classList.remove("d-none")) : (t.classList.remove("d-none"),
                n.classList.add("d-none"))
    };
    return {
        init: function() {
            o && (o.querySelectorAll("tbody tr").forEach((e => {
                    const t = e.querySelectorAll("td"),
                        n = t[3].innerText.toLowerCase();
                    let r = 0,
                        o = "minutes";
                    n.includes("yesterday") ? (r = 1, o = "days") : n.includes("mins") ?
                        (r = parseInt(n.replace(/\D/g, "")),
                            o = "minutes") : n.includes("hours") ? (r = parseInt(n.replace(/\D/g, "")),
                            o = "hours") : n.includes("days") ? (r = parseInt(n.replace(/\D/g, "")),
                            o = "days") : n.includes("weeks") && (r = parseInt(n.replace(/\D/g, "")),
                            o = "weeks");
                    const c = moment().subtract(r, o).format();
                    t[3].setAttribute("data-order", c);
                    const l = moment(t[5].innerHTML, "DD MMM YYYY, LT").format();
                    t[5].setAttribute("data-order", l)
                })),
                (e = $(o).DataTable({
                    responsive: true,
                    ajax: {
                        "url": payments_list_link,
                        "type": "POST",
                        data: {
                            _token: function(){ return csrfToken; }
                        },
                        dataSrc: function(json) {
                            spanStatAll.textContent = json.stats.all, spanStatApproved.textContent = json.stats.approved, spanStatPending.textContent	= json.stats.pending
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
                                2 : { 'title': _Pending, 'class': 'warning' },
                                3 : { 'title': _Actif, 'class': 'success' },
                                4 : { 'title': _Disabled, 'class': 'danger' },
                                6 : { 'title': _Approved, 'class': 'success' },
                                7 : { 'title': _Canceled, 'class': 'secondary' },
                                9 : { 'title': _Rejected, 'class': 'danger' },
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
                }),
                $('#kt_modal_add_payment_reload_button').on('click', function() {
                    e.ajax.reload(null, false);
                })).on("draw", (function() { l(), c(), a()
                })), l(),
                document.querySelector('[data-kt-payment-table-filter="search"]').addEventListener("keyup", (function(t) {
                    e.search(t.target.value).draw(),
                    UpdateStat(e)   
                })),
                document.querySelector('[data-kt-payment-table-filter="reset"]').addEventListener("click", (function() {
                    document.querySelector('[data-kt-payment-table-filter="form"]').querySelectorAll("select").forEach((e => {
                            $(e).val("").trigger("change")
                        })),
                        e.search("").draw(),
                        UpdateStat(e)   
                })),
                c(), (() => {
                    const t = document.querySelector('[data-kt-payment-table-filter="form"]'),
                        n = t.querySelector('[data-kt-payment-table-filter="filter"]'),
                        r = t.querySelectorAll("select");
                    n.addEventListener("click", (function() {
                        var t = "";
                        loading(true);
                        setTimeout(() => {
                            loading()
                        }, 300);
                        r.forEach(((e, n) => {
                                e.value && "" !== e.value && (0 !== n && (t += " "),  t += e.value)
                            })), e.search(t).draw(),UpdateStat(e)   
                    }))
                })
                ())
        }
    }
}();
KTUtil.onDOMContentLoaded((function() {
    KTpaymentsList.init();
}));

function UpdateStat(e) {
    // console.log(e['context'][0])
    let filtre_tab = e['context'][0]['aoData']
    let sum_tr_all = 0, sum_tr_pending = 0, sum_tr_approved = 0
    e['context'][0]['aiDisplay'].forEach(function(item){
    sum_tr_all      +=  1;

    if (parseInt(filtre_tab[item]['_aData'][6]) == 3) {
        sum_tr_approved        +=  1;

    }
    else if(parseInt(filtre_tab[item]['_aData'][6]) == 2){
        sum_tr_pending          +=  1;
        
    }
    });
    spanStatAll.textContent = sum_tr_all, spanStatPending.textContent = sum_tr_pending, spanStatApproved.textContent = sum_tr_approved  
}