<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WOOMULTI_CURRENCY_F_Frontend_Price
 */
class WOOMULTI_CURRENCY_F_Frontend_Price {
    protected $settings;
    protected $price;

    public function __construct() {

        $this->settings = new WOOMULTI_CURRENCY_F_Data();
        if ( $this->settings->get_enable() ) {
            /*Simple product*/
            add_filter( 'woocommerce_product_get_regular_price', [ $this, 'woocommerce_product_get_regular_price' ], 10, 2 );
            add_filter( 'woocommerce_product_get_sale_price', [ $this, 'woocommerce_product_get_sale_price' ], 10, 2 );
            add_filter( 'woocommerce_product_get_price', [ $this, 'woocommerce_product_get_price' ], 10, 2 );
            //
            /*Variable price*/
            add_filter( 'woocommerce_product_variation_get_price', [ $this, 'woocommerce_product_variation_get_price' ], 10, 2 );
            add_filter( 'woocommerce_product_variation_get_regular_price', [ $this, 'woocommerce_product_variation_get_regular_price' ], 10, 2 );
            add_filter( 'woocommerce_product_variation_get_sale_price', [ $this, 'woocommerce_product_variation_get_sale_price' ], 10, 2 );

            /*Variable Parent min max price*/
            add_filter( 'woocommerce_variation_prices', [ $this, 'get_woocommerce_variation_prices' ] );

            /*Pay with Multi Currencies*/
            add_action( 'init', [ $this, 'init' ], 99 );

            /*Approximately*/
            add_filter( 'woocommerce_get_price_html', [ $this, 'add_approximately_price' ], 10, 2 );

        }
    }

    /**
     * @param $html_price
     * @param $product
     *
     * @return string
     */
    public function add_approximately_price( $html_price, $product ) {
        if ( is_admin() ) {
            return $html_price;
        }

        return $html_price;
    }

    /**
     * Check on checkout page
     */
    public function init() {
        if ( is_ajax() ) {
            return;
        }
        /*Fix UX Builder of Flatsome*/
        if ( isset( $_GET['uxb_iframe'] ) ) {
            return;
        }
        $current_url = @$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
        if ( $_SERVER['SERVER_PORT'] != '80' && $_SERVER['SERVER_PORT'] != '443' ) {
            $current_url .= $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
        } else {
            $current_url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }
        // Retrieve the current post's ID based on its URL
        $id = url_to_postid( $current_url );

        $allow_multi      = $this->settings->get_enable_multi_payment();
        $current_currency = $this->settings->get_current_currency();
        if ( ! $allow_multi ) {
            if ( $id == get_option( 'woocommerce_checkout_page_id', 0 ) ) {
                $this->settings->set_current_currency( $this->settings->get_default_currency(), false );
            } elseif ( isset( $_COOKIE['wmc_current_currency_old'] ) && $_COOKIE['wmc_current_currency_old'] != $current_currency ) {
                $this->settings->set_current_currency( $_COOKIE['wmc_current_currency_old'], false );
            }
        }
    }


