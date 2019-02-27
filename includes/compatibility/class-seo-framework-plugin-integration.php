<?php
/**
 * @package AWPCP/Compatibility
 */

/**
 * Plugin integration for SEO Framework plugin.
 * @since 4.1.0
 */
function awpcp_seo_framework_integration() {
	return new AWPCP_SEOFrameworkIntegration();
}


class AWPCP_SEOFrameworkIntegration {

	/**
	 * @var object
	 */
	private $current_listing;

	/**
	 * @var string
	 */
	private $listing_post_type;


	public function __construct() {
		$container               = awpcp()->container;
		$this->listing_post_type = $container['listing_post_type'];
		$this->attachments       = $container['AttachmentsCollection'];
	}

	/**
	 * @since 4.1.0
	 */
	public function setup() {
		if ( $this->are_required_classes_loaded() ) {
			add_action( 'awpcp_before_configure_frontend_meta', [ $this, 'before_configure_frontend_meta' ] );
		}
	}

	/**
	 * @since 4.1.0
	 */
	private function are_required_classes_loaded() {
		return defined( 'THE_SEO_FRAMEWORK_VERSION' );
	}

	/**
	 * @since 4.1.0
	 */
	public function before_configure_frontend_meta( $meta ) {
		$this->current_listing = $meta->ad;
		$this->is_singular     = is_singular( $this->listing_post_type );
		$this->metadata        = [];

		if ( $this->current_listing ) {
			$this->metadata  = $meta->get_listing_metadata();
			$this->post_meta = get_post_meta( $meta->ad->ID );
		}
		add_filter( 'awpcp-should-generate-opengraph-tags', [ $this, 'configure_opengraph_meta_tags' ] );
	}

	/**
	 * - If the listing has a SEO Framework plugin override, we should use the override (don't forget
	 * to replace any snippet variables included).
	 * - If the listing has no SEO override, generate good default.
	 *
	 * @since 4.1.0
	 */
	public function configure_opengraph_meta_tags() {
		add_filter( 'the_seo_framework_ogtype_output', [ $this, 'filter_opengraph_type' ] );
		add_filter( 'the_seo_framework_ogimage_output', [ $this, 'add_opengraph_images' ] );
		add_filter( 'the_seo_framework_twitterimage_output', [ $this, 'add_opengraph_images' ] );
	}


	/**
	 * @since 4.1.0
	 */
	public function filter_opengraph_type( $type ) {

		if ( ! $this->is_singular ) {
			return $type;
		}

		return $this->metadata['http://ogp.me/ns#type'];
	}


	/**
	 * @since 4.1.0
	 */
	public function add_opengraph_images( $opengraph_image ) {
		if ( ! $this->is_singular ) {
			return $opengraph_image;
		}

		$override = isset( $this->post_meta['_social_image_url'][0] ) ? $this->post_meta['_social_image_url'][0] : null;

		if ( ! empty( $override ) ) {
			return $override;
		}

		$featured_image = $this->attachments->get_featured_attachment_of_type(
			'image',
			[ 'post_parent' => $this->current_listing->ID, ]
		);

		if ( $featured_image ) {
			return $featured_image->guid;
		}

		return $this->metadata['http://ogp.me/ns#image'];
	}
}
