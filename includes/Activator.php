<?php
namespace Wpdcs\WooCommerceCurrencySwitcher;

class Activator {
    private static $_instance = null;

    private static $_activator = false;

    private function __construct() {
        if ( is_multisite() ) {
            $active_plugin = apply_filters( 'active_plugins', get_site_option( 'active_sitewide_plugins' ) );
        }

        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_multisite_or_site_option( 'active_plugins', false ) ), true ) || isset( $active_plugin['woocommerce/woocommerce.php'] ) ) {
            self::$_activator = true;
        } else {
            add_action( 'admin_notices', [ $this, 'inactive_plugin_notice' ] );
        }
    }

    public static function check_woo_dependecies() {
        return self::$_activator;
    }

    public function inactive_plugin_notice() {
        ?>
        <div id="message" class="error">
            <p><?php printf( esc_html( __( 'Currency Switcher webhooks Need Woocommerce to be active!', 'wccs' ) ) ); ?></p>
        </div>
        <?php
    }

    /**
     * Singleton Instance Method to initiate class.
     *
     */
    public static function Instance() {
        if ( null === self::$_instance ) {
            self::$_instance = new Activator();
        }

        return self::$_instance;
    }
}
