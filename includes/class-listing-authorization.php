<?php

function awpcp_listing_authorization() {
    return new AWPCP_ListingAuthorization( awpcp_request() );
}

class AWPCP_ListingAuthorization {

    private $request;

    public function __construct( $request ) {
        $this->request = $request;
    }

    public function is_current_user_allowed_to_edit_listing( $listing ) {
        if ( awpcp_current_user_is_admin() ) {
            return true;
        }

        if ( $listing->user_id == $this->request->get_current_user()->ID ) {
            return true;
        }

        return false;
    }
}
