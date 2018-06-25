<?php

function awpcp_facebook_cache_helper() {
    return new AWPCP_FacebookCacheHelper(
        AWPCP_Facebook::instance(),
        awpcp_listing_renderer(),
        awpcp_listings_collection()
    );
}

class AWPCP_FacebookCacheHelper {

    private $facebook;
    private $listing_renderer;
    private $listings;

    public function __construct( $facebook, $listing_renderer, $listings ) {
        $this->facebook = $facebook;
        $this->listing_renderer = $listing_renderer;
        $this->listings = $listings;
    }

    public function on_place_ad( $ad ) {
        $this->schedule_clear_cache_action( $ad );
    }

    private function schedule_clear_cache_action( $listing ) {
        $this->schedule_clear_cache_action_seconds_from_now( $listing, 10 );
    }

    private function schedule_clear_cache_action_seconds_from_now( $ad, $wait_time ) {
        if ( ! wp_next_scheduled( 'awpcp-clear-ad-facebook-cache', array( $ad->ID ) ) ) {
            wp_schedule_single_event( time() + $wait_time, 'awpcp-clear-ad-facebook-cache', array( $ad->ID ) );
        }
    }

    public function on_edit_ad( $ad ) {
        $this->schedule_clear_cache_action( $ad );
    }

    public function on_approve_ad( $ad ) {
        $this->schedule_clear_cache_action( $ad );
    }

    public function handle_clear_cache_event_hook( $ad_id ) {
        try {
            $listing = $this->listings->get( $ad_id );
        } catch ( AWPCP_Exception $e ) {
            return;
        }

        $this->clear_ad_cache( $listing );
    }

    private function clear_ad_cache( $ad ) {
        if ( is_null( $ad ) || ! $this->listing_renderer->is_public( $ad ) ) {
            return;
        }

        $args = array(
            'timeout' => 30,
            'body' => array(
                'id' => url_showad( $ad->ID ),
                'scrape' => true,
                'access_token' => $this->facebook->get( 'user_token' ),
            ),
        );

        $response = wp_remote_post( 'https://graph.facebook.com/', $args  );

        if ( $this->is_successful_response( $response ) ) {
            do_action( 'awpcp-listing-facebook-cache-cleared', $ad );
        } else {
            $this->reschedule_clear_cache_action( $ad );
        }
    }

    private function is_successful_response( $response ) {
        if ( is_wp_error( $response ) || ! is_array( $response ) ) {
            return false;
        } else if ( ! isset( $response['response']['code'] ) ) {
            return false;
        } else if ( $response['response']['code'] != 200 ) {
            return false;
        }

        $listing_info = json_decode( $response['body'] );

        if ( ! isset( $listing_info->type ) || $listing_info->type != 'article' ) {
            return false;
        } else if ( empty( $listing_info->title ) ) {
            return false;
        } else if ( ! isset( $listing_info->description ) ) {
            return false;
        }

        return true;
    }

    private function reschedule_clear_cache_action( $listing ) {
        $this->schedule_clear_cache_action_seconds_from_now( $listing, 5 * MINUTE_IN_SECONDS );
    }
}
