<?php

if ( ! function_exists( 'get_all_active_payment_gateways' ) ) {

    function get_all_active_payment_gateways() {
        $gateways         = WC()->payment_gateways->get_available_payment_gateways();
        $enabled_gateways = [];
        if ( $gateways ) {
            foreach ( $gateways as $gateway ) {
                if ( 'yes' == $gateway->enabled ) {
                    $arr                = [];
                    $arr['id']          = $gateway->id;
                    $arr['title']       = $gateway->title;
                    $arr['enabled']     = $gateway->enabled;
                    $enabled_gateways[] = $arr;
                }
            }
        }

        return $enabled_gateways;
    }

}

if ( ! function_exists( 'wccs_delete_all_between' ) ) {

    function wccs_delete_all_between( $beginning, $end, $string ) {
        $beginningPos = strpos( $string, $beginning );
        $endPos       = strpos( $string, $end );
        if ( false === $beginningPos || false === $endPos ) {
            return $string;
        }

        $textToDelete = substr( $string, $beginningPos, ( $endPos + strlen( $end ) ) - $beginningPos );

        return wccs_delete_all_between( $beginning, $end, str_replace( $textToDelete, '', $string ) ); // recursion to ensure all occurrences are replaced
    }

}

if ( ! function_exists( 'wccs_get_country_currency' ) ) {

    function wccs_get_country_currency( $code ) {
        $arr = [
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

        return isset( $arr[ $code ] ) ? $arr[ $code ] : '';
    }

}

if ( ! function_exists( 'wccs_free_shipping_criteria' ) ) {

    function wccs_free_shipping_criteria() {
        if ( class_exists( 'WCCS' ) ) {

            // remove filter from plugin first
            remove_filter( 'woocommerce_package_rates', [ $GLOBALS['WCCS'], 'wccs_change_shipping_rates_cost' ], 10, 2 );

            // override shipping price
            add_filter( 'woocommerce_package_rates', 'wccs_change_free_shipping_price_criteria', 99, 2 );
            function wccs_change_free_shipping_price_criteria( $rates, $package ) {

                $coversion_rate = $GLOBALS['WCCS']->wccs_get_currency_rate();
                $decimals       = $GLOBALS['WCCS']->wccs_get_currency_decimals();

                $amount_for_free_shipping = 70.00; // set free shipping threshold
                $cart_total               = WC()->cart->subtotal;

                foreach ( $rates as $id => $rate ) {

                    if ( $coversion_rate ) {

                        $amount_for_free_shipping = round( ( $amount_for_free_shipping * $coversion_rate ), $decimals );

                        if ( $cart_total < $amount_for_free_shipping ) {
                            if ( 'free_shipping' === $rate->method_id ) {
                                unset( $rates[ $id ] );
                            }
                        }

                        if ( isset( $rates[ $id ] ) ) {
                            $rates[ $id ]->cost = round( ( $rates[ $id ]->cost * $coversion_rate ), $decimals );
                            // Taxes rate cost (if enabled)
                            $taxes = [];
                            foreach ( $rates[ $id ]->taxes as $key => $tax ) {
                                if ( $tax > 0 ) { // set the new tax cost
                                    // set the new line tax cost in the taxes array
                                    $taxes[ $key ] = round( ( $tax * $coversion_rate ), $decimals );
                                }
                            }
                            // Set the new taxes costs
                            $rates[ $id ]->taxes = $taxes;
                        }
                    } elseif ( $cart_total < $amount_for_free_shipping ) {

                        if ( 'free_shipping' === $rate->method_id ) {
                            unset( $rates[ $id ] );
                        }
                    }
                }

                return $rates;
            }
        }
    }

}

if ( ! function_exists( 'wcc_get_post_data' ) ) {

    function wcc_get_post_data( $name = '' ) {

        if ( ! isset( $_POST['_wccsnonce'] ) || ( isset( $_POST['_wccsnonce'] ) && ! wp_verify_nonce( wc_clean( $_POST['_wccsnonce'] ), '_wccsnonce' ) ) ) {
            return;
        }

        $post = $_POST;

        if ( isset( $post[ $name ] ) ) {
            return $post[ $name ];
        } else {
            return $_POST;
        }
    }

}

if ( ! function_exists( 'wccs_get_available_currencies' ) ) {

    function wccs_get_available_currencies( $exclude = [], $exclude_default = true ) {
        $currencies = get_woocommerce_currencies();

        if ( $exclude_default ) {
            $default = get_woocommerce_currency();
            if ( $default && isset( $currencies[ $default ] ) ) {
                unset( $currencies[ $default ] );
            }
        }

        foreach ( $exclude as $code ) {
            if ( isset( $currencies[ $code ] ) ) {
                unset( $currencies[ $code ] );
            }
        }

        return $currencies;
    }

}

if ( ! function_exists( 'wccs_get_currency_label' ) ) {

    function wccs_get_currency_label( $currency = null ) {
        $label = '';

        if ( ! $currency ) {
            $currency = get_woocommerce_currency();
        }

        $currencies = get_woocommerce_currencies();

        if ( isset( $currencies[ $currency ] ) ) {
            $label = $currencies[ $currency ];
        }

        return apply_filters( 'wccs_change_default_currency_label', $label );
    }

}

if ( ! function_exists( 'wccs_get_all_flags' ) ) {

    function wccs_get_all_flags() {
        $flags = [
            'AF' => 'https://restcountries.eu/data/afg.svg',
            'AX' => 'https://restcountries.eu/data/ala.svg',
            'AL' => 'https://restcountries.eu/data/alb.svg',
            'DZ' => 'https://restcountries.eu/data/dza.svg',
            'AS' => 'https://restcountries.eu/data/asm.svg',
            'AD' => 'https://restcountries.eu/data/and.svg',
            'AO' => 'https://restcountries.eu/data/ago.svg',
            'AI' => 'https://restcountries.eu/data/aia.svg',
            'AQ' => 'https://restcountries.eu/data/ata.svg',
            'AG' => 'https://restcountries.eu/data/atg.svg',
            'AR' => 'https://restcountries.eu/data/arg.svg',
            'AM' => 'https://restcountries.eu/data/arm.svg',
            'AN' => WCCS_PLUGIN_PATH . 'assets/lib/flag-icon/imgs/flags/ang.png',
            'AW' => 'https://restcountries.eu/data/abw.svg',
            'AU' => 'https://restcountries.eu/data/aus.svg',
            'AT' => 'https://restcountries.eu/data/aut.svg',
            'AZ' => 'https://restcountries.eu/data/aze.svg',
            'BS' => 'https://restcountries.eu/data/bhs.svg',
            'BH' => 'https://restcountries.eu/data/bhr.svg',
            'BD' => 'https://restcountries.eu/data/bgd.svg',
            'BB' => 'https://restcountries.eu/data/brb.svg',
            'BY' => 'https://restcountries.eu/data/blr.svg',
            'BE' => 'https://restcountries.eu/data/bel.svg',
            'BZ' => 'https://restcountries.eu/data/blz.svg',
            'BJ' => 'https://restcountries.eu/data/ben.svg',
            'BM' => 'https://restcountries.eu/data/bmu.svg',
            'BT' => 'https://restcountries.eu/data/btn.svg',
            'BO' => 'https://restcountries.eu/data/bol.svg',
            'BQ' => 'https://restcountries.eu/data/bes.svg',
            'BA' => 'https://restcountries.eu/data/bih.svg',
            'BW' => 'https://restcountries.eu/data/bwa.svg',
            'BV' => 'https://restcountries.eu/data/bvt.svg',
            'BR' => 'https://restcountries.eu/data/bra.svg',
            'IO' => 'https://restcountries.eu/data/iot.svg',
            'UM' => 'https://restcountries.eu/data/umi.svg',
            'VG' => 'https://restcountries.eu/data/vgb.svg',
            'VI' => 'https://restcountries.eu/data/vir.svg',
            'BN' => 'https://restcountries.eu/data/brn.svg',
            'BG' => 'https://restcountries.eu/data/bgr.svg',
            'BF' => 'https://restcountries.eu/data/bfa.svg',
            'BI' => 'https://restcountries.eu/data/bdi.svg',
            'KH' => 'https://restcountries.eu/data/khm.svg',
            'CM' => 'https://restcountries.eu/data/cmr.svg',
            'CA' => 'https://restcountries.eu/data/can.svg',
            'CV' => 'https://restcountries.eu/data/cpv.svg',
            'KY' => 'https://restcountries.eu/data/cym.svg',
            'CF' => 'https://restcountries.eu/data/caf.svg',
            'TD' => 'https://restcountries.eu/data/tcd.svg',
            'CL' => 'https://restcountries.eu/data/chl.svg',
            'CN' => 'https://restcountries.eu/data/chn.svg',
            'CX' => 'https://restcountries.eu/data/cxr.svg',
            'CC' => 'https://restcountries.eu/data/cck.svg',
            'CO' => 'https://restcountries.eu/data/col.svg',
            'KM' => 'https://restcountries.eu/data/com.svg',
            'CG' => 'https://restcountries.eu/data/cog.svg',
            'CD' => 'https://restcountries.eu/data/cod.svg',
            'CK' => 'https://restcountries.eu/data/cok.svg',
            'CR' => 'https://restcountries.eu/data/cri.svg',
            'HR' => 'https://restcountries.eu/data/hrv.svg',
            'CU' => 'https://restcountries.eu/data/cub.svg',
            'CW' => 'https://restcountries.eu/data/cuw.svg',
            'CY' => 'https://restcountries.eu/data/cyp.svg',
            'CZ' => 'https://restcountries.eu/data/cze.svg',
            'DK' => 'https://restcountries.eu/data/dnk.svg',
            'DJ' => 'https://restcountries.eu/data/dji.svg',
            'DM' => 'https://restcountries.eu/data/dma.svg',
            'DO' => 'https://restcountries.eu/data/dom.svg',
            'EC' => 'https://restcountries.eu/data/ecu.svg',
            'EG' => 'https://restcountries.eu/data/egy.svg',
            'SV' => 'https://restcountries.eu/data/slv.svg',
            'GQ' => 'https://restcountries.eu/data/gnq.svg',
            'ER' => 'https://restcountries.eu/data/eri.svg',
            'EE' => 'https://restcountries.eu/data/est.svg',
            'ET' => 'https://restcountries.eu/data/eth.svg',
            'EU' => 'https://restcountries.eu/data/eu.svg',
            'FK' => 'https://restcountries.eu/data/flk.svg',
            'FO' => 'https://restcountries.eu/data/fro.svg',
            'FJ' => 'https://restcountries.eu/data/fji.svg',
            'FI' => 'https://restcountries.eu/data/fin.svg',
            'FR' => 'https://restcountries.eu/data/fra.svg',
            'GF' => 'https://restcountries.eu/data/guf.svg',
            'PF' => 'https://restcountries.eu/data/pyf.svg',
            'TF' => 'https://restcountries.eu/data/atf.svg',
            'GA' => 'https://restcountries.eu/data/gab.svg',
            'GM' => 'https://restcountries.eu/data/gmb.svg',
            'GE' => 'https://restcountries.eu/data/geo.svg',
            'DE' => 'https://restcountries.eu/data/deu.svg',
            'GH' => 'https://restcountries.eu/data/gha.svg',
            'GI' => 'https://restcountries.eu/data/gib.svg',
            'GR' => 'https://restcountries.eu/data/grc.svg',
            'GL' => 'https://restcountries.eu/data/grl.svg',
            'GD' => 'https://restcountries.eu/data/grd.svg',
            'GP' => 'https://restcountries.eu/data/glp.svg',
            'GU' => 'https://restcountries.eu/data/gum.svg',
            'GT' => 'https://restcountries.eu/data/gtm.svg',
            'GG' => 'https://restcountries.eu/data/ggy.svg',
            'GN' => 'https://restcountries.eu/data/gin.svg',
            'GW' => 'https://restcountries.eu/data/gnb.svg',
            'GY' => 'https://restcountries.eu/data/guy.svg',
            'HT' => 'https://restcountries.eu/data/hti.svg',
            'HM' => 'https://restcountries.eu/data/hmd.svg',
            'VA' => 'https://restcountries.eu/data/vat.svg',
            'HN' => 'https://restcountries.eu/data/hnd.svg',
            'HK' => 'https://restcountries.eu/data/hkg.svg',
            'HU' => 'https://restcountries.eu/data/hun.svg',
            'IS' => 'https://restcountries.eu/data/isl.svg',
            'IN' => 'https://restcountries.eu/data/ind.svg',
            'ID' => 'https://restcountries.eu/data/idn.svg',
            'CI' => 'https://restcountries.eu/data/civ.svg',
            'IR' => 'https://restcountries.eu/data/irn.svg',
            'IQ' => 'https://restcountries.eu/data/irq.svg',
            'IE' => 'https://restcountries.eu/data/irl.svg',
            'IM' => 'https://restcountries.eu/data/imn.svg',
            'IL' => 'https://restcountries.eu/data/isr.svg',
            'IT' => 'https://restcountries.eu/data/ita.svg',
            'JM' => 'https://restcountries.eu/data/jam.svg',
            'JP' => 'https://restcountries.eu/data/jpn.svg',
            'JE' => 'https://restcountries.eu/data/jey.svg',
            'JO' => 'https://restcountries.eu/data/jor.svg',
            'KZ' => 'https://restcountries.eu/data/kaz.svg',
            'KE' => 'https://restcountries.eu/data/ken.svg',
            'KI' => 'https://restcountries.eu/data/kir.svg',
            'KW' => 'https://restcountries.eu/data/kwt.svg',
            'KG' => 'https://restcountries.eu/data/kgz.svg',
            'LA' => 'https://restcountries.eu/data/lao.svg',
            'LV' => 'https://restcountries.eu/data/lva.svg',
            'LB' => 'https://restcountries.eu/data/lbn.svg',
            'LS' => 'https://restcountries.eu/data/lso.svg',
            'LR' => 'https://restcountries.eu/data/lbr.svg',
            'LY' => 'https://restcountries.eu/data/lby.svg',
            'LI' => 'https://restcountries.eu/data/lie.svg',
            'LT' => 'https://restcountries.eu/data/ltu.svg',
            'LU' => 'https://restcountries.eu/data/lux.svg',
            'MO' => 'https://restcountries.eu/data/mac.svg',
            'MK' => 'https://restcountries.eu/data/mkd.svg',
            'MG' => 'https://restcountries.eu/data/mdg.svg',
            'MW' => 'https://restcountries.eu/data/mwi.svg',
            'MY' => 'https://restcountries.eu/data/mys.svg',
            'MV' => 'https://restcountries.eu/data/mdv.svg',
            'ML' => 'https://restcountries.eu/data/mli.svg',
            'MT' => 'https://restcountries.eu/data/mlt.svg',
            'MH' => 'https://restcountries.eu/data/mhl.svg',
            'MQ' => 'https://restcountries.eu/data/mtq.svg',
            'MR' => 'https://restcountries.eu/data/mrt.svg',
            'MU' => 'https://restcountries.eu/data/mus.svg',
            'YT' => 'https://restcountries.eu/data/myt.svg',
            'MX' => 'https://restcountries.eu/data/mex.svg',
            'FM' => 'https://restcountries.eu/data/fsm.svg',
            'MD' => 'https://restcountries.eu/data/mda.svg',
            'MC' => 'https://restcountries.eu/data/mco.svg',
            'MN' => 'https://restcountries.eu/data/mng.svg',
            'ME' => 'https://restcountries.eu/data/mne.svg',
            'MS' => 'https://restcountries.eu/data/msr.svg',
            'MA' => 'https://restcountries.eu/data/mar.svg',
            'MZ' => 'https://restcountries.eu/data/moz.svg',
            'MM' => 'https://restcountries.eu/data/mmr.svg',
            'NA' => 'https://restcountries.eu/data/nam.svg',
            'NR' => 'https://restcountries.eu/data/nru.svg',
            'NP' => 'https://restcountries.eu/data/npl.svg',
            'NL' => 'https://restcountries.eu/data/nld.svg',
            'NC' => 'https://restcountries.eu/data/ncl.svg',
            'NZ' => 'https://restcountries.eu/data/nzl.svg',
            'NI' => 'https://restcountries.eu/data/nic.svg',
            'NE' => 'https://restcountries.eu/data/ner.svg',
            'NG' => 'https://restcountries.eu/data/nga.svg',
            'NU' => 'https://restcountries.eu/data/niu.svg',
            'NF' => 'https://restcountries.eu/data/nfk.svg',
            'KP' => 'https://restcountries.eu/data/prk.svg',
            'MP' => 'https://restcountries.eu/data/mnp.svg',
            'NO' => 'https://restcountries.eu/data/nor.svg',
            'OM' => 'https://restcountries.eu/data/omn.svg',
            'PK' => 'https://restcountries.eu/data/pak.svg',
            'PW' => 'https://restcountries.eu/data/plw.svg',
            'PS' => 'https://restcountries.eu/data/pse.svg',
            'PA' => 'https://restcountries.eu/data/pan.svg',
            'PG' => 'https://restcountries.eu/data/png.svg',
            'PY' => 'https://restcountries.eu/data/pry.svg',
            'PE' => 'https://restcountries.eu/data/per.svg',
            'PH' => 'https://restcountries.eu/data/phl.svg',
            'PN' => 'https://restcountries.eu/data/pcn.svg',
            'PL' => 'https://restcountries.eu/data/pol.svg',
            'PT' => 'https://restcountries.eu/data/prt.svg',
            'PR' => 'https://restcountries.eu/data/pri.svg',
            'QA' => 'https://restcountries.eu/data/qat.svg',
            'XK' => 'https://restcountries.eu/data/kos.svg',
            'RE' => 'https://restcountries.eu/data/reu.svg',
            'RO' => 'https://restcountries.eu/data/rou.svg',
            'RU' => 'https://restcountries.eu/data/rus.svg',
            'RW' => 'https://restcountries.eu/data/rwa.svg',
            'BL' => 'https://restcountries.eu/data/blm.svg',
            'SH' => 'https://restcountries.eu/data/shn.svg',
            'KN' => 'https://restcountries.eu/data/kna.svg',
            'LC' => 'https://restcountries.eu/data/lca.svg',
            'MF' => 'https://restcountries.eu/data/maf.svg',
            'PM' => 'https://restcountries.eu/data/spm.svg',
            'VC' => 'https://restcountries.eu/data/vct.svg',
            'WS' => 'https://restcountries.eu/data/wsm.svg',
            'SM' => 'https://restcountries.eu/data/smr.svg',
            'ST' => 'https://restcountries.eu/data/stp.svg',
            'SA' => 'https://restcountries.eu/data/sau.svg',
            'SN' => 'https://restcountries.eu/data/sen.svg',
            'RS' => 'https://restcountries.eu/data/srb.svg',
            'SC' => 'https://restcountries.eu/data/syc.svg',
            'SL' => 'https://restcountries.eu/data/sle.svg',
            'SG' => 'https://restcountries.eu/data/sgp.svg',
            'SX' => 'https://restcountries.eu/data/sxm.svg',
            'SK' => 'https://restcountries.eu/data/svk.svg',
            'SI' => 'https://restcountries.eu/data/svn.svg',
            'SB' => 'https://restcountries.eu/data/slb.svg',
            'SO' => 'https://restcountries.eu/data/som.svg',
            'ZA' => 'https://restcountries.eu/data/zaf.svg',
            'GS' => 'https://restcountries.eu/data/sgs.svg',
            'KR' => 'https://restcountries.eu/data/kor.svg',
            'SS' => 'https://restcountries.eu/data/ssd.svg',
            'ES' => 'https://restcountries.eu/data/esp.svg',
            'LK' => 'https://restcountries.eu/data/lka.svg',
            'SD' => 'https://restcountries.eu/data/sdn.svg',
            'SR' => 'https://restcountries.eu/data/sur.svg',
            'SJ' => 'https://restcountries.eu/data/sjm.svg',
            'SZ' => 'https://restcountries.eu/data/swz.svg',
            'SE' => 'https://restcountries.eu/data/swe.svg',
            'CH' => 'https://restcountries.eu/data/che.svg',
            'SY' => 'https://restcountries.eu/data/syr.svg',
            'TW' => 'https://restcountries.eu/data/twn.svg',
            'TJ' => 'https://restcountries.eu/data/tjk.svg',
            'TZ' => 'https://restcountries.eu/data/tza.svg',
            'TH' => 'https://restcountries.eu/data/tha.svg',
            'TL' => 'https://restcountries.eu/data/tls.svg',
            'TG' => 'https://restcountries.eu/data/tgo.svg',
            'TK' => 'https://restcountries.eu/data/tkl.svg',
            'TO' => 'https://restcountries.eu/data/ton.svg',
            'TT' => 'https://restcountries.eu/data/tto.svg',
            'TN' => 'https://restcountries.eu/data/tun.svg',
            'TR' => 'https://restcountries.eu/data/tur.svg',
            'TM' => 'https://restcountries.eu/data/tkm.svg',
            'TC' => 'https://restcountries.eu/data/tca.svg',
            'TV' => 'https://restcountries.eu/data/tuv.svg',
            'UG' => 'https://restcountries.eu/data/uga.svg',
            'UA' => 'https://restcountries.eu/data/ukr.svg',
            'AE' => 'https://restcountries.eu/data/are.svg',
            'GB' => 'https://restcountries.eu/data/gbr.svg',
            'US' => 'https://restcountries.eu/data/usa.svg',
            'UY' => 'https://restcountries.eu/data/ury.svg',
            'UZ' => 'https://restcountries.eu/data/uzb.svg',
            'VU' => 'https://restcountries.eu/data/vut.svg',
            'VE' => 'https://restcountries.eu/data/ven.svg',
            'VN' => 'https://restcountries.eu/data/vnm.svg',
            'WF' => 'https://restcountries.eu/data/wlf.svg',
            'EH' => 'https://restcountries.eu/data/esh.svg',
            'YE' => 'https://restcountries.eu/data/yem.svg',
            'ZM' => 'https://restcountries.eu/data/zmb.svg',
            'ZW' => 'https://restcountries.eu/data/zwe.svg',
        ];
        /**
         * Filter
         *
         * @since 1.0.0
         */
        return apply_filters( 'wccs_flags', $flags );
    }

}

if ( ! function_exists( 'get_currency_countries' ) ) {

    function get_currency_countries( $currency_code = '' ) {
        $arr = [
            'AFN' => [ 'AF' ],
            'ALL' => [ 'AL' ],
            'DZD' => [ 'DZ' ],
            'USD' => [ 'AS', 'IO', 'GU', 'MH', 'FM', 'MP', 'PW', 'PR', 'TC', 'US', 'UM', 'VI' ],
            'EUR' => [ 'AD', 'AT', 'BE', 'CY', 'EE', 'FI', 'FR', 'GF', 'TF', 'DE', 'GR', 'GP', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'MQ', 'YT', 'MC', 'ME', 'NL', 'PT', 'RE', 'PM', 'SM', 'SK', 'SI', 'ES', 'EU' ],
            'AOA' => [ 'AO' ],
            'XCD' => [ 'AI', 'AQ', 'AG', 'DM', 'GD', 'MS', 'KN', 'LC', 'VC' ],
            'ARS' => [ 'AR' ],
            'AMD' => [ 'AM' ],
            'AWG' => [ 'AW' ],
            'AUD' => [ 'AU', 'CX', 'CC', 'HM', 'KI', 'NR', 'NF', 'TV' ],
            'AZN' => [ 'AZ' ],
            'BSD' => [ 'BS' ],
            'BHD' => [ 'BH' ],
            'BDT' => [ 'BD' ],
            'BBD' => [ 'BB' ],
            'BYR' => [ 'BY' ],
            'BYN' => [ 'BY' ],
            'BZD' => [ 'BZ' ],
            'XOF' => [ 'BJ', 'BF', 'ML', 'NE', 'SN', 'TG' ],
            'BMD' => [ 'BM' ],
            'BTN' => [ 'BT' ],
            'BOB' => [ 'BO' ],
            'BAM' => [ 'BA' ],
            'BWP' => [ 'BW' ],
            'NOK' => [ 'BV', 'NO', 'SJ' ],
            'BRL' => [ 'BR' ],
            'BND' => [ 'BN' ],
            'BGN' => [ 'BG' ],
            'BIF' => [ 'BI' ],
            'KHR' => [ 'KH' ],
            'XAF' => [ 'CM', 'CF', 'TD', 'CG', 'GQ', 'GA' ],
            'CAD' => [ 'CA' ],
            'CVE' => [ 'CV' ],
            'KYD' => [ 'KY' ],
            'CLP' => [ 'CL' ],
            'CNY' => [ 'CN' ],
            'HKD' => [ 'HK' ],
            'COP' => [ 'CO' ],
            'KMF' => [ 'KM' ],
            'CDF' => [ 'CD' ],
            'NZD' => [ 'CK', 'NZ', 'NU', 'PN', 'TK' ],
            'CRC' => [ 'CR' ],
            'HRK' => [ 'HR' ],
            'CUP' => [ 'CU' ],
            'CZK' => [ 'CZ' ],
            'DKK' => [ 'DK', 'FO', 'GL' ],
            'DJF' => [ 'DJ' ],
            'DOP' => [ 'DO' ],
            'ECS' => [ 'EC' ],
            'EGP' => [ 'EG' ],
            'SVC' => [ 'SV' ],
            'ERN' => [ 'ER' ],
            'ETB' => [ 'ET' ],
            'FKP' => [ 'FK' ],
            'FJD' => [ 'FJ' ],
            'GMD' => [ 'GM' ],
            'GEL' => [ 'GE' ],
            'GHS' => [ 'GH' ],
            'GIP' => [ 'GI' ],
            'QTQ' => [ 'GT' ],
            'GGP' => [ 'GG' ],
            'GNF' => [ 'GN' ],
            'GWP' => [ 'GW' ],
            'GYD' => [ 'GY' ],
            'HTG' => [ 'HT' ],
            'HNL' => [ 'HN' ],
            'HUF' => [ 'HU' ],
            'ISK' => [ 'IS' ],
            'INR' => [ 'IN' ],
            'IDR' => [ 'ID' ],
            'IRR' => [ 'IR' ],
            'IQD' => [ 'IQ' ],
            'GBP' => [ 'IM', 'JE', 'GS', 'GB' ],
            'ILS' => [ 'IL' ],
            'JMD' => [ 'JM' ],
            'JPY' => [ 'JP' ],
            'JOD' => [ 'JO' ],
            'KZT' => [ 'KZ' ],
            'KES' => [ 'KE' ],
            'KPW' => [ 'KP' ],
            'KRW' => [ 'KR' ],
            'KWD' => [ 'KW' ],
            'KGS' => [ 'KG' ],
            'LAK' => [ 'LA' ],
            'LBP' => [ 'LB' ],
            'LSL' => [ 'LS' ],
            'LRD' => [ 'LR' ],
            'LYD' => [ 'LY' ],
            'CHF' => [ 'LI', 'CH' ],
            'MKD' => [ 'MK' ],
            'MGF' => [ 'MG' ],
            'MWK' => [ 'MW' ],
            'MYR' => [ 'MY' ],
            'MVR' => [ 'MV' ],
            'MRO' => [ 'MR' ],
            'MUR' => [ 'MU' ],
            'MXN' => [ 'MX' ],
            'MDL' => [ 'MD' ],
            'MNT' => [ 'MN' ],
            'MAD' => [ 'MA', 'EH' ],
            'MZN' => [ 'MZ' ],
            'MMK' => [ 'MM' ],
            'NAD' => [ 'NA' ],
            'NPR' => [ 'NP' ],
            'ANG' => [ 'AN' ],
            'XPF' => [ 'NC', 'WF' ],
            'NIO' => [ 'NI' ],
            'NGN' => [ 'NG' ],
            'OMR' => [ 'OM' ],
            'PKR' => [ 'PK' ],
            'PAB' => [ 'PA' ],
            'PGK' => [ 'PG' ],
            'PYG' => [ 'PY' ],
            'PEN' => [ 'PE' ],
            'PHP' => [ 'PH' ],
            'PLN' => [ 'PL' ],
            'QAR' => [ 'QA' ],
            'RON' => [ 'RO' ],
            'RUB' => [ 'RU' ],
            'RWF' => [ 'RW' ],
            'SHP' => [ 'SH' ],
            'WST' => [ 'WS' ],
            'STD' => [ 'ST' ],
            'SAR' => [ 'SA' ],
            'RSD' => [ 'RS' ],
            'SCR' => [ 'SC' ],
            'SLL' => [ 'SL' ],
            'SGD' => [ 'SG' ],
            'SBD' => [ 'SB' ],
            'SOS' => [ 'SO' ],
            'ZAR' => [ 'ZA' ],
            'SSP' => [ 'SS' ],
            'LKR' => [ 'LK' ],
            'SDG' => [ 'SD' ],
            'SRD' => [ 'SR' ],
            'SZL' => [ 'SZ' ],
            'SEK' => [ 'SE' ],
            'SYP' => [ 'SY' ],
            'TWD' => [ 'TW' ],
            'TJS' => [ 'TJ' ],
            'TZS' => [ 'TZ' ],
            'THB' => [ 'TH' ],
            'TOP' => [ 'TO' ],
            'TTD' => [ 'TT' ],
            'TND' => [ 'TN' ],
            'TRY' => [ 'TR' ],
            'TMT' => [ 'TM' ],
            'UGX' => [ 'UG' ],
            'UAH' => [ 'UA' ],
            'AED' => [ 'AE' ],
            'UYU' => [ 'UY' ],
            'UZS' => [ 'UZ' ],
            'VUV' => [ 'VU' ],
            'VEF' => [ 'VE' ],
            'VND' => [ 'VN' ],
            'YER' => [ 'YE' ],
            'ZMW' => [ 'ZM' ],
            'ZWD' => [ 'ZW' ],
        ];

        if ( ! empty( trim( $currency_code ) ) ) {
            return isset( $arr[ $currency_code ] ) ? $arr[ $currency_code ] : '';
        }
    }

}

if ( ! function_exists( 'wccs_get_exchange_rates' ) ) {

    function wccs_get_exchange_rates( $symbols ) {
        if ( get_option( 'wccs_oer_api_key', '' ) ) {
            global $WCCS;

            $wccs_oer_api_key = get_multisite_or_site_option( 'wccs_oer_api_key', false );

            $app_id  = $wccs_oer_api_key;
            $oxr_url = 'https://openexchangerates.org/api/latest.json?app_id=' . $app_id;

            $base = $WCCS->wccs_get_default_currency();

            if ( $base ) {
                $oxr_url .= '&base=' . $base;
            }

            if ( $symbols ) {
                $oxr_url .= '&symbols=' . $symbols;
            }

            // Open CURL session:
            $curl = curl_init( $oxr_url );
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
            curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 ); // nosemgrep: audit.php.lang.misc.curl-ssl-unverified

            // Get the data:
            $json = curl_exec( $curl );
            curl_close( $curl );

            // Decode JSON response:
            $oxr_latest = json_decode( $json, true );
            return $oxr_latest;
        }

        return [];
    }

}

