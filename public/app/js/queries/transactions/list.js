
/*var KTTransactionsList = function() {
    var e, t, n, r, o = document.getElementById("kt_table_transactions"),
        l = () => {
            const c = o.querySelectorAll('[type="checkbox"]');
            t = document.querySelector('[data-kt-transaction-table-toolbar="base"]'),
                n = document.querySelector('[data-kt-transaction-table-toolbar="selected"]'),
                r = document.querySelector('[data-kt-transaction-table-select="selected_count"]');
            const s = document.querySelector('[data-kt-transaction-table-select="delete_selected"]');
            c.forEach((e => {
                    e.addEventListener("click", (function() {
                        setTimeout((function() {
                            a()
                        }), 50)
                    }))
                })),
                s.addEventListener("click", (function() {
                    Swal.fire({
                        text: "Are you sure you want to delete selected transactions?",
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
                            text: "You have deleted all selected transactions!.",
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
                            text: "Selected transactions was not deleted.",
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
                        url: transaction_list_link,
                        type: "POST",
                        data: {
                            _token: function(){ return csrfToken; }
                        },
                        error: function () { 
                            $(document).trigger('toastr.onAjaxError');
                        }
                    },
                    info: !1,
                    order: [],
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
                        orderable: !1,
                        targets: 1, 
                        render: function (data, type, full, meta) {
                            return `<!--begin::Transaction=-->
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
                                        <!--begin::Transaction details-->
                                        <div class="d-flex flex-column">
                                            <a href="javascript:;" class="text-gray-800 text-hover-primary mb-1">`+data.name+`</a>
                                            <span>`+data.email+`</span>
                                        </div>
                                        <!--begin::Transaction details-->
                                    </div>`;
                        }
                    },
                    {
                        targets: 3,
                        render: function(data, type, full, meta) {
                            var status = {
                                'ROLE_TRANSACTION': { 'title': 'Utilisateur', 'class': 'danger' },
                                'ROLE_PROFESSIONAL': { 'title': 'Professionnel', 'class': 'success' },
                                'ROLE_COMPANY': { 'title': 'Entreprise', 'class': 'warning' },
                                'ROLE_AMBASSADOR': { 'title': 'Ambassadeur', 'class': 'primary' },
                                'ROLE_ADMIN': { 'title': 'Administrateur', 'class': 'secondary' },
                                'ROLE_SUPER_ADMIN': { 'title': 'Super administrateur', 'class': 'info' },
                            };
                            if (typeof status[data] === 'undefined') {
                                return data;
                            }
                            return '<span class="badge badge-light-' + status[data].class + '">' + status[data].title + '</span>';
                        },

                    },{
                        targets: 7,
                        render: function(data, type, full, meta) {
                            var status = {
                                'M': { 'title': 'Homme', 'class': 'info' },
                                'F': { 'title': 'Femme', 'class': 'primary' },
                            };
                            if (typeof status[data] === 'undefined') {
                                return data;
                            }
                            return '<span class="badge badge-light-' + status[data].class + '">' + status[data].title + '</span>';
                        },

                    }/*,{
                        targets: 9,
                        render: function(data, type, full, meta) {
                           return '<img src="'+window.location.origin+'/app/media/flags/'+data+'.svg" class="rounded-circle me-2"  style="height:19px;" alt="'+data+'" />'+data
                        },

                    },
                    {
                        targets: 10,
                        render: function(data, type, full, meta) {
                            var status = {
                                true : { 'title': _Activated, 'class': 'success' },
                                false : { 'title': _Disabled, 'class': 'danger' },
                            };
                            if (typeof status[data] === 'undefined') {
                                return data;
                            }
                            return '<span class="badge badge-light-' + status[data].class + '">' + status[data].title + '</span>';
                        },
    
                    },
                    {
                        targets: 11,
                        render: function(data, type, full, meta) {
                            var status = {
                                true : { 'title': _Activated, 'class': 'success' },
                                false : { 'title': _Disabled, 'class': 'danger' },
                            };
                            if (typeof status[data] === 'undefined') {
                                return data;
                            }
                            return '<span class="badge badge-light-' + status[data].class + '">' + status[data].title + '</span>';
                        },
    
                    },
                    {
                        targets: 13,
                        render: function(data, type, full, meta) {
                            var status = {
                                0 : { 'title': _Pending, 'class': 'warning' },
                                1 : { 'title': _Actif, 'class': 'success' },
                                2 : { 'title': _Disabled, 'class': 'primary' },
                                3 : { 'title': _Rejected, 'class': 'info' },
                                4 : { 'title': _Deleted, 'class': 'danger' },
                            };
                            if (typeof status[data] === 'undefined') {
                                return data;
                            }
                            return '<span class="badge badge-light-' + status[data].class + '">' + status[data].title + '</span>';
                        },
    
                    },
                    {
                        orderable: 1,
                        targets: 14,
                        render: function(data, type, full, meta) {
                            return  dateFormat(moment(data, "YYYY-MM-DDTHH:mm:ssZZ").format());
                        }
                    }*,
                    {
                        orderable: 1,
                        targets: 15,
                        render: function(data, type, full, meta) {
                            return  dateFormat(moment(data, "YYYY-MM-DDTHH:mm:ssZZ").format());
                        }
                    }/*,{
                        orderable: !1,
                        targets: 16,
                        visible: (!pEditTransaction && !pDeleteTransaction) ? false : true,
                        render : function (data,type, full, meta) {
                            var updaterIcon =  `<!--begin::Update-->
                            <button class="btn btn-icon btn-active-light-primary w-30px h-30px me-3 transactionUpdater" data-id=`+data+`>
                                <i id="editTransactionOption`+data+`" class="fa fa-edit"></i>
                            </button>
                            <!--end::Update-->`;
                            var deleterIcon =  `<!--begin::Delete-->
                            <button class="btn btn-icon btn-active-light-primary w-30px h-30px transactionDeleter" 
                                data-id=`+data+` data-kt-transactions-table-filter="delete_row">
                                    <i id="deleteTransactionOption`+data+`" class="text-danger fa fa-trash-alt"></i>
                            </button>
                            <!--end::Delete-->`;
                            updaterIcon = (pEditTransaction) ? updaterIcon : '' ;
                            deleterIcon = (pDeleteTransaction) ? deleterIcon : '' ;
                            return updaterIcon + deleterIcon;
                        }
                    }*],
                    columns: [
    
                        { data: 'orderId' },
    
                        { data: 'transaction', responsivePriority: -10},
    
                        { data: 'transactionId' },
    
                        { data: 'reference' },
    
                        { data: 'method'  },
                        
                        { data: 'agregator' },
                        
                        { data: 'canal' },

                        { data: 'email' },

                        { data: 'amount'  },

                        { data: 'country'  },
    
                        { data: 'status' },
    
                        { data: 'updatedAt' },

                        { data: 'createdAt' , responsivePriority: 0},
                    ],
                    pageLength: 5,
                    lengthChange: !1,
                   
                }),
                $('#kt_modal_add_transaction_reload_button').on('click', function() {
                    e.ajax.reload(null, false);
                })).on("draw", (function() { l(), c(), a()
                })), l(),
                document.querySelector('[data-kt-transaction-table-filter="search"]').addEventListener("keyup", (function(t) {
                    e.search(t.target.value).draw()
                })),
                document.querySelector('[data-kt-transaction-table-filter="reset"]').addEventListener("click", (function() {
                    document.querySelector('[data-kt-transaction-table-filter="form"]').querySelectorAll("select").forEach((e => {
                            $(e).val("").trigger("change")
                        })),
                        e.search("").draw()
                })),
                c(), (() => {
                    const t = document.querySelector('[data-kt-transaction-table-filter="form"]'),
                        n = t.querySelector('[data-kt-transaction-table-filter="filter"]'),
                        r = t.querySelectorAll("select");
                    n.addEventListener("click", (function() {
                        var t = "";
                        r.forEach(((e, n) => {
                                e.value && "" !== e.value && (0 !== n && (t += " "),
                                    t += e.value)
                            })),
                            e.search(t).draw()
                    }))
                })
                ())
        }
    }
}();
KTUtil.onDOMContentLoaded((function() {
    KTTransactionsList.init();
}));*/

