<?php
/**
 * @package AWPCP/Frontend
 */

/**
 * @since 3.6
 */
function awpcp_query() {
    return new AWPCP_Query(
        awpcp()->container['listing_post_type']
    );
}

/**
 * @since 3.6
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class AWPCP_Query {

    /**
     * @var string
     */
    private $listing_post_type;

    /**
     * @since 4.0.0
     */
    public function __construct( $listing_post_type ) {
        $this->listing_post_type = $listing_post_type;
    }

    public function is_post_listings_page() {
        return $this->is_page_that_has_shortcode( 'AWPCPPLACEAD' );
    }

    public function is_edit_listing_page() {
        return $this->is_page_that_has_shortcode( 'AWPCPEDITAD' );
    }

    public function is_single_listing_page() {
        $is_single_listing_page = false;

        if ( $this->is_singular_listing_page() || $this->is_page_that_has_shortcode( 'AWPCPSHOWAD' ) ) {
            $is_single_listing_page = true;
        }

        // @phpcs:disable WordPress.NamingConventions.ValidHookName.UseUnderscores
        return apply_filters( 'awpcp-is-single-listing-page', $is_single_listing_page );
        // @phpcs:enable WordPress.NamingConventions.ValidHookName.UseUnderscores
    }

    public function is_reply_to_listing_page() {
        return $this->is_page_that_has_shortcode( 'AWPCPREPLYTOAD' );
    }

    public function is_browse_listings_page() {
        return $this->is_page_that_has_shortcode( 'AWPCPBROWSEADS' );
    }

    public function is_page_that_has_shortcode( $shortcode ) {
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
        return $this->is_browse_listings_page();
    }

    public function is_renew_listing_page() {
        return $this->is_page_that_has_shortcode( 'AWPCP-RENEW-AD' );
    }

    public function is_page_that_accepts_payments() {
        $accept_payments = [ 'AWPCPPLACEAD', 'AWPCP-BUY-SUBSCRIPTION', 'AWPCP-RENEW-AD', 'AWPCPBUYCREDITS' ];

        foreach ( $accept_payments as $shortcode ) {
            if ( $this->is_page_that_has_shortcode( $shortcode ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true only when the current request is for a listing displayed on
     * its own page (instead of through the Show Ads page).
     *
     * @since 4.0.0
     */
    public function is_singular_listing_page() {
        return is_singular( $this->listing_post_type );
    }
}
