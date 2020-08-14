<?php

class AWPCP_Test_Listing_Attachment_Creator extends AWPCP_UnitTestCase {

    public function test_create_attachment() {
        $listing = awpcp_tests_create_empty_listing();

        $allowed_status = AWPCP_Attachment_Status::STATUS_APPROVED;
        $attachment_name = 'Test File.jpg';
        $attachment_path = '/path/to/uploaded/file.jpg';

        $file_logic = Phake::mock( 'AWPCP_UploadedFileLogic' );
        $wordpress = Phake::mock( 'AWPCP_WordPress' );

        Phake::when( $file_logic )->get_real_name->thenReturn( $attachment_name );
        Phake::when( $file_logic )->get_path->thenReturn( $attachment_path );

        $creator = new AWPCP_Listing_Attachment_Creator( $wordpress );
        $creator->create_attachment( $listing, $file_logic, $allowed_status );

        Phake::verify( $wordpress )->handle_media_sideload(
            Phake::capture( $file_array ), Phake::capture( $listing_id ), Phake::ignoreRemaining()
        );

        $this->assertEquals( $attachment_name, $file_array['name'] );
        $this->assertEquals( $attachment_path, $file_array['tmp_name'] );
        $this->assertEquals( $listing->ID, $listing_id );

        Phake::verify( $wordpress )->update_post_meta(
            Phake::capture( $attachment_id ), '_awpcp_enabled', Phake::capture( $is_enabled )
        );
        Phake::verify( $wordpress )->update_post_meta(
            Phake::capture( $attachment_id ), '_awpcp_allowed_status', Phake::capture( $stored_allowed_status )
        );

        $this->assertTrue( $is_enabled );
        $this->assertEquals( $allowed_status, $stored_allowed_status );
    }
}
