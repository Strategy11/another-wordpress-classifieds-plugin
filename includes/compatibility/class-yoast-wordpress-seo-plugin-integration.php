<?php
/**
 * @package AWPCP/Compatibility
 */

/**
 * Constructor function for Yoast WordPress SEO Plugin Integration.
 */
function awpcp_yoast_wordpress_seo_plugin_integration() {
    $container = awpcp()->container;

    return new AWPCP_YoastWordPressSEOPluginIntegration(
        $container['Meta'],
        awpcp_query(),
        $container['AttachmentsCollection'],
        $container['Request']
    );
}

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class AWPCP_YoastWordPressSEOPluginIntegration {

    private $current_listing;

    /**
     * @var Meta
     */
    private $meta;

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

    public function __construct( $meta, $query, $attachments, $request ) {
        $this->meta        = $meta;
        $this->query       = $query;
        $this->attachments = $attachments;
        $this->request     = $request;
    }

    /**
     * @since 4.0.0
     */
    public function setup() {
        if ( $this->are_required_classes_loaded() ) {
            add_action( 'wp', [ $this, 'prepare_metadata_and_add_hooks' ] );
        }
    }

    /**
     * @since 4.0.0
     */
    private function are_required_classes_loaded() {
        if ( ! defined( 'WPSEO_VERSION' ) ) {
            // Yoast SEO doesn't seem to be loaded. Bail.
            return false;
        }

        if ( ! class_exists( 'WPSEO_OpenGraph_Image' ) ) {
            return false;
        }

        return class_exists( 'WPSEO_OpenGraph' ) && class_exists( 'WPSEO_Meta' );
    }

    /**
     * Configure integration hooks, but only on selected pages.
     *
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
     * @since 4.0.0
     */
    public function prepare_metadata_and_add_hooks() {
        if ( ! $this->query->is_single_listing_page() ) {
            return;
        }

        $post_id = $this->request->get_current_listing_id();

        if ( ! $post_id ) {
            return;
        }

        $post = get_post( $post_id );

        if ( ! is_object( $post ) ) {
            return;
        }

        $this->current_listing = $post;
        $this->is_singular     = is_singular( awpcp()->container['listing_post_type'] );
        $this->metadata        = $this->meta->get_listing_metadata();

        add_filter( 'wpseo_title', [ $this, 'filter_title' ] );
        add_filter( 'wpseo_metadesc', array( $this, 'filter_meta_description' ) );
        add_filter( 'wpseo_canonical', [ $this, 'canonical_url' ] );
        add_filter( 'wpseo_opengraph_type', [ $this, 'filter_opengraph_type' ] );
        add_filter( 'wpseo_opengraph_title', [ $this, 'filter_opengraph_title' ] );
        add_filter( 'wpseo_opengraph_desc', [ $this, 'filter_opengraph_description' ] );
        add_filter( 'wpseo_opengraph_url', [ $this, 'filter_opengraph_url' ] );
        add_filter( 'wpseo_og_article_published_time', [ $this, 'filter_opengraph_published_time' ] );
        add_filter( 'wpseo_og_article_modified_time', [ $this, 'filter_opengraph_modified_time' ] );
        add_filter( 'wpseo_opengraph_show_publish_date', '__return_true' );

        add_action( 'wpseo_add_opengraph_images', [ $this, 'add_opengraph_images' ] );

        add_filter( 'wpseo_twitter_title', [ $this, 'filter_twitter_title' ] );
        add_filter( 'wpseo_twitter_description', [ $this, 'filter_twitter_description' ] );

        add_filter( 'awpcp-should-generate-title', '__return_false' );
        add_filter( 'awpcp-should-generate-basic-meta-tags', '__return_false' );
        add_filter( 'awpcp-should-generate-rel-canonical', '__return_false' );
        add_filter( 'awpcp-should-generate-opengraph-tags', '__return_false' );
    }

    /**
     * @since 4.0.0
     */
    public function filter_title( $title ) {
        $override = WPSEO_Meta::get_value( 'title', $this->current_listing->ID );

        if ( empty( $override ) ) {
            return $this->build_title( $title );
        }

        if ( $this->is_singular ) {
            return $title;
        }

        return wpseo_replace_vars( $override, $this->current_listing );
    }

    private function build_title( $title ) {
        $separator = '';

        if ( function_exists( 'wpseo_replace_vars' ) ) {
            $separator = wpseo_replace_vars( '%%sep%%', array() );
        } elseif ( isset( $GLOBALS['sep'] ) ) {
            $separator = $GLOBALS['sep'];
        }

        return $this->meta->title_builder->build_title( $title, $separator, '' );
    }

    /**
     * @since 4.0.0
     */
    public function filter_meta_description( $description ) {
        $override = WPSEO_Meta::get_value( 'metadesc', $this->current_listing->ID );

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

        return wpseo_replace_vars( $override, $this->current_listing );
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

    /**
     * @since 4.0.0
     */
    public function filter_opengraph_type( $type ) {
        $override = WPSEO_Meta::get_value( 'og_type', $this->current_listing->ID );

        if ( ! empty( $override ) && $this->is_singular ) {
            return $type;
        }

        return 'article';
    }

    /**
     * @since 4.0.0
     */
    public function filter_opengraph_title( $title ) {
        $override = WPSEO_Meta::get_value( 'opengraph-title', $this->current_listing->ID );

        return $this->get_social_title( $title, $override );
    }

    /**
     * @since 4.0.0
     */
    private function get_social_title( $title, $override ) {
        if ( empty( $override ) ) {
            return $this->metadata['http://ogp.me/ns#title'];
        }

        if ( $this->is_singular ) {
            return $title;
        }

        return wpseo_replace_vars( $override, $this->current_listing );
    }

    /**
     * @since 4.0.0
     */
    public function filter_opengraph_description( $description ) {
        $override = WPSEO_Meta::get_value( 'opengraph-description', $this->current_listing->ID );

        return $this->get_social_description( $description, $override );
    }

    /**
     * @since 4.0.0
     */
    public function filter_opengraph_url() {
        return $this->metadata['http://ogp.me/ns#url'];
    }

    /**
     * @since 4.0.0
     */
    public function filter_opengraph_published_time() {
        return $this->metadata['http://ogp.me/ns/article#published_time'];
    }

    /**
     * @since 4.0.0
     */
    public function filter_opengraph_modified_time() {
        return $this->metadata['http://ogp.me/ns/article#modified_time'];
    }

    /**
     * @since 4.0.0
     */
    public function add_opengraph_images( $opengraph_image ) {
        $image_id  = WPSEO_Meta::get_value( 'opengraph-image-id', $this->current_listing->ID );
        $image_url = WPSEO_Meta::get_value( 'opengraph-image', $this->current_listing->ID );

        if ( empty( $image_id ) && empty( $image_url ) ) {
            $this->add_listing_images( $opengraph_image );
            return;
        }

        if ( $this->is_singular ) {
            return;
        }

        $this->add_user_defined_image( $opengraph_image, $image_id, $image_url );
    }

    /**
     * @since 4.0.0
     */
    private function add_listing_images( $opengraph_image ) {
        $featured_image = $this->attachments->get_featured_attachment_of_type(
            'image',
            [
                'post_parent' => $this->current_listing->ID,
            ]
        );

        if ( $featured_image ) {
            $opengraph_image->add_image_by_id( $featured_image->ID );
        }
    }

    /**
     * Copied from WPSEO_OpenGraph_Image::add_image_by_id_or_url().
     *
     * @since 4.0.0
     */
    private function add_user_defined_image( $opengraph_image, $image_id, $image_url ) {
        if ( ! $image_id ) {
            $opengraph_image->add_image_by_url( $image_url );
            return;
        }

        if ( $image_id === constant( 'WPSEO_OpenGraph_Image::EXTERNAL_IMAGE_ID' ) ) {
            $opengraph_image->add_image( [ 'url' => $image_url ] );
            return;
        }

        $opengraph_image->add_image_by_id( $image_id );
    }

    /**
     * @since 4.0.0
     */
    public function filter_twitter_title( $title ) {
        $override = WPSEO_Meta::get_value( 'twitter-title', $this->current_listing->ID );

        return $this->get_social_title( $title, $override );
    }

    /**
     * @since 4.0.0
     */
    public function filter_twitter_description( $description ) {
        $override = WPSEO_Meta::get_value( 'twitter-description', $this->current_listing->ID );

        return $this->get_social_description( $description, $override );
    }
}
