<?php
/**
 * Plugin Name: Woo Multi Currency
 * Plugin URI: https://getbutterfly.com/wordpress-plugins/woocommerce-multi-currency/
 * Description: Allow fixed prices in multiple currencies, multiple display prices and accepts payments in multiple currencies.
 * Version: 4.0.2
 * Author: getButterfly
 * Author URI: http://getbutterfly.com/
 * Update URI: http://getbutterfly.com/
 * Requires at least: 6.0
 * Requires Plugins: woocommerce
 * Tested up to: 6.7.1
 * License: GNU General Public License v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: woo-multi-currency
 *
 * WC requires at least: 7.0.0
 * WC tested up to: 9.5.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WOOMULTI_CURRENCY_F_VERSION', '4.0.2' );
define( 'WOOMULTI_CURRENCY_F_PLUGIN_URL', WP_PLUGIN_URL . '/' . dirname( plugin_basename( __FILE__ ) ) );
define( 'WOOMULTI_CURRENCY_F_PLUGIN_PATH', WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) );
define( 'WOOMULTI_CURRENCY_F_PLUGIN_FILE_PATH', WP_PLUGIN_DIR . '/' . plugin_basename( __FILE__ ) );

require WOOMULTI_CURRENCY_F_PLUGIN_PATH . '/includes/updater.php';

require_once ABSPATH . 'wp-admin/includes/plugin.php';

if ( is_plugin_active( 'woocommerce-multi-currency/woocommerce-multi-currency.php' ) ) {
    return;
}
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
    add_action(
        'before_woocommerce_init',
        function () {
            if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
            }
        }
    );

    $init_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'woo-multi-currency' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'define.php';

    require_once $init_file;
}

class WOOMULTI_CURRENCY_F {
    public function __construct() {

        register_activation_hook( __FILE__, [ $this, 'install' ] );
        register_deactivation_hook( __FILE__, [ $this, 'uninstall' ] );
        add_action( 'admin_notices', [ $this, 'global_note' ] );
    }

    public function global_note() {
        if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            ?>
            <div id="message" class="error">
                <p><?php _e( 'This plugin requires WooCommerce.', 'woo-multi-currency' ); ?></p>
            </div>
            <?php
        }
        if ( is_plugin_active( 'woo-multi-currency-pro/woo-multi-currency-pro.php' ) ) {
            deactivate_plugins( 'woo-multi-currency-pro/woo-multi-currency-pro.php' );

            unset( $_GET['activate'] );
        }
    }

    public function install() {
        $data_init = 'eyJhdXRvX2RldGVjdCI6IjAiLCJlbmFibGVfZGVzaWduIjoiMSIsImRlc2lnbl90aXRsZSI6IlNlbGVjdCB5b3VyIGN1cnJlbmN5IiwiZGVzaWduX3Bvc2l0aW9uIjoiMSIsInRleHRfY29sb3IiOiIjZmZmZmZmIiwibWFpbl9jb2xvciI6IiNmNzgwODAiLCJiYWNrZ3JvdW5kX2NvbG9yIjoiIzIxMjEyMSIsImlzX2NoZWNrb3V0IjoiMSIsImlzX2NhcnQiOiIxIiwiY29uZGl0aW9uYWxfdGFncyI6IiIsImZsYWdfY3VzdG9tIjoiIiwiY3VzdG9tX2NzcyI6IiIsImVuYWJsZV9tdWx0aV9wYXltZW50IjoiMSIsInVwZGF0ZV9leGNoYW5nZV9yYXRlIjoiMCIsImZpbmFuY2VfYXBpIjoiMCIsInJhdGVfZGVjaW1hbHMiOiIzIiwia2V5IjoiIn0=';

        if ( ! get_option( 'woo_multi_currency_params', '' ) ) {
            update_option( 'woo_multi_currency_params', json_decode( base64_decode( $data_init ), true ) );
        }
    }

    public function uninstall() {
    }
}

new WOOMULTI_CURRENCY_F();
