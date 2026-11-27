<?php
/**
 * @package AWPCP\Tests\Plugin\Payments\Gateways
 */

use Brain\Monkey\Functions;

/**
 * Tests for binding 2Checkout orders to the transaction they paid for.
 */
class AWPCP_2CheckoutPaymentGatewayTest extends AWPCP_UnitTestCase {

    /**
     * @var mixed
     */
    private $previous_wpdb;

    /**
     * @since x.x
     */
    public function setUp(): void {
        parent::setUp();

        $this->previous_wpdb = isset( $GLOBALS['wpdb'] ) ? $GLOBALS['wpdb'] : null;
    }

    /**
     * @since x.x
     */
    public function tearDown(): void {
        $GLOBALS['wpdb'] = $this->previous_wpdb;

        parent::tearDown();
    }

    /**
     * @since x.x
     */
    public function test_first_transaction_claims_order_number() {
        $this->mock_wpdb( 1, null );

        $this->assertTrue( $this->claim_order_number( '4093', 'transaction-a' ) );
    }

    /**
     * @since x.x
     */
    public function test_reloading_the_return_keeps_the_order_for_the_same_transaction() {
        $this->mock_wpdb( 0, 'transaction-a' );

        $this->assertTrue( $this->claim_order_number( '4093', 'transaction-a' ) );
    }

    /**
     * @since x.x
     */
    public function test_order_claimed_by_another_transaction_is_rejected() {
        $this->mock_wpdb( 0, 'transaction-a' );

        $this->assertFalse( $this->claim_order_number( '4093', 'transaction-b' ) );
    }

    /**
     * @since x.x
     */
    public function test_database_error_rejects_the_claim() {
        $this->mock_wpdb( false, null );

        $this->assertFalse( $this->claim_order_number( '4093', 'transaction-a' ) );
    }

    /**
     * @since x.x
     */
    public function test_empty_order_number_is_rejected_in_live_mode() {
        Functions\when( 'get_awpcp_option' )->justReturn( 0 );

        $this->assertFalse( $this->claim_order_number( '', 'transaction-a' ) );
    }

    /**
     * @since x.x
     *
     * @param int|false   $insert_result Rows inserted by INSERT IGNORE, or false on error.
     * @param string|null $owner         Transaction ID stored for the order number.
     */
    private function mock_wpdb( $insert_result, $owner ) {
        $wpdb          = Mockery::mock( 'wpdb' );
        $wpdb->options = 'wp_options';

        $wpdb->shouldReceive( 'prepare' )->andReturnUsing(
            function ( $query ) {
                return $query;
            }
        );
        $wpdb->shouldReceive( 'query' )->once()->andReturn( $insert_result );
        $wpdb->shouldReceive( 'get_var' )->andReturn( $owner );

        $GLOBALS['wpdb'] = $wpdb;
    }

    /**
     * @since x.x
     *
     * @param string $order_number   The 2Checkout order number.
     * @param string $transaction_id The transaction ID.
     *
     * @return bool
     */
    private function claim_order_number( $order_number, $transaction_id ) {
        Functions\when( 'awpcp_current_user_is_admin' )->justReturn( false );
        Functions\when( 'maybe_unserialize' )->returnArg();

        $transaction = new AWPCP_Payment_Transaction( array( 'id' => $transaction_id ) );
        $gateway     = Phake::partialMock( 'AWPCP_2CheckoutPaymentGateway' );
        $method      = new ReflectionMethod( $gateway, 'claim_order_number' );

        $method->setAccessible( true );

        return $method->invoke( $gateway, $order_number, $transaction );
    }
}
