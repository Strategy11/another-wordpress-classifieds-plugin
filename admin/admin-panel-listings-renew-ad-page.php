<?php

require_once(AWPCP_DIR . '/frontend/page-renew-ad.php');

function awpcp_renew_listing_moderator_admin_page() {
    $general_title = __( 'Manage Listings', 'another-wordpress-classifieds-plugin' );
    $specific_title = __( 'Renew Ad', 'another-wordpress-classifieds-plugin' );

    return new AWPCP_AdminListingsRenewAd(
        'awpcp-admin-listings-renew-ad',
        awpcp_admin_page_title( $specific_title, $general_title ),
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

class AWPCP_AdminListingsRenewAd extends AWPCP_RenewAdPage {

    protected $template = 'admin/templates/admin-page.tpl.php';

    public $menu;

    public function show_sidebar() {
        return awpcp_current_user_is_admin();;
    }

    protected function get_panel_url() {
        return awpcp_get_admin_listings_url();
    }
}
