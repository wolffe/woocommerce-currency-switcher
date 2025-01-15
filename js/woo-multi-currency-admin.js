'use strict';
jQuery(document).ready(function () {
    jQuery('.vi-ui.tabular.menu .item').tab({
        history: true,
        historyType: 'hash'
    });

    /*Setup tab*/
    var tabs,
        tabEvent = false,
        initialTab = 'general',
        navSelector = '.vi-ui.menu',
        navFilter = function (el) {
            return jQuery(el).attr('href').replace(/^#/, '');
        },
        panelSelector = '.vi-ui.tab',
        panelFilter = function () {
            jQuery(panelSelector + ' a').filter(function () {
                return jQuery(navSelector + ' a[title=' + jQuery(this).attr('title') + ']').size() != 0;
            }).each(function (event) {
                jQuery(this).attr('href', '#' + $(this).attr('title').replace(/ /g, '_'));
            });
        };

    // Initializes plugin features
    jQuery.address.strict(false).wrap(true);

    if (jQuery.address.value() == '') {
        jQuery.address.history(false).value(initialTab).history(true);
    }

    // Address handler
    jQuery.address.init(function (event) {

        // Adds the ID in a lazy manner to prevent scrolling
        jQuery(panelSelector).attr('id', initialTab);

        // Enables the plugin for all the content links
        jQuery(panelSelector + ' a').address(function () {
            return navFilter(this);
        });

        panelFilter();

        // Tabs setup
        tabs = jQuery('.vi-ui.menu')
            .tab({
                history: true,
                historyType: 'hash'
            })

        // Enables the plugin for all the tabs
        jQuery(navSelector + ' a').click(function (event) {
            tabEvent = true;
            jQuery.address.value(navFilter(event.target));
            tabEvent = false;
            return false;
        });

    });


    /*Init JS input*/
    //jQuery('select.vi-ui.dropdown').dropdown();
    jQuery('.select2').select2();
    /*Select all and Remove all countries in Currency by country*/
    jQuery('.wmc-select-all-countries').on('click', function () {
        var selectedItems = [];
        var allOptions = jQuery(this).closest('tr').find('select');
        allOptions.find('option').each(function () {
            jQuery(this).attr('selected', true);
        });
        allOptions.trigger("change");
    });

    jQuery('.wmc-remove-all-countries').on('click', function () {
        if (confirm("Would you want to remove all countries?")) {
            var selectedItems = [];
            var allOptions = jQuery(this).closest('tr').find('select');
            allOptions.find('option').each(function () {
                jQuery(this).removeAttr('selected', true);
            });
            allOptions.trigger("change");
        }
    });

    // jQuery("#IncludeFieldsMulti").select2("val", selectedItems);

    /*Save Submit button*/
    jQuery('.wmc-submit').one('click', function () {
        jQuery(this).addClass('loading');
    });
    jQuery('.select2-multiple').select2({
        width: '100%' // need to override the changed default
    });
    /*Color picker*/
    jQuery('.color-picker').iris({
        change: function (event, ui) {
            jQuery(this).parent().find('.color-picker').css({ backgroundColor: ui.color.toString() });
            var ele = jQuery(this).data('ele');
            if (ele == 'highlight') {
                jQuery('#message-purchased').find('a').css({ 'color': ui.color.toString() });
            } else if (ele == 'textcolor') {
                jQuery('#message-purchased').css({ 'color': ui.color.toString() });
            } else {
                jQuery('#message-purchased').css({ backgroundColor: ui.color.toString() });
            }
        },
        hide: true,
        border: true
    }).click(function () {
        jQuery('.iris-picker').hide();
        jQuery(this).closest('td').find('.iris-picker').show();
    });

    jQuery('body').click(function () {
        jQuery('.iris-picker').hide();
    });
    jQuery('.color-picker').click(function (event) {
        event.stopPropagation();
    });

    /*Process Currency Options*/
    remove_currency();

    function insert_currency() {
        jQuery('.vi-ui.checkbox').unbind();

        jQuery('.wmc-add-currency').unbind();
        jQuery('.wmc-add-currency').on('click', function () {
            jQuery('.wmc-currency-data').last().find('select.select2').select2('destroy');
            var new_row = jQuery('.wmc-currency-data').last().clone();
            jQuery('.wmc-currency-data').last().find('select.select2').select2();
            new_row.find('input[name="woo_multi_currency_params[currency_default]"]').attr('checked', false);
            jQuery(new_row).appendTo('.wmc-currency-options tbody');
            remove_currency();
            jQuery('.wmc-currency-data').last().find('select.select2').select2().change();
        });

        jQuery('select[name="woo_multi_currency_params[currency][]"]').on('change', function () {
            var val = jQuery(this).val();
            jQuery(this).closest('tr').find('input[name="woo_multi_currency_params[currency_default]"]').val(val);
            jQuery(this).closest('tr').removeAttr('class').addClass('wmc-currency-data ' + val + '-currency');
        });
        jQuery('.wmc-currency-options tbody').sortable();
        /*Change currency default*/
        jQuery('input[name="woo_multi_currency_params[currency_default]"]').unbind('change');
        jQuery('input[name="woo_multi_currency_params[currency_default]"]').on('change', function () {
            jQuery('.wmc-currency-options').find('input[name="woo_multi_currency_params[currency_rate][]"]').removeAttr('readonly');
            jQuery(this).closest('tr').find('input[name="woo_multi_currency_params[currency_rate][]"]').val(1).attr('readonly', true);
            var original_currency = jQuery(this).val();
            var other_currencies = [];
            jQuery('.wmc-currency-options').find('input[name="woo_multi_currency_params[currency_default]"]').each(function () {
                if (original_currency != jQuery(this).val()) {
                    other_currencies.push(jQuery(this).val());
                }
            });
        });
    }

    function remove_currency() {
        jQuery('.wmc-remove-currency').unbind();
        insert_currency();
        jQuery('.wmc-remove-currency').on('click', function () {
            if (confirm("Would you want to remove this currency?")) {
                if (jQuery('.wmc-currency-options tbody tr').length > 1) {
                    var tr = jQuery(this).closest('tr').remove();
                }
            } else {

            }
        });
    }
});
