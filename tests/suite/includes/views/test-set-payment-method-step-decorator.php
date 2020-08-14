<?php

/**
 * @group core
 */
class AWPCP_TestSetPaymentMethodStepDecorator extends AWPCP_StepDecoratorTestCase {

    protected function get_default_step_decorator( $decorated ) {
        $payments = new AWPCP_PaymentsAPI( new AWPCP_Request() );

        return $this->getMockBuilder( 'AWPCP_SetPaymentMethodStepDecorator' )
                    ->setMethods( array( 'before_get', 'before_post' ) )
                    ->setConstructorArgs( array( $decorated, $payments ) )
                    ->getMock();
    }

    public function test_get_calls_set_transaction_payment_method() {
        $this->verify_it_calls_set_transaction_payment_method( 'get' );
    }

    public function test_post_calls_set_transaction_payment_method() {
        $this->verify_it_calls_set_transaction_payment_method( 'post' );
    }

    private function verify_it_calls_set_transaction_payment_method( $request_method ) {
        $controller = $this->mock_page_that_returns_transaction( null );

        $decorated = $this->mock_step( $request_method, $this->once() );
        $payments = $this->mock_payments_api_with_method( 'set_transaction_payment_method', $this->once() );

        $step = new AWPCP_SetPaymentMethodStepDecorator( $decorated, $payments );

        if ( $request_method === 'post' ) {
            $step->post( $controller );
        } else {
            $step->get( $controller );
        }
    }
}
