<?php

/**
 * @group core
 */
class AWPCP_TestPrepareTransactionForPaymentStepDecorator extends AWPCP_StepDecoratorTestCase {

    public function setup() {
        parent::setup();

        $this->payment_completed = 'payment_completed';
        $this->checkout = 'checkout';
    }

    protected function get_default_step_decorator( $decorated ) {
        $payments = new AWPCP_PaymentsAPI( new AWPCP_Request() );
        $decorator = $this->getMockBuilder( 'AWPCP_PrepareTransactionForPaymentStepDecorator' )
                          ->setMethods( array( 'after_post' ) )
                          ->setConstructorArgs( array( $decorated, $payments, 'a', 'b' ) )
                          ->getMock();

        return $decorator;
    }

    public function test_post_set_payment_completed_as_the_next_step() {
        $methods = array( 'payment_is_not_required' => true, 'is_payment_completed' => true, 'is_ready_to_checkout' => false );
        $transaction = $this->mock_payment_transaction( $methods );
        $controller = $this->mock_page( $transaction, $this->payment_completed );

        $step = $this->get_step_decorator( 'set_transaction_status_to_payment_completed' );

        $step->post( $controller );
    }

    private function mock_page( $transaction, $next_step ) {
        $page = $this->createPartialMock( 'stdClass', array( 'get_transaction', 'set_next_step' ) );

        $page->expects( $this->once() )
             ->method( 'get_transaction' )
             ->will( $this->returnValue( $transaction ) );

        $page->expects( $this->once() )
             ->method( 'set_next_step' )
             ->with( $this->equalTo( $next_step ) );

        return $page;
    }

    private function get_step_decorator( $method ) {
        $decorated = $this->mock_step( 'post' );
        $payments = $this->mock_payments_api_with_method( $method, $this->once() );

        return new AWPCP_PrepareTransactionForPaymentStepDecorator( $decorated, $payments, $this->payment_completed, $this->checkout );
    }

    public function test_post_set_checkout_as_the_next_step() {
        $methods = array( 'payment_is_not_required' => false, 'is_payment_completed' => false, 'is_ready_to_checkout' => true );
        $transaction = $this->mock_payment_transaction( $methods );
        $controller = $this->mock_page( $transaction, $this->checkout );

        $step = $this->get_step_decorator( 'set_transaction_status_to_ready_to_checkout' );

        $step->post( $controller );
    }

    public function test_post_throws_an_exception_if_transaction_status_cant_be_set() {
        $methods = array( 'payment_is_not_required' => false, 'is_payment_completed' => false, 'is_ready_to_checkout' => false );
        $transaction = $this->mock_payment_transaction( $methods );
        $controller = $this->mock_page_that_returns_transaction( $transaction );

        $step = $this->get_step_decorator( 'set_transaction_status_to_ready_to_checkout' );

        try {
            $step->post( $controller );
            $this->fail();
        } catch ( Exception $e ) {
            // success
        }
    }
}