    /**
     * Variable Parent min max price
     *
     * @param $price_arr
     *
     * @return array
     */
    public function get_woocommerce_variation_prices( $price_arr ) {
        $temp_arr = $price_arr;
        if ( is_array( $price_arr ) && ! empty( $price_arr ) ) {
            $fixed_price = $this->settings->check_fixed_price();

            foreach ( $price_arr as $price_type => $values ) {
                foreach ( $values as $key => $value ) {

                    if ( $fixed_price ) {
                        $current_currency = $this->settings->get_current_currency();
                        if ( $temp_arr['regular_price'][ $key ] != $temp_arr['price'][ $key ] ) {
                            if ( $price_type == 'regular_price' ) {
                                $regular_price_wmcp = json_decode( get_post_meta( $key, '_regular_price_wmcp', true ), true );

                                if ( isset( $regular_price_wmcp[ $current_currency ] ) && $regular_price_wmcp[ $current_currency ] > 0 ) {
                                    $price_arr[ $price_type ][ $key ] = $regular_price_wmcp[ $current_currency ];
                                } else {
                                    $price_arr[ $price_type ][ $key ] = wmc_get_price( $value );
                                }
                            }

                            if ( $price_type == 'price' || $price_arr == 'sale_price' ) {
                                $sale_price_wmcp = json_decode( get_post_meta( $key, '_sale_price_wmcp', true ), true );

                                if ( isset( $sale_price_wmcp[ $current_currency ] ) && $sale_price_wmcp[ $current_currency ] > 0 ) {
                                    $price_arr[ $price_type ][ $key ] = $sale_price_wmcp[ $current_currency ];
                                } elseif ( $temp_arr['regular_price'][ $key ] != $temp_arr['price'][ $key ] ) {
                                    $price_arr[ $price_type ][ $key ] = wmc_get_price( $value );
                                } else {
                                    $price_arr[ $price_type ][ $key ] = wmc_get_price( $value );
                                }
                            }
                        } else {
                            $regular_price_wmcp = json_decode( get_post_meta( $key, '_regular_price_wmcp', true ), true );
                            if ( isset( $regular_price_wmcp[ $current_currency ] ) && $regular_price_wmcp[ $current_currency ] > 0 ) {
                                $price_arr[ $price_type ][ $key ] = $regular_price_wmcp[ $current_currency ];
                            } else {
                                $price_arr[ $price_type ][ $key ] = wmc_get_price( $value );
                            }
                        }
                    } else {
                        $price_arr[ $price_type ][ $key ] = wmc_get_price( $value );
                    }
                }
            }
        }

        return $price_arr;
    }

    /**
     * Sale price with product variable
     */
    public function woocommerce_product_variation_get_sale_price( $price, $product ) {
        $product_id = $product->get_id();
        if ( isset( $this->price[ $product_id ]['sale'] ) && $price ) {
            return $this->price[ $product_id ]['sale'];
        }
        if ( $this->settings->check_fixed_price() ) {
            $currenct_currency = $this->settings->get_current_currency();
            $product_id        = $product->get_id();
            $product_price     = json_decode( get_post_meta( $product_id, '_sale_price_wmcp', true ), true );
            if ( isset( $product_price[ $currenct_currency ] ) ) {
                if ( $product_price[ $currenct_currency ] > 0 ) {
                    return $this->set_cache( $product_price[ $currenct_currency ], $product_id, 'sale' );
                }
            }
        }

        return $this->set_cache( wmc_get_price( $price ), $product_id, 'sale' );
        //Do nothing to remove prices hash to alway get live price.
    }

    /**
     * Regular price with product variable
     */
    public function woocommerce_product_variation_get_regular_price( $price, $product ) {
        $product_id = $product->get_id();
        if ( isset( $this->price[ $product_id ]['regular'] ) && $price ) {
            return $this->price[ $product_id ]['regular'];
        }
        if ( $this->settings->check_fixed_price() ) {
            $currenct_currency = $this->settings->get_current_currency();
            $product_id        = $product->get_id();
            $product_price     = json_decode( get_post_meta( $product_id, '_regular_price_wmcp', true ), true );
            if ( isset( $product_price[ $currenct_currency ] ) ) {
                if ( $product_price[ $currenct_currency ] > 0 ) {
                    return $this->set_cache( $product_price[ $currenct_currency ], $product_id, 'regular' );
                }
            }
        }

        return $this->set_cache( wmc_get_price( $price ), $product_id, 'regular' );
        //Do nothing to remove prices hash to alway get live price.
    }


    /**
     * Sale product variable price
     *
     * @param $price
     * @param $obj
     */
    public function woocommerce_product_variation_get_price( $price, $product ) {
        $product_id = $product->get_id();
        if ( isset( $this->price[ $product_id ]['price'] ) && $price ) {
            return $this->price[ $product_id ]['price'];
        }
        if ( $this->settings->check_fixed_price() ) {
            $currenct_currency = $this->settings->get_current_currency();
            $product_id        = $product->get_id();
            $product_price     = json_decode( get_post_meta( $product_id, '_regular_price_wmcp', true ), true );
            $sale_price        = json_decode( get_post_meta( $product_id, '_sale_price_wmcp', true ), true );
            if ( isset( $product_price[ $currenct_currency ] ) && ! $product->is_on_sale() ) {
                if ( $product_price[ $currenct_currency ] > 0 ) {
                    return $this->set_cache( $product_price[ $currenct_currency ], $product_id, 'price' );
                }
            } elseif ( isset( $sale_price[ $currenct_currency ] ) ) {
                if ( $sale_price[ $currenct_currency ] > 0 ) {
                    return $this->set_cache( $sale_price[ $currenct_currency ], $product_id, 'price' );

                }
            }
        }

        return $this->set_cache( wmc_get_price( $price ), $product_id, 'price' );
    }

