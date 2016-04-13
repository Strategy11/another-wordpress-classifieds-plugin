<?php

require_once(AWPCP_DIR . '/frontend/page-place-ad.php');

function awpcp_place_listing_admin_page() {
    return new AWPCP_AdminListingsPlaceAd(
        'awpcp-admin-listings-place-ad',
        awpcp_admin_page_title(
            __( 'Place Ad', 'another-wordpress-classifieds-plugin' ),
            __( 'Manage Listings', 'another-wordpress-classifieds-plugin' )
        ),
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

class AWPCP_AdminListingsPlaceAd extends AWPCP_Place_Ad_Page {

    protected $template = 'admin/templates/admin-page.tpl.php';

    public $menu;

    public function should_show_title() {
        return false;
    }

    public function show_sidebar() {
        return false;
    }
}
