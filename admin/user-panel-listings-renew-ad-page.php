<?php

require_once(AWPCP_DIR . '/admin/admin-panel-listings-renew-ad-page.php');

function awpcp_renew_listing_subscriber_admin_page() {
    $general_title = __( 'Manage Listings', 'another-wordpress-classifieds-plugin' );
    $specific_title = __( 'Renew Ad', 'another-wordpress-classifieds-plugin' );

    return new AWPCP_UserListingsRenewAd(
        'awpcp-admin-listings-renew-ad',
        __( 'AWPCP Ad Management Panel - Listings - Renew Ad', 'another-wordpress-classifieds-plugin' ),
        awpcp_attachments_collection(),
        awpcp_listing_upload_limits(),
        awpcp_listing_authorization(),
        awpcp_listing_renderer(),
        awpcp_listings_api(),
        awpcp_listings_collection(),
        awpcp_payments_api(),
        awpcp_template_renderer(),
        awpcp_wordpress(),
        awpcp_request()
    );
}

class AWPCP_UserListingsRenewAd extends AWPCP_AdminListingsRenewAd {

    public function show_sidebar() {
        return false;
    }

    protected function get_panel_url() {
        return awpcp_get_user_panel_url();
    }
}
