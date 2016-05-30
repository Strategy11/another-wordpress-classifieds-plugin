<?php

function awpcp_send_listing_to_facebook_helper() {
    return new AWPCP_SendListingToFacebookHelper(
        AWPCP_Facebook::instance(),
        awpcp_send_to_facebook_helper(),
        awpcp_listing_renderer(),
        awpcp_listings_collection(),
        awpcp()->settings,
        awpcp_wordpress()
    );
}

class AWPCP_SendListingToFacebookHelper {

    private $facebook_config;
    private $facebook_helper;
    private $listing_renderer;
    private $listings_collection;
    private $settings;
    private $wordpress;

    public function __construct( $facebook_config, $facebook_helper, $listing_renderer, $listings_collection, $settings, $wordpress ) {
        $this->facebook_config = $facebook_config;
        $this->facebook_helper = $facebook_helper;
        $this->listing_renderer = $listing_renderer;
        $this->listings_collection = $listings_collection;
        $this->settings = $settings;
        $this->wordpress = $wordpress;
    }

    public function schedule_listing_if_necessary( $listing ) {
        if ( ! $this->settings->get_option( 'sends-listings-to-facebook-automatically', true ) ) {
            return;
        }

        if ( $this->listing_renderer->is_disabled( $listing ) ) {
            return;
        }

        $is_fb_page_configured = $this->facebook_config->is_page_set();
        $already_sent_to_a_fb_page = $this->wordpress->get_post_meta( $listing->ID, '_awpcp_sent_to_facebook_page', true );

        if ( $is_fb_page_configured && ! $already_sent_to_a_fb_page ) {
            $this->schedule_send_to_facebook_action( $listing );
            return;
        }

        $is_fb_group_configured = $this->facebook_config->is_group_set();
        $already_sent_to_a_fb_group = $this->wordpress->get_post_meta( $listing->ID, '_awpcp_sent_to_facebook_group', true );

        if ( $is_fb_group_configured && ! $already_sent_to_a_fb_group ) {
            $this->schedule_send_to_facebook_action( $listing );
            return;
        }
    }

    private function schedule_send_to_facebook_action( $listing ) {
        $this->wordpress->schedule_single_event(
            time() + 10,
            'awpcp-send-listing-to-facebook',
            array( $listing->ID, $this->wordpress->current_time( 'timestamp' ) )
        );
    }

    public function send_listing_to_facebook( $listing_id ) {
        try {
            $listing = $this->listings_collection->get( $listing_id );
        } catch ( AWPCP_Exception $e ) {
            return;
        }

        try {
            $this->facebook_helper->send_listing_to_facebook_page( $listing );
        } catch ( AWPCP_Exception $e ) {
            // pass
        }

        try {
            $this->facebook_helper->send_listing_to_facebook_group( $listing );
        } catch ( AWPCP_Exception $e ) {
            // pass
        }

        $this->schedule_listing_if_necessary( $listing );
    }
}
