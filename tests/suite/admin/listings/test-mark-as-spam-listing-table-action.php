<?php
/**
 * @package AWPCP\Tests\Plugin\Admin\Listings
 */

use Brain\Monkey\Functions;

/**
 * Test for Mark as Spam Listing Table Action.
 */
class AWPCP_MarkAsSPAMListingTableActionTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        $this->spam_submitter = null;
        $this->listings_logic = null;
        $this->wordpress      = null;
    }

    /**
     * @since 4.0.0
     */
    public function test_should_not_show_action_if_akismet_is_not_installed() {
        $action = $this->get_test_subject();

        // Execution.
        $should = $action->should_show_action_for( null );

        // Verification.
        $this->assertFalse( function_exists( 'akismet_init' ) );
        $this->assertFalse( $should );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_MarkAsSPAMListingTableAction(
            $this->spam_submitter,
            $this->listings_logic,
            null,
            $this->wordpress
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_should_not_show_action_if_akismet_api_key_is_not_set() {
        $this->wordpress = Mockery::mock( 'AWPCP_WordPress' );

        Functions\when( 'akismet_init' )->justReturn( null );

        $this->wordpress->shouldReceive( 'get_option' )
            ->with( 'wordpress_api_key' )
            ->andReturn( false );

        $action = $this->get_test_subject();

        // Execution.
        $should = $action->should_show_action_for( null );

        // Verification.
        $this->assertFalse( $should );
    }

    /**
     * @since 4.0.0
     */
    public function test_should_show_action_if_akismet_is_available() {
        $this->wordpress = Mockery::mock( 'AWPCP_WordPress' );

        Functions\when( 'akismet_init' )->justReturn( null );

        $this->wordpress->shouldReceive( 'get_option' )
            ->with( 'wordpress_api_key' )
            ->andReturn( 'something' );

        $action = $this->get_test_subject();

        // Execution.
        $should = $action->should_show_action_for( null );

        // Verification.
        $this->assertTrue( $should );
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
        $query_parms = null;

        $action = $this->get_test_subject();

        Functions\when( 'add_query_arg' )->alias(
            function( $params, $url ) use ( &$query_parms ) {
                $query_parms = $params;
                return $url;
            }
        );

        // Execution.
        $url = $action->get_url( $post, $current_url );

        // Verification.
        $this->assertNotEmpty( $url );
        $this->assertEquals( 'spam', $query_parms['action'] );
        $this->assertEquals( $post->ID, $query_parms['ids'] );
    }

    /**
     * @since 4.0.0
     */
    public function test_process_item_returns_error_if_listing_cant_be_deleted() {
        $this->listings_logic = Mockery::mock( 'AWPCP_ListingsAPI' );

        $this->listings_logic->shouldReceive( 'delete_listing' )->andReturn( false );

        $action = $this->get_test_subject();

        // Execution.
        $result_code = $action->process_item( null );

        // Verification.
        $this->assertEquals( 'error', $result_code );
    }

    /**
     * @since 4.0.0
     */
    public function test_process_item_returns_error_if_submitting_fails() {
        $this->spam_submitter = Mockery::mock( 'AWPCP_SpamSubmitter' );
        $this->listings_logic = Mockery::mock( 'AWPCP_ListingsAPI' );

        $this->listings_logic->shouldReceive( 'delete_listing' )->andReturn( false );

        $this->spam_submitter->shouldReceive( 'submit' )->andReturn( false );

        $action = $this->get_test_subject();

        // Execution.
        $result_code = $action->process_item( null );

        // Verification.
        $this->assertEquals( 'error', $result_code );
    }

    /**
     * @since 4.0.0
     */
    public function test_process_item_returns_success() {
        $this->spam_submitter = Mockery::mock( 'AWPCP_SpamSubmitter' );
        $this->listings_logic = Mockery::mock( 'AWPCP_ListingsAPI' );

        $this->listings_logic->shouldReceive( 'delete_listing' )->andReturn( true );

        $this->spam_submitter->shouldReceive( 'submit' )->andReturn( true );

        $action = $this->get_test_subject();

        // Execution.
        $result_code = $action->process_item( null );

        // Verification.
        $this->assertEquals( 'success', $result_code );
    }

    /**
     * @since 4.0.0
     */
    public function test_get_success_messages() {
        $result_codes = array(
            'success' => 1,
        );

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
            'error' => 1,
        );

        $action = $this->get_test_subject();

        // Execution.
        $messages = $action->get_messages( $result_codes );

        // Verification.
        $this->assertContains( 'notice-error', $messages[0] );
    }
}
