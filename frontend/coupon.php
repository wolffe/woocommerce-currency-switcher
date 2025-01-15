<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WOOMULTI_CURRENCY_F_Frontend_Coupon
 */
class WOOMULTI_CURRENCY_F_Frontend_Coupon {
    /**
     * @var WOOMULTI_CURRENCY_F_Data Holds the settings data.
     */
    private $settings;

    public function __construct() {
        // Initialize the settings property
        $this->settings = new WOOMULTI_CURRENCY_F_Data();

        if ( $this->settings->get_enable() ) {
            add_filter( 'woocommerce_coupon_get_amount', [ $this, 'woocommerce_coupon_get_amount' ], 10, 2 );
            add_filter(
                'woocommerce_coupon_get_minimum_amount',
                [
                    $this,
                    'woocommerce_coupon_get_minimum_amount',
                ]
            );
            add_filter(
                'woocommerce_coupon_get_maximum_amount',
                [
                    $this,
                    'woocommerce_coupon_get_maximum_amount',
                ]
            );
            add_filter(
                'woocommerce_boost_sales_coupon_amount_price',
                [
                    $this,
                    'woocommerce_boost_sales_coupon_amount_price',
                ]
            );
        }
    }

    /**
     * Apply with percent
     *
     * @param $data
     *
     * @return mixed
     */
    public function woocommerce_coupon_get_amount( $data, $obj ) {
        if ( $obj->is_type( [ 'percent' ] ) ) {
            return $data;
        }

        return wmc_get_price( $data );
    }


    public function woocommerce_boost_sales_coupon_amount_price( $data ) {
        return wmc_get_price( $data );
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function woocommerce_coupon_get_minimum_amount( $data ) {

        return wmc_get_price( $data );
    }

    public function woocommerce_coupon_get_maximum_amount( $data ) {
        return wmc_get_price( $data );
    }
}
