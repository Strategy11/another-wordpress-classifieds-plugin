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
            'mc_currency'    => 'USD',
        );

        $request     = Phake::mock( 'AWPCP_Request' );
        $transaction = $this->create_transaction();
        $gateway     = Phake::partialMock( 'AWPCP_PayPalStandardPaymentGateway', $request );

        Functions\when( 'get_awpcp_option' )->justReturn( 'USD' );
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
     * @since x.x
     */
    public function test_verified_payment_in_another_currency_is_invalid() {
        $_POST = $this->get_ipn_data( array( 'mc_currency' => 'JPY' ) );

        $request     = Phake::mock( 'AWPCP_Request' );
        $transaction = $this->create_transaction();
        $gateway     = $this->create_verified_gateway( $request );

        $gateway->process_payment_notification( $transaction );

        $this->assertSame( AWPCP_Payment_Transaction::PAYMENT_STATUS_INVALID, $transaction->payment_status );
        $this->assertFalse( $transaction->get( 'validated', false ) );
    }

    /**
     * @since x.x
     */
    public function test_denied_ipn_marks_verified_pending_payment_as_failed() {
        $_POST = $this->get_ipn_data( array( 'payment_status' => 'Denied' ) );

        $request     = Phake::mock( 'AWPCP_Request' );
        $transaction = $this->create_verified_transaction( AWPCP_Payment_Transaction::PAYMENT_STATUS_PENDING );
        $gateway     = $this->create_verified_gateway( $request );

        $gateway->process_payment_notification( $transaction );

        $this->assertSame( AWPCP_Payment_Transaction::PAYMENT_STATUS_FAILED, $transaction->payment_status );
        $this->assertTrue( $transaction->get( 'verified' ) );
        $this->assertSame( 'PAYMENT-TXN', $transaction->get( 'txn-id' ) );
    }

    /**
     * @since x.x
     */
    public function test_completed_ipn_marks_verified_pending_payment_as_completed() {
        $_POST = $this->get_ipn_data( array( 'payment_status' => 'Completed' ) );

        $request     = Phake::mock( 'AWPCP_Request' );
        $transaction = $this->create_verified_transaction( AWPCP_Payment_Transaction::PAYMENT_STATUS_PENDING );
        $gateway     = $this->create_verified_gateway( $request );

        $gateway->process_payment_notification( $transaction );

        $this->assertSame( AWPCP_Payment_Transaction::PAYMENT_STATUS_COMPLETED, $transaction->payment_status );
        $this->assertSame( 'PAYMENT-TXN', $transaction->get( 'txn-id' ) );
    }

    /**
     * @since x.x
     */
    public function test_reversal_ipn_marks_verified_completed_payment_as_failed() {
        $_POST = $this->get_ipn_data(
            array(
                'payment_status' => 'Reversed',
                'txn_type'       => '',
                'txn_id'         => 'REVERSAL-TXN',
                'parent_txn_id'  => 'PAYMENT-TXN',
                'mc_gross'       => '-9.99',
                'payment_gross'  => '-9.99',
            )
        );

        $request     = Phake::mock( 'AWPCP_Request' );
        $transaction = $this->create_verified_transaction( AWPCP_Payment_Transaction::PAYMENT_STATUS_COMPLETED );
        $gateway     = $this->create_verified_gateway( $request );

        $gateway->process_payment_notification( $transaction );

        $this->assertSame( AWPCP_Payment_Transaction::PAYMENT_STATUS_FAILED, $transaction->payment_status );
        $this->assertSame( 'PAYMENT-TXN', $transaction->get( 'txn-id' ) );
    }

    /**
     * @since x.x
     */
    public function test_ipn_for_another_payment_does_not_change_verified_payment() {
        $_POST = $this->get_ipn_data(
            array(
                'payment_status' => 'Denied',
                'txn_id'         => 'OTHER-TXN',
            )
        );

        $request     = Phake::mock( 'AWPCP_Request' );
        $transaction = $this->create_verified_transaction( AWPCP_Payment_Transaction::PAYMENT_STATUS_COMPLETED );
        $gateway     = $this->create_verified_gateway( $request );

        $gateway->process_payment_notification( $transaction );

        $this->assertSame( AWPCP_Payment_Transaction::PAYMENT_STATUS_COMPLETED, $transaction->payment_status );
        Phake::verify( $gateway, Phake::never() )->verify_transaction( Phake::anyParameters() );
    }

    /**
     * @since x.x
     */
    public function test_replayed_ipn_does_not_restore_failed_payment() {
        $_POST = $this->get_ipn_data( array( 'payment_status' => 'Completed' ) );

        $request     = Phake::mock( 'AWPCP_Request' );
        $transaction = $this->create_verified_transaction( AWPCP_Payment_Transaction::PAYMENT_STATUS_FAILED );
        $gateway     = $this->create_verified_gateway( $request );

        $gateway->process_payment_notification( $transaction );

        $this->assertSame( AWPCP_Payment_Transaction::PAYMENT_STATUS_FAILED, $transaction->payment_status );
    }

    /**
     * @since x.x
     */
    public function test_unverified_ipn_does_not_change_verified_payment() {
        $_POST = $this->get_ipn_data( array( 'payment_status' => 'Denied' ) );

        $request     = Phake::mock( 'AWPCP_Request' );
        $transaction = $this->create_verified_transaction( AWPCP_Payment_Transaction::PAYMENT_STATUS_COMPLETED );
        $gateway     = Phake::partialMock( 'AWPCP_PayPalStandardPaymentGateway', $request );

        Phake::when( $request )->post( 'verify_sign' )->thenReturn( 'signature' );
        Phake::when( $gateway )->verify_transaction->thenReturn( 'INVALID' );

        $gateway->process_payment_notification( $transaction );

        $this->assertSame( AWPCP_Payment_Transaction::PAYMENT_STATUS_COMPLETED, $transaction->payment_status );
        $this->assertTrue( $transaction->get( 'verified' ) );
    }

    /**
     * @since x.x
     *
     * @param array $overrides IPN fields to change.
     *
     * @return array
     */
    private function get_ipn_data( $overrides = array() ) {
        return array_merge(
            array(
                'mc_gross'       => '9.99',
                'payment_gross'  => '9.99',
                'mc_currency'    => 'USD',
                'txn_type'       => 'cart',
                'txn_id'         => 'PAYMENT-TXN',
                'custom'         => 'transaction-id',
                'payment_status' => 'Completed',
            ),
            $overrides
        );
    }

    /**
     * @since x.x
     *
     * @param AWPCP_Request $request The request mock.
     *
     * @return AWPCP_PayPalStandardPaymentGateway
     */
    private function create_verified_gateway( $request ) {
        $gateway = Phake::partialMock( 'AWPCP_PayPalStandardPaymentGateway', $request );

        Functions\when( 'get_awpcp_option' )->justReturn( 'USD' );
        Functions\when( 'awpcp_payment_failed_email' )->justReturn();
        Phake::when( $request )->post( 'verify_sign' )->thenReturn( 'signature' );
        Phake::when( $gateway )->verify_transaction->thenReturn( 'VERIFIED' );
        Phake::when( $gateway )->funds_were_sent_to_correct_receiver->thenReturn( true );

        return $gateway;
    }

    /**
     * @since x.x
     *
     * @param string $payment_status The payment status of the verified payment.
     *
     * @return AWPCP_Payment_Transaction
     */
    private function create_verified_transaction( $payment_status ) {
        $transaction = $this->create_transaction();

        $transaction->payment_status = $payment_status;
        $transaction->set( 'verified', true );
        $transaction->set( 'txn-id', 'PAYMENT-TXN' );

        return $transaction;
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
