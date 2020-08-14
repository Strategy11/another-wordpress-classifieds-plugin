<?php

/**
 * @group core
 */
class AWPCP_TestVerifyCreditPlanWasSetStepDecorator extends AWPCP_StepDecoratorTestCase {

    protected function get_default_step_decorator( $decorated ) {
        $payments = awpcp_payments_api();
        $decorator = $this->getMockBuilder( 'AWPCP_VerifyCreditPlanWasSetStepDecorator' )
                          ->setMethods( array( 'before_post' ) )
                          ->setConstructorArgs( array( $decorated, $payments ) )
                          ->getMock();

        return $decorator;
    }

    public function test_post_throws_an_exception_when_credit_plan_is_not_set() {
        $controller = $this->createPartialMock( 'stdClass', array( 'get_transaction' ) );

        $decorated = $this->mock_step( 'post', $this->never() );
        $payments = $this->mock_payments_api_with_method( 'get_transaction_credit_plan', $this->once(), null );

        $step = new AWPCP_VerifyCreditPlanWasSetStepDecorator( $decorated, $payments );

        try {
            $step->post( $controller );
            $this->fail();
        } catch (Exception $e) {
            // success
        }
    }
}
