<?php
/**
 * @package AWPCP\Tests\Payments
 */

use Brain\Monkey\Functions;

/**
 * @group core
 */
class AWPCP_TestListingPaymentTransactionHandler extends AWPCP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();

        $this->listing = (object) array( 'ID' => wp_rand() + 1 );

        $this->transaction = Phake::mock( 'AWPCP_Payment_Transaction' );
        Phake::when( $this->transaction )->get( 'ad-id' )->thenReturn( $this->listing->ID );
        Phake::when( $this->transaction )->get( 'context' )->thenReturn( 'place-ad' );
        Phake::when( $this->transaction )->was_payment_successful()->thenReturn( false );
        Phake::when( $this->transaction )->payment_is_completed()->thenReturn( false );

        $this->listings = Phake::mock( 'AWPCP_ListingsCollection' );
        Phake::when( $this->listings )->get( $this->listing->ID )->thenReturn( $this->listing );

        $this->listing_renderer = Phake::mock( 'AWPCP_ListingRenderer' );
        $this->listings_logic   = Phake::mock( 'AWPCP_ListingsAPI' );
        $this->settings         = Mockery::mock( 'AWPCP_Settings' );
        $this->wordpress        = Phake::mock( 'AWPCP_WordPress' );
    }

    /**
     * @medium
     */
    public function test_process_payment_transaction_before_payment() {
        Phake::when( $this->transaction )->is_payment_completed()->thenReturn( false );
        Phake::when( $this->transaction )->is_completed()->thenReturn( false );

        $subject = $this->get_test_subject();

        // Execution.
        $subject->process_payment_transaction( $this->transaction );

        // Verification.
        Phake::verify( $this->transaction, Phake::times( 0 ) )->get( Phake::anyParameters() );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_ListingPaymentTransactionHandler(
            $this->listing_renderer,
            $this->listings,
            $this->listings_logic,
            $this->settings,
            $this->wordpress
        );
    }

    public function test_process_payment_transaction_after_payment_completed() {
        Phake::when( $this->transaction )->is_payment_completed()->thenReturn( true );
        Phake::when( $this->transaction )->is_completed()->thenReturn( false );
        Phake::when( $this->transaction )->get( 'ad-id' )->thenReturn( null );

        $subject = $this->get_test_subject();

        // Execution.
        $subject->process_payment_transaction( $this->transaction );

        // Verification.
        Phake::verify( $this->transaction )->get( 'ad-id' );
    }

    public function test_process_payment_transaction_after_completed() {
        Phake::when( $this->transaction )->is_payment_completed()->thenReturn( false );
        Phake::when( $this->transaction )->is_completed()->thenReturn( true );

        Phake::when( $this->transaction )->get( 'ad-id' )->thenReturn( null );

        $subject = $this->get_test_subject();

        // Execution.
        $subject->process_payment_transaction( $this->transaction );

        // Verification.
        Phake::verify( $this->transaction )->get( 'ad-id' );
    }

    public function test_process_completed_transaction_after_payment_completed() {
        $this->settings->shouldReceive( 'get_option' )
            ->with( 'pay-before-place-ad' )
            ->andReturn( false );

        Phake::when( $this->transaction )->was_payment_successful()->thenReturn( true );
        Phake::when( $this->transaction )->is_payment_completed()->thenReturn( true );

        $this->check_process_completed_transaction();
    }

    private function check_process_completed_transaction() {
        $subject = $this->get_test_subject();

        // Execution.
        $subject->process_payment_transaction( $this->transaction );

        // Verification.
        Phake::verify( $this->listings_logic )->update_listing_verified_status( $this->listing, $this->transaction );
        Phake::verify( $this->wordpress )->update_post_meta(
            $this->listing->ID,
            '_awpcp_payment_status',
            $this->transaction->payment_status
        );
    }

    public function test_process_completed_transaction_after_completed() {
        Phake::when( $this->transaction )->was_payment_successful()->thenReturn( true );
        Phake::when( $this->transaction )->is_completed()->thenReturn( true );

        $this->check_process_completed_transaction();
    }

    public function test_process_completed_transaction_after_failed_canceled_or_invalid_payment() {
        Phake::when( $this->transaction )->is_completed()->thenReturn( true );
        Phake::when( $this->transaction )->was_payment_successful()->thenReturn( false );
        Phake::when( $this->transaction )->did_payment_failed()->thenReturn( true );
        Phake::when( $this->transaction )->get( 'ad-consolidated-at' )->thenReturn( date( 'Y-m-d H:i:s' ) );
        Phake::when( $this->transaction )->get( 'previous-ad-payment-status' )->thenReturn( AWPCP_Payment_Transaction::PAYMENT_STATUS_COMPLETED );

        Functions\when( 'current_time' )->justReturn( '2026-01-01 00:00:00' );

        $subject = $this->get_test_subject();

        // Execution.
        $subject->process_payment_transaction( $this->transaction );

        // Verification.
        Phake::verify( $this->listings_logic )->disable_listing( $this->listing );
        Phake::verify( $this->listings_logic, Phake::times( 0 ) )->consolidate_new_ad( $this->listing, $this->transaction );
    }

    /**
     * @since x.x
     */
    public function test_listing_is_disabled_once_when_its_payment_fails_later() {
        Phake::when( $this->transaction )->is_completed()->thenReturn( true );
        Phake::when( $this->transaction )->was_payment_successful()->thenReturn( false );
        Phake::when( $this->transaction )->did_payment_failed()->thenReturn( true );
        Phake::when( $this->transaction )->payment_is_failed()->thenReturn( true );
        Phake::when( $this->transaction )->get( 'ad-consolidated-at' )->thenReturn( '2026-01-01 00:00:00' );
        Phake::when( $this->transaction )->get( 'previous-ad-payment-status' )->thenReturn( 'Unpaid' );

        Functions\when( 'current_time' )->justReturn( '2026-01-02 00:00:00' );

        $subject = $this->get_test_subject();

        $subject->process_payment_transaction( $this->transaction );

        Phake::verify( $this->listings_logic )->disable_listing( $this->listing );
        Phake::verify( $this->transaction )->set( 'ad-disabled-at', '2026-01-02 00:00:00' );

        Phake::when( $this->transaction )->get( 'ad-disabled-at' )->thenReturn( '2026-01-02 00:00:00' );

        $subject->process_payment_transaction( $this->transaction );

        Phake::verify( $this->listings_logic, Phake::times( 1 ) )->disable_listing( $this->listing );
    }

    /**
     * @since x.x
     */
    public function test_completed_follow_up_updates_payment_status_of_pending_listing() {
        $this->transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_COMPLETED;

        Phake::when( $this->transaction )->is_completed()->thenReturn( true );
        Phake::when( $this->transaction )->was_payment_successful()->thenReturn( true );
        Phake::when( $this->transaction )->get( 'ad-consolidated-at' )->thenReturn( '2026-01-01 00:00:00' );
        Phake::when( $this->transaction )->get( 'previous-ad-payment-status' )->thenReturn( 'Unpaid' );

        $subject = $this->get_test_subject();

        $subject->process_payment_transaction( $this->transaction );

        Phake::verify( $this->wordpress )->update_post_meta(
            $this->listing->ID,
            '_awpcp_payment_status',
            AWPCP_Payment_Transaction::PAYMENT_STATUS_COMPLETED
        );
        Phake::verify( $this->listings_logic )->set_new_listing_post_status(
            $this->listing,
            AWPCP_Payment_Transaction::PAYMENT_STATUS_COMPLETED,
            true
        );
        Phake::verify( $this->listings_logic, Phake::never() )->consolidate_new_ad( Phake::anyParameters() );
    }

    /**
     * @large
     */
    public function test_process_completed_transaction_for_new_listing() {
        $this->settings->shouldReceive( 'get_option' )
            ->with( 'pay-before-place-ad' )
            ->andReturn( false );

        Phake::when( $this->transaction )->was_payment_successful()->thenReturn( true );
        Phake::when( $this->transaction )->is_payment_completed()->thenReturn( true );
        Phake::when( $this->transaction )->get( 'ad-consolidated-at' )->thenReturn( false );

        Phake::when( $this->listing_renderer )->is_disabled->thenReturn( true );

        $subject = $this->get_test_subject();

        // Execution.
        $subject->process_payment_transaction( $this->transaction );

        // Verification.
        Phake::verify( $this->listings_logic )->update_listing_verified_status( Phake::anyParameters() );
        Phake::verify( $this->listings_logic )->set_new_listing_post_status( Phake::anyParameters() );
        Phake::verify( $this->listings_logic )->consolidate_new_ad( Phake::anyParameters() );
    }
}
