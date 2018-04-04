<?php
/**
 * @package AWPCP\Admin\Listings
 */

/**
 * Send to Facebook Group listing admin action.
 */
class AWPCP_SendToFacebookGroupListingTableAction implements AWPCP_ListTableActionInterface {

    /**
     * @var object
     */
    private $facebook_helper;

    /**
     * @var object
     */
    private $wordpress;

    /**
     * @param object $facebook_helper   An instance of Send To Facebook Helper.
     * @param object $wordpress         An instance of WordPress.
     * @since 4.0.0
     */
    public function __construct( $facebook_helper, $wordpress ) {
        $this->facebook_helper = $facebook_helper;
        $this->wordpress       = $wordpress;
    }

    /**
     * @param object $post  An instance of WP_Post.
     * @since 4.0.0
     */
    public function should_show_action_for( $post ) {
        return ! $this->wordpress->get_post_meta( $post->ID, '_awpcp_sent_to_facebook_group', true );
    }

    /**
     * @param object $post  An instance of WP_Post.
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_label( $post ) {
        return _x( 'Send to Facebook Group', 'listing row action', 'another-wordpress-classifieds-plugin' );
    }

    /**
     * @param object $post          An instance of WP_Post.
     * @param string $current_url   The URL of the current page.
     * @since 4.0.0
     */
    public function get_url( $post, $current_url ) {
        $params = array(
            'action' => 'send-to-facebook-group',
            'ids'    => $post->ID,
        );

        return add_query_arg( $params, $current_url );
    }

    /**
     * @param object $post  An instance of WP_Post.
     * @since 4.0.0
     */
    public function process_item( $post ) {
        try {
            $this->facebook_helper->send_listing_to_facebook_group( $post );
        } catch ( AWPCP_NoFacebookObjectSelectedException $e ) {
            return 'no-group';
        } catch ( AWPCP_ListingAlreadySharedException $e ) {
            return 'already-sent';
        } catch ( AWPCP_ListingDisabledException $e ) {
            return 'disabled';
        } catch ( AWPCP_Exception $e ) {
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
            $message = _n( 'Classified sent to Facebook group.', '{count} classifieds sent to Facebook group.', $count, 'another-wordpress-classifieds-plugin' );
            $message = str_replace( '{count}', $count, $message );

            return awpcp_render_dismissible_success_message( $message );
        }

        if ( 'no-group' === $code ) {
            $message = _n( "1 classified couldn't be sent to Facebook because there is no group selected.", "{count} classifieds couldn't be sent to Facebook because there is no group selected.", $count, 'another-wordpress-classifieds-plugin' );
            $message = str_replace( '{count}', $count, $message );

            return awpcp_render_dismissible_error_message( $message );
        }

        if ( 'disabled' === $code ) {
            $message = _n( "1 classified was not sent to Facebook because it is currenlty disabled. If you share it, Facebook servers and users won't be able to access it.", "{count} classifieds were not sent to Facebook because they are currenlty disabled. If you share them, Facebook servers and users won't be able to access them.", $count, 'another-wordpress-classifieds-plugin' );
            $message = str_replace( '{count}', $count, $message );

            return awpcp_render_dismissible_error_message( $message );
        }

        if ( 'already-sent' === $code ) {
            $message = _n( '1 classified was already sent to the Facebook group.', '{count} classifieds were already sent to the Facebook group.', $count, 'another-wordpress-classifieds-plugin' );
            $message = str_replace( '{count}', $count, $message );

            return awpcp_render_dismissible_error_message( $message );
        }

        if ( 'error' === $code ) {
            $message = _n( 'An error occurred trying to sent a classified to the Facebook group.', 'An error occurred trying to sent {count} classifieds to the Facebook group.', $count, 'another-wordpress-classifieds-plugin' );
            $message = str_replace( '{count}', $count, $message );

            return awpcp_render_dismissible_error_message( $message );
        }

        return '';
    }
}
