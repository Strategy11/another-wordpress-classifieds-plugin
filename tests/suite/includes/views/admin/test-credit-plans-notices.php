<?php

/**
 * @group core
 */
class AWPCP_TestCreditPlansNotices extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->settings = Phake::mock( 'AWPCP_Settings_API' );
        Phake::when( $this->settings )->get_option( 'enable-credit-system' )->thenReturn( true );

        $this->payments = Phake::mock( 'AWPCP_PaymentsAPI' );
        Phake::when( $this->payments )->get_credit_plans()->thenReturn( array( /* empty */ ) );
    }

    public function test_dispatch() {
        $output = $this->get_rendered_output( new AWPCP_CreditPlansNotices( $this->settings, $this->payments ) );
        $this->assertContains( 'error', $output );
    }

    private function get_rendered_output( $subject ) {
        ob_start();
        $subject->dispatch();
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    public function test_dispatch_renders_nothing_if_credit_plans_are_defined() {
        Phake::when( $this->payments )->get_credit_plans()->thenReturn( array( 'anything here works' ) );

        $output = $this->get_rendered_output( new AWPCP_CreditPlansNotices( $this->settings, $this->payments ) );
        $this->assertEquals( '', $output );
    }

    public function test_dispatch_renders_nothing_if_credit_system_is_not_enabled() {
        Phake::when( $this->settings )->get_option( 'enable-credit-system' )->thenReturn( false );

        $output = $this->get_rendered_output( new AWPCP_CreditPlansNotices( $this->settings, null ) );
        $this->assertEquals( '', $output );
    }
}
