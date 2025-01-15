<?php
/**
 * Class WOOMULTI_CURRENCY_F_Frontend_Symbol
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WOOMULTI_CURRENCY_F_Frontend_Symbol {
    protected $settings;

    public function __construct() {

        $this->settings = new WOOMULTI_CURRENCY_F_Data();
        if ( $this->settings->get_enable() ) {

            /*Add order information*/
            add_filter( 'woocommerce_thankyou_order_id', [ $this, 'woocommerce_thankyou_order_id' ], 9 );
            add_filter( 'woocommerce_currency', [ $this, 'woocommerce_currency' ] );
            /**
             * Format price
             */
            add_filter( 'wc_get_price_decimals', [ $this, 'set_decimals' ] );
            /**
             * Symbol position
             */
            add_filter( 'woocommerce_price_format', [ $this, 'price_format' ] );

            /*Custom Symbol*/
            add_filter( 'wc_price', [ $this, 'custom_price' ], 10, 3 );
            add_filter( 'woocommerce_currency_symbol', [ $this, 'custom_currency_symbol' ], 11, 2 );
        }
    }

    /**
     * Custom symbol price
     *
     * @param $return
     * @param $price
     * @param $args
     *
     * @return mixed|void
     */
    function custom_price( $return, $price, $args ) {
        extract(
            wp_parse_args(
                $args,
                [
                    'ex_tax_label'       => false,
                    'currency'           => '',
                    'decimal_separator'  => wc_get_price_decimal_separator(),
                    'thousand_separator' => wc_get_price_thousand_separator(),
                    'decimals'           => wc_get_price_decimals(),
                    'price_format'       => get_woocommerce_price_format(),
                ]
            )
        );

        $unformatted_price = $price;
        $negative          = $price < 0;

        $currency_symbol = get_woocommerce_currency_symbol( $currency );
        $pos             = strpos( $currency_symbol, '#PRICE#' );

        if ( $pos === false ) {
            $formatted_price = ( $negative ? '-' : '' ) . sprintf( $price_format, '<span class="woocommerce-Price-currencySymbol">' . $currency_symbol . '</span>', $price );
        } else {
            $formatted_price = str_replace( '#PRICE#', $price, $currency_symbol );

        }

        $return = '<span class="woocommerce-Price-amount amount">' . $formatted_price . '</span>';

        if ( $ex_tax_label && wc_tax_enabled() ) {
            $return .= ' <small class="woocommerce-Price-taxLabel tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
        }

        /**
         * Filters the string of price markup.
         *
         * @param string $return            Price HTML markup.
         * @param string $price             Formatted price.
         * @param array  $args              Pass on the args.
         * @param float  $unformatted_price Price as float to allow plugins custom formatting. Since 3.2.0.
         */
        return apply_filters( 'wmc_wc_price', $return, $price, $args, $unformatted_price );
    }

    /**
     * Custom current symbol
     *
     * @param $currency_symbol
     * @param $currency
     *
     * @return mixed
     */
    public function custom_currency_symbol( $currency_symbol, $currency ) {
        if ( is_admin() ) {
            return $currency_symbol;
        }
        $selected_currencies = $this->settings->get_list_currencies();

        if ( is_account_page() ) {
            return $currency_symbol;
        } elseif ( isset( $selected_currencies[ $currency ] ) && isset( $selected_currencies[ $currency ]['custom'] ) && $selected_currencies[ $currency ]['custom'] != '' ) {
            $currency_symbol = $selected_currencies[ $currency ]['custom'];

        }

        return $currency_symbol;
    }

    /**
     * @param $data
     *
     * @return mixed|string|void
     */
    public function woocommerce_currency( $data ) {

        if ( is_admin() && ! is_ajax() ) {
            return $data;
        }
        if ( $this->settings->get_current_currency() ) {
            $data = $this->settings->get_current_currency();
        }

        return $data;
    }

    /**
     * Insert information about order after checkout
     *
     * @param $order_id
     *
     * @return mixed
     */
    public function woocommerce_thankyou_order_id( $order_id ) {

        $wmc_order_info = $this->settings->get_list_currencies();
        $wmc_order_info[ $this->settings->get_default_currency() ]['is_main'] = 1;
        update_post_meta( $order_id, 'wmc_order_info', $wmc_order_info );

        return $order_id;
    }

    /**
     * @return string    price format of current currency
     */
    public function price_format( $format ) {
        $selected_currencies = $this->settings->get_list_currencies();
        $currencies          = $this->settings->get_currencies();
        if ( is_order_received_page() ) {
            global $wp;
            $order_id    = $wp->query_vars['order-received'];
            $order       = wc_get_order( $order_id );
            $currency    = $order->get_currency();
            $current_pos = $selected_currencies[ $currency ]['pos'];
        } elseif ( in_array( $this->settings->get_current_currency(), $currencies ) ) {
            $current_pos = $selected_currencies[ $this->settings->get_current_currency() ]['pos'];
        } else {
            return $format;
        }

        switch ( $current_pos ) {
            case 'left':
                $format = '%1$s%2$s';
                break;
            case 'right':
                $format = '%2$s%1$s';
                break;
            case 'left_space':
                $format = '%1$s&nbsp;%2$s';
                break;
            case 'right_space':
                $format = '%2$s&nbsp;%1$s';
                break;
        }

        return $format;
    }

    /**
     * Format price
     *
     * @param $price_arg
     *
     * @return mixed
     */
    public function set_decimals( $decimal ) {
        $selected_currencies = $this->settings->get_list_currencies();

        return $selected_currencies[ $this->settings->get_current_currency() ]['decimals'] ? $selected_currencies[ $this->settings->get_current_currency() ]['decimals'] : 0;
    }
}
