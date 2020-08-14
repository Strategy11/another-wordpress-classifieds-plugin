<?php

/**
 * @group core
 */
class AWPCP_TestSetTransactionStatusToCheckoutStepDecorator extends AWPCP_StepDecoratorTestCase {

    protected function get_default_step_decorator( $decorated ) {
        $payments = awpcp_payments_api();
        $decorator = $this->getMockBuilder( 'AWPCP_SetTransactionStatusToCheckoutStepDecorator' )
                          ->setMethods( array( 'before_get', 'before_post' ) )
                          ->setConstructorARgs( array( $decorated, $payments ) )
                          ->getMock();

        return $decorator;
    }

    public function test_get_call_set_transaction_status_to_checkout() {
        $this->verify_it_calls_set_transaction_status_to_checkout( 'get' );
    }

    public function test_post_call_set_transaction_status_to_checkout() {
        $this->verify_it_calls_set_transaction_status_to_checkout( 'post' );
    }

    private function verify_it_calls_set_transaction_status_to_checkout( $request_method ) {
        $methods = array( 'is_ready_to_checkout' => true, 'is_doing_checkout' => true );
        $transaction = $this->mock_payment_transaction( $methods );
        $controller = $this->mock_page_that_returns_transaction( $transaction );

        $decorated = $this->mock_step( $request_method );
        $payments = $this->mock_payments_api_with_method( 'set_transaction_status_to_checkout', $this->once() );

        $step = new AWPCP_SetTransactionStatusToCheckoutStepDecorator( $decorated, $payments );

        if ( $request_method === 'get' ) {
            $step->get( $controller );
        } else {
            $step->post( $controller );
        }
    }

    public function test_get_throws_exception_if_status_cannot_be_set() {
        $this->verify_throws_exception_if_status_cannot_be_set( 'get' );
    }

    public function test_post_throws_exception_if_status_cannot_be_set() {
        $this->verify_throws_exception_if_status_cannot_be_set( 'post' );
    }

    private function verify_throws_exception_if_status_cannot_be_set( $request_method ) {
        $methods = array( 'is_ready_to_checkout' => true, 'is_doing_checkout' => false );
        $transaction = $this->mock_payment_transaction( $methods );
        $controller = $this->mock_page_that_returns_transaction( $transaction );

        $decorated = $this->mock_step( $request_method, $this->never() );
        $payments = $this->mock_payments_api_with_method( 'set_transaction_status_to_checkout', $this->once() );

        $step = new AWPCP_SetTransactionStatusToCheckoutStepDecorator( $decorated, $payments );

        try {
            if ( $request_method === 'get' ) {
                $step->get( $controller );
            } else {
                $step->post( $controller );
            }
            $this->fail();
        } catch (Exception $e) {
            // success
        }
    }
}
