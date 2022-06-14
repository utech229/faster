"use strict";
const SenderManager = function(){
    // t : datatable; e : permission edit; d : permission delete; l
    var t, e = pEdit, d = pDelete, l = pList, g, h = [];
    const el = document.querySelector("#tb_sender"), // el : selecteur de la table html
    cls = [
        { // order
            targets: 0,
            orderable: !1,
            responsivePriority: 2,
            render: function(data, type, full, meta) {
                return '';
            },

        },
        { // name
            targets: 1,
            responsivePriority: 0,
            render: function(data, type, full, meta) {
                return data;
            },

        },
        { // status
            targets: 2,
            responsivePriority: 3,
            render: function(data, type, full, meta) {
                return '<span class="badge badge-light-'+data.label+'">' + data.name + '</span>';
            },

        },
        { // createdAt
            targets: 3,
            responsivePriority: 4,
            render: function(data, type, full, meta) {
                return viewTime(data);
            }
        },
        { // manager
            targets: 4,
            visible: l ? true : false,
            responsivePriority: 4,
            render: function(data, type, full, meta) {
                return data[0];
            }
        },
        { // updatedAt
            targets: 5,
            responsivePriority: 5,
            render: function(data, type, full, meta) {
                return viewTime(data);
            }
        },
        { // observation
            targets: 6,
            orderable: !1,
            responsivePriority: 6,
            render : function (data,type, full, meta) {
                return "<p class='text'>"+data+"</p>";
            }
        },
        { // Actions
            targets: 7,
            orderable: !1,
            visible: (e || d) ? true : false,
            responsivePriority: 1,
            render : function (data,type, full, meta) {
                h[data] = full;
                var icons = e ? `<!--begin::Update-->
                    <button class="btn btn-icon btn-active-light-primary btn-hover-scale w-30px h-30px" id="update" data-id=`+data+`>
                        <span class="indicator-label"><i class="fa fa-edit"></i></span>
                        <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                <!--end::Update-->`:'';

                if(e){
                    icons += full[2].code !== 3 ? `<!--begin::Enable-->
                        <button class="btn btn-icon btn-active-light-primary btn-hover-scale w-30px h-30px" id="enable" data-id=`+data+`>
                            <span class="indicator-label"><i class="text-success fa fa-unlock"></i></span>
                            <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    <!--end::Enable-->` : `<!--begin::Disable-->
                        <button class="btn btn-icon btn-active-light-danger btn-hover-scale w-30px h-30px" id="disable" data-id=`+data+`>
                            <span class="indicator-label"><i class="text-warning fa fa-lock"></i></span>
                            <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    <!--end::Disable-->`;
                }

                icons += (d && full[2].code !== 5) ? `<!--begin::Delete-->
                    <button class="btn btn-icon btn-active-light-danger btn-hover-scale w-30px h-30px" id="delete" data-id=`+data+`>
                        <span class="indicator-label"><i class="text-danger fa fa-trash-alt"></i></span>
                        <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                <!--end::Delete-->`:'';

                return icons;
            }
        }
    ], // cls : colonnes de datatable
    a = document.querySelector("#add_sender"), // a : selecteur bouton ajout de sender
    m = document.querySelector("#modal_sender"), // m : selecteur du div ayant la class "modal"
    f = m.querySelector("#form"), // f : selecteur du formulaire dans le modal
    c = f.querySelector("#cancel"), // c : selecteur du bouton quitter dans le formulaire
    i = f.querySelector("#submit"), // i : selecteur du bouton envoyer dans le formulaire
    k = m.querySelector("#close"), // k : selecteur de l'icon fermé dans le modal
    s = document.querySelector("#search"), // s : selecteur de l'input recherche
    x = document.querySelector("#export"), // x : selecteur du bouton affiche/cache des boutons d'exportation
    y = ".bt-export", // y : selecteur de l'ensemble des boutons d'exportation
    u = "#update",
    v = "#enable",
    w = "#disable",
    z = "#delete",
    o = new bootstrap.Modal(m),
    r = ()=>{ f.reset(); $(f.querySelector("#sender__token")).val(_token); btnAnimation(i); },
    filling = (target)=>{
        var id = $(target.closest("button")).attr("data-id"),
        g = url_edit.replace("_1_", id);
        $(f.querySelector("#sender_manager")).val(h[id][4][1]); $(f.querySelector("#sender_manager")).trigger("change");
        $(f.querySelector("#sender_name")).val(h[id][1]);
        $(f.querySelector("#sender_status")).val(h[id][2]["uid"]); $(f.querySelector("#sender_status")).trigger("change");
        $(f.querySelector("#sender_observation")).val(h[id][6]);
        $(f.querySelector("#sender_uid")).val(h[id][7]);
    },
    post = (target, action)=>{
        const $this = target.closest("button"),
        id = $($this).attr("data-id");
        var message;
        switch (action) {
            case "1": message = _enabled_data; break;
            case "2": message = _delete_question; break;
            default: message = _disabled_data; break;
        }
        swalConfirm("warning", message, ()=>{
            btnAnimation($this, true);
            $.ajax({
                url: url_action.replace("_1_", id),
                type: 'post',
                data: {_token, action},
                dataType: 'json',
                success: function (response) {
                    swalSimple(response.type, response.message);
                    if (response.status === 'success') {
                        t.ajax.reload();
                    }
                    btnAnimation($this);
                },
                error: function (response) {
                    swalSimple("error", _Form_Error_Swal);
                    btnAnimation($this);
                    console.log(response);
                }
            });
        });
    };

    return {
        init: ()=>{
            t = $(el).DataTable({
                responsive: true,
                ajax: {
                    "url": url_get,
                    "type": "POST",
                    data: {
                        _token: function(){ return _token; },
                        manager: function(){ return ""; },
                    },
                    dataSrc: function(response){
                        e = response.data.permission.pEdit;
                        d = response.data.permission.pDelete;
                        l = response.data.permission.pList;
                        return response.data.table;
                    },
                    error: function (response) {
                        $(document).trigger('toastr.tableListError');
                    }
                },
                info: !1,
                order: [[ 1, "desc" ]],
                columnDefs: cls,
                lengthMenu: [10, 25, 100, 250, 500, 1000],
                pageLength: 10,
                language: {
                    url: _language_datatables,
                },
                dom: '<"top text-end bt-export d-none"B>rtF<"row"<"col-sm-6"l><"col-sm-6"p>>',
            });

            t.on('draw', ()=>{
                $(el).off("click", u);
                $(el).on("click", u, ($this)=>{ $this.preventDefault(); r(); filling($this.target); o.show(); });

                $(el).off("click", v);
                $(el).on("click", v, ($this)=>{ $this.preventDefault(); post($this.target, "1"); });

                $(el).off("click", w);
                $(el).on("click", w, ($this)=>{ $this.preventDefault(); post($this.target, "0"); });

                $(el).off("click", z);
                $(el).on("click", z, ($this)=>{ $this.preventDefault(); post($this.target, "2"); });
            });

            $(s).on('keyup', ($this)=>{ t.search($this.target.value).draw(); });

            $(a).on("click", ($this)=>{ $this.preventDefault(); r(); g = url_new; o.show(); });

            $(c).on("click", ($this)=>{ $this.preventDefault(); o.hide(); });

            $(k).on("click", ($this)=>{ $this.preventDefault(); o.hide(); });

            $(document).on("submit", f, ($this)=>{
                $this.preventDefault();
                btnAnimation(i, true);
                $.ajax({
                    url: g,
                    type: 'post',
                    data: new FormData(f),
                    processData: false,
                    cache: false,
                    contentType: false,
                    success: function (response) {
                        swalSimple(response.type, response.message);
                        if (response.status === 'success') {
                            t.ajax.reload();
                            o.hide();
                        }
                    },
                    error: function (response) {
                        swalSimple("error", _Form_Error_Swal);
                        btnAnimation(i);
                        console.log(response);
                    }
                });
            });

            // Action sur bouton export
            $(x).on('click', ($this)=>{ $this.preventDefault(); return $(y).hasClass('d-none')?$(y).removeClass('d-none'):$(y).addClass('d-none'); });
        }
    }
}();

KTUtil.onDOMContentLoaded((function() {
    SenderManager.init()
}));
