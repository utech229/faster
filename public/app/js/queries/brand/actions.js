"use strict";
const tableReloadButton    = $('#kt_table_brand_reload_button');
var uriLoad                = loadBrand;

$(document).on('click', '#lauch_modal_submit_folder', function(e){
    $('#kt_modal_brand_form')[0].reset();
    $('#thisU').val('');
    $('#kt_modal_create_folder').modal('show');
});

$(document).on('click', '.updateBrand', function(e){
    e.preventDefault();
    $('#waiting').modal('show');

    $.ajax({
        url: checkbrand,
        type: "post",
        data: {b : $(this).data('id'), _token : csrfToken},
        dataType: "json",
        success: function(r) {
            $('#nameB').val(r.name);
            $('#uriB').val(r.urlSite);
            $('#adressEmail').val(r.adressS);
            $('#noreplyEmail').val(r.adressN);
            $('#phoneb').val(r.phone);
            $('#phoneb').val(r.phone);
            // $('#_uSelect').val()
            // $('#observations').val(r.observations);
            $('#thisU').val(r.manager[1]);
            $('#_uSelect').val(r.manager[0]).trigger('change');
            $('#waiting').modal('hide');
            var cover = user_brand_logo_link .replace("_1_", r.uriLogo);
            $("#logo_input").css("background-image", "url(" + cover + ")");
            $('#kt_modal_create_folder').modal('show');


        },
        error: function () {
            // $(document).trigger('entityUpStop', ['#editUserOption', uid, 'fa-edit']);
            // $(document).trigger('toastr.onAjaxError');
        }
    });
});

$(document).on('click', ".sBrand", function() {
	// $(document).trigger('entityUpBegin', ['#deleteUserOption', uid, 'fa-trash-alt']);
    // var u = $(this).data('id');
	// Swal.fire({
	// 	text: _ask_confirm+$(this).data('name')+" ?",
	// 	icon: "warning",
	// 	showCancelButton: true,
	// 	buttonsStyling: false,
	// 	confirmButtonText: _Yes,
	// 	cancelButtonText: _No,
	// 	customClass: {
	// 		confirmButton: "btn fw-bold btn-danger",
	// 		cancelButton: "btn fw-bold btn-active-light-primary"
	// 	}
	// }).then(function(result) {
	// 	if (result.value) {
	// 		$.ajax({
	// 			url: validateBrand,
	// 			type: "post",
	// 			data: {u : u, _token : csrfToken},
	// 			dataType: "json",
	// 			success: function(response) {
    //                 tableReloadButton.click();
	// 				Swal.fire({
	// 					text: response.message,
	// 					icon: response.status,
	// 					buttonsStyling: false,
	// 					confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
	// 					customClass: {
	// 						confirmButton: "btn btn-primary"
	// 					}
	// 				});
	// 			},
    //             // error:function(response) {
	// 			// 	$(document).trigger('onAjaxError');
	// 			// 	$(document).trigger('entityUpStop', ['#deleteUserOption', uid, 'fa-trash-alt']);
	// 			// }
	// 		});
	// 	}
	// });
    $("#brand_name").text($(this).data('name'));
    $('#key').val($(this).data('id'));
    if($(this).data('status') == 3){
        $('input:radio[name=st]')[2].checked=true;
    }
    $('#kt_modal_reload_brand').modal('show');
});

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
                                }
                            }
                        },
                        '_mail_noreply': {
                            validators: {
                                stringLength: {
                                    min: 11,
                                    max: 30,
                                    message:  _mail_required
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
                            url: createBrandJ,
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

var KTUsersBrandStatus = function() {
    const t = document.getElementById("kt_modal_reload_brand"),
        e = t.querySelector("#kt_form_validate_brand"),
        n = new bootstrap.Modal(t);
    return {
        init: function() {
            (() => {
                var o = FormValidation.formValidation(e, {
                    // fields: {

                    // },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger,
                        bootstrap: new FormValidation.plugins.Bootstrap5({
                            rowSelector: ".fv-row",
                            eleInvalidClass: "",
                            eleValidClass: ""
                        })
                    }
                });
                // t.querySelector('[data-kt-modal-action="close"]').addEventListener("click", (t => {
                //     t.preventDefault(),  t.value && n.hide()
                // })),
                 t.querySelector('[data-kt-modal-action="cancel"]').addEventListener("click", (t => {
                    t.preventDefault(), e.reset(), n.hide()
                }));
                const i = t.querySelector('[data-kt-modal-action="submit"]');
                i.addEventListener("click", (function(t) {
                    t.preventDefault(), o && o.validate().then((function(t) {
                        console.log("validated!"), "Valid" == t ? (i.setAttribute("data-kt-indicator", "on"), i.disabled = !0,
                        $.ajax({
                            url: reload_brand,
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
                    targets: 4,
                    render: function(data, type, full, meta) {
                        var status = {
                            1 : { 'title': _pendingb, 'class': 'warning' },
                            3 : { 'title': _validateb, 'class': 'success' },
                            4 : { 'title': _canceledb, 'class': 'danger' },
                            5 : { 'title': _disabled, 'class': 'danger' },
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
                    // visible: (!pDownload && !pRefresh) ? false : true,
                    render : function (data) {
                        var detailIcon =  `<a class="btn btn-icon btn-active-light-primary w-30px h-30px me-3 detailBrand" href="`+data.link+`" target="_blank">
                            <i id="editUserOption`+data.uid+`" title="`+_detalMsg+`" class="fa fa-link"></i>
                        </a>`;
                        var updateIcon =`
                        <button class="btn btn-icon btn-active-light-primary w-30px h-30px updateBrand"
                            data-id=`+data.uid+` data-kt-users-table-filter="delete_row">
                                <i id="deleteUserOption`+data.uid+`" title="`+_msgUpdate+`" class="text-danger fa fa-edit"></i>
                        </button>`;
                        var validateIcon = `
                        <button class="btn btn-icon btn-active-light-info w-30px h-30px sBrand"
                            data-id=`+data.uid+` data-status="`+data.status+`" data-name="`+data.name+`" data-kt-users-table-filter="delete_row">
                                <i id="deleteUserOption`+data.uid+`"  title="`+_validateInfo+`" class="text-info i bi-gear-wide-connected"></i>
                        </button>`;

                        var validate = (data.pvalidate) ? validateIcon : '';
                        var etat = detailIcon + updateIcon + validate;

                        return etat;
                    }
                }
            ],
                columns: [

                    { data: 'brand'},

                    { data: 'administrator', responsivePriority: -5},

                    { data: 'urlSite', responsivePriority: -4  },

                    { data: 'emailV' , responsivePriority: 0},

                    { data: 'status'},

                    { data: 'createdAt'},

                    { data: 'action',responsivePriority: -9 },
                ],
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
    KTUsersLoadBrand.init(),
    KTUsersBrandStatus.init()
}));

//Action by self-made


