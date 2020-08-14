<?php
/**
 * @package AWPCP\Tests\Plugin\Admin\Listings
 */

use Brain\Monkey\Functions;

/**
 * Tests for Send Access Key Listing Table Action.
 */
class AWPCP_SendAccessKeyListingTableActionTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        parent::setup();

        $this->email_factory    = null;
        $this->listing_renderer = null;
    }

    /**
     * @since 4.0.0
     */
    public function test_should_show_action_for() {
        $action = $this->get_test_subject();

        // Execution.
        $should = $action->should_show_action_for( null );

        // Verification.
        $this->assertTrue( $should );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_SendAccessKeyListingTableAction(
            $this->email_factory,
            $this->listing_renderer
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
            'ID' => rand() + 1,
        );

        $query_params = null;

        Functions\when( 'add_query_arg' )->alias( function( $params, $url ) use ( &$query_params ) {
            $query_params = $params;
            return $url;
        } );

        $action = $this->get_test_subject();

        // Execution.
        $action->get_url( $post, null );

        // Verification.
        $this->assertEquals( 'send-access-key', $query_params['action'] );
        $this->assertEquals( $post->ID, $query_params['ids'] );
    }

    /**
     * @since 4.0.0
     */
    public function test_process_item() {
        $post = (object) array(
            'ID'         => rand() + 1,
            'post_title' => 'Test Post',
        );

        $email = Mockery::mock( 'AWPCP_Email' );

        $this->email_factory    = Mockery::mock( 'AWPCP_EmailFactory' );
        $this->listing_renderer = Mockery::mock( 'AWPCP_ListingRenderer' );

        $this->listing_renderer->shouldReceive( [
            'get_listing_title' => $post->post_title,
            'get_contact_name'  => 'John Doe',
            'get_contact_email' => 'john@example.org',
            'get_access_key'    => 'access-key',
        ] );

        $this->email_factory->shouldReceive( 'get_email' )->andReturn( $email );

        $email->shouldReceive( [
            'prepare' => 'message content',
            'send'    => true,
        ] );

        Functions\when( 'awpcp_format_recipient_address' )->justReturn( 'formatted address' );
        Functions\when( 'awpcp_get_edit_listing_url_with_access_key' )->justReturn( 'edit url' );

        $action = $this->get_test_subject();

        // Execution.
        $result = $action->process_item( $post );

        // Verification.
        $this->assertEquals( 'success', $result );
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
