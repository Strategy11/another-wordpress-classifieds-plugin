<?php
/**
 * @package AWPCP\Listings
 */

/**
 * Integrates with WordPress to render full listings.
 */
class AWPCP_ListingsContent {

    /**
     * @var string A post type identifier.
     */
    private $post_type;

    /**
     * @var object  An instance of Listing Content Renderer.
     */
    private $content_renderer;

    /**
     * @var object
     */
    private $wordpress;

    /**
     * @param string $post_type         The identifier of the Listing post type.
     * @param object $content_renderer  An instance of Listing Content Renderer.
     * @param object $wordpress         An instance of WordPress.
     * @since 4.0.0
     */
    public function __construct( $post_type, $content_renderer, $wordpress ) {
        $this->post_type        = $post_type;
        $this->content_renderer = $content_renderer;
        $this->wordpress        = $wordpress;
    }

    /**
     * Make sure disabled posts are returned in the posts array
     * in order to avoid a not found page and display a message instead.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function return_pending_post( $posts, $wp_query ) {
        // abort if $posts is not empty, this query ain't for us...
        if ( count( $posts ) ) {
            return $posts;
        }

        $post_id = get_query_var( 'p' );

        // get our post instead and return it as the result...
        if ( ! empty( $post_id ) ) {
            $post = get_post( $post_id );
            if ( $this->post_type !== $post->post_type ) {
                return false;
            }
            return array( get_post( $post_id ) );
        }
    }

    /**
     * Handle for the `the_content` filter.
     *
     * @param string $content   The content of the current post.
     * @since 4.0.0
     */
    public function filter_content( $content ) {
        $post = $this->wordpress->get_post();

        if ( ! $post ) {
            return $content;
        }

        if ( $this->post_type !== $post->post_type ) {
            return $content;
        }

        if ( ! is_singular( $this->post_type ) ) {
            return $content;
        }

        return $this->content_renderer->render( $content, $post );
    }
}
