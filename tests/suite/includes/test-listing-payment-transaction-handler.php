<?php
/**
 * @package AWPCP\Tests\Payments
 */

/**
 * @group core
 */
class AWPCP_TestListingPaymentTransactionHandler extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

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

        $subject = $this->get_test_subject();

        // Execution.
        $subject->process_payment_transaction( $this->transaction );

        // Verification.
        Phake::verify( $this->listings_logic )->disable_listing( Phake::anyParameters() );
        Phake::verify( $this->listings_logic, Phake::times( 0 ) )->consolidate_new_ad( $this->listing, $this->transaction );
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
