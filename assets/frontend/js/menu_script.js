jQuery(document).ready(function () {

    jQuery(document).on('click', 'li.wccs-click-for-menu', function(e) {
    
        e.preventDefault();
        e.stopPropagation();

        jQuery(this).find('ul.sub-menu').show();
    });

    jQuery(document).on( 'click', 'ul.sub-menu > li.wccs-click-for-menu', function () {
        var id = jQuery(this).attr('id');

        if ( id === '' || typeof id === "undefined" ) {
            var code = jQuery(this).find('a').attr('href');
            code = code.substr(1);
        } else {
            var code = id.substr(id.length - 3);
        }
        if(code){
            jQuery('<form>', {
                "id": "getInvoiceImage",
                "html": '<input type="hidden"name="wcc_switcher" value="' + code + '" />',
                "action": '',
                "method": 'POST'
            }).appendTo(document.body).submit();
        }
    });
});