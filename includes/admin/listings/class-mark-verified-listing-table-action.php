<?php
/**
 * @package AWPCP\Admin\Listings
 */

/**
 * Mark Listings as Verified table action.
 */
class AWPCP_MarkVerifiedListingTableAction implements AWPCP_ListTableActionInterface {

    use AWPCP_ModeratorListTableActionTrait;
    use AWPCP_ListTableActionWithMessages;

    /**
     * @var ListingsLogic
     */
    private $listings_logic;

    /**
     * @var ListingRenderer
     */
    private $listing_renderer;

    /**
     * @since 4.0.0
     */
    public function __construct( $roles_and_capabilities, $listings_logic, $listing_renderer ) {
        $this->roles_and_capabilities = $roles_and_capabilities;
        $this->listings_logic         = $listings_logic;
        $this->listing_renderer       = $listing_renderer;
    }

    /**
     * @since 4.0.0
     */
    protected function should_show_action_for_post( $post ) {
        return $this->listing_renderer->is_verified( $post ) ? false : true;
    }

    /**
     * @since 4.0.0
     */
    public function get_title() {
        return _x( 'Mark as Verified', 'listing row action', 'another-wordpress-classifieds-plugin' );
    }

    /**
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_label( $post ) {
        return $this->get_title();
    }

    /**
     * @since 4.0.0
     */
    public function get_url( $post, $current_url ) {
        $params = [
            'action' => 'mark-verified',
            'ids'    => $post->ID,
        ];

        return add_query_arg( $params, $current_url );
    }

    /**
     * @since 4.0.0
     */
    public function process_item( $post ) {
        $this->listings_logic->verify_ad( $post );

        return 'success';
    }

    /**
     * @param string $code      Result code.
     * @param int    $count     Number of posts associated with the given result
     *                          code.
     * @since 4.0.0
     */
    protected function get_message( $code, $count ) {
        if ( 'success' === $code ) {
            $message = _n( 'Ad marked as verified.', '{count} ads marked as verified.', $count, 'another-wordpress-classifieds-plugin' );
            $message = str_replace( '{count}', $count, $message );

            return awpcp_render_dismissible_success_message( $message );
        }

        if ( 'error' === $code ) {
            $message = _n( 'An error occurred trying to mark an ad as verified.', 'An error occurred trying to mark {count} ads as verified.', $count, 'another-wordpress-classifieds-plugin' );
            $message = str_replace( '{count}', $count, $message );

            return awpcp_render_dismissible_error_message( $message );
        }

        return '';
    }
}