"use strict";

var spanStatAll =	document.getElementById("stat_all"),spanStatValidated = document.getElementById("stat_validated"), spanStatPending	= document.getElementById("stat_pending"), spanStatCanceled	= document.getElementById("stat_canceled");
var spanAmountAll =	document.getElementById("tr_all"),spanAmountValidated = document.getElementById("tr_validated"), spanAmountPending	= document.getElementById("tr_pending"), spanAmountCanceled	= document.getElementById("tr_canceled");

var KTTransactionsList = function() {
    var e, t, n, r, o = document.getElementById("kt_table_transactions"),
        c = () => {
            o.querySelectorAll('[data-kt-transactions-table-filter="delete_row"]').forEach((t => {
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
            t = document.querySelector('[data-kt-transaction-table-toolbar="base"]'),
                n = document.querySelector('[data-kt-transaction-table-toolbar="selected"]'),
                r = document.querySelector('[data-kt-transaction-table-select="selected_count"]');
            const s = document.querySelector('[data-kt-transaction-table-select="delete_selected"]');
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
                        url: transaction_list_link,
                        type: "POST",
                        data: {
                            _token: function(){ return csrfToken; }
                        },
                        error: function () { 
                            $(document).trigger('toastr.onAjaxError');
                        },
                        dataSrc: function(json) {

                            spanStatAll.textContent = json.all, spanStatValidated.textContent = json.validated, spanStatPending.textContent	= json.pending, spanStatCanceled.textContent = json.canceled
                            spanAmountAll.textContent = json.sumAmount, spanAmountValidated.textContent = json.sumAmountValidated, spanAmountPending.textContent	= json.sumAmountPending, spanAmountCanceled.textContent = json.sumAmountCanceled
                            
                            return json.data;
                        }
                        
                    },
                    order: [[ 8, "desc" ]],
                    'columnDefs': [
                       
                        // Utilisateur
                        {
                            responsivePriority: 0,
                            targets: 0, 
                            render: function (data, type, full, meta) {
                                return  data[0];
                            }
                        },
                        // ID Transaction
                        {
                            responsivePriority: 1,
                            targets: 1, 
                            render: function (data, type, full, meta) {
                                return data;
                            }
                        },
                        // Réference
                        {
                            responsivePriority: 2,
                            targets: 2, 
                            render: function (data, type, full, meta) {
                                return data;
                            }
                        },
                        // Balance Avant
                        {
                            responsivePriority: 8,
                            targets: 3, 
                            render: function (data, type, full, meta) {
                                return data;
                            }
                        },
                        // Montant
                        {
                            responsivePriority: 3,
                            targets: 4, 
                            render: function (data, type, full, meta) {
                                return data;
                            }
                        },
                        // Balance Après
                        {
                            responsivePriority: 8,
                            targets: 5, 
                            render: function (data, type, full, meta) {
                                return data;
                            }
                        },
                        // Statut
                        {
                            responsivePriority: 4,
                            targets: 6, 
                            render: function (data, type, full, meta) {
                                var status = {
                                                '2' : { 'class': 'warning' },
                                                '6' : { 'class': 'success' },
                                                '7' : { 'class': 'danger' },
                                            };
                                            if (typeof status[data[0]] === 'undefined') {
                                                return data[1];
                                            }
                                            return '<span class="badge badge-light-' + status[data[0]].class + '">' + data[1] + '</span>';
                            }
                        },
                        // Brand
                        {
                            responsivePriority: 6,
                            targets: 7, 
                            render: function (data, type, full, meta) {
                                return  data;
                            }
                        },
                        // Date de création
                        {
                            responsivePriority: 5,
                            targets: 8, 
                            render: function (data, type, full, meta) {
                            return  dateFormat(moment(data, "YYYY-MM-DDTHH:mm:ssZZ").format());
                            }
                        },
                        // Date de modification
                        {
                            responsivePriority: 9,
                            targets: 9, 
                            render: function (data, type, full, meta) {
                                return  dateFormat(moment(data, "YYYY-MM-DDTHH:mm:ssZZ").format());
                            }
                        }
                    ],
                    pageLength: 10,
                    lengthChange: true,
                    "info": true,
                    lengthMenu: [10, 25, 100, 250, 500, 1000],                   
                }),
                $('#kt_modal_add_transaction_reload_button').on('click', function() {
                    e.ajax.reload(null, false);
                })).on("draw", (function() { l(), c(), a()
                })), l(),
                document.querySelector('[data-kt-transaction-table-filter="search"]').addEventListener("keyup", (function(t) {
                    e.search(t.target.value).draw()

                    let sum_amount_all =   0, sum_amount_pending = 0, sum_amount_validated = 0, sum_amount_canceled = 0, filtre_tab = e['context'][0]['aoData']
                    let sum_tr_all = 0, sum_tr_pending = 0, sum_tr_validated = 0, sum_tr_canceled = 0
                    console.log(e['context'][0]['aiDisplay']);
                    e['context'][0]['aiDisplay'].forEach(function(item){
                        
                            sum_amount_all  += parseInt(filtre_tab[item]['_aFilterData'][4]) 
                            sum_tr_all      +=  1
                            if (parseInt(filtre_tab[item]['_aData'][6][0])  == 6) {
                                sum_amount_validated += parseInt(filtre_tab[item]['_aFilterData'][4]);
                                sum_tr_validated     +=  1
                
                            }
                            else if(parseInt(filtre_tab[item]['_aData'][6][0]) == 2){
                                sum_amount_pending  += parseInt(filtre_tab[item]['_aFilterData'][4]);
                                sum_tr_pending      +=  1
                                
                            }
                            else{
                                sum_amount_canceled += parseInt(filtre_tab[item]['_aFilterData'][4]);
                                sum_tr_canceled     +=  1
                
                            }
                        
                    });
                
                    spanStatAll.textContent = sum_tr_all, spanStatPending.textContent = sum_tr_pending, spanStatValidated.textContent = sum_tr_validated, spanStatCanceled.textContent = sum_tr_canceled
                    spanAmountAll.textContent = sum_amount_all, spanAmountPending.textContent = sum_amount_pending, spanAmountValidated.textContent = sum_amount_validated, spanAmountCanceled.textContent = sum_amount_canceled
                
                })),
                document.querySelector('[data-kt-transaction-table-filter="reset"]').addEventListener("click", (function() {
                    document.querySelector('[data-kt-transaction-table-filter="form"]').querySelectorAll("select").forEach((e => {
                            $(e).val("").trigger("change");
                        })),
                        e.search("").draw()
                        UpdateStat(e)
                })),
                c(), (() => {
                    const t = document.querySelector('[data-kt-transaction-table-filter="form"]'),
                        n = t.querySelector('[data-kt-transaction-table-filter="filter"]'),
                        r = t.querySelectorAll("select");
                    n.addEventListener("click", (function() {
                        var t = "";
                        r.forEach(((e, n) => {
                                e.value && "" !== e.value && (0 !== n && (t += " "),
                                    t += e.value)
                                    
                            })),
                            
                            e.search(t).draw()
                            UpdateStat(e)
                    }))
                })
                ())
        }
    }
}();

