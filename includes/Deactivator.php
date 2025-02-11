<?php
namespace Wpdcs\WooCommerceCurrencySwitcher;

if ( ! class_exists( 'Deactivator' ) ) {
    class Deactivator {
        private static $_instance = null;

        /**
         * Contructor of class.
         *
         */
        private function __construct() {
            wp_clear_scheduled_hook( 'wccs_update_rates', [ true ] );
        }

        /**
         * Singleton Instance Method to initiate class.
         *
         */
        public static function Instance() {
            if ( null === self::$_instance ) {
                self::$_instance = new Deactivator();
            }

            return self::$_instance;
        }
    }
}