if ( ! function_exists( 'wccs_get_exchange_rates_abstract' ) ) {

    function wccs_get_exchange_rates_abstract( $symbols ) {
        if ( get_option( 'wccs_aer_api_key', '' ) ) {
            global $WCCS;

            $wccs_aer_api_key = get_multisite_or_site_option( 'wccs_aer_api_key', false );
            $app_id           = $wccs_aer_api_key;
            $base             = $WCCS->wccs_get_default_currency();

            $abstractapi_url = 'https://exchange-rates.abstractapi.com/v1/live/?api_key=' . $app_id;

            if ( $base ) {
                $abstractapi_url .= '&base=' . $base;
            }

            if ( $symbols ) {
                $abstractapi_url .= '&target=' . $symbols;
            }

            // Open CURL session:
            $curl = curl_init( $abstractapi_url );
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
            curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 ); // nosemgrep: audit.php.lang.misc.curl-ssl-unverified

            // Get the data:
            $json = curl_exec( $curl );
            curl_close( $curl );

            // Decode JSON response:
            $abstract_latest = json_decode( $json, true );
            return $abstract_latest;
        }

        return [];
    }

}

if ( ! function_exists( 'wccs_get_exchange_rates_exchangerate_api' ) ) {

    function wccs_get_exchange_rates_exchangerate_api( $symbols ) {
        if ( get_option( 'wccs_era_api_key', '' ) ) {
            global $WCCS;

            $wccs_era_api_key = get_multisite_or_site_option( 'wccs_era_api_key', false );

            $app_id        = $wccs_era_api_key;
            $base_currency = $WCCS->wccs_get_default_currency();
            $req_url       = "https://v6.exchangerate-api.com/v6/{$app_id}/latest/{$base_currency}";

            $response_json = file_get_contents( $req_url );

            if ( false !== $response_json ) {
                try {
                    $response = json_decode( $response_json, true );
                    if ( 'success' === $response['result'] ) {
                        return [
                            'conversion_rates' => $response['conversion_rates'],
                            'base'             => $base_currency,
                        ];
                    }
                } catch ( Exception $e ) {
                    return [ 'error' => 'JSON decode error' ];
                }
            }

            return [ 'error' => 'Failed to fetch exchange rates' ];
        }
    }

}