    /**
     * Regular price of simple product
     *
     * @param $price
     * @param $obj
     */
    public function woocommerce_product_get_price( $price, $product ) {
        $product_id = $product->get_id();

        if ( isset( $this->price[ $product_id ]['price'] ) && $price ) {
            return $this->price[ $product_id ]['price'];
        }

        if ( $this->settings->check_fixed_price() ) {
            $currenct_currency = $this->settings->get_current_currency();
            $product_id        = $product->get_id();
            $product_price     = json_decode( get_post_meta( $product_id, '_regular_price_wmcp', true ), true );
            $sale_price        = json_decode( get_post_meta( $product_id, '_sale_price_wmcp', true ), true );

            if ( isset( $product_price[ $currenct_currency ] ) && ! $product->is_on_sale() ) {
                if ( $product_price[ $currenct_currency ] > 0 ) {
                    return $this->set_cache( $product_price[ $currenct_currency ], $product_id, 'price' );
                }
            } elseif ( isset( $sale_price[ $currenct_currency ] ) ) {
                if ( $sale_price[ $currenct_currency ] > 0 ) {
                    return $this->set_cache( $sale_price[ $currenct_currency ], $product_id, 'price' );
                }
            }
        }

        return $this->set_cache( wmc_get_price( $price ), $product_id, 'price' );
    }

    /**
     * @param $price
     * @param $obj
     *
     * @return mixed
     */
    public function woocommerce_product_get_sale_price( $price, $product ) {
        $product_id = $product->get_id();
        if ( isset( $this->price[ $product_id ]['sale'] ) && $price ) {
            return $this->price[ $product_id ]['sale'];
        }
        if ( $this->settings->check_fixed_price() ) {
            $currenct_currency = $this->settings->get_current_currency();
            $product_id        = $product->get_id();
            $product_price     = json_decode( get_post_meta( $product_id, '_sale_price_wmcp', true ), true );
            if ( isset( $product_price[ $currenct_currency ] ) ) {
                if ( $product_price[ $currenct_currency ] > 0 ) {
                    return $this->set_cache( $product_price[ $currenct_currency ], $product_id, 'sale' );
                }
            }
        }

        return $this->set_cache( wmc_get_price( $price ), $product_id, 'sale' );
    }

    /**
     * @param $price
     * @param $obj
     *
     * @return mixed
     */
    public function woocommerce_product_get_regular_price( $price, $product ) {
        $product_id = $product->get_id();
        if ( isset( $this->price[ $product_id ]['regular'] ) && $price ) {
            return $this->price[ $product_id ]['regular'];
        }
        if ( $this->settings->check_fixed_price() ) {
            $currenct_currency = $this->settings->get_current_currency();
            $product_id        = $product->get_id();
            $product_price     = json_decode( get_post_meta( $product_id, '_regular_price_wmcp', true ), true );
            if ( isset( $product_price[ $currenct_currency ] ) ) {
                if ( $product_price[ $currenct_currency ] > 0 ) {
                    return $this->set_cache( $product_price[ $currenct_currency ], $product_id, 'regular' );
                }
            }
        }

        return $this->set_cache( wmc_get_price( $price ), $product_id, 'regular' );
    }

    /**
     * Set price to global. It will help more speedy.
     *
     * @param $price
     * @param $id
     *
     * @return mixed
     */
    protected function set_cache( $price, $id, $key ) {
        if ( $price && $id && $key ) {
            $this->price[ $id ][ $key ] = $price;

            return $this->price[ $id ][ $key ];
        } else {
            return $price;
        }
    }
}
