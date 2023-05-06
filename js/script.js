jQuery(document).ready(function () {
    const tableSelector = jQuery('#mainRegisterTable');
    // Add search inputs
    jQuery('#mainRegisterTable tr.filter td').each(function (index) {
        if (1 === index) {
            jQuery(this).html('<select class="column_search"><option value="">Все члены СРО</option>' +
                '<option value="Член СРО">Член СРО</option><option value="Исключен">Исключен</option></select>');
        } else {
            jQuery(this).html('<input type="text" class="column_search" style="width: 90%;"/>');
        }
    }).off('click');

    // Init table
    tableSelector.DataTable({
        dom: "<'row'<'length-control'l>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row d-footer'<'pagination'p><'items-info'i>>",
        responsive: true,
        language: {
            url: langFilePath,
        },
        // Remove sorting due to click on search fields
        "aoColumns": [
            {"bSortable": false},
            {"bSortable": false},
            {"bSortable": false},
            {"bSortable": false},
            {"bSortable": false}
        ],
        pageLength: parseInt(defaultTableItems),
        lengthMenu: [20, 30, 50, 100, 250, 500],
        initComplete: function () {
            const table = this;
            table
                .api()
                .columns()
                .every(function () {
                    const column = this;
                    jQuery('.column_search', column.header()).on('keyup change clear', function () {
                        if (column.visible()) {
                            column.search(this.value).draw();
                        }
                    })
                });
            jQuery('div.spinner').hide();
            tableSelector.fadeIn('normal');
        }
    });

    // Click by row to go to a detail page
    jQuery('#mainRegisterTable td').click(function() {
        let link = jQuery(this).attr('data-detail-link');
        if ('' !== link && 'undefined' !== typeof link) {
            document.location.href = link;
        }
    });
});