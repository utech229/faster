
"use strict";
var allHidden           =   [];
var group               =   [];
var tabUpdateContact    =   [];
var KTContactList = function() {
    var e, t, n, r, o = document.getElementById("kt_contacts_table"),
        c = () => {
            o.querySelectorAll('[data-kt-contact-table-filter="delete_row"]').forEach((t => {
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
            t = document.querySelector('[data-kt-contact-table-toolbar="base"]'),
                n = document.querySelector('[data-kt-contact-table-toolbar="selected"]'),
                r = document.querySelector('[data-kt-contact-table-toolbar="selected_count"]');
            const s = document.querySelector('[data-kt-contact-table-toolbar="delete_selected"]');
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
                        if (t.value) 
                        {
                            let tabUid  =  [];
                            c.forEach((t => {
                                        if(t.checked && $(t).attr("data-value") != undefined){
                                            tabUid.push($(t).attr("data-value"));
                                        }
                            }));
                            if (tabUid.length > 0) {
                                $.ajax({
                                    url: contact_delete,
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
        
                                            $('#kt_modal_add_contact_reload_button').click();
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
                            "cancel" === t.dismiss ;
                        }
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
            c && pDelete ? (r.innerHTML = l,
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
                        url: contact_list,
                        type: "POST",
                        data: {
                            _token: function(){ return csrfToken; },
                            _group: function(){ return document.querySelector('[data-kt-contact-group="group"]').value; },
                            _user: function(){ return document.querySelector('[data-kt-contact-user="user"]').value; }
                        },
                        error: function () { 
                            $(document).trigger('toastr.onAjaxError');
                        },
                        dataSrc: function(json) {
                            $("#stat_contact").text(json.data.length);
                            group=json.group;
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
                                tabUpdateContact[data[0]] = full;
                                return  `<div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" data-value="`+data[0]+`" />
                            </div>`;
                         
                            }
                        },
                        // Numéro
                        {
                            responsivePriority: 1,
                            targets: 1, 
                            render: function (data, type, full, meta) {
                                return  data;
                            }
                        },
                        // Champ1
                        {
                            responsivePriority: 3,
                            targets: 2, 
                            render: function (data, type, full, meta) {
                                return data;
                            }
                        },
                        // Champ2
                        {
                            responsivePriority: 4,
                            targets: 3, 
                            render: function (data, type, full, meta) {
                                return data;
                            }
                        },
                        // Champ3
                        {
                            responsivePriority: 5,
                            targets: 4, 
                            render: function (data, type, full, meta) {
                                return data;
                            }
                        },
                        // Champ4
                        {
                            responsivePriority: 6,
                            targets: 5, 
                            render: function (data, type, full, meta) {
                                return data;
                            }
                        },
                        // Champ5
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
                            responsivePriority: 2,
                            targets: 9, 
                            render: function (data, type, full, meta) {
                               
                                var updaterIcon =  `<!--begin::Update-->
                                <button class="btn btn-icon btn-active-light-primary w-30px h-30px me-3 contactUpdater" data-id=`+data+`>
                                    <i id="editUserOption`+data+`" class="fa fa-edit"></i>
                                </button>
                                <!--end::Update-->`;
                                var deleterIcon =  `<!--begin::Delete-->
                                <button class="btn btn-icon btn-active-light-primary w-30px h-30px contactDeleter" 
                                    data-id=`+data+`>
                                        <i id=`+data+` class="text-danger fa fa-trash-alt"></i>
                                </button>
                                <!--end::Delete-->`;
                                updaterIcon = (pUpdate) ? updaterIcon : '' ;
                                deleterIcon = (pDelete) ? deleterIcon : '' ;
                                return updaterIcon + deleterIcon;
                            }
                        }
                    ],
                    pageLength: 10,
                    lengthChange: true,
                    "info": true,
                    lengthMenu: [10, 25, 100, 250, 500, 1000],                   
                }),
                $('#kt_modal_add_contact_reload_button').on('click', function() {
                    e.ajax.reload(null, false);
                })).on("draw", (function() { l(), c(), a()
                })), l(),
                document.querySelector('[data-kt-contact-table-filter="search"]').addEventListener("keyup", (function(t) {
                    e.search(t.target.value).draw()

                })),
                $('[data-kt-contact-group="group"]').on('change', function() {
                    e.ajax.reload();
                }),
                document.querySelector('[data-kt-contact-table-filter="reset"]').addEventListener("click", (function() {
                    document.querySelector('[data-kt-contact-table-filter="form"]').querySelectorAll("select").forEach((e => {
                            $(e).val("").trigger("change");
                        })),
                        e.search("").draw()
                })),
                c(), (() => {
                    const t = document.querySelector('[data-kt-contact-table-filter="form"]'),
                        n = t.querySelector('[data-kt-contact-table-filter="filter"]'),
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
                e.on("draw", function() { l(), c(), a();
                    $("#champ1").text(group['field1'] ? group['field1'] : "Champ1") ;
                    $("#champ2").text(group['field2'] ? group['field2'] : "Champ2") ;
                    $("#champ3").text(group['field3'] ? group['field3'] : "Champ3")  ;
                    $("#champ4").text(group['field4'] ? group['field4'] : "Champ4") ;
                    $("#champ5").text(group['field5'] ? group['field5'] : "Champ5") ;
                })
        }
    };

}();

KTUtil.onDOMContentLoaded((function() {
    KTContactList.init();
}));

$('#kt_docs_repeater_basic').repeater({
    initEmpty: false,

    defaultValues: {
        'text-input': 'foo'
    },

    show: function () {
        $(this).slideDown();
        var inputs = document.querySelectorAll("[data-name=phone]");

        inputs.forEach((input, index) => {
            var div = input.closest("div.fv");
            var tel = allHidden[$(input).attr("data-index")];
            var phone = tel ? tel.getNumber() : intl[0].getNumber();
            var iti = div.querySelector(".iti");
            $(iti).remove();
            if(inputs.length == index+1){ phone = "";}
            $(div).html(`<input type="tel" value="`+phone+`" name="kt_docs_repeater_basic[`+index+`][phone]" data-index="`+index+`" data-name="phone" class="form-control " placeholder="97979797" />`)
        });
        inputs = document.querySelectorAll("[data-name=phone]");
        allHidden = [];
        inputs.forEach((input, index) => {
            allHidden[$(input).attr("data-index")] =  intlPhone(input);
        });
    },

    hide: function (deleteElement) {
        $(this).slideUp(deleteElement);
    }
});

$(document).on('click', ".contactUpdater", function(e) 
{
    var uid = $(this).data('id'),
    groupUid = tabUpdateContact[uid][0][1];

    $("#id_group_contact").val(groupUid).trigger("change");
    $("#phone_id").val(tabUpdateContact[uid][1]);
    $("#contact_id").val(uid);
    $("#contact_set1").val(tabUpdateContact[uid][2]);
    $("#contact_set2").val(tabUpdateContact[uid][3]);
    $("#contact_set3").val(tabUpdateContact[uid][4]);
    $("#contact_set4").val(tabUpdateContact[uid][5]);
    $("#contact_set5").val(tabUpdateContact[uid][6]);

    $('#kt_modal_update_contact').modal('show');
    
});

$(document).on('change',"#list_user_contact_id", function (e) {
    let uid = $(this).val();
    $.ajax({
        url:get_group,
        type:"post",
        data:{  _uid:   uid,
                _token: function() { return csrfToken; }
            },
        dataType:"json",
        success: function (response) {
            
            let obj = response.data;

            document.getElementById("group_id_add_contact").options.length=1;
            document.getElementById("list_group_id").options.length=1;
            document.getElementById("id_group_contact_import").options.length=1;
            
            for (const i of obj) 
            {
                let el1         = document.createElement("option");
                let el2         = document.createElement("option");
                let el3         = document.createElement("option");

                el1.textContent = i[1];
                el1.value = i[0];
                el2.textContent = i[1];
                el2.value = i[0];
                el3.textContent = i[1];
                el3.value = i[0];

                document.getElementById("group_id_add_contact").appendChild(el1);
                document.getElementById("list_group_id").appendChild(el2);
                document.getElementById("id_group_contact_import").appendChild(el3);
            }
            $('#kt_modal_add_contact_reload_button').click();
        },
        error: function () {
            
        }

    });
});



function infoImportFile() {
    console.log();
    window.location.href = _base_url+'/app/exemple/exemple.xlsx';
}
// Champ Importation
var myDropzone = new Dropzone("#kt_import", {
    url: url_import, // Set the url for your upload script location
    paramName: "file", // The name that will be used to transfer the file
    maxFiles: 1,
    maxFilesize: 10, // MB
    addRemoveLinks: true,
    acceptedFiles: ".xlsx,.xls,.csv",
    // autoQueue: false,
    // autoProcessQueue: false,
    accept: function(file, done) {
        $("[name=hidden_file]").val("");
        done();
    },
    success: function(file, response){
        if(response.status == "success"){
            $("[name=hidden_file]").val(response.data.url);
           
        }else swalSimple(response.type, response.message);
    }
});