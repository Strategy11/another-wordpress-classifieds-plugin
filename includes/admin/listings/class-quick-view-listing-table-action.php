<?php
/**
 * @package AWPCP\Admin\Listings
 */

/**
 * Quick View row action for Listings.
 */
class AWPCP_QuickViewListingTableAction implements AWPCP_ListTableActionInterface {

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @param object $post  An instance of WP_Post.
     * @since 4.0.0
     */
    public function should_show_action_for( $post ) {
        return true;
    }

    /**
     * @since 4.0.0
     */
    public function should_show_as_bulk_action() {
        return false;
    }

    /**
     * @since 4.0.0
     */
    public function get_title() {
        return _x( 'Quick View', 'listings row action', 'another-wordpress-classifieds-plugin' );
    }

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @param object $post  An instance of WP_Post.
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_label( $post ) {
        return $this->get_title();
    }

    /**
     * @param object $post          An instance of WP_Post.
     * @param string $current_url   The URL of the current page.
     * @since 4.0.0
     */
    public function get_url( $post, $current_url ) {
        $params = array(
            'page' => 'awpcp-admin-quick-view-listing',
            'post' => $post->ID,
        );

        return add_query_arg( $params, $current_url );
    }

    /**
     * @param object $post  An instance of WP_Post.
     * @since 4.0.0
     */
    public function process_item( $post ) {
    }

    /**
     * @param array $result_codes   An array of result codes from this action.
     * @since 4.0.0
     */
    public function get_messages( $result_codes ) {
        return array();
    }
}
