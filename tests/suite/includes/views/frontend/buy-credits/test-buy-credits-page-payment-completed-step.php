<?php

/**
 * @group core
 */
class AWPCP_TestBuyCreditsPagePaymentCompletedStep extends AWPCP_UnitTestCase {

    public function test_get_renders_the_view() {
        $request = new AWPCP_Request();
        $payments = new AWPCP_PaymentsAPI();

        $controller = $this->getMockBuilder( 'AWPCP_BuyCreditsPage' )
                           ->setMethods( array( 'render', 'skip_next_step' ) )
                           ->setConstructorArgs( array( array(), $request ) )
                           ->getMock();
        $controller->expects( $this->once() )->method( 'render' );
        $controller->expects( $this->once() )->method( 'skip_next_step' );

        $step = new AWPCP_BuyCreditsPagePaymentCompletedStep( $payments );
        $step->get( $controller );
    }

    public function test_get_renders_payment_completed_page() {
        $this->pause_filter( 'awpcp_menu_items' );

        $request = new AWPCP_Request();

        $payments = $this->getMockBuilder( 'AWPCP_PaymentsAPI' )
                         ->setMethods( array( 'render_payment_completed_page' ) )
                         ->setConstructorArgs( array( $request ) )
                         ->getMock();
        $payments->expects( $this->once() )->method( 'render_payment_completed_page' );

        $controller = new AWPCP_BuyCreditsPage( array(), $request );

        $step = new AWPCP_BuyCreditsPagePaymentCompletedStep( $payments );
        $step->get( $controller );
    }
}
