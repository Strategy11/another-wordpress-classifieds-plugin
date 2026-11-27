<?php
/**
 * @package AWPCP\Listings
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Increments the ad view counter.
 */
class AWPCP_ListingsViewCounter extends AWPCP_AjaxHandler {

    private $request;
    private $listings_logic;
    private $listings_collection;
    private $listing_renderer;

    /**
     * @since x.x Added the `$listings_collection` and `$listing_renderer` parameters.
     */
    public function __construct( $response, $request, $listings_login, $listings_collection = null, $listing_renderer = null ) {
        parent::__construct( $response );
        $this->request             = $request;
        $this->listings_logic      = $listings_login;
        $this->listings_collection = $listings_collection ? $listings_collection : awpcp_listings_collection();
        $this->listing_renderer    = $listing_renderer ? $listing_renderer : awpcp_listing_renderer();
    }

    public function ajax() {
        // No nonce check for front-end.
        if ( $this->request->is_bot() ) {
            return;
        }

        $listing = $this->get_viewable_listing( $this->request->post( 'listing_id' ) );

        if ( ! $listing ) {
            $this->error();
            return;
        }

        $this->listings_logic->increase_visits_count( $listing );
        $placeholder = awpcp_do_placeholder_legacy_views( $listing, 'awpcpadviews' );
        $this->success( [ 'placeholder' => $placeholder ] );
    }

    /**
     * Get the listing only if it exists and the current user can view it.
     *
     * @since x.x
     *
     * @param int $listing_id The ID of the listing.
     *
     * @return WP_Post|null
     */
    private function get_viewable_listing( $listing_id ) {
        try {
            $listing = $this->listings_collection->get( absint( $listing_id ) );
        } catch ( AWPCP_Exception $e ) {
            return null;
        }

        if ( ! $this->listing_renderer->current_user_can_view( $listing ) ) {
            return null;
        }

        return $listing;
    }
}
