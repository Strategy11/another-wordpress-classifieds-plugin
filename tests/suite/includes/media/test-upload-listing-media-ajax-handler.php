<?php

class AWPCP_Test_Upload_Listing_Media_Ajax_Handler extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->listings = Phake::mock( 'AWPCP_ListingsCollection' );
        $this->file_uploader = Phake::mock( 'AWPCP_FileUploader' );
        $this->meida_manager = Phake::mock( 'AWPCP_Media_Manager' );
        $this->request = Phake::mock( 'AWPCP_Request' );
        $this->response = Phake::mock( 'AWPCP_AjaxResponse' );
    }

    public function test_ajax() {
        $this->pause_filter( 'awpcp-media-uploaded' );

        $listing = awpcp_tests_create_empty_listing();
        $nonce = wp_create_nonce( 'awpcp-upload-media-for-listing-' . $listing->ID );
        $uploaded_file = (object) array( 'is_complete' => true );
        $attachment = (object) array(
            'ID' => rand() + 1,
            'post_title' => 'test-iamge.jpg',
            'post_parent' => $listing->ID,
            'status' => AWPCP_Attachment_Status::STATUS_APPROVED,
            'post_mime_type' => 'image/jpg',
            'isPrimary' => false,
            'thumbnailUrl' => 'http://example.com/test-image.jpg',
            'iconUrl' => 'http://example.com/',
        );

        $this->attachment_properties = Phake::mock( 'AWPCP_Attachment_Properties' );
        Phake::when( $this->listings )->get->thenReturn( $listing );
        Phake::when( $this->request )->post( 'nonce' )->thenReturn( $nonce );
        Phake::when( $this->file_uploader )->get_uploaded_file->thenReturn( $uploaded_file );
        Phake::when( $this->meida_manager )->add_file->thenReturn( $attachment );

        $handler = new AWPCP_UploadListingMediaAjaxHandler(
            $this->attachment_properties,
            $this->listings,
            $this->file_uploader,
            $this->meida_manager,
            $this->request,
            $this->response
        );

        $handler->ajax();

        Phake::verify( $this->response )->write( Phake::capture( $encoded_response ) );

        $response = json_decode( $encoded_response, true );

        $this->assertEquals( $listing->ID, $response['file']['listingId'] );
    }
}
