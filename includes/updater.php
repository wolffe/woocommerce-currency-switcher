<?php
// Take over the update check
add_filter( 'pre_set_site_transient_update_plugins', 'wmc_check_for_plugin_update' );

function wmc_check_for_plugin_update( $checked_data ) {
    $api_url     = 'https://getbutterfly.com/web/wp/update/';
    $plugin_slug = 'woo-multi-currency';

    if ( empty( $checked_data->checked ) ) {
        return $checked_data;
    }

    // Check if a transient exists
    $transient_key   = 'wmc_update_' . $plugin_slug;
    $cached_response = get_transient( $transient_key );

    if ( $cached_response === false ) {
        // No transient, prepare API request
        $request_args = [
            'slug'    => $plugin_slug,
            'version' => $checked_data->checked[ $plugin_slug . '/' . $plugin_slug . '.php' ],
        ];

        $request_string = wmc_prepare_request( 'basic_check', $request_args );

        // Start checking for an update
        $raw_response = wp_remote_post( $api_url, $request_string );

        if ( ! is_wp_error( $raw_response ) && ( (int) $raw_response['response']['code'] === 200 ) ) {
            $cached_response = unserialize( $raw_response['body'] );

            if ( is_object( $cached_response ) ) {
                // Cache the response in a transient for 4 hours
                set_transient( $transient_key, $cached_response, 4 * HOUR_IN_SECONDS );
            }
        }
    }

    // Feed the update data into WP updater
    if ( is_object( $cached_response ) && ! empty( $cached_response ) ) {
        $checked_data->response[ $plugin_slug . '/' . $plugin_slug . '.php' ] = $cached_response;
    }

    return $checked_data;
}

// Take over the Plugin info screen
add_filter( 'plugins_api', 'wmc_plugin_api_call', 10, 3 );

function wmc_plugin_api_call( $def, $action, $args ) {
    $api_url     = 'https://getbutterfly.com/web/wp/update/';
    $plugin_slug = 'woo-multi-currency';

    // Return default behavior for non-relevant plugins or non-info actions
    if ( empty( $args->slug ) || $args->slug !== $plugin_slug || $action !== 'plugin_information' ) {
        return $def;
    }

    // Get the current version
    $plugin_info     = get_site_transient( 'update_plugins' );
    $current_version = $plugin_info->checked[ $plugin_slug . '/' . $plugin_slug . '.php' ];
    $args->version   = $current_version;

    $request_string = wmc_prepare_request( $action, $args );

    $request = wp_remote_post( $api_url, $request_string );

    if ( is_wp_error( $request ) ) {
        $res = new WP_Error( 'plugins_api_failed', __( 'An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>', 'woocommerce-conditional-blocks' ), $request->get_error_message() );
    } else {
        $res = unserialize( $request['body'] );

        if ( $res === false ) {
            $res = new WP_Error( 'plugins_api_failed', __( 'An unknown error occurred', 'lighthouse' ), $request['body'] );
        }
    }

    return $res;
}

function wmc_prepare_request( $action, $args ) {
    global $wp_version;

    return [
        'body'       => [
            'action'  => $action,
            'request' => serialize( $args ),
            'api-key' => md5( get_bloginfo( 'url' ) ),
        ],
        'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
    ];
}
