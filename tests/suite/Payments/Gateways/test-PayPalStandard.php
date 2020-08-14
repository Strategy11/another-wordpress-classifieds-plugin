<?php
/**
 * @package AWPCP\Tests\Plugin\Payments\Gateways
 */

/**
 * Unit test for PayPal Standard payment gateway.
 */
class PayPalStandardTest extends AWPCP_UnitTestCase {

    public function test_validate_transaction_process_numeric_values_correctly() {
        $_POST = array(
            'mc_gross' => '9.99',
            'payment_gross' => '9.99',
            'txn_type' => 'cart',
        );

        $request = Phake::mock( 'AWPCP_Request' );
        $transaction = Phake::mock( 'AWPCP_Payment_Transaction' );

        $gateway = Phake::partialMock( 'AWPCP_PayPalStandardPaymentGateway', $request );

        Phake::when( $request )->post( 'verify_sign' )->thenReturn( 'whatever' );

        Phake::when( $transaction )->get_totals->thenReturn( array( 'money' => 9.99 ) );

        Phake::when( $gateway )->verify_transaction->thenReturn( 'VERIFIED' );
        Phake::when( $gateway )->funds_were_sent_to_correct_receiver->thenReturn( true );

        // Execution.
        $gateway->process_payment_completed( $transaction );
    }
}
