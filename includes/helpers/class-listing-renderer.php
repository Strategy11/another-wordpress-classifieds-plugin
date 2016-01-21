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

    public function get_listing_title( $listing ) {
        return stripslashes( $listing->post_title );
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
        $seoFriendlyUrls = get_awpcp_option('seofriendlyurls');
        $permastruc = get_option('permalink_structure');

        $awpcp_showad_pageid = awpcp_get_page_id_by_ref('show-ads-page-name');
        $base_url = get_permalink($awpcp_showad_pageid);
        $url = false;

        $params = array( 'id' => $listing->ID );

        if($seoFriendlyUrls && isset($permastruc) && !empty($permastruc)) {
            $url = sprintf( '%s/%s', trim( $base_url, '/' ), $listing->ID );

            $region = $listing->get_first_region();

            $parts = array();

            if ( get_awpcp_option( 'include-title-in-listing-url' ) ) {
                $parts[] = sanitize_title( $this->get_listing_title( $listing ) );
            }

            if( get_awpcp_option( 'include-city-in-listing-url' ) && $region ) {
                $parts[] = sanitize_title( awpcp_array_data( 'city', '', $region ) );
            }
            if( get_awpcp_option( 'include-state-in-listing-url' ) && $region ) {
                $parts[] = sanitize_title( awpcp_array_data( 'state', '', $region ) );
            }
            if( get_awpcp_option( 'include-country-in-listing-url' ) && $region ) {
                $parts[] = sanitize_title( awpcp_array_data( 'country', '', $region ) );
            }
            if( get_awpcp_option( 'include-county-in-listing-url' ) && $region ) {
                $parts[] = sanitize_title( awpcp_array_data( 'county', '', $region ) );
            }
            if( get_awpcp_option( 'include-category-in-listing-url' ) ) {
                $parts[] = sanitize_title( $this->get_category_name( $listing ) );
            }

            // always append a slash (RSS module issue)
            $url = sprintf( "%s%s", trailingslashit( $url ), join( '/', array_filter( $parts ) ) );
            $url = user_trailingslashit($url);
        } else {
            $base_url = user_trailingslashit($base_url);
            $url = add_query_arg( urlencode_deep( $params ), $base_url );
        }

        return apply_filters( 'awpcp-listing-url', $url, $listing );
    }

    public function get_edit_listing_url( $listing ) {
        return awpcp_get_edit_listing_url( $listing );
    }

    public function get_delete_listing_url( $listing ) {
        $url = $this->get_edit_listing_url( $listing );
        return apply_filters( 'awpcp-delete-listing-url', $url, $listing );
    }
}
