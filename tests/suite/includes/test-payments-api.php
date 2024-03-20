<?php
/**
 * @package AWPCP\Tests
 */

/**
 * @group core
 */
class AWPCP_TestPaymentsAPI extends AWPCP_UnitTestCase {

    public function test_render_account_balance() {
        $user = (object) [
            'ID' => wp_rand(),
        ];

        WP_Mock::userFunction( 'get_awpcp_option', [
            'args'   => 'freepay',
            'return' => 1,
        ] );

        WP_Mock::userFunction( 'get_awpcp_option', [
            'args'   => 'enable-credit-system',
            'return' => 1,
        ] );

        WP_Mock::userFunction( 'get_user_meta', [
            'args'   => [ $user->ID, 'awpcp-account-balance', true ],
            'return' => 45000,
        ] );

        WP_Mock::userFunction( 'is_admin', [
            'return' => false,
        ] );
        WP_Mock::userFunction( 'is_user_logged_in', [
            'return' => true,
        ] );
        WP_Mock::userFunction( 'wp_get_current_user', [
            'return' => $user,
        ] );

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

        WP_Mock::userFunction( 'is_admin', [
            'return' => false,
        ] );

        WP_Mock::userFunction( 'get_user_meta', [
            'args'   => [ $transaction->user_id, 'awpcp-account-balance', true ],
            'return' => 45000,
        ] );

        $this->redefine( 'AWPCP_PaymentsAPI::get_credit_plan', \Patchwork\always( $credit_plan ) );

        $api = $this->get_test_subject();

        // Execution.
        $output = $api->render_account_balance_for_transaction( $transaction );

        // Verification.
        $this->assertContains( '15,000', $output );
    }
}
