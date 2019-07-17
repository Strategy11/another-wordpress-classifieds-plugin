<?php
/**
 * Common methods for the Renew Listing table actions available for subscribers
 * and moderators.
 *
 * @package AWPCP\Admin\Listings
 */

/**
 * @since 4.0.0
 */
trait AWPCP_RenewListingTableAction {

    /**
     * @since 4.0.0
     *
     * @param object $post  An instance of WP_Post.
     */
    public function should_show_action_for( $post ) {
        if ( $this->listing_renderer->is_about_to_expire( $post ) ) {
            return true;
        }

        if ( $this->listing_renderer->has_expired( $post ) ) {
            return true;
        }

        return false;
    }

    /**
     * @since 4.0.0
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_icon_class( $post ) {
        return 'fa fa-redo';
    }

    /**
     * @since 4.0.0
     */
    public function get_title() {
        return _x( 'Renew', 'listing row action', 'another-wordpress-classifieds-plugin' );
    }

    /**
     * @since 4.0.0
     *
     * @param object $post  An instance of WP_Post.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_label( $post ) {
        return $this->get_title();
    }
}
