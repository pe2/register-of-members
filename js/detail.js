jQuery(document).ready(function () {
    jQuery('span.print-version').click(function () {
        window.print();
    });

    jQuery('span.pdf-version').click(function () {
        let name = jQuery('table.member_detail_info tr:nth-child(1) td:nth-child(2)').html() + '-' +
            jQuery('table.member_detail_info tr:nth-child(5) td:nth-child(2)').html();
        name = name.replace(/\s+/g, '_');

        /**
         * @see https://stackoverflow.com/questions/16086162/handle-file-download-from-ajax-post/23797348#23797348
         */
        jQuery.ajax({
            type: "POST",
            url: pluginPath + '/ajax/pdf-handler.php',
            data: {header: jQuery('h1').html(), table: jQuery('div.table_wrapper').html()},
            xhrFields: {
                responseType: 'blob' // to avoid binary data being mangled on charset conversion
            },
            success: function (blob, status, xhr) {
                // check for a filename
                let filename = '',
                    disposition = xhr.getResponseHeader('Content-Disposition');

                if (disposition && disposition.indexOf('attachment') !== -1) {
                    let filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/,
                        matches = filenameRegex.exec(disposition);
                    if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                }

                if (typeof window.navigator.msSaveBlob !== 'undefined') {
                    // IE workaround for "HTML7007: One or more blob URLs were revoked by closing the blob for which they were created.
                    // These URLs will no longer resolve as the data backing the URL has been freed."
                    window.navigator.msSaveBlob(blob, filename);
                } else {
                    let URL = window.URL || window.webkitURL,
                        downloadUrl = URL.createObjectURL(blob);

                    if (filename) {
                        // use HTML5 a[download] attribute to specify filename
                        let a = document.createElement("a");
                        // safari doesn't support this yet
                        if (typeof a.download === 'undefined') {
                            window.location.href = downloadUrl;
                        } else {
                            a.href = downloadUrl;
                            // a.download = filename;
                            a.download = name;
                            document.body.appendChild(a);
                            a.click();
                        }
                    } else {
                        window.location.href = downloadUrl;
                    }

                    setTimeout(function () {
                        URL.revokeObjectURL(downloadUrl);
                    }, 100); // cleanup
                }
            }
        });
    });
});