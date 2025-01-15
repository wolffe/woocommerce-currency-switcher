<?php
/**
 * Class WOOMULTI_CURRENCY_F_Frontend_Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WOOMULTI_CURRENCY_F_Frontend_Shortcode {
    protected $settings;

    public function __construct() {

        $this->settings = new WOOMULTI_CURRENCY_F_Data();

        $items = $this->settings->get_list_shortcodes();
        foreach ( $items as $k => $item ) {
            if ( $k ) {
                add_shortcode(
                    'woo_multi_currency_' . $k,
                    [
                        $this,
                        'shortcode_' . $k,
                    ]
                );
            }
        }
        add_shortcode( 'woo_multi_currency', [ $this, 'shortcode_woo_multi_currency' ] );
        add_filter( 'wmc_shortcode', [ $this, 'replace_shortcode' ], 10, 2 );
    }

    /**
     * Shortcode Currency selector
     */
    public function shortcode_woo_multi_currency() {
        $args = [ 'settings' => new WOOMULTI_CURRENCY_F_Data() ];

        // Start output buffering
        ob_start();

        wmc_get_template( 'woo-multi-currency-selector.php', $args );

        // Return the buffered content
        return ob_get_clean();
    }

    /**
     * Replace shortcode
     *
     * @param $shortcode
     * @param $data
     *
     * @return string
     */
    public function replace_shortcode( $shortcode, $data ) {
        $layout    = isset( $data['layout'] ) ? $data['layout'] : '';
        $flag_size = isset( $data['flag_size'] ) ? $data['flag_size'] : '';
        $attr      = '';
        if ( $flag_size ) {
            $attr = 'flag_size =1';
        }
        if ( $layout ) {
            $shortcode = '[woo_multi_currency_' . $layout . ' ' . $attr . ']';
        }

        return $shortcode;
    }

    /**
     * Shortcode plain horizontal
     * @return string
     */
    public function shortcode_plain_horizontal( $atts, $content = null ) {

        extract(
            shortcode_atts(
                [
                    'title' => '',
                ],
                $atts
            )
        );
        ob_start();
        if ( $title ) {
            echo '<h3>' . $title . '</h3>';
        }
        $current_currency = $this->settings->get_current_currency();
        $links            = $this->settings->get_links();
        ?>
        <div class="woo-multi-currency shortcode plain-horizontal">
            <?php
            foreach ( $links as $k => $link ) {
                if ( $current_currency ) {
                    if ( $current_currency == $k ) {
                        $class = 'active';
                    } else {
                        $class = '';
                    }
                }

                /*End override*/
                ?>
                <div class="wmc-currency <?php echo esc_attr( $class ); ?>">
                    <a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $k ); ?></a>
                </div>
            <?php } ?>
        </div>
        <?php

        $html = ob_get_clean();

        return $html;
    }

    /**
     * Plain vertical
     *
     * @param      $atts
     * @param null $content
     *
     * @return string
     */
    public function shortcode_plain_vertical( $atts, $content = null ) {

        extract(
            shortcode_atts(
                [
                    'title' => '',
                ],
                $atts
            )
        );
        $links            = $this->settings->get_links();
        $current_currency = $this->settings->get_current_currency();
        ob_start();
        if ( $title ) {
            echo '<h3>' . $title . '</h3>';
        }
        ?>
        <div class="woo-multi-currency shortcode plain-vertical">
            <div class="wmc-currency-wrapper">
                <span class="wmc-current-currency">
                    <?php echo esc_html( $current_currency ); ?>
                </span>
                <div class="wmc-sub-currency">
                    <?php
                    foreach ( $links as $k => $link ) {
                        if ( $current_currency == $k ) {
                            $class = 'active';
                        } else {
                            $class = '';
                        }
                        ?>
                        <div class="wmc-currency <?php echo esc_attr( $class ); ?>">
                            <a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $k ); ?></a>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php

        $html = ob_get_clean();

        return $html;
    }

    /**
     * List Flag Horizontal
     *
     * @param      $atts
     * @param null $content
     *
     * @return string
     */
    public function shortcode_layout3( $atts, $content = null ) {
        extract(
            shortcode_atts(
                [
                    'title'     => '',
                    'flag_size' => 0,
                ],
                $atts
            )
        );
        $current_currency = $this->settings->get_current_currency();
        $links            = $this->settings->get_links();
        ob_start();
        if ( $title ) {
            echo '<h3>' . $title . '</h3>';
        }
        if ( $flag_size ) {
            $ext_url = '256/';
            $class   = 'flag-large';
        } else {
            $ext_url = $class = '';
        }
        ?>
        <div class="woo-multi-currency shortcode plain-horizontal layout3  <?php echo esc_attr( $class ); ?>">
            <?php
            foreach ( $links as $k => $link ) {
                if ( $current_currency ) {
                    if ( $current_currency == $k ) {
                        $class = 'active';
                    } else {
                        $class = '';
                    }
                }
                /*End override*/
                $country = $this->settings->get_country_data( $k );
                ?>
                <div class="wmc-currency <?php echo esc_attr( $class ); ?>">
                    <a title="<?php echo esc_attr( $country['name'] ); ?>" href="<?php echo esc_url( $link ); ?>">
                        <img alt="<?php echo esc_attr( $country['name'] ); ?>"
                            src="<?php echo WOOMULTI_CURRENCY_F_FLAG . $ext_url . $country['code'] . '.png'; ?>">
                    </a>
                </div>
            <?php } ?>
        </div>
        <?php

        $html = ob_get_clean();

        return $html;
    }

    /**
     * List Flags vertical
     *
     * @param      $atts
     * @param null $content
     *
     * @return string
     */
    public function shortcode_layout4( $atts, $content = null ) {

        extract(
            shortcode_atts(
                [
                    'title'     => '',
                    'flag_size' => 0,
                ],
                $atts
            )
        );
        $links            = $this->settings->get_links();
        $current_currency = $this->settings->get_current_currency();
        $country          = $this->settings->get_country_data( $current_currency );
        ob_start();
        if ( $title ) {
            echo '<h3>' . $title . '</h3>';
        }
        if ( $flag_size ) {
            $ext_url = '256/';
            $class   = 'flag-large';
        } else {
            $ext_url = $class = '';
        }
        ?>
        <div class="woo-multi-currency shortcode plain-vertical layout4  <?php echo esc_attr( $class ); ?>">
            <div class="wmc-currency-wrapper">
                <span class="wmc-current-currency">
                    <img alt="<?php echo esc_attr( $country['name'] ); ?>" src="<?php echo WOOMULTI_CURRENCY_F_FLAG . $country['code'] . '.png'; ?>">
                </span>
                <div class="wmc-sub-currency">
                    <?php
                    foreach ( $links as $k => $link ) {
                        if ( $current_currency == $k ) {
                            $class = 'active';
                        }
                        /*End override*/
                        $country = $this->settings->get_country_data( $k );
                        ?>
                        <div class="wmc-currency <?php echo esc_attr( $class ); ?>">
                            <a title="<?php echo esc_attr( $country['name'] ); ?>" href="<?php echo esc_url( $link ); ?>">
                                <img alt="<?php echo esc_attr( $country['name'] ); ?>" src="<?php echo WOOMULTI_CURRENCY_F_FLAG . $ext_url . $country['code'] . '.png'; ?>">
                            </a>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php

        $html = ob_get_clean();

        return $html;
    }

    /**
     * List Flags + Currency code
     *
     * @param      $atts
     * @param null $content
     *
     * @return string
     */
    public function shortcode_layout5( $atts, $content = null ) {

        extract(
            shortcode_atts(
                [
                    'title'     => '',
                    'flag_size' => 0,
                ],
                $atts
            )
        );
        $links            = $this->settings->get_links();
        $current_currency = $this->settings->get_current_currency();
        $country          = $this->settings->get_country_data( $current_currency );
        ob_start();
        if ( $title ) {
            echo '<h3>' . $title . '</h3>';
        }
        if ( $flag_size ) {
            $ext_url = '256/';
            $class   = 'flag-large';
        } else {
            $ext_url = $class = '';
        }
        ?>
        <div class="woo-multi-currency shortcode plain-vertical layout5  <?php echo esc_attr( $class ); ?>">
            <div class="wmc-currency-wrapper">
                <span class="wmc-current-currency">
                    <span class="flags flags-<?php echo $country['code']; ?>" data-bg="<?php echo get_template_directory_uri(); ?>/assets/images/currency-flags.png"></span><?php echo esc_html( $current_currency ); ?>
                </span>
                <div class="wmc-sub-currency">
                    <?php
                    foreach ( $links as $k => $link ) {
                        if ( $current_currency == $k ) {
                            $class = 'active';
                        } else {
                            $class = '';
                        }

                        /*End override*/
                        $country = $this->settings->get_country_data( $k );

                        ?>
                        <div class="wmc-currency <?php echo esc_attr( $class ); ?>">
                            <a title="<?php echo esc_attr( $country['name'] ); ?>" href="<?php echo $link; ?>" rel="nofollow">
                                <span class="flags flags-<?php echo $country['code']; ?>" data-bg="<?php echo get_template_directory_uri(); ?>/assets/images/currency-flags.png"></span>
                                <span>
                                    <?php echo esc_html( $country['name'] ); ?>
                                </span>
                            </a>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php

        $html = ob_get_clean();

        return $html;
    }

    /**
     * Shortcode pain horizontal currencies
     * @return string
     */
    public function shortcode_layout6( $atts, $content = null ) {

        extract(
            shortcode_atts(
                [
                    'title' => '',
                ],
                $atts
            )
        );
        $links            = $this->settings->get_links();
        $current_currency = $this->settings->get_current_currency();
        ob_start();
        if ( $title ) {
            echo '<h3>' . $title . '</h3>';
        }
        ?>
        <div class="woo-multi-currency shortcode plain-horizontal">
            <?php
            foreach ( $links as $k => $link ) {
                if ( $current_currency ) {
                    if ( $current_currency == $k ) {
                        $class = 'active';
                    } else {
                        $class = '';
                    }
                }
                ?>
                <div class="wmc-currency <?php echo esc_attr( $class ); ?>">
                    <a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( get_woocommerce_currency_symbol( $k ) ); ?></a>
                </div>
            <?php } ?>
        </div>
        <?php

        $html = ob_get_clean();

        return $html;
    }

    /**
     * Pain vertical currency symbols
     *
     * @param      $atts
     * @param null $content
     *
     * @return string
     */
    public function shortcode_layout7( $atts, $content = null ) {

        extract(
            shortcode_atts(
                [
                    'title' => '',
                ],
                $atts
            )
        );
        ob_start();
        if ( $title ) {
            echo '<h3>' . $title . '</h3>';
        }
        $current_currency = $this->settings->get_current_currency();
        $symbol           = get_woocommerce_currency_symbol( $current_currency );
        $links            = $this->settings->get_links();
        ?>
        <div class="woo-multi-currency shortcode plain-vertical vertical-currency-symbols">
            <div class="wmc-currency-wrapper">
                <span class="wmc-current-currency">
                    <?php echo $symbol; ?>
                </span>

                <div class="wmc-sub-currency">
                    <?php
                    foreach ( $links as $k => $link ) {

                        if ( $current_currency == $k ) {
                            $class = 'active';
                        } else {
                            $class = '';
                        }

                        ?>
                        <div class="wmc-currency <?php echo esc_attr( $class ); ?>">
                            <a href="<?php echo esc_url( $link ); ?>"><?php echo get_woocommerce_currency_symbol( $k ); ?></a>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php

        $html = ob_get_clean();

        return $html;
    }
}
