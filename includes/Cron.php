<?php
namespace Wpdcs\WooCommerceCurrencySwitcher;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'Cron' ) ) {
    class Cron {
        private static $_instance = null;

        public function __construct() {
            // cronjob to update exchange rates for currencies
            add_action( 'wccs_update_rates', [ $this, 'wccs_update_rates_callback' ], 10, 1 );

            // custom cron recurrences
            add_filter( 'cron_schedules', [ $this, 'custom_cron_recurrence' ] );
        }

        public static function wccs_update_rates_callback( $is_cron, $selected_type ) {
            $currencies = get_multisite_or_site_option( 'wccs_currencies', [] );
            if ( count( $currencies ) ) {
                $codes    = array_keys( $currencies );
                $code_str = implode( ',', $codes );
                $latest   = [];

                // Fetch the latest exchange rates based on the selected API type
                if ( 'open_exchange_rate' == $selected_type ) {
                    $latest = wccs_get_exchange_rates( $code_str );

                } elseif ( 'abstract_api' == $selected_type ) {
                    $latest = wccs_get_exchange_rates_abstract( $code_str );
                } elseif ( 'exchange_rate_api' == $selected_type ) {
                    $latest = wccs_get_exchange_rates_exchangerate_api( $code_str );
                } elseif ( 'api_layer_fixer' == $selected_type ) {
                    $latest = wccs_get_exchange_rates_fixer( $code_str );
                }

                // Check for errors and update rates if no errors
                if ( ! isset( $latest['error'] ) ) {
                    $changed = [];
                    foreach ( $currencies as $code => $info ) {
                        // Use appropriate key for the rates based on the API
                        if ( 'open_exchange_rate' == $selected_type || 'api_layer_fixer' == $selected_type ) {
                            $rate_key = 'rates';
                        } elseif ( 'abstract_api' == $selected_type ) {
                            $rate_key = 'exchange_rates';
                        } elseif ( 'exchange_rate_api' == $selected_type ) {
                            $rate_key = 'conversion_rates';
                        }

                        if ( isset( $latest[ $rate_key ][ $code ] ) ) {
                            if ( $currencies[ $code ]['rate'] != $latest[ $rate_key ][ $code ] ) {
                                $changed[ $currencies[ $code ]['label'] ] = $latest[ $rate_key ][ $code ];
                            }
                            $currencies[ $code ]['rate'] = $latest[ $rate_key ][ $code ];
                        }
                    }
                    if ( is_multisite() ) {
                        update_site_option( 'wccs_currencies', $currencies );
                    } else {
                        update_option( 'wccs_currencies', $currencies );
                    }

                    $currencies = get_multisite_or_site_option( 'wccs_currencies', [] );
                    $send_email = get_multisite_or_site_option( 'wccs_admin_email', 0 );

                    if ( $send_email && count( $changed ) && $is_cron ) {
                        $sitename    = get_multisite_or_site_option( 'blogname', false );
                        $admin_email = get_multisite_or_site_option( 'admin_email', false );

                        if ( get_multisite_or_site_option( 'wccs_email', '' ) ) {
                            $to = get_multisite_or_site_option( 'wccs_email', false );
                        } else {
                            $to = $admin_email;
                        }
                        $subject   = __( 'Currency rates updated', 'wccs' );
                        $body      = wccs_get_email_body( 'currency_update', [ 'changed' => $changed ] );
                        $headers   = [];
                        $headers[] = 'Content-Type: text/html; charset=UTF-8';
                        $headers[] = 'From: ' . $sitename . ' <' . $admin_email . '>';

                        wp_mail( $to, $subject, $body, $headers );
                    }
                }
            }
        }

        public function custom_cron_recurrence( $schedules ) {

            $schedules['weekly'] = [
                'display'  => __( 'Weekly', 'wccs' ),
                'interval' => 604800,
            ];

            return $schedules;
        }

        /**
         * Singleton Instance Method to initiate class.
         *
         */
        public static function Instance() {
            if ( null === self::$_instance ) {
                self::$_instance = new Cron();
            }

            return self::$_instance;
        }
    }
}
