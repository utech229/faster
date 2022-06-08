/******/
(() => { 
    //webpackBootstrap /******/
    "use strict";
    var __webpack_exports__ = {};
    /*!*************************************************************************************!*\
      !*** ../../../themes/metronic/html/demo1/src/js/custom/apps/customers/list/list.js ***!
      \*************************************************************************************/


    // Class definition
    var KTLogsList = function() {
        // Define shared variables
        var datatable;
        var filterUserRole;
        var filterRequestCode;
        var table

        // Private functions
        var initLogsList = function() {
            // Set date data order
            const tableRows = table.querySelectorAll('tbody tr');

            tableRows.forEach(row => {
                const dateRow = row.querySelectorAll('td');
                const realDate = moment(dateRow[5].innerHTML, "DD MMM YYYY, LT").format(); // select date from 5th column in table
                dateRow[5].setAttribute('data-order', realDate);
            });

            // Init datatable --- more info on datatables: https://datatables.net/manual/

            datatable = $(table).DataTable({
                responsive: true,
                ajax: {
                    "url": get_logs,
                    "type": "POST"
                },
                "info": false,
                'columnDefs': [{
                        orderable: false,
                        targets: 0,
                        render: function(data, type, full, meta) {
                            return '<div class = "form-check form-check-sm form-check-custom form-check-solid" ><input class = "form-check-input" type = "checkbox" value = "' + data + '"/> </div > ';
                        }
                    },
                    {
                        targets: 4,
                        render: function(data, type, full, meta) {

                            return '<span class="badge badge-light-primary">' + data + '</span>';
                        },
                    }, // Disable ordering on column 0 (checkbox)
                    {
                        targets: 7,
                        render: function(data, type, full, meta) {
                            var status = {
                                'ROLE_USER': { 'title': 'Utilisateur', 'class': 'danger' },
                                'ROLE_PROFESSIONAL': { 'title': 'Professionnel', 'class': 'secondary' },
                                'ROLE_COMPANY': { 'title': 'Entreprise', 'class': 'warning' },
                                'ROLE_AMBASSADOR': { 'title': 'Ambassadeur', 'class': 'primary' },
                                'ROLE_ADMIN': { 'title': 'Administrateur', 'class': 'info' },
                                'ROLE_SUPER_ADMIN': { 'title': 'Super administrateur', 'class': 'success' },
                            };
                            if (typeof status[data] === 'undefined') {
                                return data;
                            }
                            return '<span class="badge badge-light-' + status[data].class + '">' + status[data].title + '</span>';
                        },

                    },
                    {
                        targets: 8,
                        render: function(data, type, full, meta) {
                            var status = {
                                '200': { 'title': '200', 'class': 'success' },
                                '201': { 'title': '201', 'class': 'secondary' },
                                '400': { 'title': '400', 'class': 'danger' },
                                '401': { 'title': '401', 'class': 'secondary' },
                                '402': { 'title': '402', 'class': 'info' },
                                '403': { 'title': '403', 'class': 'warning' },
                                '404': { 'title': '404', 'class': 'primary' },
                                '500': { 'title': '500', 'class': 'danger' },
                                '501': { 'title': '501', 'class': 'secondary' },
                            };
                            if (typeof status[data] === 'undefined') {
                                return data;
                            }
                            return '<span class="badge badge-light-' + status[data].class + '">' + status[data].title + '</span>';
                        },

                    }
                ],
                columns: [

                    { data: 'OrderID', responsivePriority: -6 },

                    { data: 'Login', responsivePriority: -10 },

                    { data: 'Name' },

                    { data: 'Action' },

                    { data: 'Ip', responsivePriority: -9 },

                    { data: 'Phone' },

                    { data: 'Agent' },

                    { data: 'Role' },

                    { data: 'Status', responsivePriority: -7 },

                    { data: 'RegisterDate'},
                ],
                lengthMenu: [10, 25, 100, 250, 500, 1000],
                pageLength: 10,
            });

            
            /*$('#reload_logs').on('click', function() {
                datatable.ajax.reload(null, false);
            });*/
            // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
            datatable.on('draw', function() {
               
            });
        }

        // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
        var handleSearchDatatable = () => {
            const filterSearch = document.querySelector('[data-kt-log-table-filter="search"]');
            filterSearch.addEventListener('keyup', function(e) {
                datatable.search(e.target.value).draw();
            });
        }

        // Filter Datatable
        var handleFilterDatatable = () => {
            // Select filter options
            filterUserRole = $('[data-kt-log-table-filter="role"]');
            filterRequestCode = $('[data-kt-log-table-filter="status"]');
            const filterButton = document.querySelector('[data-kt-log-table-filter="filter"]');

            // Filter datatable on submit
            filterButton.addEventListener('click', function() {
                // Get filter values
                const roleValue = filterUserRole.val();
                const statusValue = filterRequestCode.val();
                // Build filter string from filter options
                const filterString = roleValue + ' ' + statusValue;
                // Filter datatable --- official docs reference: https://datatables.net/reference/api/search()
                datatable.search(filterString).draw();
            });
        }

        // Reset Filter
        var handleResetForm = () => {
            // Select reset button
            const resetButton = document.querySelector('[data-kt-log-table-filter="reset"]');

            // Reset datatable
            resetButton.addEventListener('click', function() {
                // Reset month
                filterUserRole.val(null).trigger('change');

                // Reset payment type
               filterRequestCode[0].checked = true;

                // Reset datatable --- official docs reference: https://datatables.net/reference/api/search()
                datatable.search('').draw();
            });
        }

        // Public methods
        return {
            init: function() {
                table = document.querySelector('#kt_table_logs');

                if (!table) {
                    return;
                }

                initLogsList();
                handleSearchDatatable();
                handleFilterDatatable();
                handleResetForm();
            }
        }
    }();

    // On document ready
    KTUtil.onDOMContentLoaded(function() {
        KTLogsList.init();
    });
    /******/
})();
//# sourceMappingURL=list.js.map