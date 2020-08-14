<?php

/**
 * @group core
 */
class AWPCP_TestEmailVerificationCronJobs extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->listings_collection = awpcp_listings_collection();

        $this->listings = Phake::mock( 'AWPCP_ListingsAPI' );
        $this->settings = Phake::mock( 'AWPCP_Settings_API' );
        $this->wordpress = Phake::mock( 'AWPCP_WordPress' );

        Phake::when( $this->settings )->get_option( 'enable-email-verification' )->thenReturn( true );

        // verification email will be sent again if the Ad remains unverified
        // during 20 days.
        $this->resend_email_threshold = 20;
        Phake::when( $this->settings )->get_option( 'email-verification-first-threshold' )->thenReturn( $this->resend_email_threshold );

        // verification email will be sent again if the Ad is not verified after
        // 40 days of being posted.
        $this->delete_ads_threshold = 40;
        Phake::when( $this->settings )->get_option( 'email-verification-second-threshold' )->thenReturn( $this->delete_ads_threshold );

        awpcp_tests_delete_all_listings();

        $current_time = awpcp_datetime( 'timestamp' );
        $this->create_listing_that_requires_email_verification_to_be_sent_again( $current_time );
        $this->create_listing_that_does_not_require_email_verification_to_be_sent_again( $current_time );
        $this->create_listing_that_has_exceeded_the_delete_threshold( $current_time );
        $this->create_unpaid_listing( $current_time );
    }

    private function create_listing_that_requires_email_verification_to_be_sent_again( $current_time ) {
        $ad = awpcp_tests_create_empty_listing();
        $post_date = awpcp_datetime( 'mysql', $current_time - ( $this->resend_email_threshold + 1 ) * 24 * 60 * 60 );

        wp_update_post( array(
            'ID' => $ad->ID,
            'post_date' => $post_date,
            'post_date_gmt' => get_gmt_from_date( $post_date ),
        ) );

        update_post_meta( $ad->ID, '_awpcp_verification_needed', true );
        update_post_meta( $ad->ID, '_awpcp_verification_emails_sent', 1 );
    }

    private function create_listing_that_does_not_require_email_verification_to_be_sent_again( $current_time ) {
        $ad = awpcp_tests_create_empty_listing();
        $post_date = awpcp_datetime( 'mysql', $current_time - ( $this->resend_email_threshold + 1 ) * 24 * 60 * 60 );

        wp_update_post( array(
            'ID' => $ad->ID,
            'post_date' => $post_date,
            'post_date_gmt' => get_gmt_from_date( $post_date ),
        ) );

        update_post_meta( $ad->ID, '_awpcp_verification_needed', true );
        update_post_meta( $ad->ID, '_awpcp_verification_emails_sent', 2 );
    }

    private function create_listing_that_has_exceeded_the_delete_threshold( $current_time ) {
        $ad = awpcp_tests_create_empty_listing();
        $post_date = awpcp_datetime( 'mysql', $current_time - ( $this->delete_ads_threshold + 1 ) * 24 * 60 * 60 );

        wp_update_post( array(
            'ID' => $ad->ID,
            'post_date' => $post_date,
            'post_date_gmt' => get_gmt_from_date( $post_date ),
        ) );

        update_post_meta( $ad->ID, '_awpcp_verification_needed', true );
        update_post_meta( $ad->ID, '_awpcp_verification_emails_sent', 1 );
    }

    private function create_unpaid_listing( $current_time ) {
        $ad = awpcp_tests_create_empty_listing();
        $post_date = awpcp_datetime( 'mysql', $current_time - ( $this->resend_email_threshold + 1 ) * 24 * 60 * 60 );

        wp_update_post( array(
            'ID' => $ad->ID,
            'post_date' => $post_date,
            'post_date_gmt' => get_gmt_from_date( $post_date ),
        ) );

        update_post_meta( $ad->ID, '_awpcp_verification_needed', true );
        update_post_meta( $ad->ID, '_awpcp_payment_status', 'Unpaid' );
    }

    /**
     * @large
     */
    public function test_clean_up_non_verified_ads() {
        $this->pause_filter( 'awpcp_before_delete_ad' );

        Phake::when( $this->wordpress )->get_post_meta->thenReturn( 1 )->thenReturn( 2 );

        awpcp_clean_up_non_verified_ads( $this->listings_collection, $this->listings, $this->settings, $this->wordpress );

        Phake::verify( $this->listings, Phake::times( 1 ) )->delete_listing( Phake::anyParameters() );
        Phake::verify( $this->listings, Phake::times( 1 ) )->send_verification_email( Phake::anyParameters() );
    }

    public function test_clean_up_non_verified_ads_does_nothing_if_email_verification_is_disabled() {
        Phake::when( $this->settings )->get_option( 'enable-email-verification' )->thenReturn( false );

        awpcp_clean_up_non_verified_ads( $this->listings_collection, $this->listings, $this->settings, $this->wordpress );

        Phake::verify( $this->listings, Phake::times( 0 ) )->delete_listing( Phake::anyParameters() );
        Phake::verify( $this->listings, Phake::times( 0 ) )->send_verification_email( Phake::anyParameters() );
    }
}
