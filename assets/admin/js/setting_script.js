jQuery(document).ready(function ($) {

    /**Sync Order with WooCommerce Analytics for pay in user selected Currencies**/
    jQuery('#wccs_order_sync').on('click', function (e) {
        e.preventDefault();
        var current = jQuery(this);
        current.attr('disabled', true);
        var data = {
            action: 'wccs_order_sync_process',
            wccs_nonce: variables.nonce
        };

        jQuery.post(variables.ajaxurl, data, function (response) {
            if ('success' == response.status) {
                alert(response.message);
            } else {
                alert(response.message);
            }

            current.removeAttr('disabled');
        });
    });

    jQuery('.wccs_add_single_currency').on('click', function (e) {
        e.preventDefault();
        //debugger;
        var val = $(this).siblings('.wccs_get_defined_currency').val();

        if ($(this).data('type') == 'single') {
            if (val != '' && $('input[name="wccs_cfa_code[]"][value="' + val + '"]').length <= 0) {
                $('#wccs_coupon_amount_for_currencies_wrapped').append(
                    `<p class=" form-field discount_type_field">
                        <label for="wccs_cfa_value">
                            <strong>Coupon amount (${val}): </strong>                    
                        </label>
                        <input type="hidden" name="wccs_cfa_code[]" value="${val}" />
                        <input type="text" id="wccs_cfa_value" name="wccs_cfa_value[]" Placeholder="auto" value="" />
                        <a href="#" class="ml-10 button button-secondary wccs_cfa_remove">remove</a>
                    </p>`
                );
            }
        }

        if ($(this).data('type') == 'multiple') {
            if (val != '' && $('input[name="wccs_cfa_minmax_code[]"][value="' + val + '"]').length <= 0) {
                $('#wccs_coupon_minmax_amount_for_currencies_wrapped').append(
                    `<p class=" form-field discount_type_field">
                        <input type="hidden" name="wccs_cfa_minmax_code[]" value="${val}" />
                        
                        <span class="wccs_form_control">
                            <label for="wccs_cfa_min_value">
                                <strong>Minimum spend (${val}): </strong>                    
                            </label>								
                            <input type="text" id="wccs_cfa_min_value" name="wccs_cfa_min_value[]" Placeholder="auto" value="" />
                            <a href="#" class="ml-10 button button-secondary wccs_cfa_remove">remove</a>
                        </span>

                        <span class="wccs_form_control">
                            <label for="wccs_cfa_min_value">
                                <strong>Maximum spend (${val}): </strong>                    
                            </label>
                            <input type="text" id="wccs_cfa_max_value" name="wccs_cfa_max_value[]" Placeholder="auto" value="" />
                        </span>                        
                    </p>`
                );
            }
        }

    });

    jQuery('.wccs_add_all_currencies').on('click', function (e) {
        e.preventDefault();

        var options = $('.wccs_get_defined_currency option');
        var values = $.map(options, function (option) {
            if (option.value != '')
                return option.value;
        });

        //debugger;

        if ($(this).data('type') == 'single') {

            $.each(values, function (i, val) {
                if (val != '' && $('input[name="wccs_cfa_code[]"][value="' + val + '"]').length <= 0) {
                    $('#wccs_coupon_amount_for_currencies_wrapped').append(
                        `<p class=" form-field discount_type_field">
                            <label for="wccs_cfa_value">
                                <strong>Coupon amount (${val}): </strong>                    
                            </label>
                            <input type="hidden" name="wccs_cfa_code[]" value="${val}" />
                            <input type="text" id="wccs_cfa_value" name="wccs_cfa_value[]" Placeholder="auto" value="" />
                            <a href="#" class="ml-10 button button-secondary wccs_cfa_remove">remove</a>
                        </p>`
                    );
                }
            });
        }

        if ($(this).data('type') == 'multiple') {
            $.each(values, function (i, val) {
                if (val != '' && $('input[name="wccs_cfa_minmax_code[]"][value="' + val + '"]').length <= 0) {
                    $('#wccs_coupon_minmax_amount_for_currencies_wrapped').append(
                        `<p class=" form-field discount_type_field">
                            <input type="hidden" name="wccs_cfa_minmax_code[]" value="${val}" />
                            
                            <span class="wccs_form_control">
                                <label for="wccs_cfa_min_value">
                                    <strong>Minimum spend (${val}): </strong>                    
                                </label>								
                                <input type="text" id="wccs_cfa_min_value" name="wccs_cfa_min_value[]" Placeholder="auto" value="" />
                                <a href="#" class="ml-10 button button-secondary wccs_cfa_remove">remove</a>
                            </span>

                            <span class="wccs_form_control">
                                <label for="wccs_cfa_min_value">
                                    <strong>Maximum spend (${val}): </strong>                    
                                </label>
                                <input type="text" id="wccs_cfa_max_value" name="wccs_cfa_max_value[]" Placeholder="auto" value="" />
                            </span>                            
                        </p>`
                    );
                }
            });
        }

    });

    $(document).on('click', '.wccs_cfa_remove', function (e) {
        e.preventDefault();
        $(this).parents('.discount_type_field').remove();
    });

    //payment gateway
    $(document).on('click', '.wccs-payment-gateway-td button', function (e) {
        e.preventDefault();
        var code = $(this).data('code');
        // alert(code);
        $(this).toggleClass('wccs-close wccs-open');
        $(this).siblings('div.wccs_payment_gateways_container').slideToggle(100);
    });

    // add currency
    $(document).on("change", "#wccs_add_currency", function () {
        //debugger;
        $(this).attr('disabled', 'disabled');
        var value = $(this).val();
        var label = $(this).find("option:selected").text();
        var nonce = variables.nonce;

        //prepare ajax
        var data = {
            'action': 'wccs_add_currency',
            'code': value,
            'label': label,
            'nonce': nonce
        };

        $.post(variables.ajaxurl, data, function (response) {
            var obj = JSON.parse(response);
            if (obj.status) {
                $('#wccs_currencies_list').append(obj.html);

                $('.flags').prettyDropdown({
                    classic: true,
                    width: 110,
                    height: 30,
                    customClass: 'wccs_arrow'

                });

                $('#wccs_currencies_table').show();
            } else {
                console.log('wccs error: noting to add');
            }
        });

        // remove option
        $(this).find('option[value=' + value + ']').remove();

        // clear select or return to default
        if ($(this).find('option').length > 1) {
            $(this).val('');
            $(this).attr('disabled', false);
        } else {
            $(this).hide();
        }
    });

    // remove currency
    $(document).on("click", ".wccs_remove_currency", function () {
        $(this).attr('disabled', 'disabled');
        var value = $(this).data('value');
        var label = $(this).data('label');

        // add to select
        $('#wccs_add_currency').append(new Option(label, value));

        // sort select
        var opts_list = $('#wccs_add_currency').find('option');
        opts_list.sort(function (a, b) { return $(a).val() > $(b).val() ? 1 : -1; });
        $('#wccs_add_currency').html('').append(opts_list);
        $('#wccs_add_currency').val('');

        // remove currency
        $(this).closest('tr').remove();
        if ($('#wccs_currencies_table tbody tr').length == 0) {
            $('#wccs_currencies_table').hide();
        }
    });

    // update all currencies rates ajax
    $(document).on("click", "#wccs_update_all", function () {
        var button = $(this);
        button.attr('disabled', 'disabled');
        selectedType = jQuery('.wccs-api-selection').val();
        var nonce = variables.nonce;

        //prepare ajax
        var data = {
            'action': 'wccs_update_all',
            'selectedType': selectedType,
            'nonce': nonce
        };

        $.post(variables.ajaxurl, data, function (response) {
            if (response.status) {
                var data = response.rates;
                for (var k in data) {
                    if (data.hasOwnProperty(k)) {
                        $('input[name="wccs_currencies[' + k + '][rate]"]').val(data[k]);
                    }
                }
                button.attr('disabled', false);
            } else {
                console.log('wccs error: problem on updating');
                button.attr('disabled', false);
            }
        });
    });

    // update single currency rate ajax
    $(document).on("click", ".wccs_update_rate", function () {
        var button = $(this);
        button.attr('disabled', 'disabled');
        var code = $(this).data('code');
        var nonce = variables.nonce;
        selectedType = jQuery('.wccs-api-selection').val();

        //prepare ajax
        var data = {
            'action': 'wccs_update_single_rate',
            'code': code,
            'nonce': nonce,
            'selectedType': selectedType
        };

        $.post(variables.ajaxurl, data, function (response) {
            var obj = JSON.parse(response);
            if (obj.status) {
                $('input[name="wccs_currencies[' + code + '][rate]"]').val(obj.rate);
                button.attr('disabled', false);
            } else {
                console.log('wccs error: problem on updating');
                button.attr('disabled', false);
            }
        });
    });

    $(document).on("change", "input[name='wccs_update_type']", function () {
        ShowAPISection($(this).val());
    });

    ShowAPISection($("input[name='wccs_update_type']:checked").val());

    $(document).on("change", "input[name='wccs_show_in_menu']", function () {
        ShowMenuSection();
    });

    ShowMenuSection();

    $(document).on("change", "input[name='wccs_admin_email']", function () {
        ShowEmailSection();
    });

    ShowEmailSection();

    $(document).on("change", "input[name='wccs_sticky_switcher']", function () {
        ShowStickySection();
    });

    ShowStickySection();

    $(document).on("change", "input[name='wccs_pay_by_user_currency']", function () {
        ShowFixedCouponSection();
    });

    ShowFixedCouponSection();

    $(document).on("change", "input[name='wccs_currency_by_lang']", function () {
        ShowWPMLSection();
    });

    ShowWPMLSection();

    $("#wccs_currencies_list").sortable({
        cursor: "grabbing"
    });

    var dd = $('.flags').prettyDropdown({
        classic: true,
        width: 110,
        height: 30,
        customClass: 'wccs_arrow'

    });
});


