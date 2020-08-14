<?php

class AWPCP_TestAdFunctions extends AWPCP_UnitTestCase {

    public function test_calculate_ad_disabled_state_when_ad_approve_is_on() {
        awpcp()->settings->set_or_update_option( 'adapprove', true );

        $disabled = awpcp_calculate_ad_disabled_state( null,  null, null );

        $this->assertTrue( (bool) $disabled );
    }

    public function test_calculate_ad_disabled_state_when_ad_approve_is_off_and_enable_ads_pending_payment_is_on() {
        awpcp()->settings->set_or_update_option( 'adapprove', false );
        awpcp()->settings->set_or_update_option( 'enable-ads-pending-payment', true );

        $transaction = Phake::mock( 'AWPCP_Payment_Transaction' );
        $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_PENDING;

        $disabled = awpcp_calculate_ad_disabled_state( null,  $transaction, null );

        $this->assertFalse( (bool) $disabled );
    }

    public function test_calculate_ad_disabled_state_when_ad_approve_is_off_and_enable_ads_pending_payment_is_off() {
        awpcp()->settings->set_or_update_option( 'adapprove', false );
        awpcp()->settings->set_or_update_option( 'enable-ads-pending-payment', false );

        $transaction = Phake::mock( 'AWPCP_Payment_Transaction' );
        $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_PENDING;

        $disabled = awpcp_calculate_ad_disabled_state( null,  $transaction, null );

        $this->assertTrue( (bool) $disabled );
    }

    /**
     * @large
     * @since 4.0.0
     */
    public function test_get_ad_share_info() {
        $listing = awpcp_tests_create_listing();
        $attachment = awpcp_tests_create_attachment();

        wp_update_post( array(
            'ID' => $attachment->ID,
            'post_parent' => $listing->ID,
            'post_status' => 'publish',
        ) );

        update_post_meta( $attachment->ID, '_awpcp_enabled', true );
        update_post_meta( $attachment->ID, '_awpcp_allowed_status', AWPCP_Attachment_Status::STATUS_APPROVED );

        $properties = awpcp_get_ad_share_info( $listing->ID );

        $this->assertEquals( $listing->post_title, $properties['title'] );
    }
}
