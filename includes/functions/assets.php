<?php
/**
 * @package AWPCP\Functions
 */

/**
 * Takes an array of rules with script or styles IDs as keys and setting
 * IDs as values. If the value of the setting evaluates to true, then the
 * dependency will be added to the optional array of dependencies.
 *
 * Used to register script and styles that have dependencies that can be
 * toggled from the General > Advanced settings page.
 *
 * @since 4.0.0
 */
function awpcp_maybe_add_asset_dependencies( $rules, $dependencies = [] ) {
    foreach ( $rules as $dependency => $setting ) {
        if ( awpcp_should_enqueue_asset( $setting ) ) {
            $dependencies[] = $dependency;
        }
    }

    return $dependencies;
}

/**
 * @since 4.0.0
 */
function awpcp_should_enqueue_asset( $setting ) {
    $setting_value = get_awpcp_option( $setting );

    if ( $setting_value === 'both' ) {
        return true;
    }

    if ( $setting_value === 'none' ) {
        return false;
    }

    if ( is_admin() ) {
        return $setting_value === 'admin';
    }

    return $setting_value === 'frontend';
}

/**
 * @since 4.0.0
 */
function awpcp_maybe_enqueue_font_awesome_style() {
    if ( awpcp_should_enqueue_asset( 'enqueue-font-awesome-style' ) ) {
        wp_enqueue_style( 'awpcp-font-awesome' );
    }
}
