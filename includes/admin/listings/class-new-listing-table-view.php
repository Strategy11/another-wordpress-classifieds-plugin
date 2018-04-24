<?php
/**
 * @package AWPCP\Admin\Listings
 */

/**
 * New view for the Listings table.
 */
class AWPCP_NewListingTableView {

    /**
     * @var object
     */
    private $listings_collection;

    /**
     * @param object $listings_collection   An instance of Listings Collection.
     * @since 4.0.0
     */
    public function __construct( $listings_collection ) {
        $this->listings_collection = $listings_collection;
    }

    /**
     * @since 4.0.0
     */
    public function get_label() {
        return _x( 'New', 'listing view', 'another-wordpress-classifieds-plugin' );
    }

    /**
     * @param string $current_url   The URL of the current admin page.
     * @since 4.0.0
     */
    public function get_url( $current_url ) {
        $params = array(
            'awpcp_filter' => 'new',
        );

        return add_query_arg( $params, $current_url );
    }

    /**
     * @since 4.0.0
     */
    public function get_count() {
        return $this->listings_collection->count_new_listings();
    }

    /**
     * TODO: Setting the query var may remove existing information. We need to merge.
     *
     * @param object $query     An instance of WP_Query.
     * @since 4.0.0
     */
    public function pre_get_posts( $query ) {
        $query->set(
            'meta_query',
            array(
                array(
                    'key'     => '_awpcp_content_needs_review',
                    'value'   => true,
                    'compare' => '=',
                    'type'    => 'BINARY',
                ),
            )
        );
    }
}
