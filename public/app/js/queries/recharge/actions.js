"use strict";
$('.phone-required').hide();
$(document).on('click', '.cursor-balance', function(e){
    $('.phone-required').hide();
});

$(document).on('click', '.cursor-mobile-carte', function(e){
    $('.phone-required').show();
});

var KTUsersRechargeUser = function() {
    const t = document.getElementById("kt_modal_other_recharge"),
        e = t.querySelector("#kt_modal_create_recharge_other"),
        n = new bootstrap.Modal(t);
    return {
        init: function() {
            (() => {
                var o = FormValidation.formValidation(e, {
                    fields: {
                        'amount': {
                            validators: {
                                notEmpty: {
                                    message: msg_amount_required
                                }
                            }
                        },
                    },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger,
                        bootstrap: new FormValidation.plugins.Bootstrap5({
                            rowSelector: ".fv-row",
                            eleInvalidClass: "",
                            eleValidClass: ""
                        })
                    }
                });
                t.querySelector('[data-kt-modal-action="close"]').addEventListener("click", (t => {
                    t.preventDefault(), Swal.fire({
                        text: _modal_close,
                        icon: "warning",
                        showCancelButton: !0,
                        buttonsStyling: !1,
                        confirmButtonText: _Yes,
                        cancelButtonText: _No,
                        customClass: {
                            confirmButton: "btn btn-primary",
                            cancelButton: "btn btn-active-light"
                        }
                    }).then((function(t) {
                        t.value && n.hide()
                    }))
                })), t.querySelector('[data-kt-modal-action="cancel"]').addEventListener("click", (t => {
                    t.preventDefault(), Swal.fire({
                        text:  _Cancel_Question,
                        icon: "warning",
                        showCancelButton: !0,
                        buttonsStyling: !1,
                        confirmButtonText: _Yes,
                        cancelButtonText: _No,
                        customClass: {
                            confirmButton: "btn btn-primary",
                            cancelButton: "btn btn-active-light"
                        }
                    }).then((function(t) {
                        t.value ? (e.reset(), n.hide()) : "cancel" === t.dismiss && Swal.fire({
                            text: _no_cancel_form,
                            icon: "error",
                            buttonsStyling: !1,
                            confirmButtonText:  _Form_Ok_Swal_Button_Text_Notification,
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        })
                    }))
                }));
                const i = t.querySelector('[data-kt-modal-action="submit"]');
                i.addEventListener("click", (function(t) {
                    t.preventDefault(), o && o.validate().then((function(t) {
                        console.log("validated!"), "Valid" == t ? (i.setAttribute("data-kt-indicator", "on"), i.disabled = !0,
                        load.removeClass('sr-only'),
                        $.ajax({
                            url: createRecharge,
                            type: 'post',
                            data: new FormData(e),
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            cache: false,
                            success: function(response) {
                                    i.removeAttribute("data-kt-indicator"), i.disabled = !1;
                                    load.addClass('sr-only')
                                    Swal.fire({
                                        title: _Swal_success,
                                        text: response.message,
                                        icon: response.type,
                                        buttonsStyling: false,
                                        confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        }
                                    }).then((function(t) {
                                        if (response.type === 'success') {
                                            t.isConfirmed && e.reset();
                                            e.reset(),tableReloadButton.click();
                                            (isPermissionUpdating == true) ? n.hide() : null;
                                        }
                                    }))
                            },
                            error: function () {
                                $(document).trigger('onAjaxError');
                                i.removeAttribute("data-kt-indicator"), i.disabled = !1;
                                load.addClass('sr-only')
                            },
                        })) :
                        $(document).trigger('onFormError'),
                        load.addClass('sr-only');
                    }))
                }))
            })()
        }
    }
}();
KTUtil.onDOMContentLoaded((function() {
    KTUsersRechargeUser.init()
}));


//Loading TABLE RECHARGE
"use strict";
var KTUsersRoutersList = function() {
    var t, e, n, r, o;
    return {
        init: function() {
            (e = document.querySelector("#kt_recharge_table")) && (e.querySelectorAll("tbody tr").forEach((t => {
                const e = t.querySelectorAll("td"), n = moment(e[2].innerHTML, "DD MMM YYYY, LT").format();
                e[2].setAttribute("data-order", n)
            })), t = $(e).DataTable({
                responsive: true,
                ajax: {
                    "url": loadRecharge,
                    "type": "POST",
                    data: {
                        _token: function(){ return csrfToken; }
                    },
                    error: function () {
                        $(document).trigger('toastr.tableListError');
                    }
                },
                info: !1,
                order: [[ 4, "desc" ]],
                columnDefs: [{
                    orderable: !1,
                    targets: 0,
                },
                {
                    orderable: !1,
                    targets: 3,
                    render: function(data, type, full, meta) {
                        return  dateFormat(moment(data, "YYYY-MM-DDTHH:mm:ssZZ").format());
                    }
                },
                {
                    orderable: !1,
                    targets: 4,
                    render: function(data, type, full, meta) {
                        return  dateFormat(moment(data, "YYYY-MM-DDTHH:mm:ssZZ").format());
                    }
                },
                ],
                lengthMenu: [10, 25, 100, 250, 500, 1000],
                pageLength: 5,
            }),
            $('#kt_modal_create_recharge_reload_button').on('click', function() {
                t.ajax.reload(null, false);
            }),
            document.querySelector('[data-kt-recharge-table-filter="search"]').addEventListener("keyup", (function(e) {
                t.search(e.target.value).draw()
            })), e.querySelectorAll('[data-kt-recharge-table-filter="delete_row"]').forEach((e => {
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
    KTUsersRoutersList.init()
}));