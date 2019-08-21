<?php
/**
 * @package AWPCP\Listings
 */

/**
 * Authorization logic for several listing related operations.
 */
class AWPCP_ListingAuthorization {

    /**
     * @var object
     */
    private $listing_renderer;

    /**
     * @var object
     */
    private $roles;

    /**
     * @var object
     */
    private $settings;

    /**
     * @var object
     */
    private $request;

    /**
     * @param object $listing_renderer  An instance of Listing Renderer.
     * @param object $roles             An instance of Roles And Capabilities.
     * @param object $settings          An instance of SettingsAPI.
     * @param object $request           An instance of Request.
     */
    public function __construct( $listing_renderer, $roles, $settings, $request ) {
        $this->listing_renderer = $listing_renderer;
        $this->roles            = $roles;
        $this->settings         = $settings;
        $this->request          = $request;
    }

    /**
     * @since 4.0.0
     */
    public function is_current_user_allowed_to_submit_listing() {
        if ( ! $this->settings->get_option( 'onlyadmincanplaceads' ) ) {
            return true;
        }

        if ( $this->roles->current_user_is_administrator() ) {
            return true;
        }

        return false;
    }

    /**
     * @param object $listing   An instance of WP_Post.
     */
    public function is_current_user_allowed_to_edit_listing( $listing ) {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        if ( $this->roles->current_user_is_moderator() ) {
            return true;
        }

        if ( absint( $listing->post_author ) === $this->request->get_current_user()->ID ) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether current user can edit the start date of the listing.
     *
     * See https://github.com/drodenbaugh/awpcp/issues/1906#issuecomment-328189213
     * for a description of the editable start date feature.
     *
     * @since 4.0.0
     *
     * @param object $listing An instance of WP_Post.
     */
    public function is_current_user_allowed_to_edit_listing_start_date( $listing ) {
        if ( $this->roles->current_user_is_moderator() ) {
            return true;
        }

        if ( $this->settings->get_option( 'allow-start-date-modification' ) ) {
            return true;
        }

        $start_date = $this->listing_renderer->get_start_date( $listing );

        if ( empty( $start_date ) ) {
            return true;
        }

        if ( strtotime( $start_date ) > current_time( 'timestamp' ) ) {
            return true;
        }

        return false;
    }

    /**
     * @param object $listing   An instance of WP_Post.
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function is_current_user_allowed_to_edit_listing_end_date( $listing ) {
        return $this->roles->current_user_is_moderator();
    }
}
