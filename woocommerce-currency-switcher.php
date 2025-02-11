<?php
/**
 * Plugin Name: WooCommerce Currency Switcher
 * Description: Allow your customers to shop seamlessly in their preferred currency. Allow fixed prices in multiple currencies, multiple display prices and accepts payments in multiple currencies.
 * Version: 4.1.0
 * Author: getButterfly
 * Author URI: http://getbutterfly.com/
 * Update URI: http://getbutterfly.com/
 * Requires at least: 6.0
 * Requires Plugins: woocommerce
 * Tested up to: 6.7.2
 * License: GNU General Public License v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: woocommerce-currency-switcher
 *
 * WC requires at least: 7.0.0
 * WC tested up to: 9.6.1
 */

namespace Wpdcs\WooCommerceCurrencySwitcher;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

define( 'WCCS_DIR', __DIR__ );
define( 'WCCS_VERSION', '4.1.0' );
define( 'WCCS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WCCS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

require WCCS_DIR . '/includes/updater.php';

require_once WCCS_DIR . '/includes/Activator.php';
require_once WCCS_DIR . '/includes/Deactivator.php';
require_once WCCS_DIR . '/includes/Storage.php';
require_once WCCS_DIR . '/includes/SwitcherWidget.php';
require_once WCCS_DIR . '/includes/WCCS.php';
require_once WCCS_DIR . '/includes/Settings.php';
require_once WCCS_DIR . '/includes/AjaxProcess.php';
require_once WCCS_DIR . '/includes/Cron.php';

use Wpdcs\WooCommerceCurrencySwitcher\Activator;
use Wpdcs\WooCommerceCurrencySwitcher\Deactivator;
use Wpdcs\WooCommerceCurrencySwitcher\Storage;
use Wpdcs\WooCommerceCurrencySwitcher\SwitcherWidget;
use Wpdcs\WooCommerceCurrencySwitcher\WCCS;
use Wpdcs\WooCommerceCurrencySwitcher\Settings;
use Wpdcs\WooCommerceCurrencySwitcher\AjaxProcess;
use Wpdcs\WooCommerceCurrencySwitcher\Cron;

class WCCS_Currency_Switcher {
    private static $_instance = null;

    public function __clone() {
        wc_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'wccs' ), '1.0' );
    }

    public function __wakeup() {
        wc_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'wccs' ), '1.0' );
    }

    public function __construct() {
        include WCCS_PLUGIN_PATH . 'includes/Helper.php';

        Activator::Instance();
        add_action( 'woocommerce_init', [ $this, 'run' ], 10 );
        add_action( 'before_woocommerce_init', [ $this, 'hpos_compatibility' ], 10 );
        add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
        add_action( 'wp_footer', [ $this, 'wccs_call_refresh_cart_fragment' ] );

        register_activation_hook( __FILE__, [ $this, 'wccs_activation' ] );
        register_deactivation_hook( __FILE__, [ $this, 'wccs_deactivation' ] );
    }

    public function load_textdomain() {
        load_plugin_textdomain( 'wccs', false, basename( __DIR__ ) . '/languages/' );
    }

    /**
     * HPOS Compatibility & Cart & Checkout Compatibilty.
     */
    public function hpos_compatibility() {
        if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
        }
    }

    public function run() {
        if ( Activator::check_woo_dependecies() ) {

            AjaxProcess::Instance();
            Cron::Instance();
            Storage::Instance();
            $GLOBALS['WCCS'] = WCCS::Instance();
            Settings::Instance();
        }
    }

    public function wccs_activation() {
        Activator::Instance();
    }

    public function wccs_deactivation() {
        Deactivator::Instance();
    }

    public function wccs_call_refresh_cart_fragment() {
        ?>
        <script>
        setTimeout(() => {
            jQuery( document.body ).trigger( 'wc_fragment_refresh' );
        }, 300);
        </script>
        <?php
    }

    public static function Instance() {
        if ( null === self::$_instance ) {
            self::$_instance = new WCCS_Currency_Switcher();
        }

        return self::$_instance;
    }
}

WCCS_Currency_Switcher::Instance();
