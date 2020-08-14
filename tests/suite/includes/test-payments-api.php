<?php
/**
 * @package AWPCP\Tests
 */

use Brain\Monkey\Functions;

/**
 * @group core
 */
class AWPCP_TestPaymentsAPI extends AWPCP_UnitTestCase {

    public function test_render_account_balance() {
        $user = (object) [
            'ID' => wp_rand(),
        ];

        Functions\expect( 'get_awpcp_option' )
            ->with( 'freepay' )
            ->andReturn( 1 );

        Functions\expect( 'get_awpcp_option' )
            ->with( 'enable-credit-system' )
            ->andReturn( 1 );

        Functions\expect( 'get_user_meta' )
            ->with( $user->ID, 'awpcp-account-balance', true )
            ->andReturn( 45000 );

        Functions\when( 'is_admin' )->justReturn( false );
        Functions\when( 'is_user_logged_in' )->justReturn( true );
        Functions\when( 'wp_get_current_user' )->justReturn( $user );

        $api = $this->get_test_subject();

        // Execution and Verification.
        $this->assertContains( '45,000', $api->render_account_balance() );
    }

    /**
     * @since 4.0.2
     */
    private function get_test_subject() {
        return new AWPCP_PaymentsAPI( null );
    }

    /**
     * @since 4.0.2
     */
    public function test_render_account_balance_for_transaction() {
        $transaction = Mockery::mock( 'AWPCP_Payment_Transaction' );

        $transaction->user_id = wp_rand();

        $credit_plan = (object) [
            'id'      => wp_rand(),
            'credits' => 5000,
        ];

        $transaction->shouldReceive( 'get' )
            ->with( 'credit-plan' )
            ->andReturn( $credit_plan->id );

        $transaction->shouldReceive( 'get_total_credits' )
            ->andReturn( 35000 );

        Functions\when( 'is_admin' )->justReturn( false );

        Functions\expect( 'get_user_meta' )
            ->with( $transaction->user_id, 'awpcp-account-balance', true )
            ->andReturn( 45000 );

        $this->redefine( 'AWPCP_PaymentsAPI::get_credit_plan', \Patchwork\always( $credit_plan ) );

        $api = $this->get_test_subject();

        // Execution.
        $output = $api->render_account_balance_for_transaction( $transaction );

        // Verification.
        $this->assertContains( '15,000', $output );
    }
}