KTUtil.onDOMContentLoaded((function() {
    KTTransactionsList.init();
}));

function UpdateStat(e) {
    // console.log(e['context'][0])
    let sum_amount_all =   0, sum_amount_pending = 0, sum_amount_validated = 0, sum_amount_canceled = 0, filtre_tab = e['context'][0]['oPreviousSearch']['sSearch'].replace(/\s+/g, '').toLowerCase()
    let sum_tr_all = 0, sum_tr_pending = 0, sum_tr_validated = 0, sum_tr_canceled = 0
    e['context'][0]['aoData'].forEach(function(item){
        
        if (filtre_tab  == (item['_aData'][7]+item['_aData'][6][1]).replace(/\s+/g, '').toLowerCase() || filtre_tab ==  item['_aData'][7].replace(/\s+/g, '').toLowerCase() || filtre_tab == item['_aData'][6][1].replace(/\s+/g, '').toLowerCase() || filtre_tab == "" ) {
            
            sum_amount_all  += item['_aData'][4]
            sum_tr_all      +=  1

            if (item['_aData'][6][0] == 6) {
                sum_amount_validated += item['_aData'][4];
                sum_tr_validated      +=  1

            }
            else if(item['_aData'][6][0] == 2){
                sum_amount_pending += item['_aData'][4];
                sum_tr_pending      +=  1
                
            }
            else{
                sum_amount_canceled += item['_aData'][4];
                sum_tr_canceled      +=  1

            }
        }
        
        
    });

    spanStatAll.textContent = sum_tr_all, spanStatPending.textContent = sum_tr_pending, spanStatValidated.textContent = sum_tr_validated, spanStatCanceled.textContent = sum_tr_canceled
    spanAmountAll.textContent = sum_amount_all, spanAmountPending.textContent = sum_amount_pending, spanAmountValidated.textContent = sum_amount_validated, spanAmountCanceled.textContent = sum_amount_canceled

}

