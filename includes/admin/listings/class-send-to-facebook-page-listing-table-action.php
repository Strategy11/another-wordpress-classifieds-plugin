<?php
/**
 * @package AWPCP\Admin\Listings
 */

/**
 * Send to Facebook Page listing admin action.
 */
class AWPCP_SendToFacebookPageListingTableAction implements
    AWPCP_ListTableActionInterface,
    AWPCP_ConditionalListTableActionInterface {

    /**
     * @var object
     */
    private $facebook_helper;

    /**
     * @var object
     */
    private $wordpress;

    /**
     * @var object
     */
    private $roles;

    /**
     * @since 4.0.0
     *
     * @param object $facebook_helper An instance of Send To Facebook Helper.
     * @param object $roles           An instance of Roles and Capabilities.
     * @param object $wordpress       An instance of WordPress.
     */
    public function __construct( $facebook_helper, $roles, $wordpress ) {
        $this->facebook_helper = $facebook_helper;
        $this->roles           = $roles;
        $this->wordpress       = $wordpress;
    }

    /**
     * @since 4.0.0
     */
    public function is_needed() {
        return $this->roles->current_user_is_moderator();
    }

    /**
     * @param object $post  An instance of WP_Post.
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function should_show_action_for( $post ) {
        return false; // Available as a bulk action only.
    }

    /**
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_icon_class( $post ) {
        return '';
    }

    /**
     * @since 4.0.0
     */
    public function get_title() {
        return '';
    }

    /**
     * @param object $post  An instance of WP_Post.
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_label( $post ) {
        return $this->get_title();
    }

    /**
     * @param object $post          An instance of WP_Post.
     * @param string $current_url   The URL of the current page.
     * @since 4.0.0
     */
    public function get_url( $post, $current_url ) {
        return '';
    }

    /**
     * @param object $post  An instance of WP_Post.
     * @since 4.0.0
     */
    public function process_item( $post ) {
        return 'success';
    }

    /**
     * @param array $result_codes   An array of result codes from this action.
     * @since 4.0.0
     */
    public function get_messages( $result_codes ) {
        return array();
    }
}
