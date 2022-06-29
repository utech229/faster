"use strict";
var spanStatAll =	document.getElementById("stat_all"),spanStatActif = document.getElementById("stat_actif"), spanStatPending	= document.getElementById("stat_pending");

var KTUsersList = function() {
    var e, t, n, r, x = document.querySelector("#export"), y = ".bt-export", o = document.getElementById("kt_table_users"),
        c = () => {
            o.querySelectorAll('[data-kt-users-table-filter="delete_row"]').forEach((t => {
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
            t = document.querySelector('[data-kt-user-table-toolbar="base"]'),
                n = document.querySelector('[data-kt-user-table-toolbar="selected"]'),
                r = document.querySelector('[data-kt-user-table-select="selected_count"]');
            const s = document.querySelector('[data-kt-user-table-select="delete_selected"]');
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
                        url: user_list_link,
                        type: "POST",
                        data: {
                            _token: function(){ return csrfToken; }
                        },
                        dataSrc: function(json) {
                            spanStatAll.textContent = json.stats.all, spanStatActif.textContent = json.stats.actif, spanStatPending.textContent	= json.stats.pending
                            return json.data;
                        },
                        error: function () { 
                            $(document).trigger('toastr.tableListError');
                        }
                    },
                    info: !1,
                    order: [[ 8, "desc" ]],
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
                            return `<!--begin::User=-->
									<div class="d-flex align-items-center">
                                        <!--begin:: Avatar -->
                                        <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                            <a href="javascript:;">
                                                <div class="symbol-label">
                                                    <img src="`+user_avatar_link.replace("_1_", data.photo)+`" alt="`+data.name+`" class="w-100" />
                                                </div>
                                            </a>
                                        </div>
                                        <!--end::Avatar-->
                                        <!--begin::User details-->
                                        <div class="d-flex flex-column">
                                            <a href="javascript:;" class="text-gray-800 text-hover-primary mb-1">`+data.name+`</a>
                                            <span>`+data.email+`</span>
                                        </div>
                                        <!--begin::User details-->
                                    </div>`;
                        }
                    },
                    {
                        targets: 3,
                        render: function(data, type, full, meta) {
                            var status = {
                                'ROLE_AFFILIATE_USER': { 'title': 'Affilié revendeur', 'class': 'warning' },
                                'ROLE_AFFILIATE_RESELLER': { 'title': 'Affilié revendeur', 'class': 'primary' },
                                'ROLE_RESELLER': { 'title': 'Revendeur', 'class': 'warning' },
                                'ROLE_USER': { 'title': 'Utilisateur', 'class': 'info' },
                                'ROLE_ADMINISTRATOR': { 'title': 'Administrateur', 'class': 'secondary' },
                                'ROLE_SUPER_ADMINISTRATOR': { 'title': 'Super administrateur', 'class': 'info' },
                            };
                            if (typeof status[data] === 'undefined') {
                                return data;
                            }
                            return '<span class="badge badge-light-' + status[data].class + '">' + status[data].title + '</span>';
                        },

                    }, {
                        targets: 1,
                        render: function(data, type, full, meta) {
                           return '<img src="'+window.location.origin+'/app/media/flags/'+data+'.svg" class="rounded-circle me-2"  style="height:19px;" alt="'+data+'" />'+data
                        },

                    },
                    {
                        targets: 2,
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
                        targets: 4,
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
                        targets: 6,
                        render: function(data, type, full, meta) {
                            var status = {
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
                        orderable: 1,
                        targets: 7,
                        render: function(data, type, full, meta) {
                            return viewTime(data);
                        }
                    },
                    {
                        orderable: 1,
                        targets: 8,
                        render: function(data, type, full, meta) {
                            return viewTime(data);
                        }
                    },{
                        targets: 9,
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
    
                    },{
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
    
                    },{
                        targets: 11,
                        //visible: (roleLevel < 4) ? false : true,
                        render: function(data, type, full, meta) {
                            return data.name;
                        },
    
                    },{
                        orderable: !1,
                        targets: 12,
                        visible: (!pEditUser && !pDeleteUser) ? false : true,
                        render : function (data,type, full, meta) {
                            var updaterIcon =  `<!--begin::Update-->
                            <button class="btn btn-icon btn-active-light-primary w-30px h-30px me-3 userUpdater" data-id=`+data+`>
                                <i id="editUserOption`+data+`" class="fa fa-edit"></i>
                            </button>
                            <!--end::Update-->`;
                            var deleterIcon =  `<!--begin::Delete-->
                            <button class="btn btn-icon btn-active-light-primary w-30px h-30px userDeleter" 
                                data-id=`+data+` data-kt-users-table-filter="delete_row">
                                    <i id="deleteUserOption`+data+`" class="text-danger fa fa-trash-alt"></i>
                            </button>
                            <!--end::Delete-->`;
                            var priceIcon =  `<!--begin::Price-->
                            <button class="btn btn-icon btn-active-light-primary w-30px h-30px pricer" 
                                data-id=`+data+` data-kt-users-table-filter="price_row">
                                    <i id="priceOption`+data+`" class="text-info fas fa-money-bill"></i>
                            </button>
                            <!--end::Price-->`;
                            updaterIcon = (pEditUser) ? updaterIcon : '' ;
                            priceIcon   = (pEditUser) ? priceIcon : '' ;
                            deleterIcon = (pDeleteUser) ? deleterIcon : '' ;
                            return updaterIcon + deleterIcon + priceIcon;
                        }
                    }],
                    columns: [
    
                        { data: 'orderId' },
    
                        { data: 'user', responsivePriority: -10},
    
                        { data: 'phone' },
    
                        { data: 'role', responsivePriority: -8},
    
                        { data: 'country', responsivePriority: 10 },
    
                        { data: 'balance' , responsivePriority: -4 },
    
                        { data: 'status' },
    
                        { data: 'lastLogin', responsivePriority: 0 },

                        { data: 'createdAt' },

                        { data: 'isDlr',responsivePriority: -6 },

                        { data: 'postPay',responsivePriority: -5 },

                        { data: 'brand',responsivePriority: -7 },

                        { data: 'action',responsivePriority: -9 },
                    ],
                    info: true,
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
                $('#kt_modal_add_user_reload_button').on('click', function() {
                    e.ajax.reload(null, false);
                })).on("draw", (function() { l(), c(), a()
                })), l(),
                document.querySelector('[data-kt-user-table-filter="search"]').addEventListener("keyup", (function(t) {
                    e.search(t.target.value).draw()
                    UpdateStat(e)         
                })),
                document.querySelector('[data-kt-user-table-filter="reset"]').addEventListener("click", (function() {
                    document.querySelector('[data-kt-user-table-filter="form"]').querySelectorAll("select").forEach((e => {
                             
                            $(e).val("").trigger("change")
                        })),
                        e.search("").draw()
                        UpdateStat(e)
                })),
                c(), (() => {
                    const t = document.querySelector('[data-kt-user-table-filter="form"]'),
                        n = t.querySelector('[data-kt-user-table-filter="filter"]'),
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
                            e.search(t).draw(),
                            UpdateStat(e)
                    }));
                })
                ())  
        }
    }
}();
KTUtil.onDOMContentLoaded((function() {
    KTUsersList.init();
}));

function UpdateStat(e) {
    // console.log(e['context'][0])
    let filtre_tab = e['context'][0]['aoData']
    let sum_tr_all = 0, sum_tr_pending = 0, sum_tr_actif = 0
    e['context'][0]['aiDisplay'].forEach(function(item){
    sum_tr_all      +=  1;

    if (parseInt(filtre_tab[item]['_aData'][6]) == 3) {
        sum_tr_actif        +=  1;

    }
    else if(parseInt(filtre_tab[item]['_aData'][6]) == 2){
        sum_tr_pending          +=  1;
        
    }
    });
    spanStatAll.textContent = sum_tr_all, spanStatPending.textContent = sum_tr_pending, spanStatActif.textContent = sum_tr_actif   
}