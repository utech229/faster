"use strict";
var KTUsersPermissionsList = function() {
    var t, e, n, r, o;
    return {
        init: function() {
            (e = document.querySelector("#kt_permissions_table")) && (e.querySelectorAll("tbody tr").forEach((t => {
                const e = t.querySelectorAll("td"), n = moment(e[2].innerHTML, "DD MMM YYYY, LT").format();
                e[2].setAttribute("data-order", n)
            })), t = $(e).DataTable({
                responsive: true,
                ajax: {
                    "url": permission_list_link,
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
                },
                {
                    targets: 3,
                    render: function(data, type, full, meta) {
                        var status = {
                            true : { 'title': _Actif, 'class': 'success' },
                            false : { 'title': _Disabled, 'class': 'danger' },
                        };
                        if (typeof status[data] === 'undefined') {
                            return data;
                        }
                        return '<span class="badge badge-light-' + status[data].class + '">' + status[data].title + '</span>';
                    },

                }, {
                    orderable: !1,
                    targets: 5,
                    render: function(data, type, full, meta) {
                        return  dateFormat(moment(data, "YYYY-MM-DDTHH:mm:ssZZ").format());
                    }
                },
                {
                    orderable: !1,
                    targets: 6,
                    render: function(data, type, full, meta) {
                        return  dateFormat(moment(data, "YYYY-MM-DDTHH:mm:ssZZ").format());
                    }
                },
                {
                    targets: 7,
                    render: function(data, type, full, meta) {
                        var status = {
                            2 : { 'title': _Pending, 'class': 'warning' },
                            3 : { 'title': _Actif, 'class': 'success' },
                            5 : { 'title': _Disabled, 'class': 'danger' },
                            7 : { 'title': _Rejected, 'class': 'info' },
                        };
                        if (typeof status[data] === 'undefined') {
                            return data;
                        }
                        return '<span class="badge badge-light-' + status[data].class + '">' + status[data].title + '</span>';
                    },

                },{
                    orderable: !1,
                    targets: 8,
                    visible: (!pEditPermission && !pDeletePermission) ? false : true,
                    render : function (data,type, full, meta) {
                        var updaterIcon =  `<!--begin::Update-->
                        <button class="btn btn-icon btn-active-light-primary w-30px h-30px me-3 permissionUpdater" 
                        data-id=`+data+`>
                        <i id="editPermissionOption`+data+`" class="fa fa-edit"></i>
                        </button>
                        <!--end::Update-->`;
                        var deleterIcon =  `<!--begin::Delete-->
                        <button class="btn btn-icon btn-active-light-primary w-30px h-30px permissionDeleter" 
                            data-id=`+data+` data-kt-permissions-table-filter="delete_row">
                          <i id="deletePermissionOption`+data+`" class="text-danger fa fa-trash-alt"></i>
                        </button>
                        <!--end::Delete-->`;
                        updaterIcon = (pEditPermission) ? updaterIcon : '' ;
                        deleterIcon = (pDeletePermission) ?deleterIcon : '' ;
                        return updaterIcon + deleterIcon;
                    }
                }],
               columns: [

                    { data: 'OrderId' },

                    { data: 'Name', responsivePriority: -5},

                    { data: 'Code', responsivePriority: -2 },

                    { data: 'Description', responsivePriority: -4  },

                    { data: 'Roles' },

                    { data: 'CreatedAt' , responsivePriority: 0},

                    { data: 'UpdatedAt'},

                    { data: 'Status' , responsivePriority: -3},

                    { data: 'Actions',responsivePriority: -9 },
                ],
                lengthMenu: [10, 25, 100, 250, 500, 1000],
                pageLength: 5,
            }), 
            $('#kt_modal_add_permission_reload_button').on('click', function() {
                t.ajax.reload(null, false);
            }),
            document.querySelector('[data-kt-permissions-table-filter="search"]').addEventListener("keyup", (function(e) {
                t.search(e.target.value).draw()
            })), e.querySelectorAll('[data-kt-permissions-table-filter="delete_row"]').forEach((e => {
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
    KTUsersPermissionsList.init()
}));