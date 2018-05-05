<?php

/**
 * Authorization logic for several listing related operations.
 */
class AWPCP_ListingAuthorization {

    /**
     * @var object
     */
    private $listing_renderer;

    private $roles;

    /**
     * @var object
     */
    private $settings;

    private $request;

    public function __construct( $listing_renderer, $roles, $settings, $request ) {
        $this->listing_renderer = $listing_renderer;
        $this->roles            = $roles;
        $this->settings         = $settings;
        $this->request          = $request;
    }

    public function is_current_user_allowed_to_edit_listing( $listing ) {
        if ( $this->roles->current_user_is_moderator() ) {
            return true;
        }

        if ( is_user_logged_in() && $listing->post_author == $this->request->get_current_user()->ID ) {
            return true;
        }

        return false;
    }

    /**
     * @since 4.0.0
     */
    public function is_current_user_allowed_to_edit_listing_start_date( $listing ) {
        if ( $this->roles->current_user_is_moderator() ) {
            return true;
        }

        if ( ! $this->settings->get_option( 'allow-start-date-modification' ) ) {
            return false;
        }

        $start_date = $this->listing_renderer->get_start_date( $listing );

        if ( empty( $start_date ) ) {
            return false;
        }

        if ( strtotime( $start_date ) > current_time( 'timestamp' ) ) {
            return true;
        }

        return false;
    }

    /**
     * @since 4.0.0
     */
    public function is_current_user_allowed_to_edit_listing_end_date( $listing ) {
        return $this->roles->current_user_is_moderator();
    }
}
