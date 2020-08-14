<?php
/**
 * @package AWPCP\Tests\Plugin\Admin\Listings
 */

use Brain\Monkey\Functions;

/**
 * Tests for Unflag Listing Table Action.
 */
class AWPCP_UnflagListingTableActionTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        parent::setup();

        $this->listings_logic   = null;
        $this->listing_renderer = null;
    }

    /**
     * @since 4.0.0
     */
    public function test_should_show_action_for() {
        $this->listing_renderer = Mockery::mock( 'AWPCP_ListingRenderer' );

        $this->listing_renderer->shouldReceive( 'is_flagged' )->andReturn( true );

        $action = $this->get_test_subject();

        // Execution.
        $should = $action->should_show_action_for( null );

        $this->assertTrue( $should );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_UnflagListingTableAction(
            $this->listings_logic,
            $this->listing_renderer,
            null
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_should_show_action_for_returns_false_for_not_flagged_listings() {
        $this->listing_renderer = Mockery::mock( 'AWPCP_ListingRenderer' );

        $this->listing_renderer->shouldReceive( 'is_flagged' )->andReturn( false );

        $action = $this->get_test_subject();

        // Execution.
        $should = $action->should_show_action_for( null );

        $this->assertFalse( $should );
    }

    /**
     * @since 4.0.0
     */
    public function test_get_label() {
        $action = $this->get_test_subject();

        // Execution/Verification.
        $this->assertNotEmpty( $action->get_label( null ) );
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
            'action' => 'unflag',
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

        $this->listings_logic = Mockery::mock( 'AWPCP_ListingsAPI' );

        $this->listings_logic->shouldReceive( 'unflag_listing' )
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
    public function test_get_messages() {
        $result_codes = array(
            'success' => 1,
        );

        $action = $this->get_test_subject();

        // Execution.
        $messages = $action->get_messages( $result_codes );

        // Verification.
        $this->assertContains( 'notice-success', $messages[0] );
    }
}
