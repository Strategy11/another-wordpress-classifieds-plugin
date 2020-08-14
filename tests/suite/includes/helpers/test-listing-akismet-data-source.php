<?php

class AWPCP_TestListingAkismetDataSource extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->listing_renderer = Phake::mock( 'AWPCP_ListingRenderer' );
    }

    public function test_get_request_data() {
        $listing = (object) array( 'ID' => rand() + 1 );

        $listing->post_content = 'Woo!';

        Phake::when( $this->listing_renderer )->get_contact_name->thenReturn( 'John Doe' );
        Phake::when( $this->listing_renderer )->get_contact_email->thenReturn( 'john@example.com' );
        Phake::when( $this->listing_renderer )->get_website_url->thenReturn( 'http://example.com' );

        $data_source = new AWPCP_ListingAkismetDataSource( $this->listing_renderer );
        $request_data = $data_source->get_request_data( $listing );

        $this->assertArrayHasKey( 'comment_type', $request_data );
        $this->assertArrayHasKey( 'comment_author', $request_data );
        $this->assertArrayHasKey( 'comment_author_email', $request_data );
        $this->assertArrayHasKey( 'comment_author_url', $request_data );
        $this->assertArrayHasKey( 'comment_content', $request_data );
        $this->assertArrayHasKey( 'permalink', $request_data );
    }
}
