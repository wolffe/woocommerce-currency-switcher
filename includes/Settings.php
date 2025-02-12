<?php
namespace Wpdcs\WooCommerceCurrencySwitcher;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * This class defines wccs settings for the plugin.
 */
if ( ! class_exists( 'Settings' ) ) {
    class Settings {
        private static $_instance = null;
        private $default_currency = null;

        public function __construct() {
            $this->default_currency = get_woocommerce_currency();

            // add plugin setting page
            add_action( 'admin_menu', [ $this, 'wccs_custom_menu_page' ], 99 );

            // hook on saving "update_type" option
            add_action( 'update_option_wccs_update_type', [ $this, 'wccs_update_type_option_logic' ], 10, 2 );

            // hook on saving "update_rate" option
            add_action( 'update_option_wccs_update_rate', [ $this, 'wccs_update_rate_option_logic' ], 10, 2 );

            // hook on updating "woocommerce currency" option
            add_action( 'update_option_woocommerce_currency', [ $this, 'wccs_woocommerce_currency_option_logic' ], 10, 2 );

            /**
             * Add custom fields to coupon setting page admin
             */
            // Add a custom field to Admin coupon settings pages
            add_action( 'woocommerce_coupon_options', [ $this, 'wccs_add_fields_to_coupon_setting' ], 10 );
            add_action( 'woocommerce_coupon_options_usage_restriction', [ $this, 'wccs_add_fields_to_coupon_usage_restriction_setting' ], 10 );

            // Save the custom field value from Admin coupon settings pages
            add_action( 'woocommerce_coupon_options_save', [ $this, 'wccs_save_fields_to_coupon_setting' ], 10, 2 );
        }

        public function wccs_save_fields_to_coupon_setting( $post_id, $coupon ) {

            $wccs_fixed_coupon_amount  = get_multisite_or_site_option( 'wccs_fixed_coupon_amount', false );
            $wccs_pay_by_user_currency = get_multisite_or_site_option( 'wccs_pay_by_user_currency', false );

            if ( $wccs_fixed_coupon_amount && $wccs_pay_by_user_currency ) { // If fixed amount for coupon setting is enable and also shop by user currency is enable.
                // wp_die();
                if ( ! isset( $_POST['wccs_coupon_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['wccs_coupon_nonce'] ), 'wccs_coupon_nonce' ) ) {
                    wp_die( 'access denied! Nonce not verify.' );
                }

                $post = $_POST;

                if ( isset( $post['wccs_cfa_min_value'] ) && count( $post['wccs_cfa_min_value'] ) > 0 ) {
                    $coupon_minmax_price_per_currency = [];
                    foreach ( $post['wccs_cfa_min_value'] as $key => $value ) {
                        $code                                     = sanitize_text_field( $post['wccs_cfa_minmax_code'][ $key ] );
                        $coupon_fixed_price_per_currency[ $code ] = [
                            'min' => sanitize_text_field( $value ),
                            'max' => sanitize_text_field( $post['wccs_cfa_max_value'][ $key ] ),
                        ];
                    }

                    $coupon->update_meta_data( 'wccs_cfa_minmax_data', $coupon_fixed_price_per_currency );
                } else {
                    $coupon->update_meta_data( 'wccs_cfa_minmax_data', [] );
                }

                if ( isset( $post['wccs_cfa_value'] ) && count( $post['wccs_cfa_value'] ) > 0 ) {
                    $coupon_fixed_price_per_currency = [];
                    foreach ( $post['wccs_cfa_value'] as $key => $value ) {
                        $code                                     = sanitize_text_field( $post['wccs_cfa_code'][ $key ] );
                        $coupon_fixed_price_per_currency[ $code ] = sanitize_text_field( $value );
                    }

                    $coupon->update_meta_data( 'wccs_cfa_data', $coupon_fixed_price_per_currency );
                } else {
                    $coupon->update_meta_data( 'wccs_cfa_data', [] );
                }

                if ( isset( $post['product_ids'] ) ) {
                    $coupon->update_meta_data( 'product_ids', $post['product_ids'] );
                } else {
                    $coupon->update_meta_data( 'product_ids', '' );
                }

                if ( isset( $post['exclude_product_ids'] ) ) {
                    $coupon->update_meta_data( 'exclude_product_ids', $post['exclude_product_ids'] );
                } else {
                    $coupon->update_meta_data( 'exclude_product_ids', '' );
                }

                if ( isset( $post['product_categories'] ) ) {
                    $coupon->update_meta_data( 'product_categories', $post['product_categories'] );
                } else {
                    $coupon->update_meta_data( 'product_categories', '' );
                }

                if ( isset( $post['exclude_product_categories'] ) ) {
                    $coupon->update_meta_data( 'exclude_product_categories', $post['exclude_product_categories'] );
                } else {
                    $coupon->update_meta_data( 'exclude_product_categories', '' );
                }

                if ( isset( $post['customer_email'] ) ) {
                    $coupon->update_meta_data( 'customer_email', $post['customer_email'] );
                } else {
                    $coupon->update_meta_data( 'customer_email', '' );
                }

                $coupon->save();
            }
        }

        /**
         * Add custom fields to coupon usage restriction tab setting page admin
         */
        public function wccs_add_fields_to_coupon_usage_restriction_setting() {

            $wccs_fixed_coupon_amount  = get_multisite_or_site_option( 'wccs_fixed_coupon_amount', false );
            $wccs_pay_by_user_currency = get_multisite_or_site_option( 'wccs_pay_by_user_currency', false );

            if ( $wccs_fixed_coupon_amount && $wccs_pay_by_user_currency ) { // If fixed amount for coupon setting is enable and also shop by user currency is enable.

                global $post;
                $wccs_cfa_minmax_data = get_post_meta( $post->ID, 'wccs_cfa_minmax_data', true );
                $currencies           = get_multisite_or_site_option( 'wccs_currencies', [] );

                ?>
                <div class="wccs_fixed_coupon_settings">
                    <h3>WooCommerce Currency Switcher - Minimum and Maximum Spend</h3>
                    <?php
                    if ( ! empty( $currencies ) ) {
                        ?>
                        <select class="wccs_get_defined_currency">
                            <option value=""><?php echo esc_html( 'Select currency...', 'wccs' ); ?></option>
                            <?php
                            foreach ( $currencies as $currency_code => $currency_data ) {
                                ?>
                                <option value="<?php echo esc_attr( $currency_code ); ?>"><?php echo esc_attr( $currency_data['label'] ); ?></option>
                                <?php
                            }
                            ?>
                        </select>
                        <a class="button button-primary wccs_add_single_currency" data-type="multiple" id="">Add</a>
                        <a class="button button-primary wccs_add_all_currencies" data-type="multiple" id="">Add All</a>
                        <?php
                    }
                    ?>

                    <div id="wccs_coupon_minmax_amount_for_currencies_wrapped">
                        <?php
                        if ( ! empty( $wccs_cfa_minmax_data ) && count( $wccs_cfa_minmax_data ) > 0 ) {
                            foreach ( $wccs_cfa_minmax_data as $code => $value ) {
                                ?>
                                <p class=" form-field discount_type_field">
                                    <input type="hidden" name="wccs_cfa_minmax_code[]" value="<?php echo esc_attr( $code ); ?>">

                                    <span class="wccs_form_control">
                                        <label for="wccs_cfa_min_value">
                                            <strong>Minimum spend (<?php echo esc_attr( $code ); ?>): </strong>                    
                                        </label>                                
                                        <input type="text" id="wccs_cfa_min_value" name="wccs_cfa_min_value[]" Placeholder="auto" value="<?php echo esc_attr( $value['min'] ); ?>">
                                        <a href="#" class="ml-10 button button-secondary wccs_cfa_remove">remove</a>
                                    </span>

                                    <span class="wccs_form_control">
                                        <label for="wccs_cfa_min_value">
                                            <strong>Maximum spend (<?php echo esc_attr( $code ); ?>): </strong>                    
                                        </label>
                                        <input type="text" id="wccs_cfa_max_value" name="wccs_cfa_max_value[]" Placeholder="auto" value="<?php echo esc_attr( $value['max'] ); ?>">
                                    </span>                             
                                </p>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
        }

        /**
         * Add custom fields to coupon setting page admin
         */
        public function wccs_add_fields_to_coupon_setting() {

            $wccs_fixed_coupon_amount  = get_multisite_or_site_option( 'wccs_fixed_coupon_amount', false );
            $wccs_pay_by_user_currency = get_multisite_or_site_option( 'wccs_pay_by_user_currency', false );

            if ( $wccs_fixed_coupon_amount && $wccs_pay_by_user_currency ) { // If fixed amount for coupon setting is enable and also shop by user currency is enable.

                global $post;
                $wccs_cfa_data = get_post_meta( $post->ID, 'wccs_cfa_data', true );
                $currencies    = get_multisite_or_site_option( 'wccs_currencies', [] );

                ?>
                <div class="wccs_fixed_coupon_settings">
                    <h3>WooCommerce Currency Switcher - Fixed Amount</h3>
                    <input type="hidden" name="wccs_coupon_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wccs_coupon_nonce' ) ); ?>">
                    <?php
                    if ( ! empty( $currencies ) ) {
                        ?>
                        <select class="wccs_get_defined_currency">
                            <option value="">Select currency...</option>
                            <?php
                            foreach ( $currencies as $currency_code => $currency_data ) {
                                ?>
                                <option value="<?php echo esc_attr( $currency_code ); ?>"><?php echo esc_attr( $currency_data['label'] ); ?></option>
                                <?php
                            }
                            ?>
                        </select>
                        <a class="button button-primary wccs_add_single_currency" data-type="single" id="">Add</a>
                        <a class="button button-primary wccs_add_all_currencies" data-type="single" id="">Add All</a>
                        <?php
                    }
                    ?>

                    <div id="wccs_coupon_amount_for_currencies_wrapped">
                        <?php
                        if ( ! empty( $wccs_cfa_data ) && count( $wccs_cfa_data ) > 0 ) {
                            foreach ( $wccs_cfa_data as $code => $value ) {
                                ?>
                                <p class=" form-field discount_type_field">
                                    <label for="wccs_cfa_value">
                                        <strong>Coupon amount (<?php echo esc_attr( $code ); ?>): </strong>                    
                                    </label>
                                    <input type="hidden" name="wccs_cfa_code[]" value="<?php echo esc_attr( $code ); ?>">
                                    <input type="text" id="wccs_cfa_value" name="wccs_cfa_value[]" Placeholder="auto" value="<?php echo esc_attr( $value ); ?>">
                                    <a href="#" class="ml-10 button button-secondary wccs_cfa_remove">remove</a>
                                </p>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
        }

        public function wccs_custom_menu_page() {
            $cap = apply_filters( 'wc_curency_setting_cap', 'manage_options' );

            add_submenu_page( 'woocommerce', 'Currency Switcher Settings', 'Currency Switcher', $cap, 'wccs-settings', [ $this, 'wccs_settings_page_callback' ] );

            // call register settings function
            add_action( 'admin_init', [ $this, 'wccs_register_settings' ] );
        }

        public function wccs_register_settings() {
            if ( isset( $_POST['option_page'] ) ) {
                if ( ! empty( $_POST['option_page'] && wp_verify_nonce( sanitize_text_field( $_POST['custom_nonce'] ), 'custom_nonce' ) ) ) {
                    $option_page = sanitize_text_field( $_POST['option_page'] );

                    switch ( $option_page ) {
                        case 'wccs-settings-general':
                            register_setting( 'wccs-settings-general', 'wccs_api_selection' );
                            register_setting( 'wccs-settings-general', 'wccs_update_type' );
                            register_setting( 'wccs-settings-general', 'wccs_currency_display' );
                            register_setting( 'wccs-settings-general', 'wccs_oer_api_key' );
                            register_setting( 'wccs-settings-general', 'wccs_aer_api_key' );
                            register_setting( 'wccs-settings-general', 'wccs_alf_api_key' );
                            register_setting( 'wccs-settings-general', 'wccs_era_api_key' );
                            register_setting( 'wccs-settings-general', 'wccs_ipapi_key' );
                            register_setting( 'wccs-settings-general', 'wccs_update_rate' );
                            register_setting( 'wccs-settings-general', 'wccs_admin_email' );
                            register_setting( 'wccs-settings-general', 'wccs_email' );
                            register_setting( 'wccs-settings-general', 'wccs_currencies' );
                            register_setting( 'wccs-settings-general', 'wccs_show_flag' );
                            register_setting( 'wccs-settings-general', 'wccs_currency_storage' );
                            register_setting( 'wccs-settings-general', 'wccs_show_currency' );
                            register_setting( 'wccs-settings-general', 'wccs_show_in_menu' );
                            register_setting( 'wccs-settings-general', 'wccs_switcher_menu' );
                            register_setting( 'wccs-settings-general', 'wccs_shortcode_style' );
                            register_setting( 'wccs-settings-general', 'wccs_sticky_switcher' );
                            register_setting( 'wccs-settings-general', 'wccs_sticky_position' );
                            register_setting( 'wccs-settings-general', 'wccs_default_currency_flag' );
                            register_setting( 'wccs-settings-general', 'wccs_pay_by_user_currency' );
                            register_setting( 'wccs-settings-general', 'wccs_fixed_coupon_amount' );
                            register_setting( 'wccs-settings-general', 'wccs_currency_by_lang' );
                            register_setting( 'wccs-settings-general', 'wccs_lang' );
                            break;
                    }
                }
            }
        }

        public function wccs_settings_page_callback() {
            $currencies = get_multisite_or_site_option( 'wccs_currencies', [] );

            if ( ! empty( $currencies ) ) {
                $available = wccs_get_available_currencies( array_keys( $currencies ) );
            }

            settings_errors();

            $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'wccs-settings-general';
            ?>
            <div class="wrap">
                <h1><?php esc_html_e( 'WooCommerce Currency Switcher Settings', 'wccs' ); ?></h1>

                <div class="nav-tab-wrapper">
                    <a href="?page=wccs-settings&tab=wccs-settings-general" class="nav-tab <?php echo ( 'wccs-settings-general' == $active_tab ) ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'General', 'wccs' ); ?></a>
                </div>

                <form method="post" action="options.php">
                    <input type="hidden" id="custom_nonce" name="custom_nonce" value="<?php echo esc_html( wp_create_nonce( 'custom_nonce' ) ); ?>">

                    <?php
                    if ( 'wccs-settings-general' == $active_tab ) {
                        settings_fields( 'wccs-settings-general' );
                        ?>

                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row"><?php esc_html_e( 'Currencies', 'wccs' ); ?></th>
                                <td>
                                    <?php
                                    if ( isset( $available ) && count( $available ) ) {
                                        asort( $available );
                                        ?>
                                    <select id="wccs_add_currency" class="test1">
                                        <option value=""><?php esc_html_e( 'Add currency...', 'wccs' ); ?></option>
                                        <?php foreach ( $available as $code => $label ) { ?>
                                        <option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $label ); ?></option>
                                        <?php } ?>
                                    </select>
                                        <?php
                                    } else {
                                        $currencies = get_woocommerce_currencies();
                                        asort( $currencies );
                                        ?>
                                        
                                        <select id="wccs_add_currency" class="test2">
                                            <option value=""><?php esc_html_e( 'Add currency...', 'wccs' ); ?></option>
                                            
                                            <?php foreach ( $currencies as $code => $label ) { ?>
                                            <option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $label ); ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php
                                    }

                                    $wccs_update_type = get_multisite_or_site_option( 'wccs_update_type', 'fixed' );

                                    if ( 'api' == $wccs_update_type ) {
                                        ?>
                                    <button type="button" id="wccs_update_all" class="button button-update"><i class="dashicons dashicons-update"></i> <?php esc_html_e( 'Update All', 'wccs' ); ?></button>
                                    <?php } ?>

                                    <table class="widefat" id="wccs_currencies_table"<?php if ( empty( $currencies ) || 0 == count( $currencies ) ) : ?> 
                                    style="display: none;" 
                                    <?php endif; ?>>
                                        <thead>
                                            <tr>
                                                <th><?php esc_html_e( 'Code', 'wccs' ); ?></th>
                                                <th><?php esc_html_e( 'Label', 'wccs' ); ?></th>
                                                <th><?php esc_html_e( 'Rate', 'wccs' ); ?></th>
                                                <th><?php esc_html_e( 'Price Format', 'wccs' ); ?></th>
                                                <th><?php esc_html_e( 'Symbol Prefix', 'wccs' ); ?></th>
                                                <th><?php esc_html_e( 'Decimals', 'wccs' ); ?></th>
                                                <th><?php esc_html_e( 'Price Rounding', 'wccs' ); ?></th>
                                                <th><?php esc_html_e( 'Price Charming', 'wccs' ); ?></th>
                                                <th><?php esc_html_e( 'Flag', 'wccs' ); ?></th>
                                                <th style="padding: 15px 4px"><?php esc_html_e( 'Payment Gateways', 'wccs' ); ?></th>
                                                <th><?php esc_html_e( 'Actions', 'wccs' ); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody id="wccs_currencies_list">
                                        <?php

                                        $html = '';

                                        if ( ! empty( $currencies ) && ! empty( $available ) && count( $currencies ) > 0 ) {

                                            foreach ( (array) $currencies as $code => $info ) {

                                                $symbol           = get_woocommerce_currency_symbol( $code );
                                                $flags            = wccs_get_all_flags();
                                                $wccs_update_type = get_multisite_or_site_option( 'wccs_update_type', 'fixed' );

                                                $info['label']         = isset( $info['label'] ) ? $info['label'] : '';
                                                $required              = 'api' == $wccs_update_type ? 'readonly' : 'required';
                                                $info['symbol_prefix'] = isset( $info['symbol_prefix'] ) ? $info['symbol_prefix'] : '';
                                                $rounding              = isset( $info['rounding'] ) ? $info['rounding'] : '0';
                                                $charming              = isset( $info['charming'] ) ? $info['charming'] : '0';
                                                $currency_countries    = get_currency_countries( $code );
                                                $avl_payment_gateways  = get_all_active_payment_gateways();
                                                ?>
                                                <tr>

                                                <td><?php echo esc_attr( $code ); ?></td>
                                                <td><input type="text" name="wccs_currencies[<?php echo esc_attr( $code ); ?>][label]" value="<?php echo esc_attr( $info['label'] ); ?>" required></td>
                                                <?php $info['rate'] = isset( $info['rate'] ) ? $info['rate'] : ''; ?>
                                                <td><input class="wccs_w_100" type="number" min="0" step="any" name="wccs_currencies[<?php echo esc_attr( $code ); ?>][rate]" value="<?php echo esc_attr( $info['rate'] ); ?>" <?php echo esc_attr( $required ); ?>></td>

                                                <td><select name="wccs_currencies[<?php echo esc_attr( $code ); ?>][format]" class="wccs_w_150">
                                                <option value="left" <?php selected( $info['format'], 'left' ); ?>><?php echo esc_html__( 'Left', 'wccs' ); ?></option>
                                                <option value="right" <?php selected( $info['format'], 'right' ); ?>><?php echo esc_html__( 'Right', 'wccs' ); ?></option>
                                                <option value="left_space" <?php selected( $info['format'], 'left_space' ); ?>><?php echo esc_html__( 'Left with space', 'wccs' ); ?></option>
                                                <option value="right_space" <?php selected( $info['format'], 'right_space' ); ?>><?php echo esc_html__( 'Right with space', 'wccs' ); ?></option>
                                                </select></td>

                                                <td>
                                                <input class="wccs_w_50" maxlength="4" type="text" name="wccs_currencies[<?php echo esc_attr( $code ); ?>][symbol_prefix]" value="<?php echo esc_attr( $info['symbol_prefix'] ); ?>">
                                                </td>

                                                <td>
                                                    <select name="wccs_currencies[<?php echo esc_attr( $code ); ?>][decimals]" class="wccs_w_50">
                                                        <option value="0" <?php selected( $info['decimals'], '0' ); ?>><?php echo esc_html__( '0', 'wccs' ); ?></option>
                                                        <option value="1" <?php selected( $info['decimals'], '1' ); ?>><?php echo esc_html__( '1', 'wccs' ); ?></option>
                                                        <option value="2" <?php selected( $info['decimals'], '2' ); ?>><?php echo esc_html__( '2', 'wccs' ); ?></option>
                                                        <option value="3" <?php selected( $info['decimals'], '3' ); ?>><?php echo esc_html__( '3', 'wccs' ); ?></option>
                                                        <option value="4" <?php selected( $info['decimals'], '4' ); ?>><?php echo esc_html__( '4', 'wccs' ); ?></option>
                                                        <option value="5" <?php selected( $info['decimals'], '5' ); ?>><?php echo esc_html__( '5', 'wccs' ); ?></option>
                                                        <option value="6" <?php selected( $info['decimals'], '6' ); ?>><?php echo esc_html__( '6', 'wccs' ); ?></option>
                                                        <option value="7" <?php selected( $info['decimals'], '7' ); ?>><?php echo esc_html__( '7', 'wccs' ); ?></option>
                                                        <option value="8" <?php selected( $info['decimals'], '8' ); ?>><?php echo esc_html__( '8', 'wccs' ); ?></option>
                                                    </select>
                                                </td>

                                                <td>
                                                    <select name="wccs_currencies[<?php echo esc_attr( $code ); ?>][rounding]">
                                                        <option value="0" <?php selected( $rounding, '0' ); ?>><?php echo esc_html__( 'none', 'wccs' ); ?></option>
                                                        <option value="0.25" <?php selected( $rounding, '0.25' ); ?>><?php echo esc_html__( '0.25', 'wccs' ); ?></option>
                                                        <option value="0.5" <?php selected( $rounding, '0.5' ); ?>><?php echo esc_html__( '0.5', 'wccs' ); ?></option>
                                                        <option value="1" <?php selected( $rounding, '1' ); ?>><?php echo esc_html__( '1', 'wccs' ); ?></option>
                                                        <option value="5" <?php selected( $rounding, '5' ); ?>><?php echo esc_html__( '5', 'wccs' ); ?></option>
                                                        <option value="10" <?php selected( $rounding, '10' ); ?>><?php echo esc_html__( '10', 'wccs' ); ?></option>
                                                    </select>
                                                </td>
                                                
                                                <td>
                                                    <select name="wccs_currencies[<?php echo esc_attr( $code ); ?>][charming]">
                                                        <option value="0" <?php selected( $charming, '0' ); ?>><?php echo esc_html__( 'none', 'wccs' ); ?></option>
                                                        <option value="-0.01" <?php selected( $charming, '-0.01' ); ?>><?php echo esc_html__( '-0.01', 'wccs' ); ?></option>
                                                        <option value="-0.05" <?php selected( $charming, '-0.05' ); ?>><?php echo esc_html__( '-0.05', 'wccs' ); ?></option>
                                                        <option value="-0.10" <?php selected( $charming, '-0.10' ); ?>><?php echo esc_html__( '-0.10', 'wccs' ); ?></option>
                                                    </select>
                                                </td>

                                                <td>
                                                    <select class="flags" name="wccs_currencies[<?php echo esc_attr( $code ); ?>][flag]">
                                                        <option value=""><?php echo esc_html__( 'Choose Flag', 'wccs' ); ?></option>
                                                        <?php
                                                        foreach ( (array) $flags as $country => $flag ) {

                                                            foreach ( (array) $currency_countries as $value ) {
                                                                if ( $country == $value ) {
                                                                    if ( count( $currency_countries ) == 1 ) {
                                                                        $selected = 'selected="selected"';
                                                                    } else {
                                                                        $selected = '';
                                                                    }
                                                                    if ( isset( $info['flag'] ) && strtolower( $country ) == $info['flag'] ) {
                                                                        $selected = 'selected="selected"';
                                                                    }
                                                                    ?>
                                                                    <option value="<?php echo esc_attr( strtolower( $country ) ); ?>" <?php echo esc_attr( $selected ); ?> data-prefix="<span class='wcc-flag flag-icon flag-icon-<?php echo esc_attr( strtolower( $country ) ); ?>'></span>">
                                                                        (<?php echo esc_attr( $country ); ?>)
                                                                    </option>
                                                                        <?php
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </td>

                                                <td class="wccs-payment-gateway-td">
                                                    <?php
                                                    if ( isset( $avl_payment_gateways ) && ! empty( $avl_payment_gateways ) ) {
                                                        ?>
                                                        <button data-code="<?php echo esc_attr( $code ); ?>" class="button button-secondary wccs-close"><?php echo esc_html__( 'Hide Gateway', 'wccs' ); ?></button>
                                                        <div class="wccs_payment_gateways_container">
                                                            <ul>
                                                                <?php
                                                                foreach ( $avl_payment_gateways as $payment ) {

                                                                    $checked = ( isset( $info['payment_gateways'] ) && in_array( $payment['id'], $info['payment_gateways'] ) ) ? 'checked="true"' : '';
                                                                    ?>
                                                            <li> <label for="<?php echo esc_attr( $code ) . '_' . esc_attr( $payment['id'] ); ?>"> <input type="checkbox" id="<?php echo esc_attr( $code ) . '_' . esc_attr( $payment['id'] ); ?>" name="wccs_currencies[<?php echo esc_attr( $code ); ?>][payment_gateways][]" value="<?php echo esc_attr( $payment['id'] ); ?>" <?php echo esc_attr( $checked ); ?>></label><?php echo esc_attr( $payment['title'] ); ?> </label></li>
                                                                    <?php
                                                                }
                                                                ?>
                                                        </ul></div>
                                                        <?php
                                                    } else {
                                                        echo esc_html__( 'No payment Gateway is enabled', 'wccs' );
                                                    }
                                                    ?>
                                                </td>

                                                <td>
                                                    <div class="wccs_actions">
                                                        <input type="hidden" name="wccs_currencies[<?php echo esc_attr( $code ); ?>][symbol]" value="<?php echo esc_attr( $symbol ); ?>">
                                                        <?php

                                                        $wccs_update_type = get_multisite_or_site_option( 'wccs_update_type', 'fixed' );
                                                        if ( 'api' == $wccs_update_type ) {
                                                            ?>
                                                            <a href="javascript:void(0);" title="<?php echo esc_html__( 'Update rate', 'wccs' ); ?>" class="wccs_update_rate" data-code="<?php echo esc_attr( $code ); ?>"><i class="dashicons dashicons-update"></i></a>
                                                            <?php
                                                        }
                                                        ?>
                                                        <span title="<?php echo esc_html__( 'Sort', 'wccs' ); ?>" style="cursor:grab;"><i class="dashicons dashicons-move"></i></span>
                                                        <a href="javascript:void(0);" title="<?php echo esc_html__( 'Remove', 'wccs' ); ?>" class="wccs_remove_currency" data-value="<?php echo esc_attr( $code ); ?>" data-label="<?php echo esc_attr( $info['label'] ); ?>"><i class="dashicons dashicons-trash"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                                <?php
                                            }
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <?php
                            $wccs_currency_display = get_multisite_or_site_option( 'wccs_currency_display', 'symbol' );
                            ?>
                            <tr valign="top">
                                <th scope="row"><?php esc_html_e( 'Currency Display Type', 'wccs' ); ?></th>
                                <td>
                                    <label id="wccs_currency_display_for_symbol"><input id="wccs_currency_display_for_symbol" type="radio" name="wccs_currency_display" value="symbol" <?php if ( 'symbol' == $wccs_currency_display ) { ?> 
                                    checked 
                                    <?php } ?>> <?php esc_html_e( 'Symbol', 'wccs' ); ?></label>&nbsp;&nbsp;
                                    <label for="wccs_currency_display_for_iso-code" ><input id="wccs_currency_display_for_iso-code" type="radio" name="wccs_currency_display" value="iso-code" <?php if ( 'iso-code' == $wccs_currency_display ) { ?> 
                                    checked 
                                    <?php } ?>> <?php esc_html_e( 'ISO Code', 'wccs' ); ?></label>
                                    
                                    <p class="description"><?php esc_html_e( 'Choose whether to show currency symbol or currency code on shop. Default is symbol.', 'wccs' ); ?></p>
                                </td>
                            </tr>
                            <?php
                                $wccs_update_type = get_multisite_or_site_option( 'wccs_update_type', 'fixed' );
                            ?>
                            <tr valign="top">
                                <th scope="row"><?php esc_html_e( 'Exchange Rates Type', 'wccs' ); ?></th>
                                <td>
                                    <input type="radio" name="wccs_update_type" value="fixed"<?php if ( 'fixed' == $wccs_update_type ) { ?> 
                                    checked 
                                    <?php } ?>> <?php esc_html_e( 'Fixed', 'wccs' ); ?>
                                    <input type="radio" name="wccs_update_type" value="api"<?php if ( 'api' == $wccs_update_type ) { ?> 
                                    checked 
                                    <?php } ?>> <?php esc_html_e( 'API', 'wccs' ); ?>
                                    
                                    <p class="description"><?php esc_html_e( 'Choose how exchange rates will be added either manually (fixed) or automatically using API. Default is manually.', 'wccs' ); ?></p>
                                </td>
                            </tr>

                            <tr valign="top" class="wccs_api_section">
                                <th scope="row"><?php esc_html_e( 'Select Exchange Rate Provider', 'wccs' ); ?></th>
                                <td>  
                                    <select name="wccs_api_selection" class="wccs-api-selection">
                                        <option value="open_exchange_rate" <?php selected( get_multisite_or_site_option( 'wccs_api_selection', false ), 'open_exchange_rate' ); ?>><?php esc_html_e( 'Open Exchange Rate', 'wccs' ); ?></option>
                                        <option value="abstract_api" <?php selected( get_multisite_or_site_option( 'wccs_api_selection', false ), 'abstract_api' ); ?>><?php esc_html_e( 'AbstractApi Exchange Rate API', 'wccs' ); ?></option>
                                        <option value="api_layer_fixer" <?php selected( get_multisite_or_site_option( 'wccs_api_selection', false ), 'api_layer_fixer' ); ?>><?php esc_html_e( 'API Layer Fixer', 'wccs' ); ?></option>
                                        <option value="exchange_rate_api" <?php selected( get_multisite_or_site_option( 'wccs_api_selection', false ), 'exchange_rate_api' ); ?>><?php esc_html_e( 'Exchange Rate API', 'wccs' ); ?></option>
                                    </select>
                                    <p class="description"><?php esc_html_e( 'Select an exchange rate service provider.', 'wccs' ); ?></p>
                                </td>
                            </tr>

                            <tr valign="top" class="wccs_api_section">
                                <th scope="row"><?php esc_html_e( 'API key', 'wccs' ); ?></th>
                                <td class="open_exchange_rate" style="display: none">
                                    <input type="text" name="wccs_oer_api_key" value="<?php echo esc_attr( get_multisite_or_site_option( 'wccs_oer_api_key', false ) ); ?>" class="regular-text">
                                    <p class="description"><?php esc_html_e( 'Add Open Exchange Rates API key to be used to get exchange rate data (it is required). You can get from here:', 'wccs' ); ?> <a href="https://openexchangerates.org/signup" target="_blank"><?php esc_html_e( 'Get API Key', 'wccs' ); ?></a><br><?php esc_html_e( 'For API limitations', 'wccs' ); ?> <a href="https://openexchangerates.org/signup" target="_blank"><?php esc_html_e( 'Click here', 'wccs' ); ?></a></p>
                                </td>

                                <td class="abstract_api" style="display: none">
                                    <input type="text" name="wccs_aer_api_key" value="<?php echo esc_attr( get_multisite_or_site_option( 'wccs_aer_api_key', false ) ); ?>" class="regular-text">
                                    <p class="description"><?php esc_html_e( 'Add AbstractApi Exchange Rates API key to be used to get exchange rate data (it is required). You can get from here:', 'wccs' ); ?> <a href="https://www.abstractapi.com/api/exchange-rate-api" target="_blank"><?php esc_html_e( 'Get API Key', 'wccs' ); ?></a><br><?php esc_html_e( 'For API limitations', 'wccs' ); ?> <a href="https://www.abstractapi.com/api/exchange-rate-api" target="_blank"><?php esc_html_e( 'Click here', 'wccs' ); ?></a></p>
                                </td>

                                <td class="api_layer_fixer" style="display: none">
                                    <input type="text" name="wccs_alf_api_key" value="<?php echo esc_attr( get_multisite_or_site_option( 'wccs_alf_api_key', false ) ); ?>" class="regular-text">
                                    <p class="description"><?php esc_html_e( 'Add API Layer Fixer Rates API key to be used to get exchange rate data (it is required). You can get from here:', 'wccs' ); ?> <a href="https://apilayer.com/marketplace/fixer-api" target="_blank"><?php esc_html_e( 'Get API Key', 'wccs' ); ?></a><br><?php esc_html_e( 'For API limitations', 'wccs' ); ?> <a href="https://apilayer.com/marketplace/fixer-api" target="_blank"><?php esc_html_e( 'Click here', 'wccs' ); ?></a></p>
                                </td>

                                <td class="exchange_rate_api" style="display: none">
                                    <input type="text" name="wccs_era_api_key" value="<?php echo esc_attr( get_multisite_or_site_option( 'wccs_era_api_key', false ) ); ?>" class="regular-text">
                                    <p class="description"><?php esc_html_e( 'Add Exchange Rates API key to be used to get exchange rate data (it is required). You can get from here:', 'wccs' ); ?> <a href="https://www.exchangerate-api.com/" target="_blank"><?php esc_html_e( 'Get API Key', 'wccs' ); ?></a><br><?php esc_html_e( 'For API limitations', 'wccs' ); ?> <a href="https://www.exchangerate-api.com/" target="_blank"><?php esc_html_e( 'Click here', 'wccs' ); ?></a></p>
                                </td>
                            </tr>
                            
                            <tr valign="top" class="wccs_api_section">
                                <th scope="row"><?php esc_html_e( 'Update Rate', 'wccs' ); ?></th>
                                <td>
                                    <select name="wccs_update_rate">
                                        <option value="hourly"<?php if ( 'hourly' == get_multisite_or_site_option( 'wccs_update_rate', 'hourly' ) ) { ?> 
                                        selected <?php } ?>><?php esc_html_e( 'Hourly', 'wccs' ); ?></option>
                                        <option value="twicedaily"<?php if ( 'twicedaily' == get_multisite_or_site_option( 'wccs_update_rate', 'hourly' ) ) { ?> 
                                        selected <?php } ?>><?php esc_html_e( 'Twice Daily', 'wccs' ); ?></option>
                                        <option value="daily"<?php if ( 'daily' == get_multisite_or_site_option( 'wccs_update_rate', 'hourly' ) ) { ?> 
                                        selected <?php } ?>><?php esc_html_e( 'Daily', 'wccs' ); ?></option>
                                        <option value="weekly"<?php if ( 'weekly' == get_multisite_or_site_option( 'wccs_update_rate', 'hourly' ) ) { ?> 
                                        selected <?php } ?>><?php esc_html_e( 'Weekly', 'wccs' ); ?></option>
                                    </select>
                                    <p class="description"><?php esc_html_e( 'Choose how often the exchange rates for currencies will be updated by API.', 'wccs' ); ?></p>
                                </td>
                            </tr>

                            <tr valign="top" class="wccs_api_section">
                                <th scope="row"><?php esc_html_e( 'Send Email', 'wccs' ); ?></th>
                                <td>
                                    <label for="wccs_admin_email">
                                        <input type="checkbox" id="wccs_admin_email" name="wccs_admin_email" value="1" <?php if ( get_multisite_or_site_option( 'wccs_admin_email', 0 ) ) { ?> 
                                        checked <?php } ?>>
                                        <?php esc_html_e( 'Send a notification email', 'wccs' ); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e( 'Check this if you want an email to be sent each time a currency rate changes. Default is unchecked.', 'wccs' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr valign="top" class="wccs_api_section wccs_email_section">
                                <th scope="row"><?php esc_html_e( 'Email', 'wccs' ); ?></th>
                                <td>
                                    <input type="email" name="wccs_email" value="<?php if ( get_multisite_or_site_option( 'wccs_email', false ) ) { ?>
                                    <?php echo esc_attr( get_multisite_or_site_option( 'wccs_email', false ) ); } ?>">
                                    <p class="description"><?php esc_html_e( 'Add the email that will receive the updated rates. If left empty, the email will be sent to admin email. (Note: if an email address is added, only it will receive the email.)', 'wccs' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr valign="top">
                                <th scope="row"><?php esc_html_e( 'Rate Storage', 'wccs' ); ?></th>
                                <td>
                                    <select name="wccs_currency_storage">
                                        <option value="transient"<?php if ( get_multisite_or_site_option( 'wccs_currency_storage', 'transient' ) == 'transient' ) { ?> 
                                        selected<?php } ?>><?php esc_html_e( 'Transient', 'wccs' ); ?></option>
                                        <option value="session"<?php if ( get_multisite_or_site_option( 'wccs_currency_storage', 'transient' ) == 'session' ) { ?> 
                                        selected<?php } ?>><?php esc_html_e( 'Session', 'wccs' ); ?></option>
                                    </select>
                                    <p class="description"><?php esc_html_e( 'Choose the way you want to cache rates. Default is transient.', 'wccs' ); ?></p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><?php printf( esc_html( 'Default Currency Flag (%s)', 'wccs' ), esc_attr( $this->default_currency ) ); ?></th>
                                <td>
                                    <?php
                                        $currency_countries = get_currency_countries( $this->default_currency );
                                        $flags              = wccs_get_all_flags();
                                    ?>
                                    <select class="flags" name="wccs_default_currency_flag" id="wccs_default_currency_flag">
                                        <option value=""><?php esc_html_e( 'Choose Flag', 'wccs' ); ?></option>
                                        <?php

                                        foreach ( $flags as $country => $flag ) {
                                            foreach ( $currency_countries as $value ) {
                                                $single_option = '';
                                                if ( $country == $value ) {
                                                    if ( 1 == count( $currency_countries ) || strtolower( $country ) === get_multisite_or_site_option( 'wccs_default_currency_flag', true ) ) {
                                                        $selected = 'selected="selected"';
                                                    } else {
                                                        $selected = '';
                                                    }

                                                    $lower_case_country = strtolower( $country );
                                                    ?>

                                                    <option 
                                                        value="<?php echo esc_attr( $lower_case_country ); ?>"
                                                        <?php echo esc_attr( $selected ); ?>
                                                        data-prefix="<span class='wcc-flag flag-icon flag-icon-<?php echo esc_attr( $lower_case_country ); ?>'></span>">
                                                        <?php echo esc_attr( $country ); ?> <?php echo wccs_country_to_emoji( $lower_case_country ); ?>
                                                    </option>
                                                    <?php
                                                }
                                            }
                                        }
                                        ?>
                                    </select>                                
                                    <p class="description"><?php esc_html_e( 'Set the flag for your default currency.', 'wccs' ); ?></p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><?php esc_html_e( 'Flag', 'wccs' ); ?></th>
                                <td>
                                    <label for="wccs_show_flag">
                                        <input type="checkbox" id="wccs_show_flag" name="wccs_show_flag" value="1" <?php if ( get_multisite_or_site_option( 'wccs_show_flag', 1 ) ) { ?> 
                                        checked <?php } ?>>
                                        <?php esc_html_e( 'Show country flag', 'wccs' ); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e( 'Check this if you want the switcher to have country flag. Default is checked.', 'wccs' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr valign="top">
                                <th scope="row"><?php esc_html_e( 'Shop Currency', 'wccs' ); ?></th>
                                <td>
                                    <label for="wccs_pay_by_user_currency">
                                        <input type="checkbox" id="wccs_pay_by_user_currency" name="wccs_pay_by_user_currency" value="1" <?php if ( get_multisite_or_site_option( 'wccs_pay_by_user_currency', false ) ) { ?> 
                                        checked <?php } ?>>
                                        <?php esc_html_e( 'Pay in user selected currency', 'wccs' ); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e( 'Check this option to let user pay in their selected currency. Default is unchecked.', 'wccs' ); ?></p>
                                </td>
                            </tr>

                            <tr valign="top" class="wccs_fixed_coupon_amount_wrapper" style="display:none">
                                <th scope="row"><?php esc_html_e( 'Fixed amount for coupon', 'wccs' ); ?></th>
                                <td>
                                    <label for="wccs_fixed_coupon_amount">
                                        <input type="checkbox" id="wccs_fixed_coupon_amount" name="wccs_fixed_coupon_amount" value="1" <?php if ( get_multisite_or_site_option( 'wccs_fixed_coupon_amount', 0 ) ) { ?>
                                        checked<?php } ?>>
                                        <?php esc_html_e( 'Enable to set fixed amount for coupon against specific currency', 'wccs' ); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e( 'Pay in user selected currency option should be enabled.', 'wccs' ); ?></p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><?php esc_html_e( 'Currency Symbol', 'wccs' ); ?></th>
                                <td>
                                    <label for="wccs_show_currency">
                                        <input type="checkbox" id="wccs_show_currency" name="wccs_show_currency" value="1" <?php if ( get_multisite_or_site_option( 'wccs_show_currency', 1 ) ) { ?>
                                        checked<?php } ?>>
                                        <?php esc_html_e( 'Show currency symbol', 'wccs' ); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e( 'Check this if you want the switcher to have currency symbol. Default is checked.', 'wccs' ); ?></p>
                                </td>
                            </tr>

                            <?php
                            if ( function_exists( 'icl_get_languages' ) ) {
                                ?>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Currency by Language', 'wccs' ); ?></th>
                                    <td>
                                        <label for="wccs_currency_by_lang">
                                            <input type="checkbox" id="wccs_currency_by_lang" name="wccs_currency_by_lang" value="1" <?php if ( get_multisite_or_site_option( 'wccs_currency_by_lang', false ) ) { ?>
                                            checked<?php } ?>>
                                            <?php esc_html_e( 'Enable to change currency according to the users language.', 'wccs' ); ?>
                                        </label>
                                        <p class="description"><?php esc_html_e( 'Enable to change currency by WPML language.', 'wccs' ); ?></p>
                                    </td>
                                </tr>
                                
                                <tr valign="top" class="wccs_wpml_lang_wrapper" style="display:none;">
                                    <th scope="row"><?php esc_html_e( 'Languages', 'wccs' ); ?></th>
                                    <td>
                                    <?php
                                    if ( count( icl_get_languages() ) > 0 ) {
                                        ?>
                                        <ul>
                                        <?php
                                        $wccs_lang = get_multisite_or_site_option( 'wccs_lang', false );
                                        foreach ( icl_get_languages() as $lang => $details ) {
                                            ?>
                                            <li>
                                                <label style="min-width: 150px; display:inline-block">Set currency for <?php echo esc_attr( $details['translated_name'] ); ?></label>
                                                <?php
                                                if ( ! empty( $currencies ) && ! empty( $available ) && count( $currencies ) > 0 ) {
                                                    ?>
                                                    <select name="wccs_lang[<?php echo esc_attr( $lang ); ?>]" class="wccs_lang_dd">
                                                        <option value=""><?php echo esc_html__( 'Default Currency', 'wccs' ); ?></option>
                                                        <?php
                                                        foreach ( $currencies as $currency_code => $values ) {
                                                            $code = isset( $wccs_lang[ $lang ] ) ? esc_attr( $wccs_lang[ $lang ] ) : '';
                                                            if ( $code == $currency_code ) {
                                                                $selected = 'selected="selected"';
                                                            } else {
                                                                $selected = '';
                                                            }
                                                            ?>
                                                            <option value="<?php echo esc_attr( $currency_code ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $values['label'] ); ?></option>
                                                            <?php
                                                        }
                                                        ?>
                                                    </select>
                                                    <?php
                                                }
                                                ?>
                                            </li>
                                            <?php
                                        }
                                        ?>
                                        </ul>
                                        <?php
                                    }
                                    ?>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                            
                            <tr valign="top">
                                <th scope="row"><?php esc_html_e( 'Add to Menu', 'wccs' ); ?></th>
                                <td>
                                    <label for="wccs_show_in_menu">
                                        <input type="checkbox" id="wccs_show_in_menu" name="wccs_show_in_menu" value="1" <?php if ( get_multisite_or_site_option( 'wccs_show_in_menu', 0 ) ) { ?> 
                                        checked <?php } ?>>
                                        <?php esc_html_e( 'Add switcher as a menu item', 'wccs' ); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e( 'Check this if you want to add switcher to a menu as a menu and submenu item. Default is unchecked.', 'wccs' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr valign="top" class="wccs_menu_section">
                                <th scope="row"><?php esc_html_e( 'Switcher Menu', 'wccs' ); ?></th>
                                <td>
                                    <select name="wccs_switcher_menu">
                                        <?php
                                        $menus = wp_get_nav_menus();
                                        foreach ( $menus as $menu ) {
                                            ?>
                                        <option value="<?php echo esc_attr( $menu->slug ); ?>"<?php if ( get_multisite_or_site_option( 'wccs_switcher_menu', '' ) == $menu->slug ) { ?> 
                                        selected <?php } ?>><?php echo esc_html( $menu->name ); ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                    <p class="description"><?php esc_html_e( 'Choose the menu you want the switcher to be added to.', 'wccs' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr valign="top">
                                <th scope="row"><?php esc_html_e( 'Shortcodes', 'wccs' ); ?></th>
                                <td>
                                    <p class="description"><?php esc_html_e( 'Use [wcc_switcher] shortcode to view the currency switcher any place you want.', 'wccs' ); ?></p>
                                    <p class="description"><?php esc_html_e( 'Use [wcc_rates] shortcode to view the currency rates any place you want.', 'wccs' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr valign="top">
                                <th scope="row"><?php esc_html_e( 'Switcher Style', 'wccs' ); ?></th>
                                <td>
                                    <select name="wccs_shortcode_style" readonly>
                                        <option value="style_01"<?php if ( 'style_01' == get_multisite_or_site_option( 'wccs_shortcode_style', 'style_01' ) ) { ?> 
                                        selected<?php } ?>><?php esc_html_e( 'Style 1', 'wccs' ); ?></option>
                                    </select>
                                    <p class="description"><?php esc_html_e( 'Choose different style for [wcc_switcher] shortcode. Default is Style 1.', 'wccs' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr valign="top">
                                <th scope="row"><?php esc_html_e( 'Sticky Switcher', 'wccs' ); ?></th>
                                <td>
                                    <label for="wccs_sticky_switcher">
                                        <input type="checkbox" id="wccs_sticky_switcher" name="wccs_sticky_switcher" value="1" <?php if ( get_multisite_or_site_option( 'wccs_sticky_switcher', 0 ) ) { ?>
                                        checked<?php } ?>>
                                        <?php esc_html_e( 'Add sticky switcher', 'wccs' ); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e( 'Check this if you want to add sticky switcher to all website pages. Default is unchecked.', 'wccs' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr valign="top" class="wccs_sticky_section">
                                <th scope="row"><?php esc_html_e( 'Sticky Switcher Position', 'wccs' ); ?></th>
                                <td>
                                    <input type="radio" name="wccs_sticky_position" value="left"<?php if ( 'left' == get_multisite_or_site_option( 'wccs_sticky_position', 'right' ) ) { ?> 
                                    checked<?php } ?>> <?php esc_html_e( 'Left', 'wccs' ); ?>
                                    <input type="radio" name="wccs_sticky_position" value="right"<?php if ( 'right' == get_multisite_or_site_option( 'wccs_sticky_position', 'right' ) ) { ?> 
                                    checked<?php } ?>> <?php esc_html_e( 'Right', 'wccs' ); ?>
                                    
                                    <p class="description"><?php esc_html_e( 'Choose the sticky switcher position on the page either left or right. Default is right.', 'wccs' ); ?></p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><?php esc_html_e( 'Order Sync for Woo Analytics', 'wccs' ); ?></th>
                                <td>
                                    <button class="button button-primary" id="wccs_order_sync"><?php esc_html_e( 'Sync Now', 'wccs' ); ?></button>
                                </td>
                            </tr>                   
                        </table>
                        <?php
                        submit_button();
                    }

                    ?>

                </form>
            </div>
            <?php
        }

        public function wccs_update_type_option_logic( $old_value, $new_value ) {
            if ( $old_value != $new_value ) {
                if ( 'api' == $new_value ) {
                    $frequency = get_multisite_or_site_option( 'wccs_update_rate', 'hourly' );
                    if ( ( ! wp_next_scheduled( 'wccs_update_rates' ) ) && $frequency ) {
                        wp_schedule_event( strtotime( 'now' ), $frequency, 'wccs_update_rates', [ true ] );
                    }
                } else {
                    wp_clear_scheduled_hook( 'wccs_update_rates', [ true ] );
                }
            }
        }

        public function wccs_update_rate_option_logic( $old_value, $new_value ) {
            if ( 'api' == get_multisite_or_site_option( 'wccs_update_type', 'fixed' ) && $old_value != $new_value ) {
                wp_clear_scheduled_hook( 'wccs_update_rates', [ true ] );

                if ( ! wp_next_scheduled( 'wccs_update_rates' ) ) {
                    wp_schedule_event( strtotime( 'now' ), $new_value, 'wccs_update_rates', [ true ] );
                }
            }
        }

        public function wccs_woocommerce_currency_option_logic( $old_value, $new_value ) {
            if ( $old_value != $new_value ) {

                $currencies = get_multisite_or_site_option( 'wccs_currencies', [] );

                if ( isset( $currencies[ $new_value ] ) ) {
                    unset( $currencies[ $new_value ] );
                    if ( is_multisite() ) {
                        update_site_option( 'wccs_currencies', $currencies );
                    } else {
                        update_option( 'wccs_currencies', $currencies );
                    }
                }
            }
        }

        /**
         * Singleton Instance Method to initiate class.
         *
         */
        public static function Instance() {
            if ( null === self::$_instance ) {
                self::$_instance = new Settings();
            }

            return self::$_instance;
        }
    }

}
