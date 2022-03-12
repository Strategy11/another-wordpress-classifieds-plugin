<?php
/**
 * @package AWPCP\Integrations\Facebook
 */

/**
 * @since 3.8.6
 */
class AWPCP_FacebookIntegration {

    /**
     * @var ListingRenderer
     */
    private $listing_renderer;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @since 3.8.6
     */
    public function __construct( $listing_renderer, $settings, $wordpress ) {
        $this->listing_renderer = $listing_renderer;
        $this->settings         = $settings;
        $this->wordpress        = $wordpress;
    }

    /**
     * @since 3.8.6
     */
    public function on_ad_modified( $ad ) {
    }

    /**
     * @since 3.8.6
     */
    public function schedule_clear_cache_action( $ad, $wait_time = 10 ) {
        $this->schedule_action_seconds_from_now( $ad, 'awpcp-clear-ad-facebook-cache', $wait_time );
    }

    /**
     * @since 3.8.6
     */
    private function schedule_action_seconds_from_now( $ad, $action, $wait_time ) {
        $params = array( $ad->ID, $this->wordpress->current_time( 'timestamp' ) );

        if ( ! wp_next_scheduled( $action, $params ) ) {
            $this->wordpress->schedule_single_event( time() + $wait_time, $action, $params );
        }
    }

    /**
     * @since 3.8.6
     */
    public function maybe_schedelue_send_to_facebook_action( $ad ) {
		_deprecated_function( __FUNCTION__, '4.1.8' );
    }

    /**
     * @since 3.8.6
     */
    public function is_facebook_page_integration_configured() {
        $integration_method = $this->settings->get_option( 'facebook-integration-method' );

        if ( 'webhooks' === $integration_method && $this->settings->get_option( 'zapier-webhook-for-facebook-page-integration' ) ) {
            return true;
        }

        if ( 'webhooks' === $integration_method && $this->is_ifttt_webhook_configured() ) {
            return true;
        }

        return false;
    }

    /**
     * @since 3.8.6
     */
    private function is_ifttt_webhook_configured() {
        if ( ! $this->settings->get_option( 'ifttt-webhook-base-url-for-facebook-page-integration' ) ) {
            return false;
        }

        if ( ! $this->settings->get_option( 'ifttt-webhook-event-name-for-facebook-page-integration' ) ) {
            return false;
        }

        return true;
    }

    /**
     * @since 3.8.6
     */
    public function is_facebook_group_integration_configured() {
        return false;
    }

    /**
     * @since 3.8.6
     */
    private function schedule_send_to_facebook_action( $ad, $wait_time = 10 ) {
        $this->schedule_action_seconds_from_now( $ad, 'awpcp-send-listing-to-facebook', $wait_time );
    }

    /**
     * @since 3.8.6
     */
    public function on_ad_facebook_cache_cleared( $ad ) {
    }
}
