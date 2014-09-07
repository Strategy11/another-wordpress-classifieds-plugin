<?php

if ( class_exists( 'WP_CLI_Command' ) ) {

/**
 * Manage transients.
 *
 * ## EXAMPLES
 *
 *     wp transient set my_key my_value 300
 */
class Site_Transient_Command extends WP_CLI_Command {

    /**
     * Get a transient value.
     *
     * @synopsis <key> [--json]
     */
    public function get( $args, $assoc_args ) {
        list( $key ) = $args;

        $value = get_site_transient( $key );

        if ( false === $value ) {
            WP_CLI::warning( 'Site transient with key "' . $key . '" is not set.' );
            exit;
        }

        WP_CLI::print_value( $value, $assoc_args );
    }

    /**
     * Set a transient value. <expiration> is the time until expiration, in seconds.
     *
     * @synopsis <key> <value> [<expiration>]
     */
    public function set( $args ) {
        list( $key, $value ) = $args;

        $expiration = isset( $args[2] ) ? $args[2] : 0;

        if ( set_site_transient( $key, $value, $expiration ) )
            WP_CLI::success( 'Site transient added.' );
        else
            WP_CLI::error( 'Site transient could not be set.' );
    }

    /**
     * Delete a transient value.
     *
     * @synopsis <key>
     */
    public function delete( $args ) {
        list( $key ) = $args;

        if ( delete_site_transient( $key ) ) {
            WP_CLI::success( 'Site transient deleted.' );
        } else {
            if ( get_transient( $key ) )
                WP_CLI::error( 'Site transient was not deleted even though the transient appears to exist.' );
            else
                WP_CLI::warning( 'Site transient was not deleted; however, the transient does not appear to exist.' );
        }
    }

    /**
     * See whether the transients API is using an object cache or the options table.
     */
    public function type() {
        global $_wp_using_ext_object_cache, $wpdb;

        if ( $_wp_using_ext_object_cache )
            $message = 'Transients are saved to the object cache.';
        else
            $message = 'Transients are saved to the ' . $wpdb->prefix . 'options table.';

        WP_CLI::line( $message );
    }

    /**
     * Delete all expired transients.
     *
     * @subcommand delete-expired
     */
    public function delete_expired() {
        global $wpdb, $_wp_using_ext_object_cache;

        // Always delete all transients from DB too.
        $time = current_time('timestamp');
        $count = $wpdb->query(
            "DELETE a, b FROM $wpdb->options a, $wpdb->options b WHERE
            a.option_name LIKE '\_site_transient\_%' AND
            a.option_name NOT LIKE '\_site_transient\_timeout\_%' AND
            b.option_name = CONCAT( '_site_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
            AND b.option_value < $time"
        );

        if ( $count > 0 ) {
            WP_CLI::success( "$count expired site transients deleted from the database." );
        } else {
            WP_CLI::success( "No expired site transients found" );
        }

        if ( $_wp_using_ext_object_cache ) {
            WP_CLI::warning( 'Transients are stored in an external object cache, and this command only deletes those stored in the database. You must flush the cache to delete all transients.');
        }
    }

    /**
     * Delete all transients.
     *
     * @subcommand delete-all
     */
    public function delete_all() {
        global $wpdb, $_wp_using_ext_object_cache;

        // Always delete all transients from DB too.
        $count = $wpdb->query(
            "DELETE FROM $wpdb->options
            WHERE option_name LIKE '\_site\_transient\_%'"
        );

        if ( $count > 0 ) {
            WP_CLI::success( "$count site transients deleted from the database." );
        } else {
            WP_CLI::success( "No site transients found" );
        }

        if ( $_wp_using_ext_object_cache ) {
            WP_CLI::warning( 'Transients are stored in an external object cache, and this command only deletes those stored in the database. You must flush the cache to delete all transients.');
        }
    }

}

WP_CLI::add_command( 'site-transient', 'Site_Transient_Command' );

}
