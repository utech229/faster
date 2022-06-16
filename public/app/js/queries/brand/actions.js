"use strict";
const tableReloadButton    = $('#kt_table_brand_reload_button');
var uriLoad                = loadBrand;


var KTUsersBrandUser = function() {
    const t = document.getElementById("kt_modal_create_folder"),
        e = t.querySelector("#kt_modal_brand_form"),
        n = new bootstrap.Modal(t);
    return {
        init: function() {
            (() => {
                var o = FormValidation.formValidation(e, {
                    fields: {
                        '_name_brand': {
                            validators: {
                                notEmpty: {
                                    message: _brand_Required
                                },
                                stringLength: {
                                    min: 3,
                                    max: 30,
                                    message:  _brand_Required
                                },
                            }
                        },
                        '_mail_support': {
                            validators: {
                                stringLength: {
                                    min: 11,
                                    max: 30,
                                    message:  _mail_required
                                },
                                emailAddress: {
                                    message: _mail_required
                                }
                            }
                        },
                        '_mail_noreply': {
                            validators: {
                                stringLength: {
                                    min: 11,
                                    max: 30,
                                    message:  _mail_required
                                },
                                emailAddress: {
                                    message: _mail_required
                                }
                            }
                        },
                        '_phone_support': {
                            validators: {
                                notEmpty: {
                                    message: _empty_info
                                },
                                stringLength: {
                                    min: 8,
                                    max: 30,
                                    message: _phone_required
                                },
                            }
                        },
                        '_url_brand': {
                            validators: {
                                notEmpty: {
                                    message: _empty_info
                                },
                                regexp: {
                                    regexp: /^[a-zA-Z-.]+$/,
                                    message: _Only_Alphabetics
                                },
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
                    t.preventDefault(),  t.value && n.hide()
                })), t.querySelector('[data-kt-modal-action="cancel"]').addEventListener("click", (t => {
                    t.preventDefault(), e.reset(), n.hide()
                }));
                const i = t.querySelector('[data-kt-modal-action="submit"]');
                i.addEventListener("click", (function(t) {
                    t.preventDefault(), o && o.validate().then((function(t) {
                        console.log("validated!"), "Valid" == t ? (i.setAttribute("data-kt-indicator", "on"), i.disabled = !0,
                        $.ajax({
                            url: createBrand,
                            type: 'post',
                            data: new FormData(e),
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            cache: false,
                            success: function(response) {
                                    i.removeAttribute("data-kt-indicator"), i.disabled = !1;
                                    Swal.fire({
                                        text: response.message,
                                        icon: response.type,
                                        buttonsStyling: false,
                                        confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        }
                                    })
                                    if (response.type === 'success') e.reset(), n.hide(),tableReloadButton.click();
                            },
                            error: function () {
                                $(document).trigger('onAjaxError');
                                i.removeAttribute("data-kt-indicator"), i.disabled = !1;
                            },
                        })) :
                        $(document).trigger('onFormError')
                        // load.addClass('sr-only')
                        ;
                    }))
                }))
            })()
        }
    }
}();

//Load list
var KTUsersLoadBrand = function() {
    var t, e, n, r, o;
    return {
        init: function() {
            (e = document.querySelector("#kt_brand_table")) && (e.querySelectorAll("tbody tr").forEach((t => {
                const e = t.querySelectorAll("td"), n = moment(e[2].innerHTML, "DD MMM YYYY, LT").format();
                e[2].setAttribute("data-order", n)
            })), t = $(e).DataTable({
                responsive: true,
                ajax: {
                    "url": uriLoad,
                    "type": "POST",
                    data: {
                        _token: function(){ return csrfToken; }
                    },
                    error: function () {
                        $(document).trigger('toastr.tableListError');
                    }
                },
                info: !1,
                order: [[ 5, "desc" ]],
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

                }]
            }),
            $('#kt_table_brand_reload_button').on('click', function() {
                t.ajax.reload(null, false);
            }),
            document.querySelector('[data-kt-brand-table-filter="search"]').addEventListener("keyup", (function(e) {
                t.search(e.target.value).draw()
            }))
            )
        }
    }
}();

KTUtil.onDOMContentLoaded((function() {
    KTUsersBrandUser.init(),
    KTUsersLoadBrand.init()
}));

//Action by self-made


