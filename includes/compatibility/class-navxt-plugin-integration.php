<?php
/**
 * @package AWPCP/Compatibility
 */

/**
 * Constructor function for NavXT plugin integration.
 */
function awpcp_navxt_plugin_integration() {
    $container = awpcp()->container;

    return new AWPCP_NavXTPluginIntegration( $container['listing_post_type'] );
}

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class AWPCP_NavXTPluginIntegration {

    /**
     * @var object
     */
    private $current_listing;

    /**
     * @var string
     */
    private $listing_post_type;


    public function __construct( $listing_post_type ) {
        $this->listing_post_type = $listing_post_type;
    }

    /**
     * @since 4.1.0
     */
    public function setup() {
        if ( class_exists( 'breadcrumb_navxt' ) ) {
            add_action( 'awpcp_before_configure_frontend_meta', [ $this, 'before_configure_frontend_meta' ] );
        }

        return false;
    }

    /**
     * @since 4.1.0
     */
    public function before_configure_frontend_meta( $meta ) {
        $this->current_listing  = $meta->ad;
        $this->current_category = $meta->category;
        $this->is_singular      = is_singular( $this->listing_post_type );
        add_action( 'bcn_before_fill', array( $this, 'ad_breadcrumb' ), 10 );
        add_action( 'bcn_before_fill', array( $this, 'ad_category_breadcrumb' ), 10 );
    }

    /**
     * @since 4.1.0
     */
    public function ad_breadcrumb() {
        global $post, $wp_query;
        if ( ! $this->current_listing ) {
            return false;
        }
        if ( $this->is_singular ) {
            return false;
        }
        // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        $post                     = $this->current_listing;
        $wp_query->queried_object = $post;
    }

    /**
     * @since 4.1.0
     */
    public function ad_category_breadcrumb() {
        global $wp_query;
        if ( ! $this->current_category ) {
            return false;
        }
        $wp_query->is_archive     = true;
        $wp_query->is_category    = true;
        $wp_query->is_page        = false;
        $wp_query->is_singular    = false;
        $wp_query->queried_object = $this->current_category;
    }
}