if ( ! function_exists( 'wccs_get_exchange_rates_fixer' ) ) {

    function wccs_get_exchange_rates_fixer( $symbols ) {
        if ( get_option( 'wccs_alf_api_key', '' ) ) {
            global $WCCS;

            $wccs_alf_api_key = get_multisite_or_site_option( 'wccs_alf_api_key', false );

            $api_key       = $wccs_alf_api_key;
            $base_currency = $WCCS->wccs_get_default_currency();
            $symbols_param = $symbols ? '&symbols=' . $symbols : '';

            $req_url = "https://api.apilayer.com/fixer/latest?base={$base_currency}{$symbols_param}";

            $curl = curl_init();

            curl_setopt_array(
                $curl,
                [
                    CURLOPT_URL            => $req_url,
                    CURLOPT_HTTPHEADER     => [
                        'Content-Type: text/plain',
                        "apikey: {$api_key}",
                    ],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING       => '',
                    CURLOPT_MAXREDIRS      => 10,
                    CURLOPT_TIMEOUT        => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST  => 'GET',
                ]
            );

            $response = curl_exec( $curl );
            curl_close( $curl );

            if ( false !== $response ) {
                try {
                    $response_data = json_decode( $response, true );
                    if ( isset( $response_data['success'] ) && $response_data['success'] ) {
                        return [
                            'rates' => $response_data['rates'],
                            'base'  => $base_currency,
                        ];
                    } else {
                        return [ 'error' => isset( $response_data['error']['info'] ) ? $response_data['error']['info'] : 'Unknown error' ];
                    }
                } catch ( Exception $e ) {
                    return [ 'error' => 'JSON decode error' ];
                }
            }

            return [ 'error' => 'Failed to fetch exchange rates' ];
        }
        return [ 'error' => 'API key not set' ];
    }

}


