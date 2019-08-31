<?php
/**
 * @package AWPCP\Admin
 */

/**
 * Configures list table to show current user listings only for non-moderators users.
 */
class AWPCP_ListTableRestrictions {

    /**
     * @var object
     */
    private $roles_and_capabilities;

    /**
     * @var object
     */
    private $request;

    /**
     * @param object $roles_and_capabilities     An instance of Listing Authorization.
     * @param object $request                    An instance of Request.
     * @since 4.0.0
     */
    public function __construct( $roles_and_capabilities, $request ) {
        $this->roles_and_capabilities = $roles_and_capabilities;
        $this->request                = $request;
    }

    /**
     * @param object $query     An instance of WP_Query.
     * @since 4.0.0
     */
    public function pre_get_posts( $query ) {
        if ( ! $query->is_main_query() ) {
            return;
        }

        if ( $this->roles_and_capabilities->current_user_is_moderator() ) {
            return;
        }

        $query->query_vars = $this->maybe_filter_by_author( $query->query_vars );
    }

    /**
     * Add the author query var if not already set to ensure only listings
     * owned by the current user are included.
     *
     * @since 4.0.6
     */
    public function maybe_filter_by_author( $query_vars ) {
        if ( empty( $query_vars['author'] ) ) {
            $query_vars['author'] = $this->request->get_current_user_id();
        }

        return $query_vars;
    }

    /**
     * @since 4.0.6
     */
    public function maybe_add_count_listings_query_filter() {
        if ( ! $this->roles_and_capabilities->current_user_is_moderator() ) {
            add_filter( 'awpcp_count_listings_query', [ $this, 'maybe_filter_by_author' ] );
        }
    }

    /**
     * @since 4.0.6
     */
    public function maybe_remove_count_listings_query_filter() {
        if ( ! $this->roles_and_capabilities->current_user_is_moderator() ) {
            remove_filter( 'awpcp_count_listings_query', [ $this, 'maybe_filter_by_author' ] );
        }
    }
}
