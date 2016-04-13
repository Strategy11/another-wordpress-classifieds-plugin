<?php

require_once(AWPCP_DIR . '/frontend/page-edit-ad.php');

function awpcp_edit_listing_admin_page() {
    return new AWPCP_AdminListingsEditAd(
        'awpcp-admin-listings-edit-ad',
        awpcp_admin_page_title(
            __( 'Edit Ad', 'another-wordpress-classifieds-plugin' ),
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


class AWPCP_AdminListingsEditAd extends AWPCP_EditAdPage {

    protected $template = 'admin/templates/admin-page.tpl.php';

    public $menu;

    public function show_sidebar() {
        return false;
    }
}
