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
        return array_map( array( $this, 'add_administrator_capabilities_to_role' ), $administrator_roles );
    }

    public function get_administrator_roles_names() {
        $configured_roles = explode( ',', $this->settings->get_option( 'awpcpadminaccesslevel' ) );

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
        return array_map( array( $role, 'add_cap' ), $this->get_administrator_capabilities() );
    }

    public function remove_administrator_capabilities_from_role( $role_name ) {
        $role = get_role( $role_name );
        return array_map( array( $role, 'remove_cap' ), $this->get_administrator_capabilities() );
    }
}
