<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WOOMULTI_CURRENCY_F_Frontend_Design
 */
class WOOMULTI_CURRENCY_F_Frontend_Design {
    protected $settings;

    public function __construct() {

        $this->settings = new WOOMULTI_CURRENCY_F_Data();

        /*Add order information*/

        add_action( 'wp_footer', [ $this, 'show_action' ] );

        if ( $this->settings->get_enable() ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'front_end_script' ] );
        }
    }

    /**
     * Public
     */
    public function front_end_script() {
        wp_enqueue_style( 'woo-multi-currency', WOOMULTI_CURRENCY_F_CSS . 'woo-multi-currency.css' );

        /*Custom CSS*/
        $text_color       = $this->settings->get_text_color();
        $background_color = $this->settings->get_background_color();
        $main_color       = $this->settings->get_main_color();
        $custom           = '.woo-multi-currency .wmc-list-currencies .wmc-currency.active,.woo-multi-currency .wmc-list-currencies .wmc-currency:hover {background: ' . $main_color . ' !important;}
		.woo-multi-currency .wmc-list-currencies .wmc-currency,.woo-multi-currency .wmc-title {background: ' . $background_color . ' !important;}
		.woo-multi-currency .wmc-title, .woo-multi-currency .wmc-list-currencies .wmc-currency span,.woo-multi-currency .wmc-list-currencies .wmc-currency a {color: ' . $text_color . ' !important;}';
        $custom          .= $this->settings->get_custom_css();
        wp_add_inline_style( 'woo-multi-currency', $custom );
    }

    /**
     * Show Currency converter
     */
    public function show_action() {
        if ( ! $this->enable() ) {
            return;
        }

        $currency_selected = $this->settings->get_current_currency();
        $title             = $this->settings->get_design_title();
        $class             = [];
        /*Position left or right*/
        if ( ! $this->settings->get_design_position() ) {
            $class[] = 'left';
        } else {
            $class[] = 'right';
        }
        $class[] = 'style-1';
        ?>
        <div class="woo-multi-currency <?php echo esc_attr( implode( ' ', $class ) ); ?> bottom wmc-sidebar">
            <div class="wmc-list-currencies">
                <?php if ( $title ) { ?>
                    <div class="wmc-title">
                        <?php echo esc_html( $title ); ?>
                    </div>
                <?php } ?>
                <?php
                $links         = $this->settings->get_links();
                $currency_name = get_woocommerce_currencies();
                foreach ( $links as $k => $link ) {
                    $selected = '';
                    if ( $currency_selected == $k ) {
                        $selected = 'active';
                    }
                    ?>
                    <div class="wmc-currency <?php echo esc_attr( $selected ); ?>">
                        <span><?php echo esc_html( $k ); ?></span>

                        <a href="<?php echo esc_url( $link ); ?>">
                            <?php echo esc_html( $currency_name[ $k ] ); ?>
                        </a>
                    </div>
                <?php } ?>

            </div>
        </div>
        <?php
    }

    /**
     * Check design enable
     * @return bool
     *
     */
    protected function enable() {
        $enable = $this->settings->get_enable_design();
        if ( ! $enable ) {
            return false;
        }
        if ( $this->settings->is_checkout() ) {
            if ( is_checkout() ) {
                return false;
            }
        }
        if ( $this->settings->is_cart() ) {
            if ( is_cart() ) {
                return false;
            }
        }

        return true;
    }
}
