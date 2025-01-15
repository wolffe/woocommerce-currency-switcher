<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$currencies       = $settings->get_list_currencies();
$current_currency = $settings->get_current_currency();
$links            = $settings->get_links();
$currency_name    = get_woocommerce_currencies();
?>
<div class="woo-multi-currency shortcode">
    <div class="<?php echo $class; ?> wmc-currency">
        <select class="wmc-nav" onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
            <?php
            foreach ( $links as $code => $link ) {
                ?>
                <option value="<?php echo esc_url( $link ); ?>" <?php selected( $current_currency, $code ); ?>><?php echo esc_html( $currency_name[ $code ] ); ?></option>
                <?php
            }
            ?>
        </select>
    </div>
</div>
