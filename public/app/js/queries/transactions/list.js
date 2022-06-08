"use strict";


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
                        }
                    },
                    info: !1,
                    order: [[ 12, "desc" ]],
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
                        targets: 4,
                        render: function(data, type, full, meta) {
                            return '<img src="'+window.location.origin+'/app/media/svg/card-logos/'+data+'.svg" class="w-50px me-3" alt="'+data+' eContacts"/>';
                        },
    
                    },
                    {
                        targets: 10,
                        render: function(data, type, full, meta) {
                            var status = {
                                'pending' : { 'title': _Pending, 'class': 'warning' },
                                'approved' : { 'title': _Validated, 'class': 'success' },
                                'canceled' : { 'title': _Canceled, 'class': 'primary' },
                                'rejected' : { 'title': _Rejected, 'class': 'info' },
                            };
                            if (typeof status[data] === 'undefined') {
                                return data;
                            }
                            return '<span class="badge badge-light-' + status[data].class + '">' + status[data].title + '</span>';
                        },
    
                    },
                    {
                        orderable: 1,
                        targets: 1,
                        render: function(data, type, full, meta) {
                            return  dateFormat(moment(data, "YYYY-MM-DDTHH:mm:ssZZ").format());
                        }
                    },
                    {
                        orderable: 1,
                        targets: 12,
                        render: function(data, type, full, meta) {
                            return  dateFormat(moment(data, "YYYY-MM-DDTHH:mm:ssZZ").format());
                        }
                    }],
                    columns: [
    
                        { data: 'orderId' },
    
                        { data: 'user', responsivePriority: -10},
    
                        { data: 'transactionId' },
    
                        { data: 'reference' },
    
                        { data: 'method' ,responsivePriority: -7  },
                        
                        { data: 'agregator' },
                        
                        { data: 'canal' },

                        { data: 'email' },

                        { data: 'amount',responsivePriority: -8  },

                        { data: 'country'  },
    
                        { data: 'status',responsivePriority: -8 },
    
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
}));

