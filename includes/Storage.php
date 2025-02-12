<?php
namespace Wpdcs\WooCommerceCurrencySwitcher;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * This class defines caching for currency.
 */
class Storage {
    // Hold the class instance.
    private static $_instance = null;

    public $type           = 'session'; //session, transient
    private $user_ip       = null;
    private $transient_key = null;

    public function __construct( $type = '' ) {
        if ( ! empty( $type ) ) {
            $this->type = $type;
        }

        if ( 'session' == $this->type ) {
            add_action( 'template_redirect', [ $this, 'wccs_check_for_existing_session' ] );
        }

        if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
            $this->user_ip       = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP );
            $this->transient_key = md5( $this->user_ip );
        }
    }

    public function wccs_check_for_existing_session() {
        if ( ! session_id() ) {
            session_start();
        }
    }

    public function set_val( $key, $value ) {
        $value = sanitize_text_field( esc_html( $value ) );

        switch ( $this->type ) {
            case 'session':
                $_SESSION[ $key ] = $value;
                break;
            case 'transient':
                $data = get_transient( $this->transient_key );
                if ( ! is_array( $data ) ) {
                    $data = [];
                }
                $data[ $key ] = $value;
                set_transient( $this->transient_key, $data, 1 * 24 * 3600 ); //1 day
                break;
        }
    }

    public function get_val( $key ) {
        $value = null;
        switch ( $this->type ) {
            case 'session':
                if ( isset( $_SESSION[ $key ] ) && $this->is_isset( $key ) ) {
                    $value = isset( $_SESSION[ $key ] ) ? wc_clean( $_SESSION[ $key ] ) : '';
                }
                break;
            case 'transient':
                $data = get_transient( $this->transient_key );
                if ( ! is_array( $data ) ) {
                    $data = [];
                }

                if ( isset( $data[ $key ] ) ) {
                    $value = $data[ $key ];
                }
                break;
        }

        return sanitize_text_field( esc_html( $value ) );
    }

    public function is_isset( $key ) {
        $isset = false;
        switch ( $this->type ) {
            case 'session':
                $isset = isset( $_SESSION[ $key ] );
                break;
            case 'transient':
                $isset = (bool) $this->get_val( $key );
                break;

            default:
                break;
        }

        return $isset;
    }

    public function remove_val( $key ) {
        switch ( $this->type ) {
            case 'session':
                if ( $this->is_isset( $key ) ) {
                    unset( $_SESSION[ $key ] );
                }

                break;
            case 'transient':
                $data = get_transient( $this->transient_key );
                if ( isset( $data[ $key ] ) ) {
                    unset( $data[ $key ] );

                    set_transient( $this->transient_key, $data, 1 * 24 * 3600 );
                }
                break;
        }
    }

    /**
     * Singleton Instance Method to initiate class.
     *
     */
    public static function Instance() {
        if ( null === self::$_instance ) {
            self::$_instance = new Storage();
        }

        return self::$_instance;
    }
}
