<?php

/**
 * @group core
 */
class AWPCP_TestBuyCreditsPageSelectCreditPlanStep extends AWPCP_UnitTestCase {

    public function test_get_renders_the_view() {
        $request = new AWPCP_Request();
        $payments = new AWPCP_PaymentsAPI();

        $controller = $this->getMockBuilder( 'AWPCP_BuyCreditsPage' )
                         ->setMethods( array( 'render', 'skip_next_step' ) )
                         ->setConstructorArgs( array( array(), $request ) )
                         ->getMock();
        $controller->expects( $this->once() )->method( 'render' );
        $controller->expects( $this->once() )->method( 'skip_next_step' );

        $step = new AWPCP_BuyCreditsPageSelectCreditPlanStep( $payments );
        $step->get( $controller );
    }

    public function test_get_renders_credit_plans_table() {
        $this->pause_filter( 'awpcp_menu_items' );

        $request = new AWPCP_Request();

        $payments = $this->getMockBuilder( 'AWPCP_PaymentsAPI' )
                         ->setMethods( array( 'render_account_balance', 'render_credit_plans_table' ) )
                         ->setConstructorArgs( array( $request ) )
                         ->getMock();
        $payments->expects( $this->once() )->method( 'render_account_balance' );
        $payments->expects( $this->once() )->method( 'render_credit_plans_table' );

        $controller = new AWPCP_BuyCreditsPage( array(), $request );

        $step = new AWPCP_BuyCreditsPageSelectCreditPlanStep( $payments );
        $step->get( $controller );
    }
}
