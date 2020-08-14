<?php
/**
 * @package AWPCP\Tests\Plugin\Admin\Listings
 */

use Brain\Monkey\Functions;

/**
 * Tests for Renew Listing Table Action class.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class AWPCP_ModeratorRenewListingTableActionTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        parent::setup();

        $this->listings_logic      = null;
        $this->listing_renderer    = null;
        $this->email_notifications = null;
        $this->settings            = null;
    }

    /**
     * @since 4.0.0
     */
    public function test_should_show_action_for_expiring_listing() {
        $post = (object) array(
            'ID' => wp_rand() + 1,
        );

        $this->listing_renderer = Mockery::mock( 'AWPCP_ListingRenderer' );

        $this->listing_renderer->shouldReceive( 'has_expired_or_is_about_to_expire' )
            ->once()
            ->with( $post )
            ->andReturn( true );

        $action = $this->get_test_subject();

        // Execution.
        $should = $action->should_show_action_for( $post );

        // Verification.
        $this->assertTrue( $should );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_ModeratorRenewListingTableAction(
            $this->listings_logic,
            $this->listing_renderer,
            $this->email_notifications,
            null,
            $this->settings
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_get_label() {
        $action = $this->get_test_subject();

        // Execution.
        $label = $action->get_label( null );

        // Verification.
        $this->assertNotEmpty( $label );
    }

    /**
     * @since 4.0.0
     */
    public function test_get_url() {
        $post = (object) array(
            'ID' => wp_rand() + 1,
        );

        $current_url = 'https://example.org';
        $params      = array(
            'action' => 'renew',
            'ids'    => $post->ID,
        );

        Functions\expect( 'add_query_arg' )
            ->once()
            ->with( $params, $current_url )
            ->andReturn( $current_url );

        $action = $this->get_test_subject();

        // Execution.
        $url = $action->get_url( $post, $current_url );

        // Verification.
        $this->assertNotEmpty( $url );
    }

    /**
     * @since 4.0.0
     */
    public function test_process_item() {
        $post = (object) array(
            'ID' => wp_rand() + 1,
        );

        $payment_term = Mockery::mock( 'AWPCP_PaymentTerm' );

        $payment_term->shouldReceive( 'ad_can_be_renewed' )
            ->with( $post )
            ->andReturn( true );

        $this->listing_renderer    = Mockery::mock( 'AWPCP_ListingRenderer' );
        $this->listings_logic      = Mockery::mock( 'AWPCP_ListingsAPI' );
        $this->email_notifications = Mockery::mock( 'AWPCP_ListingRenewedEmailNotifications' );
        $this->settings            = Mockery::mock( 'AWPCP_Settings_API' );

        $this->listing_renderer->shouldReceive(
            [
                'has_expired'        => true,
                'is_about_to_expire' => false,
                'get_payment_term'   => $payment_term,
            ]
        );

        $this->listings_logic->shouldReceive( 'renew_listing' )
            ->once()
            ->with( $post )
            ->andReturn( true );

        $this->email_notifications->shouldReceive( 'send_user_notification' )
            ->once()
            ->with( $post )
            ->andReturn( true );

        $this->email_notifications->shouldReceive( 'send_admin_notification' )
            ->once()
            ->with( $post )
            ->andReturn( true );

        $this->settings->shouldReceive(
            [
                'get_option' => true,
            ]
        );

        $action = $this->get_test_subject();

        // Execution.
        $result_code = $action->process_item( $post );

        // Verification.
        $this->assertEquals( 1, did_action( 'awpcp-renew-ad' ) );
        $this->assertEquals( 'success', $result_code );
    }

    /**
     * @since 4.0.0
     */
    public function test_process_item_returns_not_expired_error() {
        $this->listing_renderer = Mockery::mock( 'AWPCP_ListingRenderer' );

        $this->listing_renderer->shouldReceive(
            [
                'has_expired'        => false,
                'is_about_to_expire' => false,
            ]
        );

        $action = $this->get_test_subject();

        // Execution.
        $result_code = $action->process_item( null );

        // Verification.
        $this->assertEquals( 'not-expired', $result_code );
    }

    /**
     * @since 4.0.0
     */
    public function test_process_item_returns_no_payment_error() {
        $this->listing_renderer = Mockery::mock( 'AWPCP_ListingRenderer' );

        $this->listing_renderer->shouldReceive(
            [
                'has_expired'        => false,
                'is_about_to_expire' => true,
                'get_payment_term'   => null,
            ]
        );

        $action = $this->get_test_subject();

        // Execution.
        $result_code = $action->process_item( null );

        // Verification.
        $this->assertEquals( 'no-payment', $result_code );
    }

    /**
     * @since 4.0.0
     */
    public function test_process_item_returns_error_if_payment_term_does_not_allow_to_renew_listing() {
        $post = (object) array(
            'ID' => wp_rand() + 1,
        );

        $payment_term = Mockery::mock( 'AWPCP_PaymentTerm' );

        $payment_term->shouldReceive( 'ad_can_be_renewed' )
            ->once()
            ->with( $post )
            ->andReturn( false );

        $this->listing_renderer = Mockery::mock( 'AWPCP_ListingRenderer' );

        $this->listing_renderer->shouldReceive(
            [
                'has_expired'        => false,
                'is_about_to_expire' => true,
                'get_payment_term'   => $payment_term,
            ]
        );

        $action = $this->get_test_subject();

        // Execution.
        $result_code = $action->process_item( $post );

        // Verification.
        $this->assertEquals( 'error', $result_code );
    }

    /**
     * @since 4.0.0
     */
    public function test_process_item_returns_error_if_renew_listing_fails() {
        $post = (object) array(
            'ID' => wp_rand() + 1,
        );

        $payment_term = Mockery::mock( 'AWPCP_PaymentTerm' );

        $payment_term->shouldReceive( 'ad_can_be_renewed' )
            ->once()
            ->with( $post )
            ->andReturn( true );

        $this->listing_renderer = Mockery::mock( 'AWPCP_ListingRenderer' );
        $this->listings_logic   = Mockery::mock( 'AWPCP_ListingsAPI' );

        $this->listing_renderer->shouldReceive(
            [
                'has_expired'        => false,
                'is_about_to_expire' => true,
                'get_payment_term'   => $payment_term,
            ]
        );

        $this->listings_logic->shouldReceive( 'renew_listing' )
            ->once()
            ->with( $post )
            ->andReturn( false );

        $action = $this->get_test_subject();

        // Execution.
        $result_code = $action->process_item( $post );

        // Verification.
        $this->assertEquals( 'error', $result_code );
    }

    /**
     * @since 4.0.0
     */
    public function test_get_messages() {
        $result_codes = array( 'success' => 1 );

        $action = $this->get_test_subject();

        // Execution.
        $messages = $action->get_messages( $result_codes );

        // Verification.
        $this->assertContains( 'notice-success', $messages[0] );
    }

    /**
     * @since 4.0.0
     */
    public function test_get_error_messages() {
        $result_codes = array(
            'error'       => 1,
            'not-expired' => 1,
            'no-payment'  => 1,
        );

        $action = $this->get_test_subject();

        // Execution.
        $messages = $action->get_messages( $result_codes );

        // Verification.
        $this->assertEquals( 3, count( $messages ) );
        $this->assertContains( 'notice-error', $messages[1] );
    }
}
