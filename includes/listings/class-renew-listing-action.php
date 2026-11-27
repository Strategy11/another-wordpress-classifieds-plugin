<?php
/**
 * @package AWPCP\Listings
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class AWPCP_RenewListingAction
 */
class AWPCP_RenewListingAction extends AWPCP_ListingAction {

    public function is_enabled_for_listing( $listing ) {
        $listing_renderer = awpcp_listing_renderer();

        if ( ! $listing_renderer->has_expired_or_is_about_to_expire( $listing ) ) {
            return false;
        }

        return ! $this->is_blocked_by_moderation( $listing, $listing_renderer );
    }

    /**
     * The renew page refuses owner renewals of listings that a moderator
     * disabled or that are waiting for approval, so the button is hidden too.
     *
     * @since x.x
     *
     * @param object                $listing          An instance of WP_Post.
     * @param AWPCP_ListingRenderer $listing_renderer The listing renderer.
     *
     * @return bool
     */
    private function is_blocked_by_moderation( $listing, $listing_renderer ) {
        if ( awpcp_current_user_is_moderator() ) {
            return false;
        }

        return $listing_renderer->is_disabled( $listing ) || $listing_renderer->is_pending_approval( $listing );
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

    public function render( $listing, $config = [] ) {
        $renew_url = awpcp_get_renew_ad_url( $listing->ID );

        return "<button type='button' onclick=\"window.location.href='{$renew_url}'\">{$this->get_name()}</button>";
    }
}
