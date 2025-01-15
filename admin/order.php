<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WOOMULTI_CURRENCY_F_Admin_Order {
    function __construct() {

        add_action( 'admin_init', [ $this, 'add_metabox' ], 1 );
    }

    /**
     * Add metabox to order post
     */
    public function add_metabox() {
        add_meta_box(
            'wmc_order_metabox',
            __( 'Currency Information', 'woo-multi-currency' ),
            [
                $this,
                'order_metabox',
            ],
            'shop_order',
            'side',
            'default'
        );
        add_action( 'manage_shop_order_posts_custom_column', [ $this, 'currency_columns' ], 2 );
    }

    public function currency_columns( $col ) {
        global $post, $the_order;

        if ( empty( $the_order ) || $the_order->get_id() !== $post->ID ) {
            $the_order = wc_get_order( $post->ID );
        }
        if ( $col == 'order_total' ) { ?>
        <div class="wmc-order-currency">
            <?php echo esc_html( 'Currency: ', 'woo-multi-currency' ) . get_post_meta( $the_order->get_id(), '_order_currency', true ); ?>
            </div>
            <?php
        }
    }

    /**
     * @param $post
     */
    public function order_metabox( $post ) {
        $order = new WC_Order( $post->ID );

        $order_currency = get_post_meta( $order->get_id(), '_order_currency', true );
        $wmc_order_info = get_post_meta( $order->get_id(), 'wmc_order_info', true );

        //      $rate           = 0;
        $has_info = 1;
        if ( ! isset( $wmc_order_info ) || ! is_array( $wmc_order_info ) ) {
            $has_info = 0;
        }

        ?>
        <div id="wmc_order_metabox">
            <?php if ( ! $has_info ) { ?>
                <p style="color:red"><?php esc_html_e( 'This order created when multi currency disabled, so base currency and rate is current info', 'woo-multi-currency' ); ?></p>
                <?php
            } else {
                foreach ( $wmc_order_info as $code => $currency_info ) {
                    if ( isset( $currency_info['is_main'] ) && $currency_info['is_main'] == 1 ) {
                        $wmc_order_base_currency = $code;
                        break;
                    }
                }

                $rate = $wmc_order_info[ $order_currency ]['rate'];

                ?>
                <div id="wmc_order_currency_text">
                    <p>
                        <?php esc_html_e( 'Currency', 'woo-multi-currency' ); ?> :
                        <span><?php echo $order_currency; ?></span>
                    </p>
                </div>
                <div id="wmc_order_base_currency">
                    <p>
                        <?php esc_html_e( 'Base on Currency', 'woo-multi-currency' ); ?>
                        : <span><?php echo $wmc_order_base_currency; ?></span>
                    </p>
                </div>
                <div id="wmc_order_base_currency">
                    <p>
                        <?php esc_html_e( 'Currency Rate', 'woo-multi-currency' ); ?>
                        : <span><?php echo $rate; ?></span>
                    </p>
                </div>
            <?php } ?>
        </div>
    <?php }
}

?>
