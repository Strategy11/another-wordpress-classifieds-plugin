<?php

/**
 * @group core
 */
class AWPCP_TestSetTransactionStatusToOpenStepDecorator extends AWPCP_StepDecoratorTestCase {

    protected function get_default_step_decorator( $decorated ) {
        $payments = awpcp_payments_api();
        $decorator = $this->getMockBuilder( 'AWPCP_SetTransactionStatusToOpenStepDecorator' )
                          ->setMethods( array( 'before_post' ) )
                          ->setConstructorArgs( array( $decorated, $payments ) )
                          ->getMock();

        return $decorator;
    }

    public function test_post_call_set_transaction_status_to_open() {
        $methods = array( 'is_new' => true, 'is_open' => true );
        $transaction = $this->mock_payment_transaction( $methods );
        $controller = $this->mock_page_that_returns_transaction( $transaction );

        $decorated = $this->mock_step( 'post', $this->once() );
        $payments = $this->mock_payments_api_with_method( 'set_transaction_status_to_open', $this->once() );

        $step = new AWPCP_SetTransactionStatusToOpenStepDecorator( $decorated, $payments );
        $step->post( $controller );
    }

    public function test_post_throws_exception_if_status_cannot_be_set() {
        $methods = array( 'is_new' => true, 'is_open' => false );
        $transaction = $this->mock_payment_transaction( $methods );
        $controller = $this->mock_page_that_returns_transaction( $transaction );

        $decorated = $this->mock_step( 'post', $this->never() );
        $payments = $this->mock_payments_api_with_method( 'set_transaction_status_to_open', $this->once() );

        $step = new AWPCP_SetTransactionStatusToOpenStepDecorator( $decorated, $payments );

        try {
            $step->post( $controller );
            $this->fail();
        } catch (Exception $e) {
            // success
        }
    }
}
