<?php
/**
 * @package AWPCP\Tests\Plugin\Admin\Listings
 */

use Brain\Monkey\Functions;

/**
 * Tests for Make Listing Standard table action.
 */
class AWPCP_MakeStandardListingTableActionTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setUp(): void {
        $this->listing_renderer = null;
        $this->wordpress        = null;
    }

    /**
     * @since 4.0.0
     */
    public function test_should_show_action_for() {
        $post = (object) array();

        $action = $this->get_test_subject();

        // Execution.
        $should = $action->should_show_action_for( $post );

        // Verification.
        $this->assertFalse( $should );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_MakeStandardListingTableAction(
            $this->listing_renderer,
            null,
            $this->wordpress
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
        $this->assertStringContains( 'Standard', $label );
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
            'action' => 'make-standard',
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

        $this->wordpress = Mockery::mock( 'AWPCP_WordPress' );

        $this->wordpress->shouldReceive( 'update_post_meta' )
            ->once()
            ->with( $post->ID, '_awpcp_is_featured', 0 )
            ->andReturn( true );

        $action = $this->get_test_subject();

        // Execution.
        $result_code = $action->process_item( $post );

        // Verification.
        $this->assertEquals( 'success', $result_code );
    }

    /**
     * @since 4.0.0
     */
    public function test_process_item_returns_error() {
        $post = (object) array(
            'ID' => wp_rand() + 1,
        );

        $this->wordpress = Mockery::mock( 'AWPCP_WordPress' );

        $this->wordpress->shouldReceive( 'update_post_meta' )->andReturn( false );

        $action = $this->get_test_subject();

        // Execution.
        $result_code = $action->process_item( $post );

        // Verification.
        $this->assertEquals( 'error', $result_code );
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
        $this->assertStringContains( 'notice-success', $messages[0] );
        $this->assertStringContains( 'standard', $messages[0] );
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
        $this->assertStringContains( 'notice-error', $messages[0] );
        $this->assertStringContains( 'standard', $messages[0] );
    }
}

