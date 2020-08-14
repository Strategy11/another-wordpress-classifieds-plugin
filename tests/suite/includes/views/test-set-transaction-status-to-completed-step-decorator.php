<?php

/**
 * @group core
 */
class AWPCP_TestSetTransactionStatusToCompletedStepDecorator extends AWPCP_StepDecoratorTestCase {

    protected function get_default_step_decorator( $decorated ) {
        $payments = awpcp_payments_api();
        $decorator = $this->getMockBuilder( 'AWPCP_SetTransactionStatusToCompletedStepDecorator' )
                          ->setMethods( array( 'before_get', 'before_post' ) )
                          ->setConstructorArgs( array( $decorated, $payments ) )
                          ->getMock();

        return $decorator;
    }

    public function test_get_call_set_transaction_status_to_completed() {
        $this->verify_it_call_set_transaction_status_to_completed( 'get' );
    }

    public function test_post_call_set_transaction_status_to_completed() {
        $this->verify_it_call_set_transaction_status_to_completed( 'post' );
    }

    private function verify_it_call_set_transaction_status_to_completed( $request_method ) {
        $transaction = Phake::mock('AWPCP_Payment_Transaction');
        Phake::when( $transaction )->is_completed()->thenReturn( false )->thenReturn( true );

        $controller = $this->mock_page_that_returns_transaction( $transaction );

        $decorated = $this->mock_step( $request_method );
        $payments = $this->mock_payments_api_with_method( 'set_transaction_status_to_completed', $this->once() );

        $step = new AWPCP_SetTransactionStatusToCompletedStepDecorator( $decorated, $payments );

        if ( $request_method === 'post' ) {
            $step->post( $controller );
        } else {
            $step->get( $controller );
        }
    }
}
