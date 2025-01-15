<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WOOMULTI_CURRENCY_F_Admin_Settings {
    static $params;

    public function __construct() {
        add_action( 'admin_init', [ $this, 'save_meta_boxes' ] );
    }

    private function stripslashes_deep( $value ) {
        $value = is_array( $value ) ? array_map( 'stripslashes_deep', $value ) : stripslashes( $value );

        return $value;
    }

    /**
     * Save post meta
     *
     * @param $post
     *
     * @return bool
     */
    public function save_meta_boxes() {
        if ( ! isset( $_POST['_woo_multi_currency_nonce'] ) || ! isset( $_POST['woo_multi_currency_params'] ) ) {
            return false;
        }
        if ( ! wp_verify_nonce( $_POST['_woo_multi_currency_nonce'], 'woo_multi_currency_settings' ) ) {
            return false;
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            return false;
        }
        $data = $_POST['woo_multi_currency_params'];

        /*Override WooCommerce Currency*/
        if ( isset( $data['currency_default'] ) && $data['currency_default'] && isset( $data['currency'] ) ) {
            update_option( 'woocommerce_currency', $data['currency_default'] );
            $index = array_search( $data['currency_default'], $data['currency'] );
            /*Override WooCommerce Currency*/
            if ( isset( $data['currency_pos'][ $index ] ) && $index && $data['currency_pos'][ $index ] ) {
                update_option( 'woocommerce_currency_pos', $data['currency_pos'][ $index ] );
            }
            if ( isset( $data['currency_decimals'][ $index ] ) ) {
                update_option( 'woocommerce_price_num_decimals', $data['currency_decimals'][ $index ] );
            }
            if ( count( $data['currency'] ) > 10 ) {
                array_splice( $data['currency'], 0, 2 );
                array_splice( $data['currency_decimals'], 0, 2 );
                array_splice( $data['currency_pos'], 0, 2 );
                array_splice( $data['currency_rate'], 0, 2 );
                array_splice( $data['currency_custom'], 0, 2 );
            }
        }

        update_option( 'woo_multi_currency_params', $data );
        delete_transient( 'wmc_update_exchange_rate' );
    }

    /**
     * Set Nonce
     * @return string
     */
    protected static function set_nonce() {
        return wp_nonce_field( 'woo_multi_currency_settings', '_woo_multi_currency_nonce' );
    }

    /**
     * Set field in meta box
     *
     * @param      $field
     * @param bool $multi
     *
     * @return string
     */
    protected static function set_field( $field, $multi = false ) {
        if ( $field ) {
            if ( $multi ) {
                return 'woo_multi_currency_params[' . $field . '][]';
            } else {
                return 'woo_multi_currency_params[' . $field . ']';
            }
        } else {
            return '';
        }
    }

    /**
     * Get Post Meta
     *
     * @param $field
     *
     * @return bool
     */
    public static function get_field( $field, $default = '' ) {
        global $wmc_settings;
        $params = $wmc_settings;

        if ( self::$params ) {
            $params = self::$params;
        } else {
            self::$params = $params;
        }
        if ( isset( $params[ $field ] ) && $field ) {
            return $params[ $field ];
        } else {
            return $default;
        }
    }

    /**
     * Check element in array
     *
     * @param $arg
     * @param $index
     *
     * @return bool
     */
    protected static function data_isset( $arg, $index, $default = false ) {
        if ( isset( $arg[ $index ] ) ) {
            return $arg[ $index ];
        } else {
            return $default;
        }
    }

    /**
     * Get list shortcode
     * @return array
     */
    public static function page_callback() {
        self::$params = get_option( 'woo_multi_currency_params', [] );
        ?>
        <div class="wrap woo-multi-currency">
            <h2><?php esc_attr_e( 'WooCommerce Multi Currency Settings', 'woo-multi-currency' ); ?></h2>
            <form method="post" action="" class="vi-ui form">
                <?php echo ent2ncr( self::set_nonce() ); ?>

                <div class="vi-ui attached tabular menu">
                    <div class="item active" data-tab="general">
                        <a href="#general"><?php esc_html_e( 'General', 'woo-multi-currency' ); ?></a>
                    </div>
                    <div class="item" data-tab="checkout">
                        <a href="#checkout"><?php esc_html_e( 'Checkout', 'woo-multi-currency' ); ?></a>
                    </div>
                    <div class="item" data-tab="design">
                        <a href="#design"><?php esc_html_e( 'Design', 'woo-multi-currency' ); ?></a>
                    </div>
                </div>
                <div class="vi-ui bottom attached tab segment active" data-tab="general">
                    <!-- Tab Content !-->
                    <table class="optiontable form-table">
                        <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo self::set_field( 'enable' ); ?>">
                                    <?php esc_html_e( 'Enable', 'woo-multi-currency' ); ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php echo self::set_field( 'enable' ); ?>" type="checkbox" <?php checked( self::get_field( 'enable' ), 1 ); ?> tabindex="0" class="hidden" value="1" name="<?php echo self::set_field( 'enable' ); ?>">
                                    <label></label>
                                </div>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo self::set_field( 'enable_fixed_price' ); ?>">
                                    <?php esc_html_e( 'Fixed Price', 'woo-multi-currency' ); ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php echo self::set_field( 'enable_fixed_price' ); ?>" type="checkbox" <?php checked( self::get_field( 'enable_fixed_price' ), 1 ); ?> tabindex="0" class="hidden" value="1" name="<?php echo self::set_field( 'enable_fixed_price' ); ?>">
                                    <label></label>
                                </div>
                                <p class="description"><?php esc_html_e( 'Regular Price and Sale Price are displayed for each product. Use this option to set accurate prices that are not based on exchange rates.', 'woo-multi-currency' ); ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" colspan="2">
                                <label for="<?php echo self::set_field( 'enable_mobile' ); ?>">
                                    <?php esc_html_e( 'Currency Options', 'woo-multi-currency' ); ?>
                                </label>
                            </th>
                        </tr>
                        </tbody>
                    </table>

                    <table class="vi-ui  wp-list-table widefat striped table-view-list wmc-currency-options">
                        <thead>
                            <tr>
                                <th scope="col" class="manage-column">Default</th>
                                <th scope="col" class="manage-column"><?php esc_html_e( 'Currency', 'woo-multi-currency' ); ?></th>
                                <th scope="col" class="manage-column"><?php esc_html_e( 'Position', 'woo-multi-currency' ); ?></th>
                                <th scope="col" class="manage-column">
                                    <?php esc_html_e( 'Rate + Exchange Fee', 'woo-multi-currency' ); ?>
                                    <br><small>Default is <code>1</code></small>
                                </th>
                                <th scope="col" class="manage-column"><?php esc_html_e( 'Number of Decimals', 'woo-multi-currency' ); ?></th>
                                <th scope="col" class="manage-column"><?php esc_html_e( 'Custom Symbol', 'woo-multi-currency' ); ?></th>
                                <th scope="col" class="manage-column"><?php esc_html_e( 'Action', 'woo-multi-currency' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $currencies        = self::get_field( 'currency', [ get_option( 'woocommerce_currency' ) ] );
                            $currency_pos      = self::get_field( 'currency_pos', [ get_option( 'woocommerce_currency_pos' ) ] );
                            $currency_rate     = self::get_field( 'currency_rate', [ 1 ] );
                            $currency_rate_fee = self::get_field( 'currency_rate_fee', [ 0.0000 ] );
                            $currency_decimals = self::get_field( 'currency_decimals', [ get_option( 'woocommerce_price_num_decimals' ) ] );
                            $currency_custom   = self::get_field( 'currency_custom', [] );
                            if ( is_array( $currencies ) ) {
                                if ( count( array_filter( $currencies ) ) < 1 ) {
                                    $currencies        = [];
                                    $currency_pos      = [];
                                    $currency_rate     = [];
                                    $currency_rate_fee = [];
                                    $currency_decimals = [];
                                    $currency_custom   = [];
                                }
                            } else {
                                $currencies        = [];
                                $currency_pos      = [];
                                $currency_rate     = [];
                                $currency_rate_fee = [];
                                $currency_decimals = [];
                                $currency_custom   = [];
                            }
                            $wc_currencies = get_woocommerce_currencies();
                            foreach ( $currencies as $key => $currency ) {
                                if ( self::get_field( 'currency_default', get_option( 'woocommerce_currency' ) ) == $currency ) {
                                    $disabled = 'readonly';
                                } else {
                                    $disabled = '';
                                }
                                ?>
                                <tr class="wmc-currency-data <?php echo $currency . '-currency'; ?>">
                                    <td class="collapsing">
                                        <div class="vi-ui toggle checkbox">
                                            <input type="radio" <?php checked( self::get_field( 'currency_default', get_option( 'woocommerce_currency' ) ), $currency ); ?> tabindex="0" class="hidden" value="<?php echo esc_attr( $currency ); ?>" name="<?php echo self::set_field( 'currency_default' ); ?>">
                                            <label></label>
                                        </div>
                                    </td>
                                    <td>
                                        <select name="<?php echo self::set_field( 'currency', 1 ); ?>" class="vi-ui select2">
                                            <?php foreach ( $wc_currencies as $k => $wc_currency ) { ?>
                                                <option <?php selected( $currency, $k ); ?> value="<?php echo esc_attr( $k ); ?>"><?php echo $k . '-' . esc_html( $wc_currency ) . ' (' . get_woocommerce_currency_symbol( $k ) . ')'; ?></option>
                                            <?php } ?>
                                        </select>
                                    <td>
                                        <select name="<?php echo self::set_field( 'currency_pos', 1 ); ?>">
                                            <option <?php selected( self::data_isset( $currency_pos, $key ), 'left' ); ?> value="left"><?php esc_html_e( 'Left $99', 'woo-multi-currency' ); ?></option>
                                            <option <?php selected( self::data_isset( $currency_pos, $key ), 'right' ); ?> value="right"><?php esc_html_e( 'Right 99$', 'woo-multi-currency' ); ?></option>
                                            <option <?php selected( self::data_isset( $currency_pos, $key ), 'left_space' ); ?> value="left_space"><?php esc_html_e( 'Left with space $ 99', 'woo-multi-currency' ); ?></option>
                                            <option <?php selected( self::data_isset( $currency_pos, $key ), 'right_space' ); ?> value="right_space"><?php esc_html_e( 'Right with space 99 $', 'woo-multi-currency' ); ?></option>
                                        </select>
                                    <td>
                                        <input <?php echo $disabled; ?> type="text" name="<?php echo self::set_field( 'currency_rate', 1 ); ?>" value="<?php echo self::data_isset( $currency_rate, $key, '1' ); ?>">
                                    </td>

                                    <td>
                                        <input type="text" name="<?php echo self::set_field( 'currency_decimals', 1 ); ?>" value="<?php echo self::data_isset( $currency_decimals, $key, '2' ); ?>">
                                    </td>
                                    <td>
                                        <input type="text" placeholder="e.g.: EUR #PRICE#" name="<?php echo self::set_field( 'currency_custom', 1 ); ?>" value="<?php echo self::data_isset( $currency_custom, $key ); ?>">
                                    </td>
                                    <td>
                                        <div class="vi-ui  small red button wmc-remove-currency">
                                            <?php esc_html_e( 'Remove', 'woo-multi-currency' ); ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot class="full-width">
                            <tr>
                                <th colspan="8">
                                    <div class="vi-ui right floated primary labeled button wmc-add-currency">
                                        <?php esc_html_e( 'Add Currency', 'woo-multi-currency' ); ?>
                                    </div>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                    <p class="description"><?php esc_html_e( '* Custom Symbol: Set a custom symbol for each currency in your list and define how it will be displayed. This is useful when multiple currencies share the same symbol. Leave this field empty to use the default symbol. Examples: Set US$ for US dollars, and the system will display US$100 instead of the default $100. Use the #PRICE# placeholder to create a custom format. For instance, setting US #PRICE# $ will display US 100 $.', 'woo-multi-currency' ); ?></p>
                </div>
    
                <!-- Design !-->
                <div class="vi-ui bottom attached tab segment" data-tab="design">
                    <!-- Tab Content !-->
                    <h3><?php esc_html_e( 'Currencies Bar', 'woo-multi-currency' ); ?></h3>
                    <table class="optiontable form-table">
                        <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo self::set_field( 'enable_design' ); ?>">
                                    <?php esc_html_e( 'Enable', 'woo-multi-currency' ); ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php echo self::set_field( 'enable_design' ); ?>" type="checkbox" <?php checked( self::get_field( 'enable_design' ), 1 ); ?> tabindex="0" class="hidden" value="1" name="<?php echo self::set_field( 'enable_design' ); ?>">
                                    <label></label>
                                </div>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php esc_html_e( 'Title', 'woo-multi-currency' ); ?></label>
                            </th>
                            <td>
                                <input class="regular-text" type="text" name="<?php echo self::set_field( 'design_title' ); ?>" value="<?php echo self::get_field( 'design_title' ); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="<?php echo self::set_field( 'position' ); ?>">
                                    <?php esc_html_e( 'Position', 'woo-multi-currency' ); ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui form">
                                    <div class="fields">
                                        <div class="four wide field">
                                            <img src="<?php echo WOOMULTI_CURRENCY_F_IMAGES . 'position_1.jpg'; ?>" class="vi-ui centered medium image middle aligned ">

                                            <div class="vi-ui toggle checkbox center aligned segment">
                                                <input id="<?php echo self::set_field( 'design_position' ); ?>" type="radio" <?php checked( self::get_field( 'design_position', 0 ), 0 ); ?> tabindex="0" class="hidden" value="0" name="<?php echo self::set_field( 'design_position' ); ?>">
                                                <label><?php esc_attr_e( 'Left', 'woo-multi-currency' ); ?></label>
                                            </div>

                                        </div>
                                        <div class="two wide field">
                                        </div>

                                        <div class="four wide field">
                                            <img src="<?php echo WOOMULTI_CURRENCY_F_IMAGES . 'position_2.jpg'; ?>" class="vi-ui centered medium image middle aligned ">

                                            <div class="vi-ui toggle checkbox center aligned segment">
                                                <input id="<?php echo self::set_field( 'design_position' ); ?>" type="radio" <?php checked( self::get_field( 'design_position' ), 1 ); ?> tabindex="0" class="hidden" value="1" name="<?php echo self::set_field( 'design_position' ); ?>">
                                                <label><?php esc_attr_e( 'Right', 'woo-multi-currency' ); ?></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php esc_html_e( 'Text color', 'woo-multi-currency' ); ?></label>
                            </th>
                            <td>
                                <input type="text" class="color-picker" name="<?php echo self::set_field( 'text_color' ); ?>" value="<?php echo self::get_field( 'text_color', '#fff' ); ?>" style="background-color: <?php echo esc_attr( self::get_field( 'text_color', '#fff' ) ); ?>">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php esc_html_e( 'Main color', 'woo-multi-currency' ); ?></label>
                            </th>
                            <td>
                                <input type="text" class="color-picker" name="<?php echo self::set_field( 'main_color' ); ?>" value="<?php echo self::get_field( 'main_color', '#f78080' ); ?>" style="background-color: <?php echo esc_attr( self::get_field( 'main_color', '#f78080' ) ); ?>">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php esc_html_e( 'Background color', 'woo-multi-currency' ); ?></label>
                            </th>
                            <td>
                                <input type="text" class="color-picker" name="<?php echo self::set_field( 'background_color' ); ?>" value="<?php echo self::get_field( 'background_color', '#212121' ); ?>" style="background-color: <?php echo esc_attr( self::get_field( 'background_color', '#212121' ) ); ?>">
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <h3><?php esc_html_e( 'Conditional Tags', 'woo-multi-currency' ); ?></h3>
                    <table class="optiontable form-table">
                        <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo self::set_field( 'is_checkout' ); ?>">
                                    <?php esc_html_e( 'Checkout page', 'woo-multi-currency' ); ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php echo self::set_field( 'is_checkout' ); ?>" type="checkbox" <?php checked( self::get_field( 'is_checkout' ), 1 ); ?> tabindex="0" class="hidden" value="1" name="<?php echo self::set_field( 'is_checkout' ); ?>">
                                    <label></label>
                                </div>
                                <p class=""><?php esc_html_e( 'Enable this setting to hide the Currencies Bar on the Checkout page.', 'woo-multi-currency' ); ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo self::set_field( 'is_cart' ); ?>">
                                    <?php esc_html_e( 'Cart page', 'woo-multi-currency' ); ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php echo self::set_field( 'is_cart' ); ?>" type="checkbox" <?php checked( self::get_field( 'is_cart' ), 1 ); ?> tabindex="0" class="hidden" value="1" name="<?php echo self::set_field( 'is_cart' ); ?>">
                                    <label></label>
                                </div>
                                <p class=""><?php esc_html_e( 'Enable this setting to hide the Currencies Bar on the Cart page.', 'woo-multi-currency' ); ?></p>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <h3><?php esc_html_e( 'Custom', 'woo-multi-currency' ); ?></h3>
                    <table class="optiontable form-table">
                        <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php esc_html_e( 'CSS', 'woo-multi-currency' ); ?></label>
                            </th>
                            <td>
                                <textarea class="large-text code" placeholder=".woo-multi-currency{}" name="<?php echo self::set_field( 'custom_css' ); ?>"><?php echo self::get_field( 'custom_css', '' ); ?></textarea>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Checkout !-->
                <div class="vi-ui bottom attached tab segment" data-tab="checkout">
                    <!-- Tab Content !-->
                    <table class="optiontable form-table">
                        <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo self::set_field( 'enable_multi_payment' ); ?>">
                                    <?php esc_html_e( 'Pay in Multiple Currencies', 'woo-multi-currency' ); ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php echo self::set_field( 'enable_multi_payment' ); ?>" type="checkbox" <?php checked( self::get_field( 'enable_multi_payment' ), 1 ); ?> tabindex="0" class="hidden" value="1" name="<?php echo self::set_field( 'enable_multi_payment' ); ?>">
                                    <label><?php esc_html_e( 'Enable this option to allow customers to pay in different currencies', 'woo-multi-currency' ); ?></label>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <p>
                    <button class="vi-ui button labeled button button-primary wmc-submit">
                        <?php esc_html_e( 'Save Changes', 'woo-multi-currency' ); ?>
                    </button>
                </p>
            </form>
        </div>

        <?php
    }
}
