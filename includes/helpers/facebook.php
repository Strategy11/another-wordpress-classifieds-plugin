<?php

/**
 * Helper class used to handle API calls & configuration for Facebook integration.
 * @since 3.0.2
 */
class AWPCP_Facebook {

    const GRAPH_API_VERSION = 'v2.12';

    public function validate_config() {
		_deprecated_function( __FUNCTION__, '4.1.8' );
    }

    /**
     * @since 3.8.6
     */
    public function get_required_permissions() {
		_deprecated_function( __FUNCTION__, '4.1.8' );
        return array();
    }

    public function set_access_token() {
		_deprecated_function( __FUNCTION__, '4.1.8' );
    }

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self( awpcp()->settings );
        }
        return self::$instance;
    }

    public function get_user_pages() {
		_deprecated_function( __FUNCTION__, '4.1.8' );
    }

    public function get_user_groups() {
		_deprecated_function( __FUNCTION__, '4.1.8' );
    }

    public function get_login_url( $redirect_uri = '', $scope = '' ) {
		_deprecated_function( __FUNCTION__, '4.1.8' );
    }

    public function token_from_code() {
		_deprecated_function( __FUNCTION__, '4.1.8' );
    }

    public function api_request() {
		_deprecated_function( __FUNCTION__, '4.1.8' );
    }

    /**
     * @since 3.0.2
     */
    public function get_last_error() {
		_deprecated_function( __FUNCTION__, '4.1.8' );
        return '';
    }

    public function is_page_set() {
		_deprecated_function( __FUNCTION__, '4.1.8' );
        return false;
    }

    public function is_group_set() {
		_deprecated_function( __FUNCTION__, '4.1.8' );
        return false;
    }
}
