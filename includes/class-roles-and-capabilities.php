<?php
/**
 * @package AWPCP
 */

// phpcs:disable

class AWPCP_RolesAndCapabilities {

    private $settings;
    private $request;

    public function __construct( $settings, $request ) {
        $this->settings = $settings;
        $this->request = $request;
    }

    public function setup_roles_capabilities() {
        $administrator_roles = $this->get_administrator_roles_names();
        $subscriber_roles    = $this->get_subscriber_roles_names();

        array_walk( $administrator_roles, array( $this, 'add_administrator_capabilities_to_role' ) );
        array_walk( $subscriber_roles, array( $this, 'add_subscriber_capabilities_to_role' ) );

        $this->create_moderator_role();
    }

    public function get_administrator_roles_names() {
        $selected_roles = $this->settings->get_option( 'awpcpadminaccesslevel' );
        return $this->get_administrator_roles_names_from_string( $selected_roles );
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function get_administrator_roles_names_from_string( $string ) {
        $configured_roles = explode( ',', $string );

        if ( in_array( 'editor', $configured_roles ) ) {
            $roles_names = array( 'administrator', 'editor' );
        } else {
            $roles_names = array( 'administrator' );
        }

        return $roles_names;
    }

    /**
     * @param array $administrator_roles    An array of names of roles that are
     *                                      have administrator capabilities.
     * @since 4.0.0
     */
    public function get_subscriber_roles_names() {
        $standard_roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );

        return array_diff( $standard_roles, $this->get_administrator_roles_names() );
    }

    /**
     * @param string $role_name     The name of the role to modify.
     */
    public function add_administrator_capabilities_to_role( $role_name ) {
        return $this->add_capabilities_to_role( get_role( $role_name ), $this->get_administrator_capabilities() );
    }

    public function get_administrator_capabilities() {
        return array_merge( array( 'manage_awpcp_classifieds' ), $this->get_moderator_capabilities() );
    }

    /**
     * @since 4.0.0
     */
    public function get_moderator_capabilities() {
        $capabilities = array(
            $this->get_moderator_capability(),
        );

        return array_merge( $capabilities, $this->get_subscriber_capabilities() );
    }

    /**
     * @since 4.0.0
     */
    public function get_moderator_capability() {
        return 'edit_others_awpcp_classifieds';
    }

    private function add_capabilities_to_role( $role, $capabilities ) {
        return array_map( array( $role, 'add_cap' ), $capabilities );
    }

    public function remove_administrator_capabilities_from_role( $role_name ) {
        $role = get_role( $role_name );
        return array_map( array( $role, 'remove_cap' ), $this->get_administrator_capabilities() );
    }

    public function add_subscriber_capabilities_to_role( $role_name ) {
        return $this->add_capabilities_to_role( geT_role( $role_name ), $this->get_subscriber_capabilities() );
    }

    public function get_subscriber_capabilities() {
        return array(
            $this->get_subscriber_capability(),
        );
    }

    /**
     * @since 4.0.0
     */
    public function get_subscriber_capability() {
        return 'edit_awpcp_classifieds';
    }

    /**
     * @since 4.0.0
     */
    public function get_dashboard_capability() {
        if ( $this->settings->get_option( 'enable-user-panel' ) ) {
            return $this->get_subscriber_capability();
        }

        return $this->get_moderator_capability();
    }
    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    private function create_moderator_role() {
        $role = get_role( 'awpcp-moderator' );

        $capabilities = array_merge( array( 'read' ), $this->get_moderator_capabilities() );
        $capabilities = array_combine( $capabilities, array_pad( array(), count( $capabilities ), true ) );

        if ( is_null( $role ) ) {
            $role = add_role( 'awpcp-moderator', __( 'Classifieds Moderator', 'another-wordpress-classifieds-plugin' ), $capabilities );
        } else {
            $this->add_capabilities_to_role( $role, array_keys( $capabilities ) );
        }
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function remove_moderator_role() {
        if ( get_role( 'awpcp-moderator' ) ) {
            return remove_role( 'awpcp-moderator' );
        } else {
            return false;
        }
    }

    public function current_user_is_administrator() {
        return $this->current_user_can( 'manage_awpcp_classifieds' );
    }

    private function current_user_can( $capabilities ) {
        // If the current user is being setup before the "init" action has fired,
        // strange (and difficult to debug) role/capability issues will occur.
        if ( ! did_action( 'set_current_user' ) ) {
            _doing_it_wrong( __FUNCTION__, "Trying to call current_user_is_*() before the current user has been set.", '3.3.1' );
        }

        return $this->user_can( $this->request->get_current_user(), $capabilities );
    }

    private function user_can( $user, $capabilities ) {
        if ( ! is_object( $user ) || empty( $capabilities ) ) {
            return false;
        }

        if ( ! is_array( $capabilities ) ) {
            $capabilities = array( $capabilities );
        }

        foreach ( $capabilities as $capability ) {
            if ( ! user_can( $user, $capability ) ) {
                return false;
            }
        }

        return true;
    }

    public function current_user_is_moderator() {
        return $this->current_user_can( 'edit_others_awpcp_classifieds' );
    }

    public function user_is_administrator( $user_id ) {
        return $this->user_can( get_userdata( $user_id ), 'manage_awpcp_classifieds' );
    }
}
