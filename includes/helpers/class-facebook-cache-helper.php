<?php
/**
 * @package AWPCP\Helpers
 */

/**
 * A helper class used to clear ads information from Facebook cache so that
 * the social snippets show up to date content when the URLs are shared.
 */
class AWPCP_FacebookCacheHelper {

    /**
     * @var FacebookIntegration
     */
    private $facebook_integration;

    /**
     * @var ListingRenderer
     */
    private $listing_renderer;

    /**
     * @var ListingsCollection
     */
    private $ads;

    /**
     * @var Settings
     */
    private $settings;

    public function __construct( $facebook_integration, $listing_renderer, $ads, $settings ) {
    }

    public function handle_clear_cache_event_hook( $ad_id ) {

    }
}
