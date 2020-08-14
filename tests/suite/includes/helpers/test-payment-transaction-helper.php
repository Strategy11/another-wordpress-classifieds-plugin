<?php

class AWPCP_TestPaymentTransactionHelper extends AWPCP_UnitTestCase {

    public function test_get_or_create_transaction_properly_sets_user_id() {
        $this->login_as_subscriber();

        $request = Phake::mock( 'AWPCP_Request' );
        Phake::when( $request )->param( 'transaction_id' )->thenReturn( false );

        $helper = new AWPCP_PaymentTransactionHelper( array(), $request );
        $transaction = $helper->get_or_create_transaction();

        $this->assertGreaterThan( 0, $transaction->user_id );
    }
}
