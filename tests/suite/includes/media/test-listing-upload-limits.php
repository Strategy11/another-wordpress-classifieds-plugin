<?php

class AWPCP_TestListingUploadLimits extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->attachments = Phake::mock( 'AWPCP_Attachments_Collection' );
        $this->file_types = Phake::mock( 'AWPCP_FileTypes' );
        $this->listing_renderer = Phake::mock( 'AWPCP_ListingRenderer' );
        $this->settings = Phake::mock( 'AWPCP_Settings_API' );
    }

    public function test_get_listing_upload_limits_returns_uploaded_file_count() {
        $uploaded_images = 5;

        $listing = awpcp_tests_create_empty_listing();

        Phake::when( $this->attachments )
            ->count_attachments_of_type( 'image', Phake::ignoreRemaining() )
            ->thenReturn( $uploaded_images );

        $upload_limits = new AWPCP_ListingUploadLimits( $this->attachments, $this->file_types, $this->listing_renderer, $this->settings );
        $listing_upload_limits = $upload_limits->get_listing_upload_limits( $listing );

        $this->assertEquals( $uploaded_images, $listing_upload_limits['images']['uploaded_file_count'] );
    }

    public function test_get_listing_upload_limits_honors_allowed_images_attribute() {
        $listing = (object) array( 'ID' => rand() + 1 );

        $payment_term = Phake::mock( 'AWPCP_PaymentTerm' );
        $payment_term->images = 0;

        Phake::when( $this->listing_renderer )->get_payment_term->thenReturn( $payment_term );
        Phake::when( $this->settings )->get_option( 'imagesallowedfree', Phake::ignoreRemaining() )->thenReturn( 5 );

        $listing_upload_limits = new AWPCP_ListingUploadLimits( $this->attachments, $this->file_types, $this->listing_renderer, $this->settings );
        $upload_limits = $listing_upload_limits->get_listing_upload_limits( $listing );

        $this->assertEquals( 0, $upload_limits['images']['allowed_file_count'] );
    }
}
