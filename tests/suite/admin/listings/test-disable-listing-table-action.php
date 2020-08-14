<?php
/**
 * @package AWPCP\Tests\Plugin\Admin\Listings
 */

/**
 * Unit tests for Disable Listing Table Action.
 */
class AWPCP_DisableListingTableActionTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function test_process_item_disables_a_listing() {
        $post = (object) array(
            'ID' => wp_rand() + 1,
        );

        $listings_logic   = Mockery::mock( 'AWPCP_ListingsAPI' );
        $listing_renderer = Mockery::mock( 'AWPCP_ListingRenderer' );

        $listing_renderer->shouldReceive( 'is_disabled' )
            ->andReturn( false );

        $listings_logic->shouldReceive( 'disable_listing' )
            ->once()
            ->with( $post )
            ->andReturn( true );

        $action = new AWPCP_DisableListingTableAction( $listings_logic, $listing_renderer, null );

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

        $action = new AWPCP_DisableListingTableAction( null, null, null );

        // Execution.
        $messages = $action->get_messages( $result_codes );

        // Verification.
        $this->assertContains( 'notice-success', $messages[0] );
        $this->assertContains( 'is-dismissible', $messages[0] );
    }
}

