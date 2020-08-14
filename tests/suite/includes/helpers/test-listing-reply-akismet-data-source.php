<?php

class AWPCP_TestListingReplyAkismetDataSource extends AWPCP_UnitTestCase {

    public function test_get_request_data() {
        $reply = array(
            'awpcp_sender_name' => 'John Doe',
            'awpcp_sender_email' => 'john@example.com',
            'awpcp_contact_message' => 'Woo!',
            'ad_id' => rand() + 1
        );

        $data_source = new AWPCP_ListingReplyAkismetDataSource();
        $request_data = $data_source->get_request_data( $reply );

        $this->assertArrayHasKey( 'comment_type', $request_data );
        $this->assertArrayHasKey( 'comment_author', $request_data );
        $this->assertArrayHasKey( 'comment_author_email', $request_data );
        $this->assertArrayHasKey( 'comment_content', $request_data );
        $this->assertArrayHasKey( 'permalink', $request_data );
    }
}
