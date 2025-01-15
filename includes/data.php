<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WOOMULTI_CURRENCY_F_Data {
    private $params;

    /**
     * WOOMULTI_CURRENCY_F_Data constructor.
     * Init setting
     */
    public function __construct() {

        global $wmc_settings;

        if ( ! $wmc_settings ) {
            $wmc_settings                  = get_option( 'woo_multi_currency_params', [] );
            $wmc_settings['currency_core'] = get_option( 'woocommerce_currency' );
            $wmc_settings['decimals_core'] = get_option( 'woocommerce_price_num_decimals' );
        }
        $this->params = $wmc_settings;
        $args         = [
            'enable'               => 0,
            'enable_fixed_price'   => 0,
            'currency_default'     => $wmc_settings['currency_core'],
            'currency'             => [ $wmc_settings['currency_core'] ],
            'currency_rate'        => [ 1 ],
            'currency_decimals'    => [ $wmc_settings['decimals_core'] ],
            'currency_custom'      => [],
            'currency_pos'         => [],
            'enable_multi_payment' => 0,
            'key'                  => '',
            'update_exchange_rate' => '',
            'enable_design'        => 0,
            'title'                => '',
            'design_position'      => 0,
            'text_color'           => '#fff',
            'background_color'     => '#212121',
            'main_color'           => '#f78080',
            'is_checkout'          => 0,
            'is_cart'              => 0,
            'custom_css'           => '',
            'rate_decimals'        => 2,

        ];
        $this->params = apply_filters( 'wmc_settings_args', wp_parse_args( $this->params, $args ) );
    }

    /**
     * Check  hidden on cart page
     * @return mixed|void
     */
    public function is_cart() {
        return apply_filters( 'wmc_is_cart', $this->params['is_cart'] );
    }

    /**
     * Check  hidden on checkout page
     * @return mixed|void
     */
    public function is_checkout() {
        return apply_filters( 'wmc_is_checkout', $this->params['is_checkout'] );
    }

    /**
     * Get custom CSS
     * @return mixed|void
     */
    public function get_rate_decimals() {
        return apply_filters( 'wmc_get_rate_decimals', $this->params['rate_decimals'] );
    }

    /**
     * Get custom CSS
     * @return mixed|void
     */
    public function get_custom_css() {
        return apply_filters( 'wmc_get_custom_css', $this->params['custom_css'] );
    }

    /**
     * get_country_currency.
     *
     * 237 countries.
     * Two-letter country code (ISO 3166-1 alpha-2) => Three-letter currency code (ISO 4217).
     */
    function get_currency_code( $country_code ) {
        if ( ! $country_code ) {
            return false;
        }
        $arg = [
            'AF' => 'AFN',
            'AL' => 'ALL',
            'DZ' => 'DZD',
            'AS' => 'USD',
            'AD' => 'EUR',
            'AO' => 'AOA',
            'AI' => 'XCD',
            'AQ' => 'XCD',
            'AG' => 'XCD',
            'AR' => 'ARS',
            'AM' => 'AMD',
            'AW' => 'AWG',
            'AU' => 'AUD',
            'AT' => 'EUR',
            'AZ' => 'AZN',
            'BS' => 'BSD',
            'BH' => 'BHD',
            'BD' => 'BDT',
            'BB' => 'BBD',
            'BY' => 'BYR',
            'BE' => 'EUR',
            'BZ' => 'BZD',
            'BJ' => 'XOF',
            'BM' => 'BMD',
            'BT' => 'BTN',
            'BO' => 'BOB',
            'BA' => 'BAM',
            'BW' => 'BWP',
            'BV' => 'NOK',
            'BR' => 'BRL',
            'IO' => 'USD',
            'BN' => 'BND',
            'BG' => 'BGN',
            'BF' => 'XOF',
            'BI' => 'BIF',
            'KH' => 'KHR',
            'CM' => 'XAF',
            'CA' => 'CAD',
            'CV' => 'CVE',
            'KY' => 'KYD',
            'CF' => 'XAF',
            'TD' => 'XAF',
            'CL' => 'CLP',
            'CN' => 'CNY',
            'HK' => 'HKD',
            'CX' => 'AUD',
            'CC' => 'AUD',
            'CO' => 'COP',
            'KM' => 'KMF',
            'CG' => 'XAF',
            'CD' => 'CDF',
            'CK' => 'NZD',
            'CR' => 'CRC',
            'HR' => 'HRK',
            'CU' => 'CUP',
            'CY' => 'EUR',
            'CZ' => 'CZK',
            'DK' => 'DKK',
            'DJ' => 'DJF',
            'DM' => 'XCD',
            'DO' => 'DOP',
            'EC' => 'ECS',
            'EG' => 'EGP',
            'SV' => 'SVC',
            'GQ' => 'XAF',
            'ER' => 'ERN',
            'EE' => 'EUR',
            'ET' => 'ETB',
            'FK' => 'FKP',
            'FO' => 'DKK',
            'FJ' => 'FJD',
            'FI' => 'EUR',
            'FR' => 'EUR',
            'GF' => 'EUR',
            'TF' => 'EUR',
            'GA' => 'XAF',
            'GM' => 'GMD',
            'GE' => 'GEL',
            'DE' => 'EUR',
            'GH' => 'GHS',
            'GI' => 'GIP',
            'GR' => 'EUR',
            'GL' => 'DKK',
            'GD' => 'XCD',
            'GP' => 'EUR',
            'GU' => 'USD',
            'GT' => 'QTQ',
            'GG' => 'GGP',
            'GN' => 'GNF',
            'GW' => 'GWP',
            'GY' => 'GYD',
            'HT' => 'HTG',
            'HM' => 'AUD',
            'HN' => 'HNL',
            'HU' => 'HUF',
            'IS' => 'ISK',
            'IN' => 'INR',
            'ID' => 'IDR',
            'IR' => 'IRR',
            'IQ' => 'IQD',
            'IE' => 'EUR',
            'IM' => 'GBP',
            'IL' => 'ILS',
            'IT' => 'EUR',
            'JM' => 'JMD',
            'JP' => 'JPY',
            'JE' => 'GBP',
            'JO' => 'JOD',
            'KZ' => 'KZT',
            'KE' => 'KES',
            'KI' => 'AUD',
            'KP' => 'KPW',
            'KR' => 'KRW',
            'KW' => 'KWD',
            'KG' => 'KGS',
            'LA' => 'LAK',
            'LV' => 'EUR',
            'LB' => 'LBP',
            'LS' => 'LSL',
            'LR' => 'LRD',
            'LY' => 'LYD',
            'LI' => 'CHF',
            'LT' => 'EUR',
            'LU' => 'EUR',
            'MK' => 'MKD',
            'MG' => 'MGF',
            'MW' => 'MWK',
            'MY' => 'MYR',
            'MV' => 'MVR',
            'ML' => 'XOF',
            'MT' => 'EUR',
            'MH' => 'USD',
            'MQ' => 'EUR',
            'MR' => 'MRO',
            'MU' => 'MUR',
            'YT' => 'EUR',
            'MX' => 'MXN',
            'FM' => 'USD',
            'MD' => 'MDL',
            'MC' => 'EUR',
            'MN' => 'MNT',
            'ME' => 'EUR',
            'MS' => 'XCD',
            'MA' => 'MAD',
            'MZ' => 'MZN',
            'MM' => 'MMK',
            'NA' => 'NAD',
            'NR' => 'AUD',
            'NP' => 'NPR',
            'NL' => 'EUR',
            'AN' => 'ANG',
            'NC' => 'XPF',
            'NZ' => 'NZD',
            'NI' => 'NIO',
            'NE' => 'XOF',
            'NG' => 'NGN',
            'NU' => 'NZD',
            'NF' => 'AUD',
            'MP' => 'USD',
            'NO' => 'NOK',
            'OM' => 'OMR',
            'PK' => 'PKR',
            'PW' => 'USD',
            'PA' => 'PAB',
            'PG' => 'PGK',
            'PY' => 'PYG',
            'PE' => 'PEN',
            'PH' => 'PHP',
            'PN' => 'NZD',
            'PL' => 'PLN',
            'PT' => 'EUR',
            'PR' => 'USD',
            'QA' => 'QAR',
            'RE' => 'EUR',
            'RO' => 'RON',
            'RU' => 'RUB',
            'RW' => 'RWF',
            'SH' => 'SHP',
            'KN' => 'XCD',
            'LC' => 'XCD',
            'PM' => 'EUR',
            'VC' => 'XCD',
            'WS' => 'WST',
            'SM' => 'EUR',
            'ST' => 'STD',
            'SA' => 'SAR',
            'SN' => 'XOF',
            'RS' => 'RSD',
            'SC' => 'SCR',
            'SL' => 'SLL',
            'SG' => 'SGD',
            'SK' => 'EUR',
            'SI' => 'EUR',
            'SB' => 'SBD',
            'SO' => 'SOS',
            'ZA' => 'ZAR',
            'GS' => 'GBP',
            'SS' => 'SSP',
            'ES' => 'EUR',
            'LK' => 'LKR',
            'SD' => 'SDG',
            'SR' => 'SRD',
            'SJ' => 'NOK',
            'SZ' => 'SZL',
            'SE' => 'SEK',
            'CH' => 'CHF',
            'SY' => 'SYP',
            'TW' => 'TWD',
            'TJ' => 'TJS',
            'TZ' => 'TZS',
            'TH' => 'THB',
            'TG' => 'XOF',
            'TK' => 'NZD',
            'TO' => 'TOP',
            'TT' => 'TTD',
            'TN' => 'TND',
            'TR' => 'TRY',
            'TM' => 'TMT',
            'TC' => 'USD',
            'TV' => 'AUD',
            'UG' => 'UGX',
            'UA' => 'UAH',
            'AE' => 'AED',
            'GB' => 'GBP',
            'US' => 'USD',
            'UM' => 'USD',
            'UY' => 'UYU',
            'UZ' => 'UZS',
            'VU' => 'VUV',
            'VE' => 'VEF',
            'VN' => 'VND',
            'VI' => 'USD',
            'WF' => 'XPF',
            'EH' => 'MAD',
            'YE' => 'YER',
            'ZM' => 'ZMW',
            'ZW' => 'ZWD',
        ];

        return apply_filters( 'wmc_get_currency_code', $arg[ $country_code ], $arg, $country_code );
    }

    /**
     * Get country code by currency
     *
     * @param $currency_code
     */
    public function get_country_data( $currency_code ) {
        $countries     = [
            'AFN' => 'AF',
            'ALL' => 'AL',
            'DZD' => 'DZ',
            'USD' => 'US',
            'EUR' => 'EU',
            'AOA' => 'AO',
            'XCD' => 'LC',
            'ARS' => 'AR',
            'AMD' => 'AM',
            'AWG' => 'AW',
            'AUD' => 'AU',
            'AZN' => 'AZ',
            'BSD' => 'BS',
            'BHD' => 'BH',
            'BDT' => 'BD',
            'BBD' => 'BB',
            'BYR' => 'BY',
            'BZD' => 'BZ',
            'XOF' => 'BJ',
            'BMD' => 'BM',
            'BTN' => 'BT',
            'BOB' => 'BO',
            'BAM' => 'BA',
            'BWP' => 'BW',
            'NOK' => 'NO',
            'BRL' => 'BR',
            'BND' => 'BN',
            'BGN' => 'BG',
            'BIF' => 'BI',
            'KHR' => 'KH',
            'XAF' => 'CM',
            'CAD' => 'CA',
            'CVE' => 'CV',
            'KYD' => 'KY',
            'CLP' => 'CL',
            'CNY' => 'CN',
            'HKD' => 'HK',
            'COP' => 'CO',
            'KMF' => 'KM',
            'CDF' => 'CD',
            'NZD' => 'NZ',
            'CRC' => 'CR',
            'HRK' => 'HR',
            'CUP' => 'CU',
            'CZK' => 'CZ',
            'DKK' => 'DK',
            'DJF' => 'DJ',
            'DOP' => 'DO',
            'ECS' => 'EC',
            'EGP' => 'EG',
            'SVC' => 'SV',
            'ERN' => 'ER',
            'ETB' => 'ET',
            'FKP' => 'FK',
            'FJD' => 'FJ',
            'GMD' => 'GM',
            'GEL' => 'GE',
            'GHS' => 'GH',
            'GIP' => 'GI',
            'QTQ' => 'GT',
            'GGP' => 'GG',
            'GNF' => 'GN',
            'GWP' => 'GW',
            'GYD' => 'GY',
            'HTG' => 'HT',
            'HNL' => 'HN',
            'HUF' => 'HU',
            'ISK' => 'IS',
            'INR' => 'IN',
            'IDR' => 'ID',
            'IRR' => 'IR',
            'IQD' => 'IQ',
            'GBP' => 'GB',
            'ILS' => 'IL',
            'JMD' => 'JM',
            'JPY' => 'JP',
            'JOD' => 'JO',
            'KZT' => 'KZ',
            'KES' => 'KE',
            'KPW' => 'KP',
            'KRW' => 'KR',
            'KWD' => 'KW',
            'KGS' => 'KG',
            'LAK' => 'LA',
            'LBP' => 'LB',
            'LSL' => 'LS',
            'LRD' => 'LR',
            'LYD' => 'LY',
            'CHF' => 'CH',
            'MKD' => 'MK',
            'MGF' => 'MG',
            'MWK' => 'MW',
            'MYR' => 'MY',
            'MVR' => 'MV',
            'MRO' => 'MR',
            'MUR' => 'MU',
            'MXN' => 'MX',
            'MDL' => 'MD',
            'MNT' => 'MN',
            'MAD' => 'MA',
            'MZN' => 'MZ',
            'MMK' => 'MM',
            'NAD' => 'NA',
            'NPR' => 'NP',
            'ANG' => 'AN',
            'XPF' => 'WF',
            'NIO' => 'NI',
            'NGN' => 'NG',
            'OMR' => 'OM',
            'PKR' => 'PK',
            'PAB' => 'PA',
            'PGK' => 'PG',
            'PYG' => 'PY',
            'PEN' => 'PE',
            'PHP' => 'PH',
            'PLN' => 'PL',
            'QAR' => 'QA',
            'RON' => 'RO',
            'RUB' => 'RU',
            'RWF' => 'RW',
            'SHP' => 'SH',
            'WST' => 'WS',
            'STD' => 'ST',
            'SAR' => 'SA',
            'RSD' => 'RS',
            'SCR' => 'SC',
            'SLL' => 'SL',
            'SGD' => 'SG',
            'SBD' => 'SB',
            'SOS' => 'SO',
            'ZAR' => 'ZA',
            'SSP' => 'SS',
            'LKR' => 'LK',
            'SDG' => 'SD',
            'SRD' => 'SR',
            'SZL' => 'SZ',
            'SEK' => 'SE',
            'SYP' => 'SY',
            'TWD' => 'TW',
            'TJS' => 'TJ',
            'TZS' => 'TZ',
            'THB' => 'TH',
            'TOP' => 'TO',
            'TTD' => 'TT',
            'TND' => 'TN',
            'TRY' => 'TR',
            'TMT' => 'TM',
            'UGX' => 'UG',
            'UAH' => 'UA',
            'AED' => 'AE',
            'UYU' => 'UY',
            'UZS' => 'UZ',
            'VUV' => 'VU',
            'VEF' => 'VE',
            'VND' => 'VN',
            'YER' => 'YE',
            'ZMW' => 'ZM',
            'ZWD' => 'ZW',
        ];
        $country_names = WC()->countries->countries;
        $data          = [];
        if ( isset( $countries[ $currency_code ] ) && $currency_code ) {
            $data['code'] = $countries[ $currency_code ];
            switch ( $currency_code ) {
                case 'EUR':
                    $data['name'] = esc_attr__( 'Euro', 'woo-multi-currency' );
                    break;
                case 'USD':
                    $data['name'] = esc_attr__( 'US Dollar', 'woo-multi-currency' );
                    break;
                case 'GBP':
                    $data['name'] = esc_attr__( 'British Pound Sterling', 'woo-multi-currency' );
                    break;
                case 'CAD':
                    $data['name'] = esc_attr__( 'Canadian Dollar', 'woo-multi-currency' );
                    break;
                case 'AUD':
                    $data['name'] = esc_attr__( 'Australian Dollar', 'woo-multi-currency' );
                    break;
                case 'NZD':
                    $data['name'] = esc_attr__( 'New Zealand Dollar', 'woo-multi-currency' );
                    break;
                case 'NOK':
                    $data['name'] = esc_attr__( 'Norweigian Krone', 'woo-multi-currency' );
                    break;
                case 'SEK':
                    $data['name'] = esc_attr__( 'Swedish Krona', 'woo-multi-currency' );
                    break;
                case 'DKK':
                    $data['name'] = esc_attr__( 'Danish Krone', 'woo-multi-currency' );
                    break;
                default:
                    $data['name'] = isset( $country_names[ $countries[ $currency_code ] ] ) ? $country_names[ $countries[ $currency_code ] ] : 'Unknown';
            }
        } else {
            $data['code'] = '_unknown';
            $data['name'] = 'Unknown';
        }

        return $data;
    }


    /**
     * Get Links to redirect
     * @return array
     */
    public function get_links() {
        $links               = [];
        $selected_currencies = $this->get_list_currencies();
        $current_currency    = $this->get_current_currency();
        if ( count( $selected_currencies ) ) {
            foreach ( $selected_currencies as $k => $currency ) {
                /*Override min price and max price*/
                $arg = [ 'wmc-currency' => $k ];
                if ( $current_currency == $k ) {
                    if ( isset( $_GET['min_price'] ) ) {
                        $arg['min_price'] = $_GET['min_price'];
                    }
                    if ( isset( $_GET['max_price'] ) ) {
                        $arg['max_price'] = $_GET['max_price'];
                    }
                } else {
                    if ( isset( $_GET['min_price'] ) ) {
                        $arg['min_price'] = ( $_GET['min_price'] / $selected_currencies[ $current_currency ]['rate'] ) * $currency['rate'];
                    }
                    if ( isset( $_GET['max_price'] ) ) {
                        $arg['max_price'] = ( $_GET['max_price'] / $selected_currencies[ $current_currency ]['rate'] ) * $currency['rate'];
                    }
                }
                $link        = apply_filters( 'wmc_get_link', add_query_arg( $arg ), $k, $currency );
                $links[ $k ] = $link;
            }
        }

        return apply_filters( 'wmc_get_links', $links );
    }

    /**
     * List shortcodes on content
     * @return mixed|void
     */
    public function get_list_shortcodes() {
        return apply_filters(
            'wmc_get_list_shortcodes',
            [
                ''                 => esc_html__( 'Default', 'woo-multi-currency' ),
                'plain_horizontal' => esc_html__( 'Plain Horizontal', 'woo-multi-currency' ),
                'plain_vertical'   => esc_html__( 'Plain Vertical', 'woo-multi-currency' ),
                'layout3'          => esc_html__( 'List Flag Horizontal', 'woo-multi-currency' ),
                'layout4'          => esc_html__( 'List Flag Vertical', 'woo-multi-currency' ),
                'layout5'          => esc_html__( 'List Flag + Currency Code', 'woo-multi-currency' ),
                'layout6'          => esc_html__( 'Horizontal Currency Symbols', 'woo-multi-currency' ),
                'layout7'          => esc_html__( 'Vertical Currency Symbols', 'woo-multi-currency' ),
            ]
        );
    }

    /**
     * Check fixed price
     * @return mixed|void
     */
    public function check_fixed_price() {
        return apply_filters( 'wmc_check_fixed_price', $this->params['enable_fixed_price'] );
    }

    /**
     * Get title on design
     * @return mixed|void
     */
    public function get_design_title() {
        return apply_filters( 'wmc_get_design_title', $this->params['design_title'] );
    }

    /**
     * Get Main color
     * @return mixed|void
     */
    public function get_main_color() {
        return apply_filters( 'wmc_get_main_color', $this->params['main_color'] );
    }

    /**
     * Check design enable
     * @return mixed|void
     */
    public function get_enable_design() {
        if ( $this->params['enable_design'] && $this->params['enable'] ) {
            return apply_filters( 'wmc_get_enable_design', $this->params['enable_design'] );
        } else {
            return false;
        }
    }

    /**
     * Get design position
     * @return mixed|void
     */
    public function get_design_position() {
        return apply_filters( 'wmc_get_design_position', $this->params['design_position'] );
    }

    /**
     * Get text color on design
     * @return mixed|void
     */
    public function get_text_color() {
        return apply_filters( 'wmc_text_color', $this->params['text_color'] );
    }

    /**
     * Get backround color of design
     * @return mixed|void
     */
    public function get_background_color() {
        return apply_filters( 'wmc_background_color', $this->params['background_color'] );
    }

    /**
     * Set currency in Cookie
     *
     * @param     $currency_code
     * @param int $override
     */
    public function set_current_currency( $currency_code, $checkout = true ) {
        /*Clear cache with W3 total cache*/
        if ( is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) {
            w3tc_flush_all();
        }
        /*Clear WP super cache*/
        if ( is_plugin_active( 'wp-super-cache/wp-cache.php' ) ) {
            global $file_prefix, $cache_enabled;
            if ( isset( $cache_enabled ) && $cache_enabled == true && isset( $_GET['wmc-currency'] ) ) {
                wp_cache_clean_cache( $file_prefix );
            }
        }

        /*Clear Autoptimize cache*/
        if ( is_plugin_active( 'autoptimize/autoptimize.php' ) ) {
            autoptimizeCache::clearall();
        }

        /*Clear WP Fastest Cache*/
        if ( is_plugin_active( 'wp-fastest-cache/wpFastestCache.php' ) ) {
            $wpfc = new WpFastestCache();
            $wpfc->deleteCache();
        }

        /*Clear WP Rocket*/
        if ( is_plugin_active( 'wp-rocket/wp-rocket.php' ) ) {
            set_transient( 'rocket_clear_cache', 'all', HOUR_IN_SECONDS );
            // Remove all cache files.
            $lang = isset( $_GET['lang'] ) && 'all' !== $_GET['lang'] ? sanitize_key( $_GET['lang'] ) : '';
            // Remove all cache files.
            rocket_clean_domain( $lang );

            // Remove all minify cache files.
            rocket_clean_minify();

            // Remove cache busting files.
            rocket_clean_cache_busting();

            // Generate a new random key for minify cache file.
            $options                   = get_option( WP_ROCKET_SLUG );
            $options['minify_css_key'] = create_rocket_uniqid();
            $options['minify_js_key']  = create_rocket_uniqid();
            remove_all_filters( 'update_option_' . WP_ROCKET_SLUG );
            update_option( WP_ROCKET_SLUG, $options );

            //          rocket_dismiss_box( 'rocket_warning_plugin_modification' );

        }
        if ( $currency_code ) {
            setcookie( 'wmc_current_currency', $currency_code, time() + 60 * 60 * 24, '/' );
            $_COOKIE['wmc_current_currency'] = $currency_code;
        }
        if ( ! $this->get_enable_multi_payment() && $checkout ) {
            setcookie( 'wmc_current_currency_old', $currency_code, time() + 60 * 60 * 24, '/' );
            $_COOKIE['wmc_current_currency_old'] = $currency_code;
        }
    }

    /**
     * Get current currency
     * @return mixed
     */
    public function get_current_currency() {

        /*Check currency*/
        $selected_currencies = $this->get_currencies();

        if ( isset( $_GET['wmc-currency'] ) && in_array( $_GET['wmc-currency'], $selected_currencies ) ) {
            $current_currency = $_GET['wmc-currency'];
        } elseif ( isset( $_COOKIE['wmc_current_currency'] ) && in_array( $_COOKIE['wmc_current_currency'], $selected_currencies ) ) {
            $current_currency = $_COOKIE['wmc_current_currency'];
        } else {
            $current_currency = get_option( 'woocommerce_currency' );
        }

        return $current_currency;
    }

    /**
     * Get exchange rate
     * @return mixed|void
     */
    public function get_update_exchange_rate() {
        return apply_filters( 'wmc_get_update_exchange_rate', $this->params['update_exchange_rate'] );
    }

    public function get_currencies() {

        return apply_filters( 'wmc_get_currencies', $this->params['currency'] );
    }

    /**
     * Get Purchased code
     * @return mixed|void
     */
    public function get_key() {
        return apply_filters( 'wmc_get_key', $this->params['key'] );
    }

    /**
     * Check enable pay with multi currencies
     * @return mixed|void
     */
    public function get_enable_multi_payment() {
        return apply_filters( 'wmc_get_enable_multi_payment', $this->params['enable_multi_payment'] );
    }

    /**
     * Check Enable plugin
     * @return mixed|void
     */
    public function get_enable() {

        return apply_filters( 'wmc_get_enable', $this->params['enable'] );
    }


    /**
     * Get currency default
     * @return mixed|void
     */
    public function get_default_currency() {
        return apply_filters( 'wmc_get_default_currency', $this->params['currency_default'] );
    }

    /**
     * Get list currencies
     * @return mixed|void
     */
    public function get_list_currencies() {
        $data = [];
        if ( count( $this->params['currency'] ) ) {
            foreach ( $this->params['currency'] as $k => $currency ) {
                $data[ $currency ]['rate']     = $this->params['currency_rate'][ $k ];
                $data[ $currency ]['pos']      = $this->params['currency_pos'][ $k ];
                $data[ $currency ]['decimals'] = $this->params['currency_decimals'][ $k ];
                $data[ $currency ]['custom']   = $this->params['currency_custom'][ $k ];
            }
        }

        return apply_filters( 'wmc_get_list_currencies', $data );
    }
}

new WOOMULTI_CURRENCY_F_Data();
