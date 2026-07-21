<?php
/**
 * @package AWPCP\Tests\Plugin\Payments\Gateways
 */

use Brain\Monkey\Functions;

/**
 * Unit test for PayPal Standard payment gateway.
 */
class PayPalStandardTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.4.8
     *
     * @return void
     */
    public function tearDown(): void {
        $_POST = array();

        parent::tearDown();
    }

    /**
     * @since 4.4.8
     */
    public function test_invalid_user_return_waits_for_ipn_verification() {
        $request     = Phake::mock( 'AWPCP_Request' );
        $transaction = $this->create_transaction();
        $gateway     = Phake::partialMock( 'AWPCP_PayPalStandardPaymentGateway', $request );

        Phake::when( $request )->post( 'verify_sign' )->thenReturn( 'signature' );
        Phake::when( $gateway )->verify_transaction( $transaction )->thenReturn( 'INVALID' );

        $gateway->process_payment_completed( $transaction );

        $this->assertSame( AWPCP_Payment_Transaction::PAYMENT_STATUS_NOT_VERIFIED, $transaction->payment_status );
        $this->assertTrue( $transaction->get( 'pending_verification' ) );
    }

    /**
     * @since 4.4.8
     */
    public function test_invalid_ipn_marks_payment_as_invalid() {
        $request     = Phake::mock( 'AWPCP_Request' );
        $transaction = $this->create_transaction();
        $gateway     = Phake::partialMock( 'AWPCP_PayPalStandardPaymentGateway', $request );

        $transaction->set( 'pending_verification', true );

        Phake::when( $request )->post( 'verify_sign' )->thenReturn( 'signature' );
        Phake::when( $gateway )->verify_transaction( $transaction )->thenReturn( 'INVALID' );

        $gateway->process_payment_notification( $transaction );

        $this->assertSame( AWPCP_Payment_Transaction::PAYMENT_STATUS_INVALID, $transaction->payment_status );
        $this->assertFalse( $transaction->get( 'pending_verification' ) );
    }

    /**
     * @since 4.4.8
     */
    public function test_verified_paypal_pending_payment_remains_pending() {
        $_POST = array(
            'mc_gross'       => '9.99',
            'payment_gross'  => '9.99',
            'txn_type'       => 'cart',
            'custom'         => 'transaction-id',
            'payment_status' => 'Pending',
        );

        $request     = Phake::mock( 'AWPCP_Request' );
        $transaction = $this->create_transaction();
        $gateway     = Phake::partialMock( 'AWPCP_PayPalStandardPaymentGateway', $request );

        Phake::when( $request )->post( 'verify_sign' )->thenReturn( 'whatever' );

        Phake::when( $gateway )->verify_transaction->thenReturn( 'VERIFIED' );
        Phake::when( $gateway )->funds_were_sent_to_correct_receiver->thenReturn( true );

        $gateway->process_payment_completed( $transaction );

        $this->assertSame( AWPCP_Payment_Transaction::PAYMENT_STATUS_PENDING, $transaction->payment_status );
        $this->assertTrue( $transaction->get( 'validated' ) );
    }

    /**
     * @since 4.4.8
     */
    public function test_verified_payment_with_mismatched_transaction_id_is_invalid() {
        $_POST = array(
            'mc_gross'       => '9.99',
            'payment_gross'  => '9.99',
            'txn_type'       => 'cart',
            'custom'         => 'another-transaction-id',
            'payment_status' => 'Completed',
        );

        $request     = Phake::mock( 'AWPCP_Request' );
        $transaction = $this->create_transaction();
        $gateway     = Phake::partialMock( 'AWPCP_PayPalStandardPaymentGateway', $request );

        Functions\when( 'awpcp_payment_failed_email' )->justReturn();
        Phake::when( $request )->post( 'verify_sign' )->thenReturn( 'signature' );
        Phake::when( $gateway )->verify_transaction( $transaction )->thenReturn( 'VERIFIED' );

        $gateway->process_payment_completed( $transaction );

        $this->assertSame( AWPCP_Payment_Transaction::PAYMENT_STATUS_INVALID, $transaction->payment_status );
        $this->assertFalse( $transaction->get( 'validated', false ) );
    }

    /**
     * @since 4.4.8
     *
     * @return AWPCP_Payment_Transaction
     */
    private function create_transaction() {
        Functions\when( 'awpcp_current_user_is_admin' )->justReturn( false );
        Functions\when( 'maybe_unserialize' )->returnArg();

        $transaction = new AWPCP_Payment_Transaction( array( 'id' => 'transaction-id' ) );
        $transaction->add_item( 1, 'Listing', 'Listing fee', AWPCP_Payment_Transaction::PAYMENT_TYPE_MONEY, 9.99 );

        return $transaction;
    }
}
