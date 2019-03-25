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

        if ( ! empty( $query->query_vars['author'] ) ) {
            return;
        }

        if ( $this->roles_and_capabilities->current_user_is_moderator() ) {
            return;
        }

        $query->query_vars['author'] = $this->request->get_current_user()->ID;
    }
}
