
"use strict";
var tabUpdateGroup = [];
var KTGroupList = function() {
    var e, t, n, r, o = document.getElementById("kt_contacts_group_table"),
        c = () => {
            o.querySelectorAll('[data-kt-contact-group-table-filter="delete_row"]').forEach((t => {
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
            t = document.querySelector('[data-kt-contact-group-table-toolbar="base"]'),
                n = document.querySelector('[data-kt-contact-group-table-toolbar="selected"]'),
                r = document.querySelector('[data-kt-contact-group-table-toolbar="selected_count"]');
            const s = document.querySelector('[data-kt-contact-group-table-toolbar="delete_selected"]');
            c.forEach((e => {
                    e.addEventListener("click", (function() {
                        setTimeout((function() {
                            a()
                        }), 50)
                    }))
                })),
                s.addEventListener("click", (function() {
                    Swal.fire({
                        text: _Deletion_request,
                        icon: "warning",
                        showCancelButton: !0,
                        buttonsStyling: !1,
                        confirmButtonText: _Yes,
                        cancelButtonText: _No,
                        customClass: {
                            confirmButton: "btn fw-bold btn-danger",
                            cancelButton: "btn fw-bold btn-active-light-primary"
                        }
                    }).then((function(t) {
                        if (t.value) {
                            let tabUid  =  [];
                            c.forEach((t => {
                                        if(t.checked && $(t).attr("data-value") != undefined){
                                            tabUid.push($(t).attr("data-value"));
                                        }
                            }));
                            if (tabUid.length > 0) {
                                $.ajax({
                                    url: group_delete,
                                    type: 'post',
                                    data: { tabUid: tabUid, _token: function(){ return csrfToken; }},
                                    dataType: 'json',
                                    success: function(response) {
                                        Swal.fire({
                                            text: response.message,
                                            icon: response.type,
                                            buttonsStyling: false,
                                            confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                                            customClass: {
                                                confirmButton: "btn btn-primary"
                                            }
                                        });
        
                                        if (response.type === 'success') {
        
                                            $('#kt_modal_add_contact_group_reload_button').click();
                                        }
                                    },
                                    error: function () { 
                                        $(document).trigger('onAjaxError');
                                        loading()
                                    },
                                })
                            }

                          
                        }
                        else{
                            "cancel" === t.dismiss && $(document).trigger('onAjaxInfo');
                        }
                        // t.value ? 
                        // Swal.fire({
                        //     text: "You have deleted all selected customers!.",
                        //     icon: "success",
                        //     buttonsStyling: !1,
                        //     confirmButtonText: "Ok, got it!",
                        //     customClass: {
                        //         confirmButton: "btn fw-bold btn-primary"
                        //     }
                        // }).then((function() {
                        //     c.forEach((t => {
                        //         t.checked && e.row($(t.closest("tbody tr"))).remove().draw()
                        //     }));
                        //     o.querySelectorAll('[type="checkbox"]')[0].checked = !1
                        // })).then((function() {
                        //     a(),
                        //         l()
                        // })) : "cancel" === t.dismiss && Swal.fire({
                        //     text: "Selected customers was not deleted.",
                        //     icon: "error",
                        //     buttonsStyling: !1,
                        //     confirmButtonText: "Ok, got it!",
                        //     customClass: {
                        //         confirmButton: "btn fw-bold btn-primary"
                        //     }
                        // })
                    }))
                }))
        };
        const a = () => {
            
            const e = o.querySelectorAll('tbody [type="checkbox"]');
            let c = !1,
                l = 0;
            e.forEach((e => {
                e.checked && (c = !0, l++);
                
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
                        url: group_list,
                        type: "POST",
                        data: {
                            _token: function(){ return csrfToken; },
                            _uid: function(){ return document.querySelector('[data-kt-contact-group="user"]').value; }
                        },
                        error: function () { 
                            $(document).trigger('toastr.onAjaxError');
                        },
                        dataSrc: function(json) {
                            return json.data;
                        }
                        
                    },
                    order: [[ 7, "desc" ]],
                    'columnDefs': [
                       
                        // Uid
                        {
                            orderable: !1,
                            responsivePriority: 0,
                            targets: 0, 
                            render: function (data, type, full, meta) {
                                tabUpdateGroup[data] = full;
                                return  `<div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" data-value="`+data+`" />
                            </div>`;
                         
                            }
                        },
                        // Nom
                        {
                            responsivePriority: 1,
                            targets: 1, 
                            render: function (data, type, full, meta) {
                                return  data;
                            }
                        },
                        // Param1
                        {
                            responsivePriority: 2,
                            targets: 2, 
                            render: function (data, type, full, meta) {
                                return data;
                            }
                        },
                        // Param2
                        {
                            responsivePriority: 3,
                            targets: 3, 
                            render: function (data, type, full, meta) {
                                return data;
                            }
                        },
                        // Param3
                        {
                            responsivePriority: 5,
                            targets: 4, 
                            render: function (data, type, full, meta) {
                                return data;
                            }
                        },
                        // Param4
                        {
                            responsivePriority: 6,
                            targets: 5, 
                            render: function (data, type, full, meta) {
                                return data;
                            }
                        },
                         // Param5
                         {
                            responsivePriority: 7,
                            targets: 6, 
                            render: function (data, type, full, meta) {
                                return data;
                            }
                        },
                        // Date de création
                        {
                            responsivePriority: 8,
                            targets: 7, 
                            render: function (data, type, full, meta) {
                            return  dateFormat(moment(data, "YYYY-MM-DDTHH:mm:ssZZ").format());
                            }
                        },
                         // Date de modification
                         {
                            responsivePriority: 9,
                            targets: 8, 
                            render: function (data, type, full, meta) {
                            return  dateFormat(moment(data, "YYYY-MM-DDTHH:mm:ssZZ").format());
                            }
                        },
                        // Action
                        {
                            orderable: !1,
                            responsivePriority: 4,
                            targets: 9, 
                            render: function (data, type, full, meta) {
                                var updaterIcon =  `<!--begin::Update-->
                                <button class="btn btn-icon btn-active-light-primary w-30px h-30px me-3 groupUpdater" data-id=`+data+`>
                                    <i id="editUserOption`+data+`" class="fa fa-edit"></i>
                                </button>
                                <!--end::Update-->`;
                                var deleterIcon =  `<!--begin::Delete-->
                                <button class="btn btn-icon btn-active-light-primary w-30px h-30px groupDeleter" 
                                    data-id=`+data+` >
                                        <i id=`+data+` class="text-danger fa fa-trash-alt"></i>
                                </button>
                                <!--end::Delete-->`;
                                // updaterIcon = (pEditUser) ? updaterIcon : '' ;
                                // deleterIcon = (pDeleteUser) ? deleterIcon : '' ;
                                return updaterIcon + deleterIcon;
                            }
                        }
                        
                    ],
                    pageLength: 10,
                    lengthChange: true,
                    "info": true,
                    lengthMenu: [10, 25, 100, 250, 500, 1000],                   
                }),
                $('#kt_modal_add_contact_group_reload_button').on('click', function() {
                    e.ajax.reload(null, false);
                })), l(),
                document.querySelector('[data-kt-contact-group-table-filter="search"]').addEventListener("keyup", (function(t) {
                    e.search(t.target.value).draw()

                })),
                document.querySelector('[data-kt-contact-group-table-filter="reset"]').addEventListener("click", (function() {
                    document.querySelector('[data-kt-contact-group-table-filter="form"]').querySelectorAll("select").forEach((e => {
                            $(e).val("").trigger("change");
                        })),
                        e.search("").draw()
                })),
                $('[data-kt-contact-group="user"]').on('change', function() {
                    e.ajax.reload();
                }),
                c(), (() => {
                    const t = document.querySelector('[data-kt-contact-group-table-filter="form"]'),
                        n = t.querySelector('[data-kt-contact-group-table-filter="filter"]'),
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
                ());

            e.on("draw", function() { l(), c(), a()})
        }
    }
}();

$(document).on('click', ".groupUpdater", function(e) 
{
    var uid = $(this).data('id');
    $("#group_uid").val(uid);
    $("#group_name").val(tabUpdateGroup[uid][1]);
    $("#group_set1").val(tabUpdateGroup[uid][2]);
    $("#group_set2").val(tabUpdateGroup[uid][3]);
    $("#group_set3").val(tabUpdateGroup[uid][4]);
    $("#group_set4").val(tabUpdateGroup[uid][5]);
    $("#group_set5").val(tabUpdateGroup[uid][6]);

    $('#kt_modal_update_contact_group').modal('show');

});