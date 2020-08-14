<?php
/**
 * @package AWPCP\Tests\Plugin\Admin\Listings
 */

use Brain\Monkey\Functions;

/**
 * Tests for Send to Facebook Group listing admin action.
 */
class AWPCP_SendToFacebookGroupListingTableActionTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        $this->facebook_helper        = null;
        $this->roles_and_capabilities = Mockery::mock( 'AWPCP_RolesAndCapabilities' );
        $this->wordpress              = null;
    }

    /**
     * @since 4.0.0
     */
    public function test_should_show_action_for() {
        $post = (object) [];

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
        return new AWPCP_SendToFacebookGroupListingTableAction(
            $this->facebook_helper,
            $this->roles_and_capabilities,
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
        $this->assertContains( 'Facebook Group', $label );
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
            'action' => 'send-to-facebook-group',
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

        $this->facebook_helper = Mockery::mock( 'AWPCP_SendToFacebookHelper' );

        $this->facebook_helper->shouldReceive( 'send_listing_to_facebook_group' )
            ->once()
            ->with( $post )
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

        $this->facebook_helper = Mockery::mock( 'AWPCP_SendToFacebookHelper' );

        $this->facebook_helper->shouldReceive( 'send_listing_to_facebook_group' )
            ->once()
            ->with( $post )
            ->andThrow( new AWPCP_Exception() );

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
        $this->assertContains( 'sent to Facebook group', $messages[0] );
    }

    /**
     * @since 4.0.0
     */
    public function test_get_no_group_error_messages() {
        $result_codes = array(
            'no-group' => 1,
        );

        $action = $this->get_test_subject();

        // Execution.
        $messages = $action->get_messages( $result_codes );

        // Verification.
        $this->assertContains( 'notice-error', $messages[0] );
        $this->assertContains( 'group selected', $messages[0] );
    }

    /**
     * @since 4.0.0
     */
    public function test_get_disabled_error_messages() {
        $result_codes = array(
            'disabled' => 1,
        );

        $action = $this->get_test_subject();

        // Execution.
        $messages = $action->get_messages( $result_codes );

        // Verification.
        $this->assertContains( 'notice-error', $messages[0] );
        $this->assertContains( 'disabled', $messages[0] );
    }

    /**
     * @since 4.0.0
     */
    public function test_get_already_sent_error_messages() {
        $result_codes = array(
            'already-sent' => 1,
        );

        $action = $this->get_test_subject();

        // Execution.
        $messages = $action->get_messages( $result_codes );

        // Verification.
        $this->assertContains( 'notice-error', $messages[0] );
        $this->assertContains( 'already sent', $messages[0] );
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
        $this->assertContains( 'to the Facebook group.', $messages[0] );
    }
}
