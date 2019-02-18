<?php
/**
 * @pacakge AWPCP\Frontend
 */

/**
 * Renders the content for the individual page of an ad.
 */
class AWPCP_Show_Ad_Page {

	public function __construct() {
		add_filter('awpcp-ad-details', array($this, 'oembed'));
	}

	/**
	 * Acts on awpcp-ad-details filter to add oEmbed support
	 */
	public function oembed($content) {
		global $wp_embed;

		$usecache = $wp_embed->usecache;
		$wp_embed->usecache = false;
		$content = $wp_embed->run_shortcode($content);
		$content = $wp_embed->autoembed($content);
		$wp_embed->usecache = $usecache;

		return $content;
	}

    /**
     * TODO: Get instances of all necessary objects as constructor arguments.
     */
	public function dispatch() {
        $listings_content_renderer = awpcp()->container['ListingsContentRenderer'];
        $listing_id                = awpcp_request()->get_current_listing_id();

        if ( ! $listing_id ) {
            $browse_listings_url = awpcp_get_page_url( 'browse-ads-page-name' );

            $message = __( 'No ad ID was specified. Return to {browse_listings_link}browse all ads{/browse_listings_link}.', 'another-wordpress-classifieds-plugin' );
            $message = str_replace( '{browse_listings_link}', '<a href="' . esc_url( $browse_listings_url ) . '">', $message );
            $message = str_replace( '{/browse_listings_link}', '</a>', $message );

            return awpcp_print_error( $message );
        }

        try {
            $post = awpcp_listings_collection()->get( $listing_id );
        } catch ( AWPCP_Exception $e ) {
            $browse_listings_url = awpcp_get_page_url( 'browse-ads-page-name' );

            $message = __( 'No ad was found with ID equal to {listing_id}. Return to {browse_listings_link}browse all ads{/browse_listings_link}.', 'another-wordpress-classifieds-plugin' );
            $message = str_replace( '{listing_id}', $listing_id, $message );
            $message = str_replace( '{browse_listings_link}', '<a href="' . esc_url( $browse_listings_url ) . '">', $message );
            $message = str_replace( '{/browse_listings_link}', '</a>', $message );

            return awpcp_print_error( $message );
        }

        if ( ! awpcp_request()->is_bot() ) {
            awpcp_listings_api()->increase_visits_count( $post );
        }

        return $listings_content_renderer->render(
            apply_filters( 'the_content', $post->post_content ),
            $post
        );
	}
}
