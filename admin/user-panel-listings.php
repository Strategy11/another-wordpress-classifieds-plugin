<?php

// require_once(AWPCP_DIR . '/admin/admin-panel-listings.php');
require_once(AWPCP_DIR . '/admin/user-panel-listings-place-ad-page.php');
require_once(AWPCP_DIR . '/admin/user-panel-listings-edit-ad-page.php');
require_once(AWPCP_DIR . '/admin/user-panel-listings-renew-ad-page.php');

function awpcp_manage_listings_user_panel_page() {
    return new AWPCP_UserListings();
}

class AWPCP_UserListings extends AWPCP_Admin_Listings {

    public function show_sidebar() {
        return false;
    }

    public function renew_ad() {
        if ( awpcp_current_user_is_moderator() ) {
            return parent::renew_ad();
        } else {
            $page = new AWPCP_UserListingsRenewAd();
            return $page->dispatch('renew');
        }
    }
}
