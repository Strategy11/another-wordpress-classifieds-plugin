<?php
/**
 * @package AWPCP\Tests\Plugin\Frontend
 */

// phpcs:disable Squiz.Commenting.FunctionComment.Missing

use Brain\Monkey\Functions;

/**
 * Unit tests for Listing Form Steps Component class.
 */
class AWPCP_SubmitListingFormStepsTest extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->payments = Phake::mock( 'AWPCP_PaymentsAPI' );
        $this->settings = Phake::mock( 'AWPCP_Settings_API' );
        $this->request  = Phake::mock( 'AWPCP_Request' );

        Phake::when( $this->payments )->get_payment_terms->thenReturn( array( 'foo' => array() ) );
    }

    public function test_login_step_is_shown_when_user_is_not_logged_in() {
        $this->logout();

        Phake::when( $this->settings )->get_option( 'requireuserregistration' )->thenReturn( true );

        $output = $this->get_test_subject()->get_steps( [ 'transaction' => null ] );

        $this->assertContains( 'Login/Registration', $output );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_SubmitListingFormSteps(
            $this->payments,
            $this->settings,
            $this->request
        );
    }

    public function test_login_step_is_not_shown_if_registration_is_not_required() {
        $this->logout();

        Phake::when( $this->settings )->get_option( 'requireuserregistration' )->thenReturn( false );

        $output = $this->get_test_subject()->get_steps( [ 'transaction' => null ] );

        $this->assertNotContains( 'Login/Registration', $output );
    }

    public function test_login_step_is_not_shown_when_user_is_logged_in() {
        $this->login_as_subscriber();

        $output = $this->get_test_subject()->get_steps( [ 'transaction' => null ] );

        $this->assertNotContains( 'Login/Registration', $output );
    }

    public function test_login_step_is_shown_when_user_just_logged_in() {
        $this->login_as_subscriber();

        Phake::when( $this->request )->param( 'loggedin', Phake::ignoreRemaining() )->thenReturn( true );

        $output = $this->get_test_subject()->get_steps( [ 'transaction' => null ] );

        $this->assertContains( 'Login/Registration', $output );
    }

    public function test_login_step_is_shown_when_user_logged_in_during_the_post_listing_workflow() {
        $this->login_as_subscriber();

        $transaction = Phake::mock( 'AWPCP_Payment_Transaction' );

        Phake::when( $transaction )->get( 'user-just-logged-in', Phake::ignoreRemaining() )->thenReturn( true );
        Phake::when( $this->payments )->get_transaction_payment_term( $transaction )->thenReturn( null );

        $output = $this->get_test_subject()->get_steps( [ 'transaction' => $transaction ] );

        $this->assertContains( 'Login/Registration', $output );
    }

    public function test_payment_steps_are_not_shown_if_current_user_is_administrator() {
        $this->login_as_administrator();

        Phake::when( $this->payments )->payments_enabled->thenReturn( true );

        $output = $this->get_test_subject()->get_steps( [ 'transaction' => null ] );

        $this->assertNotContains( '>Checkout', $output );
        $this->assertNotContains( '>Payment', $output );
    }

    public function test_upload_files_step_is_not_shown_if_no_payment_term_is_found() {
        $this->logout();

        $transaction = Phake::mock( 'AWPCP_Payment_Transaction' );

        Phake::when( $this->payments )->get_transaction_payment_term->thenReturn( null );

        $output = $this->get_test_subject()->get_steps( [ 'transaction' => $transaction ] );

        $this->assertNotContains( '>Upload Files', $output );
    }
}
