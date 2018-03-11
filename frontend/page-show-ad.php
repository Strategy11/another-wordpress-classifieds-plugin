<?php

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

        $post = awpcp_listings_collection()->get( awpcp_request()->get_current_listing_id() );

        return $listings_content_renderer->render(
            apply_filters( 'the_content', $post->post_content ),
            $post
        );
	}
}
