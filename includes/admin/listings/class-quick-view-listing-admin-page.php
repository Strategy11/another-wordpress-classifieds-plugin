<?php
/**
 * @package AWPCP\Admin\Listings
 */

/**
 * Handler for the Quick View Listing admin page.
 */
class AWPCP_QuickViewListingAdminPage {

    /**
     * @var object
     */
    private $content_renderer;

    /**
     * @var object
     */
    private $listing_renderer;

    /**
     * @var object
     */
    private $listings_collection;

    /**
     * @var object
     */
    private $template_renderer;

    /**
     * @var object
     */
    private $wordpress;

    /**
     * @var object
     */
    private $request;

    /**
     * @param object $content_renderer  An instance of Listing Content Renderer.
     * @param object $listing_renderer  An instance of Listing Renderer.
     * @param object $listings_collection   An instance of Listings Collection.
     * @param object $template_renderer     An instance of Template Renderer.
     * @param object $wordpress             An instance of WordPress.
     * @param object $request               An instance of Request.
     * @since 4.0.0
     */
    public function __construct( $content_renderer, $listing_renderer, $listings_collection, $template_renderer, $wordpress, $request ) {
        $this->content_renderer    = $content_renderer;
        $this->listing_renderer    = $listing_renderer;
        $this->listings_collection = $listings_collection;
        $this->template_renderer   = $template_renderer;
        $this->wordpress           = $wordpress;
        $this->request             = $request;
    }

    /**
     * TODO: Do we need to check authorization here?
     *
     * @since 4.0.0
     */
    public function dispatch() {
        $post_id = $this->request->param( 'post' );

        try {
            $post = $this->listings_collection->get( $post_id );
        } catch ( AWPCP_Exception $e ) {
            // TODO: Redirect and show notice.
            return '';
        }

        $template = 'admin/listings/quick-view-listing-admin-page.tpl.php';

        $listing_title = $this->listing_renderer->get_listing_title( $post );

        $edit_listing_link_text = __( 'Edit {listing-title}', 'another-wordpress-classifieds-plugin' );
        $edit_listing_link_text = str_replace( '{listing-title}', '&lsaquo;' . $listing_title . '&rsaquo;', $edit_listing_link_text );

        $params = array(
            'edit_listing_url'       => $this->wordpress->get_edit_post_link( $post ),
            'edit_listing_link_text' => $edit_listing_link_text,
            'listings_url'           => remove_query_arg( array( 'page', 'post', '_wpnonce' ) ),
            'content'                => $this->content_renderer->render_content_with_notices(
                // TODO: Is it a good idea to call `the_content` on the admin?
                apply_filters( 'the_content', $post->post_content ),
                $post
            ),
        );

        return $this->template_renderer->render_template( $template, $params );
    }
}
