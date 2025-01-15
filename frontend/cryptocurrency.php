<?php

/**
 * Class WOOMULTI_CURRENCY_Frontend_Cryptocurrency
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WOOMULTI_CURRENCY_F_Frontend_Cryptocurrency {
    protected $settings;

    public function __construct() {
        $this->settings = new WOOMULTI_CURRENCY_F_Data();
        if ( $this->settings->get_enable() ) {
            /*Fix round function with case default decimals = 0 and other currency decimal > 0*/
            add_filter( 'woocommerce_currencies', [ $this, 'woocommerce_currencies' ] );
            add_filter( 'woocommerce_currency_symbols', [ $this, 'woocommerce_currency_symbols' ] );

        }
    }

    /**
     * @param $currency
     *
     * @return mixed
     */
    public function woocommerce_currencies( $currency ) {
        $currency['LTC'] = __( 'Litecoin', 'woo-multi-currency' );
        $currency['ETH'] = __( 'Ethereum', 'woo-multi-currency' );

        return $currency;
    }

    /**
     *
     */
    public function woocommerce_currency_symbols( $symbols ) {
        $symbols['LTC'] = 'LTC';
        $symbols['ETH'] = 'ETH';

        return $symbols;
    }
}
