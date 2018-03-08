<?php
/**
 * @package AWPCP\Listings
 */

/**
 * Class that renders the information shown on individual lisitngs pages.
 */
class AWPCP_ListingsContentRenderer {

    /**
     * @var object
     */
    private $listing_renderer;

    /**
     * @param object $listing_renderer  An instance of ListingRenderer.
     */
    public function __construct( $listing_renderer ) {
        $this->listing_renderer = $listing_renderer;
    }

    /**
     * @param string $content   The content of the post as passed to the
     *                          `the_content` filter.
     * @param object $post      An instance of WP_Post.
     * @since 4.0.0
     */
    public function render( $content, $post ) {
        $output = apply_filters( 'awpcp-show-listing-content-replacement', null, $content, $post );

        if ( ! is_null( $output ) ) {
            return $output;
        }

        // Filters to provide alternative method of storing custom layouts.
        if ( has_action( 'awpcp_single_ad_template_action' ) || has_filter( 'awpcp_single_ad_template_filter' ) ) {
            do_action( 'awpcp_single_ad_template_action' );
            return apply_filters( 'awpcp_single_ad_template_filter' );
        }

        if ( $this->listing_renderer->is_disabled( $post ) && ! $this->current_user_can_see_disabled_listing( $post ) ) {
            $message = __( 'The Ad you are trying to view is pending approval. Once the Administrator approves it, it will be active and visible.', 'another-wordpress-classifieds-plugin' );
            return awpcp_print_error( $message );
        }

        // TODO: We may need to move this to a different place to avoid over-counting.
        if ( ! awpcp_request()->is_bot() ) {
            awpcp_listings_api()->increase_visits_count( $post );
        }

        $show_messages = true;

        if ( $show_messages ) {
            return implode( "\n", $this->render_messages( $post ) ) . $this->render_content( $content, $post );
        }

        return $this->render_content( $content, $post );
    }

    /**
     * @param object $post An instance of WP_Post.
     * @since 4.0.0
     */
    private function current_user_can_see_disabled_listing( $post ) {
        $is_preview = false;

        $current_user_is_moderator     = awpcp_current_user_is_moderator();
        $current_user_is_listing_owner = $this->current_user_is_listing_owner( $post );

        return $current_user_is_moderator || $current_user_is_listing_owner || $is_preview;
    }

    /**
     * @param object $post An instance of WP_Post.
     * @since 4.0.0
     */
    private function current_user_is_listing_owner( $post ) {
        if ( $post->post_author > 0 && wp_get_current_user()->ID === $post->post_author ) {
            return true;
        }

        return false;
    }

    /**
     * @param object $post An instance of WP_Post.
     * @since 4.0.0
     */
    private function render_messages( $post ) {
        $messages = array();

        $is_listing_disabled = $this->listing_renderer->is_disabled( $post );
        $is_listing_verified = $this->listing_renderer->is_verified( $post );

        if ( awpcp_request_param( 'verified' ) && $is_listing_verified ) {
            $messages[] = awpcp_print_message( __( 'Your email address was successfully verified.', 'another-wordpress-classifieds-plugin' ) );
        }

        if ( $is_listing_disabled ) {
            $warnings = $this->get_disabled_listing_warnings( $post );
            $messages = array_merge( $messages, $warnings );
        } elseif ( ! $is_listing_verified ) {
            $warnings = $this->get_unverified_listing_warnings( $post );
            $messages = array_merge( $messages, $warnings );
        }

        return $messages;
    }

    /**
     * @param object $post An instance of WP_Post.
     * @since 4.0.0
     */
    private function get_disabled_listing_warnings( $post ) {
        $warnings = array();

        if ( awpcp_current_user_is_moderator() ) {
            $message    = __( 'This Ad is currently disabled until the Administrator approves it. Only you (the Administrator) and the author can see it.', 'another-wordpress-classifieds-plugin' );
            $warnings[] = awpcp_print_error( $message );
        } elseif ( $this->current_user_can_see_disabled_listing( $post ) ) {
            $message    = __( 'This Ad is currently disabled until the Administrator approves it. Only you (the author) can see it.', 'another-wordpress-classifieds-plugin' );
            $warnings[] = awpcp_print_error( $message );
        }

        return $warnings;
    }

    /**
     * @param object $post An instance of WP_Post.
     * @since 4.0.0
     */
    private function get_unverified_listing_warnings( $post ) {
        $warnings = array();

        if ( $this->current_user_can_see_disabled_listing( $post ) ) {
            $message    = __( 'This Ad is currently disabled until you verify the email address used for the contact information. Only you (the author) can see it.', 'another-wordpress-classifieds-plugin' );
            $warnings[] = awpcp_print_error( $message );
        }

        return $warnings;
    }

    /**
     * Handles AWPCPSHOWAD shortcode.
     *
     * @param string $content   The content of the post.
     * @param object $post      An instance of WP_Post.
     * @return Show Ad page content.
     */
    public function render_content( $content, $post ) {
        /* Enqueue necessary scripts. */
        awpcp_maybe_add_thickbox();
        wp_enqueue_script( 'awpcp-page-show-ad' );

        $awpcp = awpcp();

        $awpcp->js->set( 'page-show-ad-flag-ad-nonce', wp_create_nonce( 'flag_ad' ) );

        $awpcp->js->localize(
            'page-show-ad', array(
                'flag-confirmation-message' => __( 'Are you sure you want to flag this ad?', 'another-wordpress-classifieds-plugin' ),
                'flag-success-message'      => __( 'This Ad has been flagged.', 'another-wordpress-classifieds-plugin' ),
                'flag-error-message'        => __( 'An error occurred while trying to flag the Ad.', 'another-wordpress-classifieds-plugin' ),
            )
        );

        $preview = false;

        if ( isset( $preview ) && $preview ) {
            $preview = true;
        } elseif ( 'preview' === awpcp_request_param( 'adstatus' ) ) {
            $preview = true;
        }

        $content_before_page = apply_filters( 'awpcp-content-before-listing-page', '' );
        $content_after_page  = apply_filters( 'awpcp-content-after-listing-page', '' );

        $output = '<div id="classiwrapper">%s<!--awpcp-single-ad-layout-->%s</div><!--close classiwrapper-->';
        $output = sprintf( $output, $content_before_page, $content_after_page );

        $layout = awpcp_get_listing_single_view_layout( $post );
        $layout = awpcp_do_placeholders( $post, $layout, 'single' );

        $output = str_replace( '<!--awpcp-single-ad-layout-->', $layout, $output );
        $output = apply_filters( 'awpcp-show-ad', $output, $post->ID );

        return $output;
    }
}
