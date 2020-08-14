<?php

/**
 * @group core
 */
class AWPCP_TestBuyCreditsPageCheckoutStep extends AWPCP_UnitTestCase {

    public function test_get_renders_the_view() {
        $request = new AWPCP_Request();
        $payments = new AWPCP_PaymentsAPI();

        $controller = $this->getMockBuilder( 'AWPCP_BuyCreditsPage' )
                           ->setMethods( array( 'render' ) )
                           ->setConstructorArgs( array( array(), $request ) )
                           ->getMock();
        $controller->expects( $this->once() )->method( 'render' );

        $step = new AWPCP_BuyCreditsPageCheckoutStep( $payments );
        $step->get( $controller );
    }

    public function test_get_renders_checkout_page() {
        $this->pause_filter( 'awpcp_menu_items' );

        $request = new AWPCP_Request();

        $payments = $this->getMockBuilder( 'AWPCP_PaymentsAPI' )
                         ->setMethods( array( 'render_checkout_page' ) )
                         ->setConstructorArgs( array( $request ) )
                         ->getMock();
        $payments->expects( $this->once() )->method( 'render_checkout_page' );

        $controller = new AWPCP_BuyCreditsPage( array(), $request );

        $step = new AWPCP_BuyCreditsPageCheckoutStep( $payments );
        $step->get( $controller );
    }
}
