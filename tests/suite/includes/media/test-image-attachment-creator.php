<?php
/**
 * @package AWPCP\Tests\Media
 */

use Brain\Monkey\Functions;

/**
 * Unit tests for Image Attachment Creator.
 */
class AWPCP_Test_Image_Attachment_Creator extends AWPCP_UnitTestCase {

    /**
     * * @since unknown
     */
    public function setup() {
        parent::setup();

        $this->attachments_creator    = Phake::mock( 'AWPCP_Listing_Attachment_Creator' );
        $this->attachments_logic      = Mockery::mock( 'AWPCP_Attachments_Logic' );
        $this->attachments_collection = Mockery::mock( 'AWPCP_Attachments_Collection' );
        $this->listings_logic         = Mockery::mock( 'AWPCP_ListingsAPI' );
        $this->settings               = Phake::mock( 'AWPCP_Settings_API' );
    }

    /**
     * @since unknown
     */
    public function test_create_attachment_as_moderator() {
        Functions\when( 'awpcp_current_user_is_moderator' )->justReturn( true );

        $this->verify_create_attachment( AWPCP_Attachment_Status::STATUS_APPROVED );
    }

    /**
     * @param string $expected_status   The expected status for the attachment.
     */
    private function verify_create_attachment( $expected_status ) {
        $file_logic = new stdClass();
        $listing    = (object) [
            'ID' => wp_rand(),
        ];

        $this->attachments_collection->shouldReceive( 'get_featured_image' )
            ->andReturn( null );

        $creator = $this->get_test_subject();

        $creator->create_attachment( $listing, $file_logic );

        Phake::verify( $this->attachments_creator )->create_attachment(
            Phake::capture( $passed_listing ),
            Phake::capture( $passed_file_logic ),
            Phake::capture( $allowed_status )
        );

        $this->assertEquals( $listing, $passed_listing );
        $this->assertEquals( $file_logic, $passed_file_logic );
        $this->assertEquals( $expected_status, $allowed_status );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_Image_Attachment_Creator(
            $this->attachments_creator,
            $this->attachments_logic,
            $this->attachments_collection,
            $this->listings_logic,
            $this->settings
        );
    }

    /**
     * @since unknown
     */
    public function test_create_attachment_as_subscriber_when_imagesapprove_is_enabled() {
        Functions\when( 'awpcp_current_user_is_moderator' )->justReturn( false );

        Phake::when( $this->settings )->get_option( 'imagesapprove' )->thenReturn( true );

        $this->verify_create_attachment( AWPCP_Attachment_Status::STATUS_AWAITING_APPROVAL );
    }

    /**
     * @since 4.0.0
     */
    public function test_create_attachment_marks_listings_as_having_images_awaiting_approval() {
        $file       = (object) [];
        $attachment = (object) [];
        $post       = (object) [
            'ID' => wp_rand(),
        ];

        $this->attachments_collection->shouldReceive( 'get_featured_image' )
            ->andReturn( null );

        $this->attachments_logic->shouldReceive( 'set_attachment_as_featured' )
            ->andReturn( true );

        $this->listings_logic->shouldReceive( 'mark_as_having_images_awaiting_approval' )
            ->once()
            ->with( $post );

        Functions\when( 'awpcp_current_user_is_moderator' )->justReturn( false );

        Phake::when( $this->settings )->get_option( 'imagesapprove' )->thenReturn( true );
        Phake::when( $this->attachments_creator )->create_attachment->thenReturn( $attachment );

        $this->get_test_subject()->create_attachment( $post, $file );
    }
}
