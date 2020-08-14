<?php

/**
 * @group core
 */
class AWPCP_TestVerifyTransactionExistsStepDecorator extends AWPCP_StepDecoratorTestCase {

    protected function get_default_step_decorator( $decorated ) {
        return $this->getMockBuilder( 'AWPCP_VerifyTransactionExistsStepDecorator' )
                    ->setMethods( array( 'before_get', 'before_post' ) )
                    ->setConstructorArgs( array( $decorated ) )
                    ->getMock();
    }

    public function test_get_throws_an_exception_if_transaction_does_not_exists() {
        $this->verify_it_throws_an_exception_if_transaction_does_not_exists( 'get' );
    }

    public function test_post_throws_an_exception_if_transaction_does_not_exists() {
        $this->verify_it_throws_an_exception_if_transaction_does_not_exists( 'post' );
    }

    private function verify_it_throws_an_exception_if_transaction_does_not_exists( $request_method ) {
        $controller = $this->mock_page_that_returns_transaction( null );

        $decorated = $this->mock_step( $request_method, $this->never() );
        $step = new AWPCP_VerifyTransactionExistsStepDecorator( $decorated );

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
