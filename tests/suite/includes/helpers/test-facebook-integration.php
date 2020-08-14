<?php
/**
 * @package AWPCP\Tests
 */

// @phpcs:disable Squiz.Commenting

use Brain\Monkey\Functions;

class AWPCP_TestSendListingToFacebookHelper extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->facebook_config  = Phake::mock( 'AWPCP_Facebook' );
        $this->facebook_helper  = Phake::mock( 'AWPCP_SendToFacebookHelper' );
        $this->settings         = Phake::mock( 'AWPCP_Settings_API' );
        $this->listing_renderer = Phake::mock( 'AWPCP_ListingRenderer' );
        $this->wordpress        = Phake::mock( 'AWPCP_WordPress' );
    }

    public function test_schedule_listing_if_necessary() {
        $listing = (object) [
            'ID' => wp_rand(),
        ];

        $current_time    = wp_rand();
        $cron_parameters = array( $listing->ID, $current_time );

        Phake::when( $this->settings )->get_option( 'sends-listings-to-facebook-automatically', true )->thenReturn( true );
        Phake::when( $this->settings )->get_option( 'facebook-integration-method' )->thenReturn( 'facebook-api' );
        Phake::when( $this->settings )->get_option( 'facebook-page' )->thenReturn( 'FACEBOOKPAGEID' );

        Phake::when( $this->listing_renderer )->is_public->thenReturn( true );

        Phake::when( $this->wordpress )->current_time->thenReturn( $current_time );

        Functions\when( 'wp_next_scheduled' )->justReturn( false );

        $this->get_test_subject()->maybe_schedelue_send_to_facebook_action( $listing );

        Phake::verify( $this->wordpress )->schedule_single_event(
            Phake::capture( $timestamp ),
            Phake::capture( $hook ),
            Phake::capture( $args )
        );

        $this->assertEquals( 'awpcp-send-listing-to-facebook', $hook );
        $this->assertEquals( $cron_parameters, $args );
    }

    /**
     * @since 3.8.6
     */
    private function get_test_subject() {
        return new AWPCP_FacebookIntegration(
            $this->listing_renderer,
            $this->settings,
            $this->wordpress
        );
    }
}
