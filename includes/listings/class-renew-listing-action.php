<?php
/**
 * @package AWPCP\Listings
 */

/**
 * Class AWPCP_RenewListingAction
 */
class AWPCP_RenewListingAction extends AWPCP_ListingAction {

    private $wordpress;

    public function __construct( $wordpress ) {
        $this->wordpress = $wordpress;
    }

    public function is_enabled_for_listing( $listing ) {
        if ( $this->wordpress->get_post_meta( $listing->ID, '_awpcp_expired', true ) ) {
            return true;
        }
        return false;
    }

    public function get_name() {
        return __( 'Renew', 'another-wordpress-classifieds-plugin' );
    }

    public function get_slug() {
        return 'renew-ad';
    }

    public function get_description() {
        return __( 'You can use this button to renew your ad.', 'another-wordpress-classifieds-plugin' );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function render( $listing, $config = [] ) {
        $renew_url = awpcp_get_renew_ad_url( $listing->ID );
        return "<a class='button' href='{$renew_url}'> {$this->get_name()} </a>";
    }
}
