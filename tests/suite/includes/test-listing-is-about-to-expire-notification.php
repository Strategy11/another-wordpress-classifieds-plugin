<?php

class AWPCP_TestListingIsAboutToExpireNotification extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->listing_renderer = Phake::mock( 'AWPCP_ListingRenderer' );
        $this->settings = Phake::mock( 'AWPCP_Settings_API' );

        Phake::when( $this->settings )->get_option( 'renew-ad-email-subject' )->thenReturn( 'Your ad will expire in %d days.' );
        Phake::when( $this->settings )->get_option( 'renew-ad-email-body' )->thenReturn( 'This is an automated notification that your ad will expire in %d days.' );
    }

    public function test_render_for_listing_with_end_date_set_to_earlier_today() {
        $listing = awpcp_tests_create_empty_listing();

        Phake::when( $this->listing_renderer )->get_end_date->thenReturn(
            date( 'Y-m-d H:i:s', current_time( 'timestamp' ) - 1 )
        );

        $notification = new AWPCP_ListingIsAboutToExpireNotification( $this->listing_renderer, $this->settings );
        $subject = $notification->render_subject( $listing );
        $body = $notification->render_body( $listing );

        $this->assertEquals( 'Your ad will expire in less than 1 days.', $subject );
        $this->assertContains( 'less than 1 days.', $body );
    }

    public function test_render_for_already_expired_listing() {
        $listing = awpcp_tests_create_empty_listing();

        Phake::when( $this->listing_renderer )->get_end_date->thenReturn( '1988-08-24 04:30:00' );
        Phake::when( $this->listing_renderer )->has_expired->thenReturn( true );

        $notification = new AWPCP_ListingIsAboutToExpireNotification( $this->listing_renderer, $this->settings );
        $subject = $notification->render_subject( $listing );
        $body = $notification->render_body( $listing );

        $this->assertEquals( 'Your ad will expire in 0 days.', $subject );
        $this->assertContains( '0 days.', $body );
    }
}
