<?php

/**
 * @group core
 */
class AWPCP_TestVerifyPaymentCanBeProcessedStepDecorator extends AWPCP_StepDecoratorTestCase {

    protected function get_default_step_decorator( $decorated ) {
        return $this->getMockBuilder( 'AWPCP_VerifyPaymentCanBeProcessedStepDecorator' )
                    ->setMethods( array( 'before_get', 'before_post' ) )
                    ->setConstructorArgs( array( $decorated ) )
                    ->getMock();
    }

    public function test_get_throws_an_exception_if_payment_cant_be_processed() {
        $this->verify_it_throws_an_exception_if_payment_cant_be_processed( 'get' );
    }

    public function test_post_throws_an_exception_if_payment_cant_be_processed() {
        $this->verify_it_throws_an_exception_if_payment_cant_be_processed( 'post' );
    }

    private function verify_it_throws_an_exception_if_payment_cant_be_processed( $request_method ) {
        $methods = array( 'is_doing_checkout' => false, 'is_processing_payment' => false );
        $transaction = $this->mock_payment_transaction( $methods );
        $controller = $this->mock_page_that_returns_transaction( $transaction );

        $decorated = $this->mock_step( $request_method, $this->never() );
        $step = new AWPCP_VerifyPaymentCanBeProcessedStepDecorator( $decorated );

        try {
            if ( $request_method === 'post' ) {
                $step->post( $controller );
            } else {
                $step->get( $controller );
            }
            $this->fail();
        } catch (Exception $e) {
            // success
        }
    }
}
