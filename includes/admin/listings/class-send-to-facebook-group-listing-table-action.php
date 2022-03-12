<?php
/**
 * @package AWPCP\Admin\Listings
 */

/**
 * Send to Facebook Group listing admin action.
 */
class AWPCP_SendToFacebookGroupListingTableAction implements
    AWPCP_ListTableActionInterface,
    AWPCP_ConditionalListTableActionInterface {

    /**
     * @since 4.0.0
     */
    public function is_needed() {
		_deprecated_function( __FUNCTION__, '4.1.8' );
        return $this->roles->current_user_is_moderator();
    }

    /**
     * @since 4.0.0
     */
    public function should_show_action_for() {
		_deprecated_function( __FUNCTION__, '4.1.8' );
        return false; // Available as a bulk action only.
    }

    /**
     * @since 4.0.0
     */
    public function get_icon_class() {
		_deprecated_function( __FUNCTION__, '4.1.8' );
        return '';
    }

    /**
     * @since 4.0.0
     */
    public function get_title() {
		_deprecated_function( __FUNCTION__, '4.1.8' );
        return '';
    }

    /**
     * @since 4.0.0
     */
    public function get_label() {
		_deprecated_function( __FUNCTION__, '4.1.8' );
        return $this->get_title();
    }

    /**
     * @since 4.0.0
     */
    public function get_url() {
		_deprecated_function( __FUNCTION__, '4.1.8' );
    }

    /**
     * @since 4.0.0
     */
    public function process_item() {
		_deprecated_function( __FUNCTION__, '4.1.8' );
        return 'success';
    }

    /**
     * @since 4.0.0
     */
    public function get_messages() {
		_deprecated_function( __FUNCTION__, '4.1.8' );
        return array();
    }
}
