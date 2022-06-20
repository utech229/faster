"use strict";
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
                            0 : { 'title': _Pending, 'class': 'warning' },
                            1 : { 'title': _Validated, 'class': 'success' },
                            3 : { 'title': _Canceled, 'class': 'primary' },
                            2 : { 'title': _Rejected, 'class': 'danger' },
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
                pageLength: 5,
            }), 
            $('#kt_modal_add_payment_reload_button').on('click', function() {
                t.ajax.reload(null, false);
            }),
            document.querySelector('[data-kt-payment-table-filter="search"]').addEventListener("keyup", (function(e) {
                t.search(e.target.value).draw()
            })), e.querySelectorAll('[data-kt-payment-table-filter="delete_row"]').forEach((e => {
                e.addEventListener("click", (function(e) {
                    e.preventDefault();
                    const n = e.target.closest("tr"),
                        o = n.querySelectorAll("td")[0].innerText;
                    Swal.fire({
                        text: "Are you sure you want to delete " + o + "?",
                        icon: "warning",
                        showCancelButton: !0,
                        buttonsStyling: !1,
                        confirmButtonText: "Yes, delete!",
                        cancelButtonText: "No, cancel",
                        customClass: {
                            confirmButton: "btn fw-bold btn-danger",
                            cancelButton: "btn fw-bold btn-active-light-primary"
                        }
                    }).then((function(e) {
                        e.value ? Swal.fire({
                            text: "You have deleted " + o + "!.",
                            icon: "success",
                            buttonsStyling: !1,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary"
                            }
                        }).then((function() {
                            t.row($(n)).remove().draw()
                        })) : "cancel" === e.dismiss && Swal.fire({
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
            })))
        }
    }
}();
KTUtil.onDOMContentLoaded((function() {
    KTUsersPaymentList.init()
}));