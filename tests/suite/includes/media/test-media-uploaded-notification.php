<?php

use Brain\Monkey\Functions;

use function Patchwork\always;
use function Patchwork\redefine;
use function Patchwork\relay;

class AWPCP_Test_Media_Uploaded_Notification extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->listing = (object) array( 'ID' => rand() + 1 );
        $this->attachment = (object) array();

        $this->attachment_properties = Phake::mock( 'AWPCP_Attachment_Properties' );
        $this->attachments = Phake::mock( 'AWPCP_Attachments_Collection' );
        $this->listing_renderer = Phake::mock( 'AWPCP_ListingRenderer' );
        $this->listings = Phake::mock( 'AWPCP_ListingsCollection' );
        $this->settings = Phake::mock( 'AWPCP_Settings_API' );
        $this->wordpress = Phake::mock( 'AWPCP_WordPress' );
        $this->request = Phake::mock( 'AWPCP_Request' );

        Phake::when( $this->settings )->get_option( 'send-media-uploaded-notification-to-administrators' )->thenReturn( true );
    }

    /**
     * Test notification is sent if images notification is enabled and
     * listing posted notification is diabled.
     */
    public function test_notification_is_scheduled_when_listing_posted_notification_is_disabled() {
        Phake::when( $this->request )->param( 'context' )->thenReturn( 'post-listing' );
        Phake::when( $this->settings )->get_option( 'notifyofadposted' )->thenReturn( false );

        $notification = new AWPCP_Media_Uploaded_Notification(
            $this->attachment_properties,
            null,
            null,
            null,
            $this->settings,
            $this->wordpress,
            $this->request
        );

        $notification->maybe_schedule_notification( $this->attachment, $this->listing );

        Phake::verify( $this->wordpress )->schedule_single_event( Phake::anyParameters() );
    }

    private function get_next_scheduled_event( $listing ) {
        return wp_next_scheduled( "awpcp-media-uploaded-notification", array( $listing->ad_id ) );
    }

    /**
     * Test notification is sent if images notification is enabled and
     * listing edited notification is diabled.
     */
    public function test_notification_is_scheduled_when_listing_edited_notification_is_disabled() {
        Phake::when( $this->request )->param( 'context' )->thenReturn( 'edit-listing' );
        Phake::when( $this->settings )->get_option( 'send-listing-updated-notification-to-administrators' )->thenReturn( false );

        $notification = new AWPCP_Media_Uploaded_Notification(
            $this->attachment_properties,
            null,
            null,
            null,
            $this->settings,
            $this->wordpress,
            $this->request
        );

        $notification->maybe_schedule_notification( $this->attachment, $this->listing );

        Phake::verify( $this->wordpress )->schedule_single_event( Phake::anyParameters() );
    }

    /**
     * Test notification is not sent if listing posted or listing edited
     * notification is enabled.
     */
    public function test_notification_is_not_scheduled_when_listing_posted_notification_is_enabled() {
        Phake::when( $this->request )->param( 'context' )->thenReturn( 'post-listing' );
        Phake::when( $this->settings )->get_option( 'notifyofadposted' )->thenReturn( true );

        $notification = new AWPCP_Media_Uploaded_Notification(
            null,
            null,
            null,
            null,
            $this->settings,
            $this->wordpress,
            $this->request
        );

        $notification->maybe_schedule_notification( $this->attachment, $this->listing );

        Phake::verify( $this->wordpress, Phake::times( 0 ) )->schedule_single_event( Phake::anyParameters() );
    }

    public function test_notification_is_not_scheduled_when_listing_edited_notification_is_enabled() {
        Phake::when( $this->request )->param( 'context' )->thenReturn( 'edit-listing' );
        Phake::when( $this->settings )->get_option( 'send-listing-updated-notification-to-administrators' )->thenReturn( true );

        $notification = new AWPCP_Media_Uploaded_Notification(
            null,
            null,
            null,
            null,
            $this->settings,
            $this->wordpress,
            $this->request
        );

        $notification->maybe_schedule_notification( $this->attachment, $this->listing );

        Phake::verify( $this->wordpress, Phake::times( 0 ) )->schedule_single_event( Phake::anyParameters() );
    }

    /**
     * Test notification is always sent for images uploaded from the Manage Media screen.
     */
    public function test_notification_is_scheduled_when_media_is_added_from_manage_media_screen() {
        Phake::when( $this->request )->param( 'context' )->thenReturn( 'manage-media' );

        $notification = new AWPCP_Media_Uploaded_Notification(
            $this->attachment_properties,
            null,
            null,
            null,
            $this->settings,
            $this->wordpress,
            $this->request
        );

        $notification->maybe_schedule_notification( $this->attachment, $this->listing );

        Phake::verify( $this->wordpress )->schedule_single_event( Phake::anyParameters() );
    }

    public function test_notification_is_scheduled_when_media_is_added_from_manage_media_screen_and_is_awaiting_approval() {
        Phake::when( $this->request )->param( 'context' )->thenReturn( 'manage-media' );
        Phake::when( $this->settings )->get_option( 'send-media-uploaded-notification-to-administrators' )->thenReturn( false );
        Phake::when( $this->attachment_properties )->is_awaiting_approval->thenReturn( true );

        $notification = new AWPCP_Media_Uploaded_Notification(
            $this->attachment_properties,
            null,
            null,
            null,
            $this->settings,
            $this->wordpress,
            $this->request
        );

        $notification->maybe_schedule_notification( $this->attachment, $this->listing );

        Phake::verify( $this->wordpress )->schedule_single_event( Phake::anyParameters() );
    }

    public function test_send_notification() {
        $listing = (object) array( 'ID' => rand() + 1 );
        $attachment = (object) array( 'ID' => rand() + 1, 'post_title' => 'Test Attachment' );
        $other_attachments = array();

        Phake::when( $this->listings )->get( $listing->ID )->thenReturn( $listing );
        Phake::when( $this->attachments )->get( $attachment->ID )->thenReturn( $attachment );

        Functions::expect( 'get_option' )
            ->once()
            ->with( "awpcp-media-uploaded-notification-files-{$listing->ID}", array() )
            ->andReturn( array(
                array( 'id' => $attachment->ID ),
            ) );

        Functions::when( 'awpcp_admin_email_to' )->justReturn( 'admin@example.org' );
        Functions::when( 'awpcp_get_admin_listings_url' )->justReturn( 'http://example.org/wp-admin/admin.php?page=awpcp-admin-listings' );
        Functions::when( 'awpcp_get_blog_name' )->justReturn( 'Test Blog' );
        Functions::when( 'home_url' )->justReturn( 'http://example.org' );

        redefine( 'AWPCP_Email::prepare', function( $template, $params ) use ( &$other_attachments ) {
            $other_attachments = $params['other_attachments'];
            relay();
        } );

        redefine( 'AWPCP_Email::send', always( true ) );

        $notification = new AWPCP_Media_Uploaded_Notification(
            $this->attachment_properties,
            $this->attachments,
            $this->listing_renderer,
            $this->listings,
            null,
            null,
            null
        );

        /* Execution */
        $notification->send_notification( $listing->ID );

        /* Verification */
        $this->assertContains( $attachment, $other_attachments );
    }
}
