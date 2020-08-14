<?php

/**
 * @group core
 */
class AWPCP_TestSetCreditPlanStepDecorator extends AWPCP_StepDecoratorTestCase {

    protected function get_default_step_decorator( $decorated ) {
        $payments = new AWPCP_PaymentsAPI( new AWPCP_Request() );

        return $this->getMockBuilder( 'AWPCP_SetCreditPlanStepDecorator' )
                    ->setMethods( array( 'before_get', 'before_post' ) )
                    ->setConstructorArgs( array( $decorated, $payments ) )
                    ->getMock();
    }

    public function test_post_calls_set_transaction_payment_method() {
        $transaction = $this->createPartialMock( 'stdClass', array( 'remove_all_items' ) );
        $controller = $this->mock_page_that_returns_transaction( $transaction );

        $decorated = $this->mock_step( 'post', $this->once() );
        $payments = $this->mock_payments_api_with_method( 'set_transaction_credit_plan', $this->once() );

        $step = new AWPCP_SetCreditPlanStepDecorator( $decorated, $payments );
        $step->post( $controller );
    }
}
