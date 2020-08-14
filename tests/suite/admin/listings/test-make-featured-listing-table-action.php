<?php
/**
 * @package AWPCP\Tests\Plugin\Admin\Listings
 */

use Brain\Monkey\Functions;

/**
 * Tests for Make Listing Featured table action.
 */
class AWPCP_MakeFeaturedListingTableActionTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        $this->roles_and_capabilities = null;
        $this->listing_renderer       = null;
        $this->wordpress              = null;
    }

    /**
     * @since 4.0.0
     */
    public function test_should_show_action_for_always_returns_false() {
        $post = (object) array();

        $this->roles_and_capabilities = Mockery::mock( 'AWPCP_RolesAndCapabilities' );

        $this->roles_and_capabilities->shouldReceive( 'current_user_is_moderator' )
            ->andReturn( true );

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
        return new AWPCP_MakeFeaturedListingTableAction(
            $this->roles_and_capabilities,
            $this->listing_renderer,
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
            'action' => 'make-featured',
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
            ->with( $post->ID, '_awpcp_is_featured', true )
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
