<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WOOMULTI_CURRENCY_F_Frontend_Shipping
 */
class WOOMULTI_CURRENCY_F_Frontend_Shipping {
    protected $cache = [];

    /**
     * @var WOOMULTI_CURRENCY_F_Data Holds the settings data.
     */
    private $settings;

    public function __construct() {
        // Initialize the settings property
        $this->settings = new WOOMULTI_CURRENCY_F_Data();

        if ( $this->settings->get_enable() ) {
            global $wpdb;

            $raw_methods_sql = "SELECT method_id, method_order, instance_id, is_enabled FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE is_enabled = 1 order by instance_id ASC;";
            $raw_methods     = $wpdb->get_results( $raw_methods_sql );

            if ( count( $raw_methods ) ) {
                foreach ( $raw_methods as $method ) {
                    /*Free Shipping and Flat rate of WooCommerce Core*/
                    if ( $method->method_id == 'free_shipping' ) {
                        add_filter(
                            'option_woocommerce_' . trim( $method->method_id ) . '_' . intval( $method->instance_id ) . '_settings',
                            [
                                $this,
                                'free_cost',
                            ]
                        );
                    } elseif ( $method->method_id == 'betrs_shipping' ) {
                        /*Compatible with WooCommerce Table Rate Shipping*/
                        add_filter(
                            'option_betrs_shipping_options-' . intval( $method->instance_id ),
                            [
                                $this,
                                'table_rate_shipping',
                            ]
                        );
                    }
                }
            }
            add_filter( 'woocommerce_package_rates', [ $this, 'woocommerce_package_rates' ] );
        }
    }

    public function table_rate_shipping( $options ) {
        $new_options = $options;
        if ( ! empty( $new_options ) ) {
            // step through each table rate row
            foreach ( $new_options['settings'] as $o_key => $option ) {

                foreach ( $option['rows'] as $r_key => $row ) {
                    $costs = $row['costs'];
                    if ( is_array( $costs ) ) {
                        foreach ( $costs as $k => $cost ) {
                            switch ( $cost['cost_type'] ) {
                                case '%':
                                    break;
                                default:
                                    $options['settings'][ $o_key ]['rows'][ $r_key ]['costs'][ $k ]['cost_value'] = wmc_get_price( $cost['cost_value'] );
                            }
                        }
                    }
                }
            }
        }

        return $options;
    }

    /**
     * Shipping cost
     *
     * @param $methods
     *
     * @return mixed
     */
    public function woocommerce_package_rates( $methods ) {

        if ( count( array_filter( $methods ) ) ) {

            foreach ( $methods as $k => $method ) {
                if ( $method->method_id == 'betrs_shipping' || $method->method_id == 'printful_shipping' ) {
                    continue;
                }
                if ( isset( $this->cache[ $k ] ) && $this->cache[ $k ] && $k ) {
                    $method->set_cost( $this->cache[ $k ] );
                } else {
                    $cost = wmc_get_price( $method->get_cost() );
                    $method->set_cost( $cost );
                    $this->cache[ $k ] = $cost;
                }
                if ( count( $method->get_taxes() ) ) {
                    $new_tax = [];
                    foreach ( $method->get_taxes() as $k => $tax ) {
                        $new_tax[ $k ] = wmc_get_price( $tax );
                    }
                    $method->set_taxes( $new_tax );
                }
            }
        }

        return $methods;
    }

    /**
     * Tax on free ship
     *
     * @param $data
     *
     * @return mixed
     */
    public function free_cost( $data ) {
        if ( ! $this->settings->get_default_currency() ) {
            return $data;
        }

        if ( isset( $data['min_amount'] ) ) {
            $data['min_amount'] = wmc_get_price( $data['min_amount'] );
        }

        return $data;
    }
}
