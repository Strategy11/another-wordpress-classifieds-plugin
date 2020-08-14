<?php

define( 'AWPCP_TESTS_UNDEFINED', '!"·!"·"%$"%' );

class AWPCP_StepDecoratorTestCase extends AWPCP_UnitTestCase {

    public function test_get() {
        $step = $this->mock_step( 'get' );
        $decorator = $this->get_default_step_decorator( $step );
        $decorator->get( null );
    }

    protected function mock_step( $method, $matcher = null ) {
        $step = $this->createPartialMock( 'stdClass', array( $method ) );
        $step->expects( $matcher ? $matcher : $this->once() )->method( $method );
        return $step;
    }

    protected function get_default_step_decorator( $decorated ) {
        return new AWPCP_StepDecorator( $decorated );
    }

    public function test_post() {
        $step = $this->mock_step( 'post' );
        $decorator = $this->get_default_step_decorator( $step );
        $decorator->post( null );
    }

    protected function mock_payment_transaction( $methods = array() ) {
        $transaction = $this->createPartialMock( 'stdClass', array_keys( $methods ) );

        foreach ( $methods as $name => $return_value ) {
            $transaction->expects( $this->any() )
                        ->method( $name )
                        ->will( $this->returnValue( $return_value ) );
        }

        return $transaction;
    }

    protected function mock_page_that_returns_transaction( $transaction ) {
        // TODO: can we mock a generic object? I tried with stdClass and was failing.
        $controller = Phake::mock( 'AWPCP_BuyCreditsPage' );

        Phake::when( $controller )->get_transaction()->thenReturn( $transaction );
        Phake::when( $controller )->get_or_create_transaction()->thenReturn( $transaction );

        return $controller;
    }

    protected function mock_payments_api_with_method( $method, $matcher=null, $return_value=AWPCP_TESTS_UNDEFINED ) {
        $payments = $this->getMockBuilder( 'AWPCP_PaymentsAPI' )
                         ->setMethods( array( $method ) )
                         ->setConstructorArgs( array( new AWPCP_Request() ) )
                         ->getMock();

        $matcher = $matcher ? $matcher : $this->once();

        if ( $return_value !== AWPCP_TESTS_UNDEFINED ) {
            $payments->expects( $matcher )
                     ->method( $method )
                     ->will( $this->returnValue( $return_value ) );
        } else {
            $payments->expects( $matcher )
                     ->method( $method );
        }

        return $payments;
    }
}
