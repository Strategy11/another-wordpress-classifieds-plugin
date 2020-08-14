<?php
/**
 * @package AWPCP\Tests\Plugin\Admin\Listings
 */

/**
 * Unit tests for Enable Listing Table Action.
 */
class AWPCP_EnableListingTableActionTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        parent::setup();

        $this->listings_logic   = Mockery::mock( 'AWPCP_Listings_API' );
        $this->listing_renderer = Mockery::mock( 'AWPCP_ListingRenderer' );
    }

    /**
     * @since 4.0.0
     */
    public function test_should_show_action_for_returns_false_for_expired_listings() {
        $post = (object) [];

        $this->listing_renderer->shouldReceive( 'is_disabled' )->andReturn( false );
        $this->listing_renderer->shouldReceive( 'is_pending_approval' )->andReturn( false );

        $this->assertFalse( $this->get_test_subject()->should_show_action_for( $post ) );
    }

    /**
     * @since 4.0.0
     */
    public function get_test_subject() {
        return new AWPCP_EnableListingTableAction(
            $this->listings_logic,
            $this->listing_renderer,
            null
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_process_item() {
        $post = (object) array(
            'ID' => wp_rand() + 1,
        );

        $this->listing_renderer->shouldReceive( 'is_public' )->andReturn( false );

        $this->listings_logic->shouldReceive( 'enable_listing' )
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
    public function test_get_success_messages() {
        $result_codes = array(
            'success' => 1,
        );

        $action = $this->get_test_subject();

        // Execution.
        $messages = $action->get_messages( $result_codes );

        // Verification.
        $this->assertContains( 'notice-success', $messages[0] );
        $this->assertContains( 'is-dismissible', $messages[0] );
    }
}
