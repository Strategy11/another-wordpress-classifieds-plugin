<?php

/**
 * Helper class used to handle API calls & configuration for Facebook integration.
 * @since 3.0.2
 */
class AWPCP_Facebook {

    const GRAPH_API_VERSION = 'v2.12';

    public function __construct( $settings ) {
        $this->settings = $settings;
    }

    public function validate_config( &$errors ) {
    }

    /**
     * @since 3.8.6
     */
    public function get_required_permissions() {
        return array();
    }

    public function set_access_token( $key_or_token = '' ) {
    }

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self( awpcp()->settings );
        }
        return self::$instance;
    }

    public function get_user_pages() {
    }

    public function get_user_groups() {
    }

    public function get_login_url( $redirect_uri = '', $scope = '' ) {
    }

    public function token_from_code( $code, $redirect_uri='' ) {
    }

    public function api_request( $path, $method = 'GET', $args = array(), $notoken=false, $json_decode=true ) {   
    }

    /**
     * @since 3.0.2
     */
    public function get_last_error() {
        return $this->last_error;
    }

    public function is_page_set() {
        return false;
    }

    public function is_group_set() {
        return (bool) $this->settings->get_option( 'facebook-group' );
    }
}
