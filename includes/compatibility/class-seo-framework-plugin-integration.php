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
		$this->title_builder   = $meta->title_builder;
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
		add_filter( 'the_seo_framework_pre_get_document_title', [ $this, 'filter_document_title' ] );
		add_filter( 'the_seo_framework_description_output', [ $this, 'filter_listing_description' ] );
		add_filter( 'the_seo_framework_ogtype_output', [ $this, 'filter_opengraph_type' ] );
		add_filter( 'the_seo_framework_ogimage_output', [ $this, 'add_opengraph_images' ] );
		add_filter( 'the_seo_framework_ogtitle_output', [ $this, 'filter_opengraph_title' ] );
		add_filter( 'the_seo_framework_ogurl_output', [ $this, 'filter_opengraph_url' ] );
		add_filter( 'the_seo_framework_twitterimage_output', [ $this, 'add_opengraph_images' ] );
		add_filter( 'the_seo_framework_ogdescription_output', [ $this, 'filter_twitter_description' ] );
		add_filter( 'the_seo_framework_twittertitle_output', [ $this, 'filter_twitter_title' ] );
		add_filter( 'the_seo_framework_rel_canonical_output', [ $this, 'canonical_url' ] );
	}


	/**
	 * @since 4.1.0
	 */
	public function filter_document_title( $title ) {
		$separator = '';
		if ( $this->is_singular ) {
			return $title;
		}

		$override = isset( $this->post_meta['_genesis_title'][0] ) ? $this->post_meta['_genesis_title'][0] : null;

		if ( ! empty( $override ) ) {
			return $override;
		}

		$title = $this->current_listing->post_title;

		return $this->title_builder->build_title( $title, $separator, '' );
	}

	/**
	 * @since 4.1.0
	 */
	public function filter_listing_description( $description ) {
		if ( $this->is_singular ) {
			return $description;
		}

		$override = isset( $this->post_meta['_genesis_description'][0] ) ? $this->post_meta['_genesis_description'][0] : null;

		if ( ! empty( $override ) ) {
			return $override;
		}

		return $this->metadata['http://ogp.me/ns#description'];
	}

	/**
	 * @since 4.1.0
	 */
	public function filter_opengraph_type( $type ) {

		if ( $this->is_singular ) {
			return $type;
		}

		return $this->metadata['http://ogp.me/ns#type'];
	}


	/**
	 * @since 4.1.0
	 */
	public function add_opengraph_images( $opengraph_image ) {
		if ( $this->is_singular ) {
			return $opengraph_image;
		}

		$image_url = isset( $this->post_meta['_social_image_url'] ) ? $this->post_meta['_social_image_url'] : null;

		if ( empty( $image_url ) ) {
			$featured_image = $this->attachments->get_featured_attachment_of_type(
				'image',
				[ 'post_parent' => $this->current_listing->ID, ]
			);

			if ( $featured_image ) {
				return $featured_image->guid;
			}
		}

		return $this->metadata['http://ogp.me/ns#image'];
	}

	/**
	 * @since 4.1.0
	 */
	public function filter_opengraph_title( $title ) {
		if ( $this->is_singular ) {
			return $title;
		}

		$override = isset( $this->post_meta['_open_graph_title'][0] ) ? $this->post_meta['_open_graph_title'][0] : null;

		if ( ! empty( $override ) ) {
			return $override;
		}

		return $this->metadata['http://ogp.me/ns#title'];
	}

	/**
	 * @since 4.1.0
	 */
	public function filter_opengraph_url() {
		return $this->metadata['http://ogp.me/ns#url'];
	}

	/**
	 * @since 4.1.0
	 */
	public function filter_opengraph_description( $description ) {
		if ( $this->is_singular ) {
			return $description;
		}

		$override = isset( $this->post_meta['_open_graph_description'][0] ) ? $this->post_meta['_open_graph_description'][0] : null;

		if ( ! empty( $override ) ) {
			return $override;
		}

		return $this->metadata['http://ogp.me/ns#description'];
	}

	/**
	 * @since 4.1.0
	 */
	public function filter_twitter_title( $title ) {
		if ( $this->is_singular ) {
			return $title;
		}

		$override = isset( $this->post_meta['_twitter_title'][0] ) ? $this->post_meta['_twitter_title'][0] : null;

		if ( ! empty( $override ) ) {
			return $override;
		}

		return $this->metadata['http://ogp.me/ns#title'];
	}

	/**
	 * @since 4.1.0
	 */
	public function filter_twitter_description( $description ) {
		if ( $this->is_singular ) {
			return $description;
		}

		$override = isset( $this->post_meta['_twitter_description'][0] ) ? $this->post_meta['_twitter_description'][0] : null;

		if ( ! empty( $override ) ) {
			return $override;
		}

		return $this->metadata['http://ogp.me/ns#description'];
	}

	/**
	 * TODO: move to a parent class for all SEO plugin integrations.
	 */
	public function canonical_url( $url ) {
		if ( $this->is_singular ) {
			return $url;
		}

		$override = isset( $this->post_meta['_genesis_canonical_uri'][0] ) ? $this->post_meta['_genesis_canonical_uri'][0] : null;

		if ( ! empty( $override ) ) {
			return $override;
		}

		$awpcp_canonical_url = awpcp_rel_canonical_url();

		if ( $awpcp_canonical_url ) {
			return $awpcp_canonical_url;
		}

		return $url;
	}
}
