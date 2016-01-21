<?php

/**
 * @since 3.3
 */
function awpcp_listing_renderer() {
    return new AWPCP_ListingRenderer( awpcp_categories_collection() );
}

/**
 * @since 3.3
 */
class AWPCP_ListingRenderer {

    private $categories;

    public function __construct( $categories ) {
        $this->categories = $categories;
    }

    public function get_category_name( $listing ) {
        $categories = $this->categories->find_by_listing_id( $listing->ID );

        if ( empty( $categories ) ) {
            return null;
        }

        return $categories[0]->name;
    }

    public function get_view_listing_link( $listing ) {
        $url = $this->get_view_listing_url( $listing );
        $title = $listing->get_title();

        return sprintf( '<a href="%s" title="%s">%s</a>', $url, esc_attr( $title ), $title );
    }

    public function get_view_listing_url( $listing ) {
        return url_showad( $listing->ad_id );
    }

    public function get_edit_listing_url( $listing ) {
        return awpcp_get_edit_listing_url( $listing );
    }

    public function get_delete_listing_url( $listing ) {
        $url = $this->get_edit_listing_url( $listing );
        return apply_filters( 'awpcp-delete-listing-url', $url, $listing );
    }
}
