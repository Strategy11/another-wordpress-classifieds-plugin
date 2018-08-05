<?php
/**
 * @package AWPCP\Admin\Listings
 */

/**
 * Mark Listing Sold table action.
 */
class AWPCP_MarkSoldListingTableAction implements AWPCP_ListTableActionInterface {

    use AWPCP_ModeratorListTableActionTrait;

    /**
     * @var object
     */
    private $wordpress;

    /**
     * @param object $roles_and_capabilities    An instance of Roles and Capabilities.
     * @param object $wordpress                 An instance of WordPress.
     * @since 4.0.0
     */
    public function __construct( $roles_and_capabilities, $wordpress ) {
        $this->roles_and_capabilities = $roles_and_capabilities;
        $this->wordpress              = $wordpress;
    }

    /**
     * @param object $post  An instance of WP_Post.
     * @since 4.0.0
     */
    protected function should_show_action_for_post( $post ) {
        return ! $this->wordpress->get_post_meta( $post->ID, '_awpcp_is_sold', true );
    }

    /**
     * @param object $post  An instance of WP_Post.
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_label( $post ) {
        return _x( 'Mark as Sold', 'listing row action', 'another-wordpress-classifieds-plugin' );
    }

    /**
     * @param object $post          An instance of WP_Post.
     * @param string $current_url   The URL of the current page.
     * @since 4.0.0
     */
    public function get_url( $post, $current_url ) {
        $params = array(
            'action' => 'mark-sold',
            'ids'    => $post->ID,
        );

        return add_query_arg( $params, $current_url );
    }

    /**
     * @param object $post  An instance of WP_Post.
     * @since 4.0.0
     */
    public function process_item( $post ) {
        if ( ! $this->wordpress->update_post_meta( $post->ID, '_awpcp_is_sold', true ) ) {
            return 'error';
        }

        return 'success';
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
            $message = _n( 'Ad marked as sold.', '{count} ads marked as sold.', $count, 'another-wordpress-classifieds-plugin' );
            $message = str_replace( '{count}', $count, $message );

            return awpcp_render_dismissible_success_message( $message );
        }

        if ( 'error' === $code ) {
            $message = _n( 'An error occurred trying to mark an ad as sold.', 'An error occurred trying to mark {count} ads as sold.', $count, 'another-wordpress-classifieds-plugin' );
            $message = str_replace( '{count}', $count, $message );

            return awpcp_render_dismissible_error_message( $message );
        }

        return '';
    }
}

