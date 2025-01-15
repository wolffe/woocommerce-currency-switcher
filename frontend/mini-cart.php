<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WOOMULTI_CURRENCY_F_Frontend_Mini_Cart
 */
class WOOMULTI_CURRENCY_F_Frontend_Mini_Cart {
    /**
     * @var WOOMULTI_CURRENCY_F_Data Holds the settings data.
     */
    private $settings;

    public function __construct() {
        // Initialize the settings property
        $this->settings = new WOOMULTI_CURRENCY_F_Data();

        if ( $this->settings->get_enable() ) {
            add_action( 'woocommerce_cart_loaded_from_session', [ $this, 'woocommerce_before_mini_cart' ], 99 );
            add_action( 'wp_enqueue_scripts', [ $this, 'remove_session' ] );

        }
    }

    public function remove_session() {
        $list_currencies = $this->settings->get_currencies();
        if ( isset( $_GET['wmc-currency'] ) && in_array( $_GET['wmc-currency'], $list_currencies ) ) {
            wp_enqueue_script( 'woo-multi-currency', WOOMULTI_CURRENCY_F_JS . 'woo-multi-currency.js', [ 'jquery' ], WOOMULTI_CURRENCY_F_VERSION );
        }
    }

    /**
     * Recalculator for mini cart
     */
    public function woocommerce_before_mini_cart() {
        WC()->cart->calculate_totals();
    }
}
