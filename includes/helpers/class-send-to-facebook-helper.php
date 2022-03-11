<?php
/**
 * @package AWPCP\Listings\Facebook
 */

function awpcp_send_to_facebook_helper() {
    return new AWPCP_SendToFacebookHelper(
        AWPCP_Facebook::instance(),
        awpcp_facebook_integration(),
        awpcp_listing_renderer(),
        awpcp_listings_collection(),
        awpcp()->settings,
        awpcp_wordpress()
    );
}

class AWPCP_SendToFacebookHelper {

    /**
     * @var Facebook
     */
    private $facebook;

    /**
     * @var FacebookIntegration
     */
    private $facebook_integration;

    /**
     * @var ListingsCollection
     */
    private $listings;

    /**
     * @var Settings
     */
    private $settings;

    public function __construct( $facebook, $facebook_integration, $listing_renderer, $listings, $settings, $wordpress ) {
        $this->facebook             = $facebook;
        $this->facebook_integration = $facebook_integration;
        $this->listing_renderer     = $listing_renderer;
        $this->listings             = $listings;
        $this->settings             = $settings;
        $this->wordpress            = $wordpress;
    }

    public function send_listing_to_facebook( $listing_id ) {
    }

    /**
     * @return true     If the ad is successfully send to Facebook. An exception is
     *                  thrown otherwise.
     */
    public function send_listing_to_facebook_page( $listing ) {
        if ( $this->wordpress->get_post_meta( $listing->ID, '_awpcp_sent_to_facebook_page', true ) ) {
            throw new AWPCP_ListingAlreadySharedException( __( 'The ad was already sent to a Facebook Page.', 'another-wordpress-classifieds-plugin' ) );
        }

        if ( ! $this->listing_renderer->is_public( $listing ) ) {
            throw new AWPCP_ListingDisabledException( __( "The ad is currently disabled. If you share it, Facebook servers and users won't be able to access it.", 'another-wordpress-classifieds-plugin' ) );
        }

        $integration_method = $this->settings->get_option( 'facebook-integration-method' );
        $listing_sent       = false;

        if ( empty( $integration_method ) ) {
            throw new AWPCP_NoIntegrationMethodDefined();
        }

        if ( 'webhooks' === $integration_method ) {
            $listing_sent = $this->send_listing_to_facebook_page_using_webhook( $listing );
        }

        if ( ! $listing_sent ) {
            throw new AWPCP_Exception( 'Unknown error.' );
        }

        $this->wordpress->update_post_meta( $listing->ID, '_awpcp_sent_to_facebook_page', true );

        return $listing_sent;
    }

    /**
     * @since 3.8.6
     */
    private function send_listing_to_facebook_page_using_webhook( $listing ) {
        $webhooks = $this->get_webhooks_for_facebook_page_integration( $listing );

        if ( empty( $webhooks ) ) {
            throw new AWPCP_Exception( 'There is no webhook configured to send ads to a Facebook Page.' );
        }

        return $this->process_webhooks( $webhooks );
    }

    /**
     * @since 3.8.6
     */
    private function get_webhooks_for_facebook_page_integration( $listing ) {
        $zapier_webhook      = $this->settings->get_option( 'zapier-webhook-for-facebook-page-integration' );
        $ifttt_webhook_base  = $this->settings->get_option( 'ifttt-webhook-base-url-for-facebook-page-integration' );
        $ifttt_webhook_event = $this->settings->get_option( 'ifttt-webhook-event-name-for-facebook-page-integration' );
        $ifttt_webhook       = $this->build_ifttt_webhook_url( $ifttt_webhook_base, $ifttt_webhook_event );
        $properties          = $this->get_listing_properties( $listing );
        $webhooks            = array();

        if ( $zapier_webhook ) {
            $webhooks['zapier'] = array(
                'url'  => $zapier_webhook,
                'data' => array(
                    'url'         => $properties['url'],
                    'title'       => $properties['title'],
                    'description' => $properties['description'],
                ),
            );
        }

        if ( $ifttt_webhook ) {
            $webhooks['ifttt'] = array(
                'url'  => $ifttt_webhook,
                'data' => array(
                    'value1' => $properties['url'],
                    'value2' => $properties['title'],
                    'value3' => $properties['description'],
                ),
            );
        }

        return $webhooks;
    }

    /**
     * @since 3.8.6
     */
    private function build_ifttt_webhook_url( $base_url, $event_name ) {
        if ( empty( $base_url ) || empty( $event_name ) ) {
            return false;
        }

        if ( ! preg_match( '/' . preg_quote( 'https://maker.ifttt.com/use/', '/' ) . '(\w+)/', $base_url, $matches ) ) {
            return false;
        }

        return "https://maker.ifttt.com/trigger/$event_name/with/key/{$matches[1]}";
    }

    /**
     * @since 3.8.6
     */
    private function get_listing_properties( $listing ) {
        $properties = awpcp_get_ad_share_info( $listing->ID );

        return array(
            'url'         => $properties['url'],
            'title'       => $properties['title'],
            'description' => htmlspecialchars( $properties['description'], ENT_QUOTES, get_bloginfo('charset') ),
        );
    }

    /**
     * @since 3.8.6
     */
    private function process_webhooks( $webhooks ) {
        $webhook_sent = false;

        foreach ( $webhooks as $webhook ) {
            $params = array(
                'headers' => array(
                    'content-type' => 'application/json; charset=' . get_bloginfo( 'charset' ),
                ),
                'body' => wp_json_encode( $webhook['data'] ),
            );

            $response = wp_remote_post( $webhook['url'], $params );

            if ( 200 === intval( wp_remote_retrieve_response_code( $response ) ) ) {
                $webhook_sent = true;
            }
        }

        return $webhook_sent;
    }

    /**
     * Users should choose Friends (or something more public), not Only Me, when the application
     * request the permission, to avoid error:
     *
     * OAuthException: (#200) Insufficient permission to post to target on behalf of the viewer.
     *
     * http://stackoverflow.com/a/19653226/201354
     *
     * @param object $listing   An instance of WP_Post.
     * @throws AWPCP_Exception  If no group has been selected on the configuration.
     * @throws AWPCP_Exception  If the listing was already shared to a Facebook group.
     * @throws AWPCP_Exception  If the listing is not public.
     */
    public function send_listing_to_facebook_group( $listing ) {
        
    }
}
