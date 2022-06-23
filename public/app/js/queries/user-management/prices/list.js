"use strict";


var KTpricesList = function() {
    var e, t, n, r, x = document.querySelector("#export"), y = ".bt-export", o = document.getElementById("kt_table_prices"),
        c = () => {
            o.querySelectorAll('[data-kt-prices-table-filter="delete_row"]').forEach((t => {
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
            t = document.querySelector('[data-kt-price-table-toolbar="base"]'),
                n = document.querySelector('[data-kt-price-table-toolbar="selected"]'),
                r = document.querySelector('[data-kt-price-table-select="selected_count"]');
            const s = document.querySelector('[data-kt-price-table-select="delete_selected"]');
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
                        url: price_list_link.replace("_1_", user_uid),
                        type: "POST",
                        data: {
                            _token: function(){ return csrfToken; }
                        },
                        error: function () { 
                            $(document).trigger('toastr.tableListError');
                        }
                    },
                    info: !1,
                    order: [[ 3, "desc" ]],
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
                        targets: 1,
                        render: function(data, type, full, meta) {
                           return '<img src="'+window.location.origin+'/app/media/flags/'+data+'.svg" class="rounded-circle me-2"  style="height:19px;" alt="'+data+'" />'+data
                        },

                    },
                    {
                        orderable: !1,
                        targets: 5,
                        render : function (data,type, full, meta) {
                            var updaterIcon =  `<!--begin::Update-->
                            <button class="btn btn-icon btn-active-light-primary w-30px h-30px me-3 priceUpdater" data-id=`+data+`>
                                <i id="editpriceOption`+data+`" class="fa fa-edit"></i>
                            </button>
                            <!--end::Update-->`;
                            return updaterIcon;
                        }
                    }],
                    columns: [
    
                        { data: 'orderId' },
    
                        { data: 'name', responsivePriority: -5},

                        { data: 'dial', responsivePriority: -8},

                        { data: 'code', responsivePriority: -7},
                        
                        { data: 'price', responsivePriority: -6},
    
                        { data: 'action',responsivePriority: -9 },
                    ],
                    pageLength: 10,
                    lengthChange: !1,
                    language: {
                        url: _language_datatables,
                    },
                    dom: '<"top text-end bt-export d-none"B>rtF<"row"<"col-sm-6"l><"col-sm-6"p>>',
                   
                }),
                // Action sur bouton export
                $(x).on('click', ($this)=>{ $this.preventDefault(); return $(y).hasClass('d-none')?$(y).removeClass('d-none'):$(y).addClass('d-none'); }),
                $('#kt_modal_add_price_reload_button').on('click', function() {
                    e.ajax.reload(null, false);
                })).on("draw", (function() { l(), c(), a()
                })), l(),
                document.querySelector('[data-kt-price-table-filter="search"]').addEventListener("keyup", (function(t) {
                    e.search(t.target.value).draw()
                })) 
                )
                
        }
    }
}();
KTUtil.onDOMContentLoaded((function() {
    KTpricesList.init();
}));