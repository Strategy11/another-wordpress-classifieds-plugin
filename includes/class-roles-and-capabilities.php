<?php

function awpcp_roles_and_capabilities() {
    return new AWPCP_RolesAndCapabilities( awpcp()->settings );
}

class AWPCP_RolesAndCapabilities {

    private $settings;

    public function __construct( $settings ) {
        $this->settings = $settings;
    }

    public function setup_roles_capabilities() {
        $administrator_roles = $this->get_administrator_roles_names();
        array_walk( $administrator_roles, array( $this, 'add_administrator_capabilities_to_role' ) );

        $this->create_moderator_role();
    }

    public function get_administrator_roles_names() {
        $selected_roles = $this->settings->get_option( 'awpcpadminaccesslevel' );
        return $this->get_administrator_roles_names_from_string( $selected_roles );
    }

    public function get_administrator_roles_names_from_string( $string ) {
        $configured_roles = explode( ',', $string );

        if ( in_array( 'editor', $configured_roles ) ) {
            $roles_names = array( 'administrator', 'editor' );
        } else {
            $roles_names = array( 'administrator' );
        }

        return $roles_names;
    }

    public function get_administrator_capabilities() {
        return array(
            'manage_classifieds',
            'manage_classifieds_listings',
            'edit_classifieds_listings',
            'edit_others_classifieds_listings'
        );
    }

    public function add_administrator_capabilities_to_role( $role_name ) {
        $role = get_role( $role_name );
        return $this->add_capabilities_to_role( $role, $this->get_administrator_capabilities() );
    }

    private function add_capabilities_to_role( $role, $capabilities ) {
        return array_map( array( $role, 'add_cap' ), $capabilities );
    }

    public function remove_administrator_capabilities_from_role( $role_name ) {
        $role = get_role( $role_name );
        return array_map( array( $role, 'remove_cap' ), $this->get_administrator_capabilities() );
    }

    private function create_moderator_role() {
        $role = get_role( 'awpcp-moderator' );
        $capabilities = array(
            'read' => true,
            'manage_classifieds_listings' => true,
            'edit_classifieds_listings' => true,
            'edit_others_classifieds_listings' => true,
        );

        if ( is_null( $role ) ) {
            $role = add_role( 'awpcp-moderator', __( 'Classifieds Moderator', 'AWPCP' ), $capabilities );
        } else {
            $this->add_capabilities_to_role( $role, array_keys( $capabilities ) );
        }
    }
}
