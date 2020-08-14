<?php

class AWPCP_Test_Edit_Listing_Link_Placeholder extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->listing_renderer = Phake::mock( 'AWPCP_ListingRenderer' );
        $this->authorization = Phake::mock( 'AWPCP_ListingAuthorization' );
    }

    public function test_constructor() {
        $placeholder = awpcp_edit_listing_link_placeholder();
        $this->assertInstanceOf( 'AWPCP_EditListingLinkPlaceholder', $placeholder );
    }

    public function test_do_placeholder() {
        $this->login_as_subscriber();

        $listing = awpcp_tests_create_listing();

        Phake::when( $this->authorization )->is_current_user_allowed_to_edit_listing->thenReturn( true );
        Phake::when( $this->listing_renderer )->get_listing_title->thenReturn( $listing->post_title );

        $placeholder = new AWPCP_EditListingLinkPlaceholder( $this->listing_renderer, $this->authorization );
        $output = $placeholder->do_placeholder( $listing, 'edit_listing_link', 'single-listing?' );

        $this->assertContains( $listing->post_title, $output );
    }
}
