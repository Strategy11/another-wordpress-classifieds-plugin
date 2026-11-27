<?php
/**
 * @package AWPCP\Listings
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Loop Integration adds filter handlers to overwrite
 * some of the attributes of the pages used by the plugin
 * to display classifieds content.
 */
class AWPCP_Loop_Integration {

    /**
     * @var object.
     */
    private $listing_renderer;

    /**
     * @var object.
     */
    private $listings;

    /**
     * @var object.
     */
    private $wordpress;

    /**
     * @var object.
     */
    private $request;

    /**
     * Constructor.
     *
     * @param object $listing_renderer An instance of Listing Renderer.
     * @param object $listings         An instance of Listings Collection.
     * @param object $wordpress        An instance of WordPress.
     * @param object $request          An instnace of Request.
     */
    public function __construct( $listing_renderer, $listings, $wordpress, $request ) {
        $this->listing_renderer = $listing_renderer;
        $this->listings         = $listings;
        $this->wordpress        = $wordpress;
        $this->request          = $request;
    }

    /**
     * Setup filters and actions handlers.
     */
    public function setup() {
        if ( is_page( $this->get_show_listing_page_id() ) && $this->request->get_current_listing_id() ) {
            add_filter( 'the_title', array( $this, 'maybe_overwrite_page_title' ), 10, 2 );
        }

        if ( is_singular( awpcp()->container['listing_post_type'] ) ) {
            add_filter( 'the_title', array( $this, 'maybe_hide_listing_title' ), 10, 2 );
            add_filter( 'single_post_title', array( $this, 'maybe_hide_listing_title' ), 10, 2 );
        }
    }

    /**
     * Replaces the title of a listing shown as a single post with the title
     * of the Show Listing page when the current user is not allowed to view
     * the listing.
     *
     * @since x.x
     *
     * @param string      $title The title of the post.
     * @param int|WP_Post $post  The post ID or object being processed.
     *
     * @return string
     */
    public function maybe_hide_listing_title( $title, $post = null ) {
        $post_id = is_object( $post ) ? $post->ID : (int) $post;
        $page_id = $this->get_show_listing_page_id();

        // Only the queried listing; also prevents recursion through get_the_title().
        if ( ! $post_id || get_queried_object_id() !== $post_id || $page_id <= 0 ) {
            return $title;
        }

        try {
            $listing = $this->listings->get( $post_id );
        } catch ( AWPCP_Exception $e ) {
            return $title;
        }

        if ( $this->listing_renderer->current_user_can_view( $listing ) ) {
            return $title;
        }

        return get_the_title( $page_id );
    }

    /**
     * Helper function used to retrie the ID of the Show Listing page.
     */
    private function get_show_listing_page_id() {
        return awpcp_get_page_id_by_ref( 'show-ads-page-name' );
    }

    /**
     * Attempts to replace the title of the Show Listing page
     * with the title of the listing being displayed.
     *
     * @param string $title     The title of the post.
     * @param int    $post_id   The ID of the post that is being processed.
     */
    public function maybe_overwrite_page_title( $title, $post_id = null ) {
        if ( is_null( $post_id ) ) {
            $post    = $this->wordpress->get_post();
            $post_id = isset( $post->ID ) ? $post->ID : null;
        }

        if ( $this->get_show_listing_page_id() !== $post_id ) {
            return $title;
        }

        $listing_id = $this->request->get_current_listing_id();

        try {
            $listing = $this->listings->get( $listing_id );
        } catch ( AWPCP_Exception $e ) {
            return $title;
        }

        if ( ! $this->listing_renderer->current_user_can_view( $listing ) ) {
            return $title;
        }

        return $this->listing_renderer->get_listing_title( $listing );
    }
}
