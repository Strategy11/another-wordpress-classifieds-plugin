<?php
/**
 * @package AWPCP\Tests\Payments
 */

use Brain\Monkey\Functions;

/**
 * Tests for removing the credits of a credit plan purchase whose payment failed later.
 */
class AWPCP_PaymentsAPICreditsRevertTest extends AWPCP_UnitTestCase {

    /**
     * @since x.x
     */
    public function test_credits_are_removed_once_when_purchase_payment_fails() {
        $transaction = $this->create_transaction( AWPCP_Payment_Transaction::PAYMENT_STATUS_FAILED );
        $transaction->set( 'credits-purchase-processed', true );

        $api = $this->get_test_subject( 15 );

        $api->maybe_revert_credits_purchase( $transaction );
        $api->maybe_revert_credits_purchase( $transaction );

        Phake::verify( $api, Phake::times( 1 ) )->set_account_balance( 7, 5 );
        $this->assertTrue( $transaction->get( 'credits-purchase-reverted' ) );
    }

    /**
     * @since x.x
     */
    public function test_credits_are_kept_when_payment_did_not_fail() {
        $transaction = $this->create_transaction( AWPCP_Payment_Transaction::PAYMENT_STATUS_COMPLETED );
        $transaction->set( 'credits-purchase-processed', true );

        $api = $this->get_test_subject( 15 );

        $api->maybe_revert_credits_purchase( $transaction );

        Phake::verify( $api, Phake::never() )->set_account_balance( Phake::anyParameters() );
    }

    /**
     * @since x.x
     */
    public function test_credits_are_kept_when_purchase_was_never_processed() {
        $transaction = $this->create_transaction( AWPCP_Payment_Transaction::PAYMENT_STATUS_FAILED );

        $api = $this->get_test_subject( 15 );

        $api->maybe_revert_credits_purchase( $transaction );

        Phake::verify( $api, Phake::never() )->set_account_balance( Phake::anyParameters() );
    }

    /**
     * @since x.x
     *
     * @param int $balance The user's current account balance.
     *
     * @return AWPCP_PaymentsAPI
     */
    private function get_test_subject( $balance ) {
        Functions\when( 'is_admin' )->justReturn( false );

        $api = Phake::partialMock( 'AWPCP_PaymentsAPI', Phake::mock( 'AWPCP_Request' ) );

        Phake::when( $api )->get_transaction_credit_plan( Phake::anyParameters() )->thenReturn( (object) array( 'credits' => 10 ) );
        Phake::when( $api )->get_account_balance( 7 )->thenReturn( $balance );
        Phake::when( $api )->set_account_balance( Phake::anyParameters() )->thenReturn( true );

        return $api;
    }

    /**
     * @since x.x
     *
     * @param string $payment_status The transaction payment status.
     *
     * @return AWPCP_Payment_Transaction
     */
    private function create_transaction( $payment_status ) {
        Functions\when( 'awpcp_current_user_is_admin' )->justReturn( false );
        Functions\when( 'maybe_unserialize' )->returnArg();

        $transaction                 = new AWPCP_Payment_Transaction( array( 'id' => 'transaction-id' ) );
        $transaction->user_id        = 7;
        $transaction->payment_status = $payment_status;

        return $transaction;
    }
}
