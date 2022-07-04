
"use strict";

var spanNbrBrand =	document.getElementById("nbr_brand"),spanAllCommission = document.getElementById("all_commission");

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
                            _token: function(){ return csrfToken; },
                            _uid: function(){ return document.querySelector('[data-kt-commission-table-filter="user"]').value; }
                        },
                        error: function () { 
                            $(document).trigger('toastr.onAjaxError');
                        },
                        dataSrc: function(json) {
                            let commission = 0
                                json.data.forEach(element => {
                                commission  += element[1]
                            });
                            spanNbrBrand.textContent        =   json.data.length;
                            spanAllCommission.textContent   =   commission;
                            return json.data;
                        }
                        
                    },
                    order: [[ 4, "desc" ]],
                    'columnDefs': [
                       
                        // Marque
                        {
                            responsivePriority: 0,
                            targets: 0, 
                            render: function (data, type, full, meta) {
                                return  data;
                            }
                        },
                        //  Commission
                        {
                            responsivePriority: 1,
                            targets: 1, 
                            render: function (data, type, full, meta) {
                                return data;
                            }
                        },
                        // Statut
                        {
                            responsivePriority:2 ,
                            targets: 2, 
                            render: function (data, type, full, meta) {
                                var status = {
                                                '2' : { 'class': 'warning' },
                                                '6' : { 'class': 'success' },
                                                '7' : { 'class': 'primary' },
                                            };
                                            if (typeof status[data[0]] === 'undefined') {
                                                return data[1];
                                            }
                                            return '<span class="badge badge-light-' + status[data[0]].class + '">' + data[1] + '</span>';
                            }
                        },
                        // Date de création
                        {
                            responsivePriority: 3,
                            targets: 3, 
                            render: function (data, type, full, meta) {
                            return  dateFormat(moment(data, "YYYY-MM-DDTHH:mm:ssZZ").format());
                            }
                        },
                        // Date de modification
                        {
                            responsivePriority: 4,
                            targets: 4, 
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
                document.querySelector('[data-kt-commission-table-filter="search"]').addEventListener("keyup", (function(t) {
                    e.search(t.target.value).draw()
                    let filtre_tab = e['context'][0]['aoData'], commission = 0;
                    spanNbrBrand.textContent        =  e['context'][0]['aiDisplay'].length;
                    e['context'][0]['aiDisplay'].forEach(function(item){

                        commission += parseInt(filtre_tab[item]['_aFilterData'][1]);
                        
                    });
                    spanAllCommission.textContent   =   commission;

                })),
                $('[data-kt-commission-table-filter="user"]').on('change', function() {
                    e.ajax.reload();
                }),
                c(), 
                (() => {
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


