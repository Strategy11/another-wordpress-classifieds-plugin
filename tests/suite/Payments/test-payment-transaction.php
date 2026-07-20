<?php
/**
 * @package AWPCP\Tests\Payments
 */

use Brain\Monkey\Functions;

/**
 * Unit tests for payment transaction status transitions.
 */
class AWPCP_TestPaymentTransaction extends AWPCP_UnitTestCase {

    /**
     * @since x.x
     */
    public function test_unverified_payment_cannot_complete_transaction() {
        $transaction = $this->create_transaction();
        $errors      = array();

        $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_NOT_VERIFIED;
        $transaction->_set_status( AWPCP_Payment_Transaction::STATUS_PAYMENT );

        $this->assertFalse( $transaction->set_status( AWPCP_Payment_Transaction::STATUS_PAYMENT_COMPLETED, $errors ) );
        $this->assertSame( AWPCP_Payment_Transaction::STATUS_PAYMENT, $transaction->get_status() );
        $this->assertNotEmpty( $errors );
    }

    /**
     * @since x.x
     */
    public function test_pending_payment_can_complete_transaction() {
        $transaction = $this->create_transaction();
        $errors      = array();

        $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_PENDING;
        $transaction->_set_status( AWPCP_Payment_Transaction::STATUS_PAYMENT );

        $this->assertTrue( $transaction->set_status( AWPCP_Payment_Transaction::STATUS_PAYMENT_COMPLETED, $errors ) );
        $this->assertSame( AWPCP_Payment_Transaction::STATUS_PAYMENT_COMPLETED, $transaction->get_status() );
    }

    /**
     * @since x.x
     *
     * @return AWPCP_Payment_Transaction
     */
    private function create_transaction() {
        Functions\when( 'awpcp_current_user_is_admin' )->justReturn( false );
        Functions\when( 'maybe_unserialize' )->returnArg();

        return new AWPCP_Payment_Transaction( array( 'id' => 'transaction-id' ) );
    }
}
