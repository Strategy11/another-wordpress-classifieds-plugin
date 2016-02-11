<?php

/**
 * @since 3.3
 */
function awpcp_listing_renderer() {
    return new AWPCP_ListingRenderer(
        awpcp_categories_collection(),
        awpcp_basic_regions_api(),
        awpcp_payments_api(),
        awpcp_wordpress()
    );
}

/**
 * @since 3.3
 */
class AWPCP_ListingRenderer {

    private $categories;
    private $regions;
    private $payments;
    private $wordpress;

    public function __construct( $categories, $regions, $payments, $wordpress ) {
        $this->categories = $categories;
        $this->regions = $regions;
        $this->payments = $payments;
        $this->wordpress = $wordpress;
    }

    public function get_listing_title( $listing ) {
        return stripslashes( $listing->post_title );
    }

    public function get_category( $listing ) {
        $categories = $this->categories->find_by_listing_id( $listing->ID );

        if ( empty( $categories ) ) {
            return null;
        }

        return $categories[0];
    }

    public function get_category_name( $listing ) {
        $category = $this->get_category( $listing );
        return is_object( $category ) ? $category->name : null;
    }

    public function get_category_id( $listing ) {
        $category = $this->get_category( $listing );
        return is_object( $category ) ? $category->term_id : null;
    }

    public function get_contact_name( $listing ) {
        return $this->wordpress->get_post_meta( $listing->ID, '_contact_name', true );
    }

    public function get_contact_email( $listing ) {
        return $this->wordpress->get_post_meta( $listing->ID, '_contact_email', true );
    }

    public function get_access_key( $listing ) {
        return $this->wordpress->get_post_meta( $listing->ID, '_access_key', true );
    }

    /**
     * TODO: Rename to get_formatted_end_date
     * @since feature/1112
     */
    public function get_end_date( $listing ) {
        $end_date = $this->get_plain_end_date( $listing );
        return $this->get_formatted_date( $end_date );
    }

    /**
     * TODO: Rename to get_end_date
     * @since feature/1112
     */
    public function get_plain_end_date( $listing ) {
        return $this->wordpress->get_post_meta( $listing->ID, '_end_date', true );
    }

    /**
     * @since feature/1112
     */
    private function get_formatted_date( $mysql_date ) {
        if ( ! empty( $mysql_date ) ) {
            $formatted_date = awpcp_datetime( 'awpcp-date', strtotime( $mysql_date ) );
        } else {
            $formatted_date = '';
        }

        return $formatted_date;
    }

    /**
     * @since feature/1112
     */
    public function get_start_date( $listing ) {
        $start_date = $this->wordpress->get_post_meta( $listing->ID, '_start_date', true );
        return $this->get_formatted_date( $start_date );
    }

    public function get_regions( $listing ) {
        $regions = array();

        foreach ( $this->regions->find_by_ad_id( $listing->ID ) as $region ) {
            $regions[] = array(
                'country' => $region->country,
                'county' => $region->county,
                'state' => $region->state,
                'city' => $region->city
            );
        }

        return $regions;
    }

    public function get_first_region( $listing ) {
        $regions = $this->get_regions( $listing );
        return count( $regions ) > 0 ? $regions[0] : null;
    }

    public function is_verified( $listing ) {
        if ( $this->wordpress->get_post_meta( $listing->ID, '_verification_needed' ) ) {
            return false;
        }

        return true;
    }

    public function is_disabled( $listing ) {
        return $listing->post_status == 'disabled';
    }

    public function has_expired( $listing ) {
        return $this->has_expired_on_date( $listing, current_time( 'timestamp' ) );
    }

    private function has_expired_on_date( $listing, $timestamp ) {
        $end_date = $this->get_plain_end_date( $listing );

        if ( ! empty( $end_date ) ) {
            $end_date = strtotime( $end_date );
        } else {
            $end_date = 0;
        }

        return $end_date < $timestamp;
    }

    public function is_about_to_expire( $listing ) {
        if ( $this->has_expired( $listing ) ) {
            return false;
        }

        $end_of_date_range = awpcp_calculate_end_of_renew_email_date_range_from_now();
        $one_second_after_end_of_date_range = $end_of_date_range + 1;

        return $this->has_expired_on_date( $listing, $one_second_after_end_of_date_range );
    }

    public function get_payment_status( $listing ) {
        return $this->wordpress->get_post_meta( $listing->ID, '_payment_status', true );
    }

    public function get_payment_term( $listing ) {
        $payment_term_id = $this->wordpress->get_post_meta( $listing->ID, '_payment_term_id', true );
        $payment_term_type = $this->wordpress->get_post_meta( $listing->ID, '_payment_term_type', true );

        return $this->payments->get_payment_term( $payment_term_id, $payment_term_type );
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

            $region = $this->get_first_region( $listing );

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