function ShowAPISection(radio) {
    if (radio == 'fixed') {
        jQuery('.wccs_api_section').hide();
    } else {
        jQuery('.wccs_api_section').show();
    }
}

function ShowMenuSection() {
    if (jQuery("input[name='wccs_show_in_menu']").is(':checked')) {
        jQuery('.wccs_menu_section').show()
    } else {
        jQuery('.wccs_menu_section').hide();
    }
}

function ShowEmailSection() {
    if (jQuery("input[name='wccs_admin_email']").is(':checked')) {
        jQuery('.wccs_email_section').show()
    } else {
        jQuery('.wccs_email_section').hide();
    }
}

function ShowStickySection() {
    if (jQuery("input[name='wccs_sticky_switcher']").is(':checked')) {
        jQuery('.wccs_sticky_section').show()
    } else {
        jQuery('.wccs_sticky_section').hide();
    }
}

function ShowFixedCouponSection() {
    if (jQuery("input[name='wccs_pay_by_user_currency']").is(':checked')) {
        jQuery('.wccs_fixed_coupon_amount_wrapper').show()
    } else {
        jQuery('.wccs_fixed_coupon_amount_wrapper').hide();
    }
}

function ShowWPMLSection() {
    if (jQuery("input[name='wccs_currency_by_lang']").is(':checked')) {
        jQuery('.wccs_wpml_lang_wrapper').show()
    } else {
        jQuery('.wccs_wpml_lang_wrapper').hide();
    }
}

jQuery(document).ready(function () {

    var currencyInput = jQuery("input[name='wwp_user_role_currency']");
    var currencyDropdown = jQuery('tr.select-currency-dropdown');
    var currencyDiv = jQuery('div.select-currency');

    function toggleCurrencyDropdown() {
        if (currencyInput.is(':checked')) {
            currencyDropdown.show();
        } else {
            currencyDropdown.hide();
        }
    }

    function toggleCurrencyDiv() {
        if (currencyInput.is(':checked')) {
            currencyDiv.show();
        } else {
            currencyDiv.hide();
        }
    }

    toggleCurrencyDropdown();
    toggleCurrencyDiv();

    currencyInput.on('change', toggleCurrencyDropdown);
    currencyInput.on('change', toggleCurrencyDiv);
});

jQuery(document).ready(function ($) {

    const savedValue = $(".wccs-api-selection").val();
    if (savedValue) {
        APISelection(savedValue);
    }

    $(document).on("change", ".wccs-api-selection", function () {
        APISelection($(this).val());
    });

    function APISelection(value) {
        const sections = ['.open_exchange_rate', '.abstract_api', '.api_layer_fixer', '.exchange_rate_api'];
        sections.forEach(section => $(section).hide());
        $(`.${value}`).show();
    }
});