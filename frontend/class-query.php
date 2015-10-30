<?php

/**
 * @since 3.5.4
 */
function awpcp_query() {
    return new AWPCP_Query();
}

/**
 * @since 3.5.4
 */
class AWPCP_Query {

    public function is_browse_listings_page() {
        return $this->queried_object_is_page_that_has_shortcode( 'AWPCPBROWSEADS' );
    }

    private function queried_object_is_page_that_has_shortcode( $shortcode ) {
        global $wp_the_query;

        if ( ! $wp_the_query || ! $wp_the_query->is_page() ) {
            return false;
        }

        $page = $wp_the_query->get_queried_object();

        if ( ! $page || ! has_shortcode( $page->post_content, $shortcode ) ) {
            return false;
        }

        return true;
    }

    public function is_browse_categories_page() {
        return $this->queried_object_is_page_that_has_shortcode( 'AWPCPBROWSECATS' );
    }
}