if ( ! function_exists( 'wccs_get_email_body' ) ) {

    function wccs_get_email_body( $type, $args = [] ) {
        $message = '';

        switch ( $type ) {
            case 'currency_update':
                if ( isset( $args['changed'] ) && count( $args['changed'] ) ) {
                    $message .= 'Dear admin,<br/>';
                    $message .= 'This email was sent to you to let you know which currencies rates were updated.';
                    $message .= '<table>';
                    $message .= '<thead>';
                    $message .= '<tr>';
                    $message .= '<th>' . __( 'Currency', 'wccs' ) . '</th><th>' . __( 'New Rate', 'wccs' ) . '</th>';
                    $message .= '</tr>';
                    $message .= '</thead>';
                    $message .= '<tbody>';

                    foreach ( $args['changed'] as $label => $rate ) {
                        $message .= '<tr>';
                        $message .= '<td>' . $label . '</td><td>' . $rate . '</td>';
                        $message .= '</tr>';
                    }

                    $message .= '</tbody>';
                    $message .= '<table>';
                }
                break;
        }

        return $message;
    }

}

if ( ! function_exists( 'get_multisite_or_site_option' ) ) {

    function get_multisite_or_site_option( $key, $value = false ) {
        if ( is_multisite() ) {
            $wccs_option = get_site_option( $key, $value );
        } else {
            $wccs_option = get_option( $key, $value );
        }

        return $wccs_option;
    }

}
