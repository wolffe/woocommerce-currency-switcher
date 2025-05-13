<?php
/**
 * Plugin Name: WooCommerce Currency Switcher
 * Description: Allow your customers to shop seamlessly in their preferred currency. Allow fixed prices in multiple currencies, multiple display prices and accepts payments in multiple currencies.
 * Version: 4.3.1
 * Author: getButterfly
 * Author URI: http://getbutterfly.com/
 * Update URI: http://getbutterfly.com/
 * Requires at least: 6.0
 * Requires Plugins: woocommerce
 * Tested up to: 6.8.1
 * License: GNU General Public License v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: woocommerce-currency-switcher
 *
 * WC requires at least: 7.0.0
 * WC tested up to: 9.8.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check if WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return;
}

// Updater
require_once plugin_dir_path( __FILE__ ) . 'includes/updater.php';

class WC_Currency_Switcher {
    private static $instance = null;
    private $currencies      = [];

    public static function get_instance() {
        if ( self::$instance == null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Load currencies from options
        $currency_codes = get_option( 'wc_currency_switcher_currency_codes', [ 'EUR', 'GBP', 'USD' ] );
        foreach ( $currency_codes as $code ) {
            $this->currencies[ $code ] = get_woocommerce_currency_symbol( $code );
        }

        // HPOS compatibility
        add_action( 'before_woocommerce_init', [ $this, 'declare_hpos_compatibility' ] );

        // Add WooCommerce settings
        add_filter( 'woocommerce_settings_tabs_array', [ $this, 'add_settings_tab' ], 50 );
        add_action( 'woocommerce_settings_tabs_currency_switcher', [ $this, 'settings_tab' ] );
        add_action( 'woocommerce_update_options_currency_switcher', [ $this, 'update_settings' ] );

        // Filter available payment gateways based on currency
        add_filter( 'woocommerce_available_payment_gateways', [ $this, 'filter_gateways_based_on_currency' ], 10, 1 );

        // Add gateway title filter
        add_filter( 'woocommerce_gateway_title', [ $this, 'append_gateway_method_to_title' ], 10, 2 );

        // Add product-specific gateway settings
        add_filter( 'woocommerce_product_data_tabs', [ $this, 'add_product_gateway_tab' ] );
        add_action( 'woocommerce_product_data_panels', [ $this, 'add_product_gateway_settings' ] );
        add_action( 'woocommerce_process_product_meta', [ $this, 'save_product_gateway_settings' ] );

        // Add admin scripts
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );

        // Add currency switcher
        add_action( 'wp_footer', [ $this, 'display_currency_switcher' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

        // Add shortcode
        add_shortcode( 'wcc_switcher', [ $this, 'shortcode_currency_switcher' ] );

        // AJAX handler for currency switching
        add_action( 'wp_ajax_switch_currency', [ $this, 'ajax_switch_currency' ] );
        add_action( 'wp_ajax_nopriv_switch_currency', [ $this, 'ajax_switch_currency' ] );

        // Auto-detect currency on first visit
        add_action( 'template_redirect', [ $this, 'auto_detect_currency' ] );

        // Initialize session currency from option
        add_action( 'template_redirect', [ $this, 'maybe_set_session_currency' ], 5 );

        // Admin product fields
        add_action( 'woocommerce_product_options_pricing', [ $this, 'add_custom_price_fields' ] );
        add_action( 'woocommerce_process_product_meta', [ $this, 'save_custom_price_fields' ] );
        add_action( 'woocommerce_variation_options_pricing', [ $this, 'add_variation_custom_price_fields' ], 10, 3 );
        add_action( 'woocommerce_save_product_variation', [ $this, 'save_variation_custom_price_fields' ], 10, 2 );

        // Price filters
        add_filter( 'woocommerce_product_get_price', [ $this, 'get_custom_price' ], 10, 2 );
        add_filter( 'woocommerce_product_get_regular_price', [ $this, 'get_custom_price' ], 10, 2 );
        add_filter( 'woocommerce_product_get_sale_price', [ $this, 'get_custom_price' ], 10, 2 );
        add_filter( 'woocommerce_cart_item_price', [ $this, 'get_custom_cart_item_price' ], 10, 3 );
        add_filter( 'woocommerce_cart_item_subtotal', [ $this, 'get_custom_cart_item_subtotal' ], 10, 3 );

        // Variation price filters
        add_filter( 'woocommerce_variation_price', [ $this, 'get_custom_price' ], 10, 2 );
        add_filter( 'woocommerce_variation_regular_price', [ $this, 'get_custom_price' ], 10, 2 );
        add_filter( 'woocommerce_variation_sale_price', [ $this, 'get_custom_price' ], 10, 2 );
        add_filter( 'woocommerce_get_variation_price', [ $this, 'get_custom_price' ], 10, 2 );
        add_filter( 'woocommerce_get_variation_regular_price', [ $this, 'get_custom_price' ], 10, 2 );
        add_filter( 'woocommerce_get_variation_sale_price', [ $this, 'get_custom_price' ], 10, 2 );
        add_filter( 'woocommerce_variation_prices', [ $this, 'get_custom_variation_prices' ], 10, 2 );
        add_filter( 'woocommerce_variation_prices_price', [ $this, 'get_custom_price' ], 10, 2 );
        add_filter( 'woocommerce_variation_prices_regular_price', [ $this, 'get_custom_price' ], 10, 2 );
        add_filter( 'woocommerce_variation_price_html', [ $this, 'get_custom_variation_price_html' ], 10, 2 );
        add_filter( 'woocommerce_available_variation', [ $this, 'get_custom_available_variation' ], 10, 3 );

        // Currency symbol filter
        add_filter( 'woocommerce_currency_symbol', [ $this, 'change_currency_symbol' ], 999 );

        // Transactional currency filter
        add_filter( 'woocommerce_currency', [ $this, 'set_transactional_currency' ], 999 );
    }

    public function declare_hpos_compatibility() {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'email_templates', __FILE__, true );
        }
    }

    public function enqueue_scripts() {
        wp_enqueue_style(
            'woocommerce-currency-switcher',
            plugins_url( 'assets/css/style.css', __FILE__ ),
            [],
            '4.3.0'
        );

        wp_enqueue_script(
            'woocommerce-currency-switcher',
            plugins_url( 'assets/js/currency-switcher.js', __FILE__ ),
            [ 'jquery' ],
            '4.3.0',
            true
        );

        // Get custom prices for all products in cart and current product
        $custom_prices  = [];
        $variation_data = [];

        // Get cart prices
        if ( WC()->cart ) {
            foreach ( WC()->cart->get_cart() as $cart_item ) {
                if ( isset( $cart_item['data'] ) && is_object( $cart_item['data'] ) ) {
                    $product    = $cart_item['data'];
                    $product_id = $product->get_id();

                    foreach ( $this->currencies as $code => $symbol ) {
                        if ( $code !== get_woocommerce_currency() ) {
                            $meta_key     = '_price_' . strtolower( $code );
                            $custom_price = get_post_meta( $product_id, $meta_key, true );
                            if ( ! empty( $custom_price ) ) {
                                $custom_prices[ $product_id ][ $code ] = (float) $custom_price;
                            }
                        }
                    }
                }
            }
        }

        // Get current product prices if on product page
        if ( is_product() ) {
            global $product;
            if ( $product && is_object( $product ) ) {
                $product_id = $product->get_id();

                // If variable product, get variation prices
                if ( $product->is_type( 'variable' ) ) {
                    $variations = $product->get_available_variations();
                    foreach ( $variations as $variation ) {
                        $variation_id  = $variation['variation_id'];
                        $variation_obj = wc_get_product( $variation_id );

                        if ( $variation_obj ) {
                            // Store variation data
                            $variation_data[ $variation_id ] = [
                                'attributes'            => $variation['attributes'],
                                'display_price'         => $variation['display_price'],
                                'display_regular_price' => $variation['display_regular_price'],
                                'price_html'            => $variation['price_html'],
                                'is_in_stock'           => $variation['is_in_stock'],
                                'is_purchasable'        => $variation['is_purchasable'],
                                'variation_id'          => $variation_id,
                                'parent_id'             => $product_id,
                            ];

                            // Get custom prices for variation
                            foreach ( $this->currencies as $code => $symbol ) {
                                if ( $code !== get_woocommerce_currency() ) {
                                    $meta_key     = '_price_' . strtolower( $code );
                                    $custom_price = get_post_meta( $variation_id, $meta_key, true );
                                    if ( ! empty( $custom_price ) ) {
                                        $custom_prices[ $variation_id ][ $code ] = (float) $custom_price;
                                    } else {
                                        // If no variation price, try parent product
                                        $parent_price = get_post_meta( $product_id, $meta_key, true );
                                        if ( ! empty( $parent_price ) ) {
                                            $custom_prices[ $variation_id ][ $code ] = (float) $parent_price;
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    // For regular products
                    foreach ( $this->currencies as $code => $symbol ) {
                        if ( $code !== get_woocommerce_currency() ) {
                            $meta_key     = '_price_' . strtolower( $code );
                            $custom_price = get_post_meta( $product_id, $meta_key, true );
                            if ( ! empty( $custom_price ) ) {
                                $custom_prices[ $product_id ][ $code ] = (float) $custom_price;
                            }
                        }
                    }
                }
            }
        }

        wp_localize_script(
            'woocommerce-currency-switcher',
            'wcCurrencySwitcher',
            [
                'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
                'defaultCurrency' => get_woocommerce_currency(),
                'currentCurrency' => $this->get_current_currency(),
                'customPrices'    => $custom_prices,
                'variationData'   => $variation_data,
                'currencies'      => $this->currencies,
                'isCheckout'      => is_checkout(),
            ]
        );
    }

    private function get_default_currency() {
        return get_woocommerce_currency();
    }

    private function get_current_currency() {
        $session_currency = null;
        // Check session first on frontend
        if ( ! is_admin() && WC()->session ) {
            $session_currency = WC()->session->get( 'wcc_selected_currency' );
        }

        // If session has a value and it's valid, use it
        if ( ! empty( $session_currency ) && array_key_exists( $session_currency, $this->currencies ) ) {
            return $session_currency;
        }

        // Fallback to option if session not set or invalid (safe outside filter)
        return get_option( 'wc_currency_switcher_currency', $this->get_default_currency() );
    }

    public function display_currency_switcher() {
        $current_currency = $this->get_current_currency();
        $current_theme    = get_option( 'wc_currency_switcher_theme', 'light' );
        $current_position = get_option( 'wc_currency_switcher_position', 'bottom-right' );
        ?>
        <div id="currency-switcher" class="currency-switcher theme-<?php echo esc_attr( $current_theme ); ?> position-<?php echo esc_attr( $current_position ); ?>">
            <select id="currency-select">
            <?php foreach ( $this->currencies as $code => $symbol ) : ?>
                    <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $code, $current_currency ); ?>>
                        <?php echo esc_html( $code . ' ' . $symbol ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    public function ajax_switch_currency() {
        $currency = isset( $_POST['currency'] ) ? sanitize_text_field( $_POST['currency'] ) : '';

        if ( array_key_exists( $currency, $this->currencies ) ) {
            // Still update option for persistence across sessions/devices
            update_option( 'wc_currency_switcher_currency', $currency );

            // Set session value for current user
            if ( WC()->session ) {
                if ( ! WC()->session->has_session() ) {
                    WC()->session->set_customer_session_cookie( true );
                }
                WC()->session->set( 'wcc_selected_currency', $currency );
            } else {
                // Handle case where session might not be available (e.g., REST API?)
                // For now, we rely on the option as fallback.
            }

            // Clear any WooCommerce transients to ensure fresh price calculations
            if ( class_exists( 'WC_Cache_Helper' ) ) {
                WC_Cache_Helper::get_transient_version( 'product', true );
            }

            wp_send_json_success(
                [
                    'currency' => $currency,
                    'message'  => 'Currency switched successfully',
                ]
            );
        } else {
            wp_send_json_error(
                [
                    'message' => 'Invalid currency',
                ]
            );
        }

        wp_die();
    }

    public function add_custom_price_fields() {
        global $post;

        echo '<div class="options_group">';
        echo '<p class="form-field"><strong>' . __( 'Fixed Currency Prices', 'woocommerce-currency-switcher' ) . '</strong></p>';

        foreach ( $this->currencies as $code => $symbol ) {
            // Skip default currency
            if ( $code === get_woocommerce_currency() ) {
                continue;
            }

            $meta_key = '_price_' . strtolower( $code );

            woocommerce_wp_text_input(
                [
                    'id'                => $meta_key,
                    'label'             => sprintf( __( 'Price (%s)', 'woocommerce-currency-switcher' ), $code . ' ' . $symbol ),
                    'description'       => sprintf( __( 'Fixed price in %s', 'woocommerce-currency-switcher' ), $code ),
                    'desc_tip'          => true,
                    'type'              => 'number',
                    'custom_attributes' => [
                        'step' => 'any',
                        'min'  => '0',
                    ],
                ]
            );
        }

        echo '</div>';
    }

    public function save_custom_price_fields( $post_id ) {
        foreach ( $this->currencies as $code => $symbol ) {
            // Skip default currency
            if ( $code === get_woocommerce_currency() ) {
                continue;
            }

            $meta_key = '_price_' . strtolower( $code );

            if ( isset( $_POST[ $meta_key ] ) ) {
                $price = wc_format_decimal( sanitize_text_field( $_POST[ $meta_key ] ) );
                update_post_meta( $post_id, $meta_key, $price );
            }
        }
    }

    public function add_variation_custom_price_fields( $loop, $variation_data, $variation ) {
        foreach ( $this->currencies as $code => $symbol ) {
            // Skip default currency
            if ( $code === get_woocommerce_currency() ) {
                continue;
            }

            $meta_key = '_price_' . strtolower( $code );
            $price    = get_post_meta( $variation->ID, $meta_key, true );

            woocommerce_wp_text_input(
                [
                    'id'                => $meta_key . '[' . $loop . ']',
                    'name'              => $meta_key . '[' . $loop . ']',
                    'value'             => $price,
                    'label'             => sprintf( __( 'Price (%s)', 'woocommerce-currency-switcher' ), $code . ' ' . $symbol ),
                    'description'       => sprintf( __( 'Fixed price in %s', 'woocommerce-currency-switcher' ), $code ),
                    'desc_tip'          => true,
                    'type'              => 'number',
                    'custom_attributes' => [
                        'step' => 'any',
                        'min'  => '0',
                    ],
                    'wrapper_class'     => 'form-row form-row-first',
                ]
            );
        }
    }

    public function save_variation_custom_price_fields( $variation_id, $loop ) {
        foreach ( $this->currencies as $code => $symbol ) {
            // Skip default currency
            if ( $code === get_woocommerce_currency() ) {
                continue;
            }

            $meta_key    = '_price_' . strtolower( $code );
            $price_array = isset( $_POST[ $meta_key ] ) ? $_POST[ $meta_key ] : [];

            if ( isset( $price_array[ $loop ] ) ) {
                $price = wc_format_decimal( sanitize_text_field( $price_array[ $loop ] ) );
                update_post_meta( $variation_id, $meta_key, $price );
            }
        }
    }

    public function get_custom_available_variation( $variation, $variable_product, $variation_obj ) {
        $current_currency = $this->get_current_currency();

        if ( $current_currency === get_woocommerce_currency() ) {
            return $variation;
        }

        $variation_id = $variation_obj->get_id();
        $meta_key     = '_price_' . strtolower( $current_currency );

        // Try to get variation-specific price
        $custom_price = get_post_meta( $variation_id, $meta_key, true );
        if ( empty( $custom_price ) ) {
            // If no variation price, try parent product
            $parent_id    = $variation_obj->get_parent_id();
            $custom_price = get_post_meta( $parent_id, $meta_key, true );
        }

        if ( ! empty( $custom_price ) ) {
            $price                              = (float) $custom_price;
            $variation['display_price']         = $price;
            $variation['display_regular_price'] = $price;
            $variation['price_html']            = wc_price( $price );
        }

        return $variation;
    }

    public function get_custom_variation_price_html( $price_html, $product ) {
        $current_currency = $this->get_current_currency();

        if ( $current_currency === get_woocommerce_currency() ) {
            return $price_html;
        }

        $variation_id = $product->get_id();
        $meta_key     = '_price_' . strtolower( $current_currency );

        // Try to get variation-specific price
        $custom_price = get_post_meta( $variation_id, $meta_key, true );
        if ( empty( $custom_price ) ) {
            // If no variation price, try parent product
            $parent_id    = $product->get_parent_id();
            $custom_price = get_post_meta( $parent_id, $meta_key, true );
        }

        if ( ! empty( $custom_price ) ) {
            $price         = (float) $custom_price;
            $regular_price = (float) $custom_price;
            $sale_price    = (float) $custom_price;

            if ( $sale_price < $regular_price ) {
                $price_html = wc_format_sale_price( $regular_price, $sale_price );
            } else {
                $price_html = wc_price( $price );
            }
        }

        return $price_html;
    }

    public function get_custom_variation_prices( $prices, $product ) {
        $current_currency = $this->get_current_currency();

        if ( $current_currency === get_woocommerce_currency() ) {
            return $prices;
        }

        $variation_prices         = [];
        $variation_regular_prices = [];
        $variation_sale_prices    = [];

        foreach ( $prices['price'] as $variation_id => $price ) {
            $variation = wc_get_product( $variation_id );
            if ( $variation ) {
                $meta_key = '_price_' . strtolower( $current_currency );

                // Try to get variation-specific price
                $custom_price = get_post_meta( $variation_id, $meta_key, true );
                if ( empty( $custom_price ) ) {
                    // If no variation price, try parent product
                    $parent_id    = $variation->get_parent_id();
                    $custom_price = get_post_meta( $parent_id, $meta_key, true );
                }

                if ( ! empty( $custom_price ) ) {
                    $variation_prices[ $variation_id ]         = (float) $custom_price;
                    $variation_regular_prices[ $variation_id ] = (float) $custom_price;
                    $variation_sale_prices[ $variation_id ]    = (float) $custom_price;
                } else {
                    $variation_prices[ $variation_id ]         = $price;
                    $variation_regular_prices[ $variation_id ] = $prices['regular_price'][ $variation_id ];
                    $variation_sale_prices[ $variation_id ]    = $prices['sale_price'][ $variation_id ];
                }
            }
        }

        return [
            'price'         => $variation_prices,
            'regular_price' => $variation_regular_prices,
            'sale_price'    => $variation_sale_prices,
        ];
    }

    public function get_custom_price( $price, $product ) {
        static $recursion = false;

        if ( $recursion ) {
            return $price;
        }

        $recursion = true;

        try {
            $current_currency = $this->get_current_currency();

            // If using default currency, return original price
            if ( $current_currency === get_woocommerce_currency() ) {
                return $price;
            }

            // Get the product ID
            $product_id = $product->get_id();

            // For variations, try to get variation-specific price first
            if ( $product->is_type( 'variation' ) ) {
                $meta_key = '_price_' . strtolower( $current_currency );

                // First try to get variation-specific price
                $variation_price = get_post_meta( $product_id, $meta_key, true );
                if ( ! empty( $variation_price ) ) {
                    return (float) $variation_price;
                }

                // If no variation-specific price, try parent product
                $parent_id    = $product->get_parent_id();
                $parent_price = get_post_meta( $parent_id, $meta_key, true );
                if ( ! empty( $parent_price ) ) {
                    return (float) $parent_price;
                }

                // If no custom prices found, return original price
                return $price;
            } else {
                // For regular products
                $meta_key     = '_price_' . strtolower( $current_currency );
                $custom_price = get_post_meta( $product_id, $meta_key, true );
                if ( ! empty( $custom_price ) ) {
                    return (float) $custom_price;
                }
            }
        } finally {
            $recursion = false;
        }

        // If no custom price, return original price
        return $price;
    }

    public function get_custom_cart_item_price( $price, $cart_item, $cart_item_key ) {
        $product = $cart_item['data'];
        return $this->get_custom_price( $price, $product );
    }

    public function get_custom_cart_item_subtotal( $subtotal, $cart_item, $cart_item_key ) {
        $product = $cart_item['data'];
        $price   = $this->get_custom_price( $product->get_price(), $product );
        return wc_price( $price * $cart_item['quantity'] );
    }

    public function change_currency_symbol( $symbol ) {
        static $recursion = false;

        if ( $recursion ) {
            return $symbol;
        }

        $recursion = true;

        try {
            // Only change symbol on frontend
            if ( ! is_admin() ) {
                $current_currency = $this->get_current_currency();
                if ( isset( $this->currencies[ $current_currency ] ) ) {
                    return $this->currencies[ $current_currency ];
                }
            }
        } finally {
            $recursion = false;
        }

        return $symbol;
    }

    public function auto_detect_currency() {
        // Only run on frontend and if no currency is set
        if ( is_admin() || get_option( 'wc_currency_switcher_currency' ) ) {
            return;
        }

        // Check if auto-detection is enabled
        if ( '1' !== get_option( 'wc_currency_switcher_auto_detect', '0' ) ) {
            return;
        }

        // Check if we have a cached country
        $user_country = get_transient( 'wc_currency_switcher_user_country' );

        if ( ! $user_country ) {
            // Use WooCommerce's MaxMind GeoIP
            if ( class_exists( 'WC_Geolocation' ) ) {
                $location = WC_Geolocation::geolocate_ip();

                if ( $location && isset( $location['country'] ) ) {
                    $user_country = $location['country'];
                    // Cache the country for 24 hours
                    set_transient( 'wc_currency_switcher_user_country', $user_country, DAY_IN_SECONDS );
                }
            }
        }

        // Map country to currency
        if ( $user_country ) {
            $currency_map = [
                // Eurozone countries
                'AT' => 'EUR', // Austria
                'BE' => 'EUR', // Belgium
                'CY' => 'EUR', // Cyprus
                'EE' => 'EUR', // Estonia
                'FI' => 'EUR', // Finland
                'FR' => 'EUR', // France
                'DE' => 'EUR', // Germany
                'GR' => 'EUR', // Greece
                'IE' => 'EUR', // Ireland
                'IT' => 'EUR', // Italy
                'LV' => 'EUR', // Latvia
                'LT' => 'EUR', // Lithuania
                'LU' => 'EUR', // Luxembourg
                'MT' => 'EUR', // Malta
                'NL' => 'EUR', // Netherlands
                'PT' => 'EUR', // Portugal
                'SK' => 'EUR', // Slovakia
                'SI' => 'EUR', // Slovenia
                'ES' => 'EUR', // Spain

            // GBP countries
                'GB' => 'GBP', // United Kingdom
                'IM' => 'GBP', // Isle of Man
                'JE' => 'GBP', // Jersey
                'GG' => 'GBP', // Guernsey

            // USD countries and territories
                'US' => 'USD', // United States
                'AS' => 'USD', // American Samoa
                'GU' => 'USD', // Guam
                'MP' => 'USD', // Northern Mariana Islands
                'PR' => 'USD', // Puerto Rico
                'VI' => 'USD', // U.S. Virgin Islands
            ];

            if ( isset( $currency_map[ $user_country ] ) ) {
                $detected_currency = $currency_map[ $user_country ];

                // Only set if it's a supported currency
                if ( isset( $this->currencies[ $detected_currency ] ) ) {
                    update_option( 'wc_currency_switcher_currency', $detected_currency );
                }
            }
        }
    }

    public function maybe_set_session_currency() {
        if ( is_admin() || ! WC()->session ) {
            return;
        }

        // If session already has the currency, do nothing
        if ( WC()->session->get( 'wcc_selected_currency' ) ) {
            return;
        }

        // Ensure session is started if needed
        if ( ! WC()->session->has_session() ) {
            WC()->session->set_customer_session_cookie( true );
        }

        // Get currency from option (safe here, not inside filter)
        $option_currency = get_option( 'wc_currency_switcher_currency' );

        if ( ! empty( $option_currency ) && array_key_exists( $option_currency, get_woocommerce_currencies() ) ) {
            WC()->session->set( 'wcc_selected_currency', $option_currency );
        }
    }

    public function add_settings_tab( $settings_tabs ) {
        $settings_tabs['currency_switcher'] = __( 'Currency Switcher', 'woocommerce-currency-switcher' );
        return $settings_tabs;
    }

    public function settings_tab() {
        // Check if WooCommerce is active
        if ( ! class_exists( 'WooCommerce' ) ) {
            echo '<div class="notice notice-error"><p>' . __( 'WooCommerce is not active. Please install and activate WooCommerce to use this plugin.', 'woocommerce-currency-switcher' ) . '</p></div>';
            return;
        }

        // Get all settings
        $settings = $this->get_settings();
        woocommerce_admin_fields( $settings );

        // Get gateway settings
        $gateway_settings = get_option( 'wc_currency_switcher_gateway_settings', [] );

        // Get all WooCommerce payment gateways
        $gateways = WC()->payment_gateways->payment_gateways();

        // Get all available currencies
        $currencies = get_woocommerce_currencies();

        // Display the gateway settings form
        ?>
        <h2><?php _e( 'Payment Gateway Settings', 'woocommerce-currency-switcher' ); ?></h2>
        <p><?php _e( 'Configure which payment gateways are available for each currency.', 'woocommerce-currency-switcher' ); ?></p>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e( 'Payment Gateway', 'woocommerce-currency-switcher' ); ?></th>
                    <th><?php _e( 'Method', 'woocommerce-currency-switcher' ); ?></th>
                    <th><?php _e( 'Supported Currencies', 'woocommerce-currency-switcher' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ( $gateways as $gateway_id => $gateway ) :
                    // Get enabled currencies for this gateway
                    $enabled_currencies = [];
                    foreach ( $currencies as $currency_code => $currency_name ) {
                        $key = $gateway_id . '_' . $currency_code;
                        if ( isset( $gateway_settings[ $key ] ) && $gateway_settings[ $key ] ) {
                            $enabled_currencies[] = $currency_code;
                        }
                    }
                    ?>
                    <tr>
                        <td><?php echo esc_html( $gateway->title ); ?></td>
                        <td><code><?php echo esc_html( $gateway_id ); ?></code></td>
                        <td>
                            <select class="wc-enhanced-select" 
                                    name="gateway_<?php echo esc_attr( $gateway_id ); ?>[]" 
                                    multiple="multiple" 
                                    style="width: 100%;">
                                <?php foreach ( $currencies as $currency_code => $currency_name ) : ?>
                                    <option value="<?php echo esc_attr( $currency_code ); ?>" 
                                            <?php echo in_array( $currency_code, $enabled_currencies ) ? 'selected="selected"' : ''; ?>>
                                        <?php echo esc_html( $currency_name ); ?> (<?php echo esc_html( $currency_code ); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('.wc-enhanced-select').select2({
                    placeholder: '<?php _e( 'Select currencies...', 'woocommerce-currency-switcher' ); ?>',
                    allowClear: true
                });
            });
        </script>
        <?php
    }

    public function update_settings() {
        woocommerce_update_options( $this->get_settings() );

        // Update currencies array with WooCommerce's native symbols
        $currency_codes = get_option( 'wc_currency_switcher_currency_codes', [] );
        $currencies     = [];

        foreach ( $currency_codes as $code ) {
            if ( ! empty( $code ) ) {
                $currencies[ $code ] = get_woocommerce_currency_symbol( $code );
            }
        }

        update_option( 'wc_currency_switcher_currencies', $currencies );

        // Update gateway settings
        $gateways     = WC()->payment_gateways->payment_gateways();
        $new_settings = [];
        foreach ( $gateways as $gateway_id => $gateway ) {
            if ( isset( $_POST[ 'gateway_' . $gateway_id ] ) ) {
                $selected_currencies = array_map( 'sanitize_text_field', $_POST[ 'gateway_' . $gateway_id ] );
                foreach ( $selected_currencies as $currency_code ) {
                    $key                  = $gateway_id . '_' . $currency_code;
                    $new_settings[ $key ] = 1;
                }
            }
        }

        update_option( 'wc_currency_switcher_gateway_settings', $new_settings );
    }

    public function admin_scripts( $hook ) {
        if ( 'woocommerce_page_wc-settings' !== $hook ) {
            return;
        }

        wp_enqueue_script( 'wc-enhanced-select' );
        wp_enqueue_style( 'woocommerce_admin_styles' );
    }

    public function get_settings() {
        $settings = [
            'section_title'  => [
                'name' => __( 'Currency Settings', 'woocommerce-currency-switcher' ),
                'type' => 'title',
                'desc' => sprintf(
                    __( '<div class="woocommerce-settings-notice"><p><strong>How this plugin works:</strong></p><p>This is not a traditional currency switcher that automatically converts prices using exchange rates. Instead, it allows you to set fixed prices for each product in different currencies:</p><ol><li>Select the currencies you want to support below</li><li>For each product, you can set specific prices in each supported currency</li><li>When a customer switches currency, they will see the exact price you set for that currency</li><li>This is useful when you want to set specific prices for different markets or regions</li></ol><p><strong>Example:</strong> If you sell a product for $10 USD, you can set it to €8 EUR for European customers, £7 GBP for UK customers, etc. The prices are completely independent and not automatically converted.</p></div>', 'woocommerce-currency-switcher' )
                ),
                'id'   => 'wc_currency_switcher_section_title',
            ],
            'currency_codes' => [
                'name'              => __( 'Currency Codes', 'woocommerce-currency-switcher' ),
                'type'              => 'multiselect',
                'class'             => 'wc-enhanced-select',
                'css'               => 'min-width: 350px;',
                'desc'              => __( 'Select the currencies you want to support.', 'woocommerce-currency-switcher' ),
                'id'                => 'wc_currency_switcher_currency_codes',
                'options'           => get_woocommerce_currencies(),
                'default'           => [ 'EUR', 'GBP', 'USD' ],
                'custom_attributes' => [
                    'data-placeholder' => __( 'Select currencies', 'woocommerce-currency-switcher' ),
                ],
            ],
            'theme'          => [
                'name'    => __( 'Theme', 'woocommerce-currency-switcher' ),
                'type'    => 'select',
                'desc'    => __( 'Choose the theme for the currency switcher.', 'woocommerce-currency-switcher' ),
                'id'      => 'wc_currency_switcher_theme',
                'options' => [
                    'light' => __( 'Light', 'woocommerce-currency-switcher' ),
                    'dark'  => __( 'Dark', 'woocommerce-currency-switcher' ),
                ],
                'default' => 'light',
            ],
            'position'       => [
                'name'    => __( 'Position', 'woocommerce-currency-switcher' ),
                'type'    => 'select',
                'desc'    => __( 'Choose the position of the currency switcher on the frontend.', 'woocommerce-currency-switcher' ),
                'id'      => 'wc_currency_switcher_position',
                'options' => [
                    'center-left'   => __( 'Center Left', 'woocommerce-currency-switcher' ),
                    'bottom-left'   => __( 'Bottom Left', 'woocommerce-currency-switcher' ),
                    'middle-bottom' => __( 'Middle Bottom', 'woocommerce-currency-switcher' ),
                    'bottom-right'  => __( 'Bottom Right', 'woocommerce-currency-switcher' ),
                    'center-right'  => __( 'Center Right', 'woocommerce-currency-switcher' ),
                ],
                'default' => 'bottom-right',
            ],
            'auto_detect'    => [
                'name'    => __( 'Auto-detect Currency', 'woocommerce-currency-switcher' ),
                'type'    => 'checkbox',
                'desc'    => sprintf(
                    __( 'Automatically detect and set currency based on customer location.<br><br><strong>Important:</strong> For auto-detection to work, you need to:<br>1. Install and configure the MaxMind GeoIP library in WooCommerce<br>2. Set "Default customer location" to "Geolocate" in <a href="%1$s">WooCommerce > Settings > General</a><br><br>For more information, please read the <a href="%2$s" target="_blank">WooCommerce documentation on geolocation</a>.', 'woocommerce-currency-switcher' ),
                    admin_url( 'admin.php?page=wc-settings&tab=general' ),
                    'https://woocommerce.com/document/maxmind-geolocation-integration/'
                ),
                'id'      => 'wc_currency_switcher_auto_detect',
                'default' => '0',
            ],
            'section_end'    => [
                'type' => 'sectionend',
                'id'   => 'wc_currency_switcher_section_end',
            ],
        ];

        return apply_filters( 'wc_currency_switcher_settings', $settings );
    }

    public function shortcode_currency_switcher( $atts ) {
        // Get current currency and theme
        $current_currency = $this->get_current_currency();
        $current_theme    = get_option( 'wc_currency_switcher_theme', 'light' );

        // Output the switcher
        $out = '<div class="currency-switcher-shortcode theme-' . esc_attr( $current_theme ) . '">
            <select id="currency-select-shortcode">';

        foreach ( $this->currencies as $code => $symbol ) {
            $out .= '<option value="' . esc_attr( $code ) . '" ' . selected( $code, $current_currency, false ) . '>' .
                esc_html( $code . ' ' . $symbol ) .
            '</option>';
        }
            $out .= '</select>
        </div>';

        return $out;
    }

    public function filter_gateways_based_on_currency( $gateway_list ) {
        // First filter by product-specific settings if we're on the checkout page
        if ( is_checkout() && WC()->cart ) {
            $cart_items                = WC()->cart->get_cart();
            $product_specific_gateways = [];

            // Get allowed gateways for each product in cart
            foreach ( $cart_items as $cart_item ) {
                $product_id       = $cart_item['product_id'];
                $allowed_gateways = get_post_meta( $product_id, '_allowed_payment_gateways', true );

                if ( ! empty( $allowed_gateways ) && is_array( $allowed_gateways ) ) {
                    if ( empty( $product_specific_gateways ) ) {
                        $product_specific_gateways = $allowed_gateways;
                    } else {
                        // Only keep gateways that are allowed for all products
                        $product_specific_gateways = array_intersect( $product_specific_gateways, $allowed_gateways );
                    }
                }
            }

            // If we have product-specific gateways, filter the list and return
            if ( ! empty( $product_specific_gateways ) ) {
                foreach ( $gateway_list as $gateway_id => $gateway ) {
                    if ( ! in_array( $gateway_id, $product_specific_gateways ) ) {
                        unset( $gateway_list[ $gateway_id ] );
                    }
                }
                return $gateway_list;
            }
        }

        // Only filter by currency if no product-specific settings are in effect
        $current_currency = $this->get_current_currency();
        $gateway_settings = get_option( 'wc_currency_switcher_gateway_settings', [] );

        foreach ( $gateway_list as $gateway_id => $gateway ) {
            $key = $gateway_id . '_' . $current_currency;
            if ( ! isset( $gateway_settings[ $key ] ) || ! $gateway_settings[ $key ] ) {
                unset( $gateway_list[ $gateway_id ] );
            }
        }

        return $gateway_list;
    }

    public function append_gateway_method_to_title( $title, $gateway_id ) {
        // Get the gateway object
        $gateways = WC()->payment_gateways->payment_gateways();
        if ( isset( $gateways[ $gateway_id ] ) ) {
            $gateway = $gateways[ $gateway_id ];
            // Get the gateway name from the gateway object
            $gateway_name = $gateway->get_method_title();
            // Only append if the name is different from the title
            if ( $gateway_name !== $title ) {
                return $title . ' (' . $gateway_name . ')';
            }
        }
        return $title;
    }

    public function add_product_gateway_tab( $tabs ) {
        $tabs['payment_gateways'] = [
            'label'    => __( 'Payment Gateways', 'woocommerce-currency-switcher' ),
            'target'   => 'payment_gateways_product_data',
            'class'    => [ 'show_if_simple', 'show_if_variable' ],
            'priority' => 70,
        ];
        return $tabs;
    }

    public function add_product_gateway_settings() {
        global $post;

        // Get all available payment gateways
        $gateways = WC()->payment_gateways->payment_gateways();

        // Get saved allowed gateways for this product
        $allowed_gateways = get_post_meta( $post->ID, '_allowed_payment_gateways', true );
        if ( ! is_array( $allowed_gateways ) ) {
            $allowed_gateways = [];
        }

        echo '<div id="payment_gateways_product_data" class="panel woocommerce_options_panel">';
        echo '<div class="options_group">';
        echo '<p class="form-field"><strong>' . __( 'Payment Gateways', 'woocommerce-currency-switcher' ) . '</strong></p>';
        echo '<p class="description">' . __( 'Select which payment gateways are allowed for this product. Leave empty to allow all gateways.', 'woocommerce-currency-switcher' ) . '</p>';

        foreach ( $gateways as $gateway_id => $gateway ) {
            $label = $gateway->get_method_title();
            if ( empty( $label ) ) {
                $label = $gateway->title;
            }
            $label .= ' (' . $gateway_id . ')';

            woocommerce_wp_checkbox(
                [
                    'id'          => 'allowed_payment_gateways[' . $gateway_id . ']',
                    'label'       => $label,
                    'description' => $gateway->get_description(),
                    'value'       => in_array( $gateway_id, $allowed_gateways ) ? 'yes' : 'no',
                ]
            );
        }

        echo '</div>';
        echo '</div>';
    }

    public function save_product_gateway_settings( $post_id ) {
        if ( isset( $_POST['allowed_payment_gateways'] ) ) {
            $allowed_gateways = array_keys( array_filter( $_POST['allowed_payment_gateways'] ) );
            update_post_meta( $post_id, '_allowed_payment_gateways', $allowed_gateways );
        } else {
            update_post_meta( $post_id, '_allowed_payment_gateways', [] );
        }
    }

    public function set_transactional_currency( $currency ) {
        // Don't change currency in admin or if session unavailable
        if ( is_admin() || ! WC()->session ) {
            return $currency;
        }

        // Get currency from session
        $selected_currency = WC()->session->get( 'wcc_selected_currency' );

        // If session has a value and it's a valid WooCommerce currency, use it
        if ( ! empty( $selected_currency ) && array_key_exists( $selected_currency, get_woocommerce_currencies() ) ) {
            return $selected_currency;
        }

        // Otherwise, return the original currency (likely store default)
        return $currency;
    }
}

// Initialize the plugin
add_action( 'plugins_loaded', [ 'WC_Currency_Switcher', 'get_instance' ] );
