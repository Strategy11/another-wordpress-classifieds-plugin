<?php

/**
 * @group core
 */
class AWPCP_TestBuyCreditsPageFinalStep extends AWPCP_UnitTestCase {

    public function test_get_and_post_renders_the_view() {
        $request = new AWPCP_Request();
        $payments = new AWPCP_PaymentsAPI();

        $controller = $this->getMockBuilder( 'AWPCP_BuyCreditsPage' )
                           ->setMethods( array( 'render', 'skip_next_step' ) )
                           ->setConstructorArgs( array( array(), $request ) )
                           ->getMock();
        $controller->expects( $this->exactly( 2 ) )->method( 'render' );
        $controller->expects( $this->exactly( 2 ) )->method( 'skip_next_step' );

        $step = new AWPCP_BuyCreditsPageFinalStep( $payments );

        $step->get( $controller );
        $step->post( $controller );
    }

    public function test_get_and_post_renders_payment_completed_page() {
        $this->pause_filter( 'awpcp_menu_items' );

        $request = new AWPCP_Request();

        $payments = $this->getMockBuilder( 'AWPCP_PaymentsAPI' )
                         ->setMethods( array( 'render_account_balance' ) )
                         ->setConstructorArgs( array( $request ) )
                         ->getMock();
        $payments->expects( $this->exactly( 2 ) )->method( 'render_account_balance' );

        $controller = new AWPCP_BuyCreditsPage( array(), $request );

        $step = new AWPCP_BuyCreditsPageFinalStep( $payments );

        $step->get( $controller );
        $step->post( $controller );
    }
}
