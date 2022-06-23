"use strict";
const SMSCampaignManagerError = function(){
    const el = document.querySelector("#tb_error"), // el : selecteur de la table html
    columns = [
        { // Number
            targets: 0,
            responsivePriority: 0,
            render: function(data, type, full, meta) {
                return data;
            },

        },
        { // Phone
            targets: 1,
            responsivePriority: 1,
            render: function(data, type, full, meta) {
                return data;
            },

        },
        { // messageError
            targets: 2,
            orderable: !1,
            responsivePriority: 3,
            render: function(data, type, full, meta) {
                return "<p class='fs-7 fw-bolder text-danger'>"+data+"</p>";
            },

        },
        { // full phone
            targets: 3,
            orderable: !1,
            responsivePriority: 4,
            render: function(data, type, full, meta) {
                return data['dial_code']+'<br/>'+data['code']+'<br/>'+data['country'];
            },

        },
        { // message original
            targets: 4,
            orderable: !1,
            visible: !1,
            responsivePriority: 5,
            render: function(data, type, full, meta) {
                return data;
            }
        },
        { // Actions
            targets: 5,
            orderable: !1,
            responsivePriority: 2,
            render : function (data, type, full, meta) {
                return `<!--begin::Update-->
                    <button class="btn btn-icon btn-active-light-primary btn-hover-scale w-30px h-30px" id="update" data-id=`+data[0]+` data-campaign=`+data[1]+`>
                        <span class="indicator-label"><i class="fa fa-edit"></i></span>
                        <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                <!--end::Update-->
                <!--begin::Delete-->
                    <button class="btn btn-icon btn-active-light-danger btn-hover-scale w-30px h-30px" id="delete" data-id=`+data[0]+` data-campaign=`+data[1]+`>
                        <span class="indicator-label"><i class="text-danger fa fa-trash-alt"></i></span>
                        <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                <!--end::Delete-->`;
            }
        }
    ];
    var datatable;

    return {
        init: (data)=>{
            datatable = $(el).DataTable({ // Initiation du datatable
                destroy: true,
                responsive: true,
                data,
                info: !1,
                order: [[ 5, "asc" ]],
                columnDefs: columns,
                lengthMenu: [10, 25, 100, 250, 500, 1000],
                pageLength: 10,
                language: {
                    url: _language_datatables,
                },
                dom: '<"top text-end bt-export d-none"B>rtF<"row"<"col-sm-6"l><"col-sm-6"p>>',
            });

            datatable.on('draw', ()=>{});
        }
    }
}();

// KTUtil.onDOMContentLoaded((function() {
//     SMSCampaignManagerError.init()
// }));
