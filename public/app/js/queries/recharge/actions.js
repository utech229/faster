"use strict";
const tableReloadButton    = $('#kt_table_recharge_reload_button');
var uriLoad                = loadRecharge;
$('.phone-required').hide();
$(document).on('click', '.cursor-balance', function(e){
    $('.phone-required').hide();
});


$(document).on('click', '.cursor-mobile-carte', function(e){
    $('.phone-required').show();
});

$(document).on('click', '#self_recharge', function(e){
    $('.uAction').hide();
    $('#cursorBalance').hide();
    $('input:radio[name=typeProcess]')[1].checked=true;
    $('#kt_modal_recharge').modal('show');
});


var KTUsersRechargeUser = function() {
    const t = document.getElementById("kt_modal_recharge"),
        e = t.querySelector("#kt_modal_recharge_form"),
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
                        'phone': {
                            validators: {
                                notEmpty: {
                                    message: msg_phone_required
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
                    t.preventDefault(),  t.value && n.hide()
                })), t.querySelector('[data-kt-modal-action="cancel"]').addEventListener("click", (t => {
                    t.preventDefault(), e.reset(), n.hide()
                }));
                const i = t.querySelector('[data-kt-modal-action="submit"]');
                i.addEventListener("click", (function(t) {
                    t.preventDefault(), o && o.validate().then((function(t) {
                        console.log("validated!"), "Valid" == t ? (i.setAttribute("data-kt-indicator", "on"), i.disabled = !0,
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
var KTUsersLoadRecharge = function() {
    var t, e, n, r, o;
    return {
        init: function() {
            (e = document.querySelector("#kt_recharge_table")) && (e.querySelectorAll("tbody tr").forEach((t => {
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
                    },
                },
                info: !1,
                order: [[ 5, "desc" ]],

                columnDefs: [{
                    orderable: !1,
                    targets: 0,
                },
                {
                    targets: 4,
                    render: function(data, type, full, meta) {
                        var status = {
                            2 : { 'title': _Pending, 'class': 'warning' },
                            6 : { 'title': _approved, 'class': 'success' },
                            7 : { 'title': _canceled, 'class': 'danger' },
                        };
                        if (typeof status[data] === 'undefined') {
                            return data;
                        }
                        return '<span class="badge badge-light-' + status[data].class + '">' + status[data].title + '</span>';
                    },

                },
                {
                    orderable: !1,
                    targets: 6,
                    visible: (!pDownload && !pRefresh) ? false : true,
                    render : function (data,type, full, meta) {
                        var downloadIcon =  `<button class="btn btn-icon btn-active-light-primary w-30px h-30px me-3 download" data-id=`+data.uid+`>
                            <i id="editUserOption`+data.uid+`" aDownload title="`+_downloadMsg+`" class="fa fa-download"></i>
                        </button>`;
                        var reloadIcon =`
                        <button class="btn btn-icon btn-active-light-primary w-30px h-30px reloadRecharge"
                            data-id=`+data.uid+` data-kt-users-table-filter="delete_row">
                                <i id="deleteUserOption`+data.uid+`" title="`+_reloadMsg+`" class="text-danger fonticon-repeat"></i>
                        </button>`;

                        var etat = (data.status== 6) ? downloadIcon : (data.status==7) ? '': reloadIcon ;

                        return etat;
                    }
                }
            ],
                columns: [

                    { data: 'ref'},

                    { data: 'amount', responsivePriority: -5},

                    { data: 'emailS', responsivePriority: -4  },

                    { data: 'emailR' , responsivePriority: 0},

                    { data: 'status'},

                    { data: 'date'},

                    { data: 'action',responsivePriority: -9 },
                ],
            }),
            $('#kt_table_recharge_reload_button').on('click', function() {
                t.ajax.reload(null, false);
            }),
            document.querySelector('[data-kt-recharge-table-filter="search"]').addEventListener("keyup", (function(e) {
                t.search(e.target.value).draw()
            }))
            )
        }
    }
}();

KTUtil.onDOMContentLoaded((function() {
    KTUsersRechargeUser.init(),
    KTUsersLoadRecharge.init()
}));

//Action by self-made


