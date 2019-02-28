<?php
/**
 * @package AWPCP/Compatibility
 */

/**
 * Plugin integration for SEO Framework plugin.
 * @since 4.1.0
 */
function awpcp_seo_framework_integration() {
	$container = awpcp()->container;

	return new AWPCP_SEOFrameworkIntegration(
		$container['listing_post_type'],
		awpcp_query(),
		$container['AttachmentsCollection'],
		$container['Request']
	);
}


class AWPCP_SEOFrameworkIntegration {

	private $current_listing;

	/**
	 * @var string
	 */
	private $listing_post_type;

	/**
	 * @var Query
	 */
	private $query;

	/**
	 * @var AttachmentsCollection
	 */
	private $attachments;

	/**
	 * @var Request
	 */
	private $request;


	public function __construct( $listing_post_type, $query, $attachments, $request ) {
		$this->listing_post_type = $listing_post_type;
		$this->query             = $query;
		$this->attachments       = $attachments;
		$this->request           = $request;
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
			$this->metadata = $meta->get_listing_metadata();
		}
		add_filter( 'awpcp-should-generate-opengraph-tags', [ $this, 'configure_opengraph_meta_tags' ] );
	}

	/**
	 * On Show Ad page:
	 * - If the listing has a SEO override, we should use the override (don't forget
	 * to replace any snippet variables included).
	 * - If the listing has no SEO override, generate good default.
	 *
	 * On an Ad own page:
	 * - If the listing has a SEO override, we use the override without attempting
	 * to replace any variables. Yoast must have already done that.
	 * - If the listing has no SEO override, generate a good default.
	 *
	 * @since 4.1.0
	 */
	public function configure_opengraph_meta_tags() {
		add_filter( 'the_seo_framework_pre_get_document_title', [ $this, 'filter_document_title' ] );
		add_filter( 'the_seo_framework_description_output', [ $this, 'filter_listing_description' ] );
		add_filter( 'the_seo_framework_ogtype_output', [ $this, 'filter_opengraph_type' ] );
		add_filter( 'the_seo_framework_ogimage_output', [ $this, 'add_opengraph_images' ] );
		add_filter( 'the_seo_framework_ogtitle_output', [ $this, 'filter_opengraph_title' ] );
		add_filter( 'the_seo_framework_ogdescription_output', [ $this, 'filter_opengraph_description' ] );
		add_filter( 'the_seo_framework_ogurl_output', [ $this, 'filter_opengraph_url' ] );
		add_filter( 'the_seo_framework_available_twitter_cards', [ $this, 'twitter_cards' ] );
		add_filter( 'the_seo_framework_twitterimage_output', [ $this, 'add_opengraph_images' ] );
		add_filter( 'the_seo_framework_twitterdescription_output', [ $this, 'filter_twitter_description' ] );
		add_filter( 'the_seo_framework_twittertitle_output', [ $this, 'filter_twitter_title' ] );
		add_filter( 'the_seo_framework_rel_canonical_output', [ $this, 'canonical_url' ] );

		return false;
	}


	/**
	 * @since 4.1.0
	 */
	public function filter_document_title( $title ) {
		$override = get_post_meta( $this->current_listing->ID, '_genesis_title', true );

		if ( empty( $override ) ) {
			return $this->build_title( $title );
		}

		if ( $this->is_singular ) {
			return $title;
		}

		return $override;
	}

	private function build_title( $title ) {
		$separator = '';

		if ( isset( $GLOBALS['sep'] ) ) {
			$separator = $GLOBALS['sep'];
		}

		return $this->title_builder->build_title( $title, $separator, '' );
	}

	/**
	 * @since 4.1.0
	 */
	public function filter_listing_description( $description ) {
		$override = get_post_meta( $this->current_listing->ID, '_genesis_description', true );

		return $this->get_social_description( $description, $override );
	}

	/**
	 * @since 4.0.0
	 */
	private function get_social_description( $description, $override ) {
		if ( empty( $override ) ) {
			return $this->metadata['http://ogp.me/ns#description'];
		}

		if ( $this->is_singular ) {
			return $description;
		}

		return $override;
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
		$override = get_post_meta( $this->current_listing->ID, '_social_image_url', true );

		if ( empty( $override ) ) {
			$featured_image = $this->attachments->get_featured_attachment_of_type(
				'image',
				[ 'post_parent' => $this->current_listing->ID, ]
			);

			if ( $featured_image ) {
				return $featured_image->guid;
			}

			return $this->metadata['http://ogp.me/ns#image'];
		}

		return $override;
	}


	/**
	 * @since 4.1.0
	 */
	public function filter_opengraph_title( $title ) {
		$override = get_post_meta( $this->current_listing->ID, '_open_graph_title', true );

		return $this->get_social_title( $title, $override );
	}

	/**
	 * @since 4.1.0
	 */
	private function get_social_title( $title, $override ) {
		if ( empty( $override ) ) {
			return $this->metadata['http://ogp.me/ns#title'];
		}

		if ( $this->is_singular ) {
			return $title;
		}

		return $override;
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
		$override = get_post_meta( $this->current_listing->ID, '_open_graph_description', true );

		return $this->get_social_description( $description, $override );
	}

	/**
	 * @since 4.1.0
	 */
	public function filter_twitter_title( $title ) {
		$override = get_post_meta( $this->current_listing->ID, '_twitter_title', true );

		return $this->get_social_title( $title, $override );
	}

	/**
	 * @since 4.1.0
	 */
	public function filter_twitter_description( $description ) {
		$override = get_post_meta( $this->current_listing->ID, '_twitter_description', true );

		return $this->get_social_description( $description, $override );
	}

	/**
	 * TODO: move to a parent class for all SEO plugin integrations.
	 */
	public function canonical_url( $url ) {
		$awpcp_canonical_url = awpcp_rel_canonical_url();

		if ( $awpcp_canonical_url ) {
			return $awpcp_canonical_url;
		}

		return $url;
	}

	public function twitter_cards() {
		return [ 'summary_large_image' ];
	}
}
