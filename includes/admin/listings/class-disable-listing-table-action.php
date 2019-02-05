<?php
/**
 * @package AWPCP\Admin\Listings
 */

/**
 * Disable Listing row action for Listings.
 */
class AWPCP_DisableListingTableAction implements AWPCP_ListTableActionInterface {

    /**
     * @var object
     */
    private $listings_logic;

    /**
     * @var object
     */
    private $listing_renderer;

    /**
     * @param object $listings_logic    An instance of Listings API.
     * @param object $listing_renderer  An instance of Listing Renderer.
     * @since 4.0.0
     */
    public function __construct( $listings_logic, $listing_renderer ) {
        $this->listings_logic   = $listings_logic;
        $this->listing_renderer = $listing_renderer;
    }

    /**
     * @param object $post  An instance of WP_Post.
     * @since 4.0.0
     */
    public function should_show_action_for( $post ) {
        return $this->listing_renderer->is_public( $post );
    }

    /**
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_icon_class( $post ) {
        return 'fa fa-times';
    }

    /**
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_title() {
        return _x( 'Disable', 'listing row action', 'another-wordpress-classifieds-plugin' );
    }

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @param object $post  An instance of WP_Post.
     * @since 4.0.0
     */
    public function get_label( $post ) {
        return $this->get_title();
    }

    /**
     * @param object $post          An instance of WP_Post.
     * @param string $current_url   The URL of the current page.
     * @since 4.0.0
     */
    public function get_url( $post, $current_url ) {
        $params = array(
            'action' => 'disable',
            'ids'    => $post->ID,
        );

        return add_query_arg( $params, $current_url );
    }

    /**
     * TODO: Trying to disable an already disabled item should trigger an error/notice.
     * TODO: Perform authorization.
     *
     * @param object $post  An instance of WP_Post.
     * @since 4.0.0
     */
    public function process_item( $post ) {
        if ( $this->listings_logic->disable_listing( $post ) ) {
            return 'success';
        }

        return 'error';
    }

    /**
     * @param array $result_codes   An array of result codes from this action.
     * @since 4.0.0
     */
    public function get_messages( $result_codes ) {
        $messages = array();

        foreach ( $result_codes as $code => $count ) {
            $messages[] = $this->get_message( $code, $count );
        }

        return $messages;
    }

    /**
     * @param string $code      Result code.
     * @param int    $count     Number of posts associated with the given result
     *                          code.
     * @since 4.0.0
     */
    private function get_message( $code, $count ) {
        if ( 'success' === $code ) {
            $message = _n( 'The ad was successfully disabled.', '{count} ads were successfully disabled.', $count, 'another-wordpress-classifieds-plugin' );
            $message = str_replace( '{count}', $count, $message );

            return awpcp_render_dismissible_success_message( $message );
        }

        return '';
    }
}

