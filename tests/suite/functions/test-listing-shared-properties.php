<?php

use function Patchwork\redefine;
use function Patchwork\always;

class AWPCP_Test_Listing_Shared_Propeties extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->db = $GLOBALS['wpdb'];

        $uploads_dir = $GLOBALS['awpcp']->settings->get_runtime_option( 'awpcp-uploads-dir' );

        if ( ! is_dir( $uploads_dir . '/images' ) ) {
            mkdir( $uploads_dir . '/images', 0777, true );
        }

        touch( $uploads_dir . '/images/test-image-1.jpg' );
        touch( $uploads_dir . '/images/test-image-2.jpg' );
    }

    public function teardown() {
        parent::setup();

        $uploads_dir = $GLOBALS['awpcp']->settings->get_runtime_option( 'awpcp-uploads-dir' );

        unlink( $uploads_dir . '/images/test-image-1.jpg' );
        unlink( $uploads_dir . '/images/test-image-2.jpg' );
    }

    public function test_awpcp_get_ad_share_info() {
        $listing = awpcp_tests_create_listing();

        $rejected_image = awpcp_tests_create_attachment( array(
            'post_parent' => $listing->ID,
        ) );

        update_post_meta( $rejected_image->ID, '_awpcp_allowed_status', AWPCP_Attachment_Status::STATUS_REJECTED );
        update_post_meta( $rejected_image->ID, '_awpcp_enabled', true );

        $approved_image = awpcp_tests_create_attachment( array(
            'post_parent' => $listing->ID,
        ) );

        update_post_meta( $approved_image->ID, '_awpcp_allowed_status', AWPCP_Attachment_Status::STATUS_APPROVED );
        update_post_meta( $approved_image->ID, '_awpcp_enabled', true );

        $properties = awpcp_get_ad_share_info( $listing->ID );

        $this->assertEquals( 1, count( $properties['images'] ) );
        $this->assertTrue( isset( $properties['images'][0] ) );
    }

    public function test_get_listing_shared_info_strips_whitespace_from_description() {
        $listing = (object) array(
            'ID' => rand() + 1,
            'post_title' => 'Test Listing',
            'post_content' => " This\tis\r\nSparta! \n",
            'post_date' => current_time( 'mysql' ),
            'post_modified' => current_time( 'mysql' ),
        );

        $listings_collection = Phake::mock( 'AWPCP_ListingsCollection' );

        Phake::when( $listings_collection )->get( $listing->ID )->thenReturn( $listing );

        redefine( 'awpcp_listings_collection', always( $listings_collection ) );

        /* Execution */
        $properties = awpcp_get_ad_share_info( $listing->ID );

        /* Verification */
        $this->assertEquals( 'This is Sparta!', $properties['description'] );
    }
}
