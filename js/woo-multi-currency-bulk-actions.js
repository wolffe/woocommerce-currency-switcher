'use strict';

jQuery(document).ready(function () {
    function init_custom_variation() {
        jQuery('.wc-metaboxes-wrapper a.do_variation_action').bind('click', function (c) {
            jQuery(this).unbind('click');
            var do_variation_action = jQuery('select.variation_actions').val();

            jQuery('.woocommerce_variations').ajaxComplete(function (event, request, settings) {
                if (settings.data.lastIndexOf("action=woocommerce_load_variations", 0) === 0) {

                    if (do_variation_action.match(/wbs_regular_price-/i) || do_variation_action.match(/wbs_sale_price-/i)) {
                        var value = window.prompt('Enter your price');
                        if (value != null) {

                            if (do_variation_action.match(/wbs_regular_price-/i)) {
                                var currency = do_variation_action.replace(/wbs_regular_price-/i, '');
                                jQuery('.wbs-variable-regular-price-' + currency).val(value).change();
                            } else {
                                var currency = do_variation_action.replace(/wbs_sale_price-/i, '');
                                jQuery('.wbs-variable-sale-price-' + currency).val(value).change();
                            }

                        }
                    }
                    jQuery('.woocommerce_variations').unbind('ajaxComplete');
                }

            });
            init_custom_variation();
        });
    }

    init_custom_variation();
});