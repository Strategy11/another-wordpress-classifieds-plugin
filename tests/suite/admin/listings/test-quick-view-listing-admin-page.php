<?php
/**
 * @package AWPCP\Tests\Plugin\Admin\Listings
 */

use Brain\Monkey\Functions;

/**
 * Unit tests for the QuickViewListingAdminPage class.
 */
class AWPCP_QuickViewListingAdminPageTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function test_dispatch() {
        $post_id = wp_rand() + 1;
        $post    = (object) array(
            'post_content' => '',
        );

        $content_renderer    = Mockery::mock( 'AWPCP_ListingsContentRenderer' );
        $listing_renderer    = Mockery::mock( 'AWPPC_ListingRenderer' );
        $listings_collection = Mockery::mock( 'AWPCP_ListingsCollection' );
        $template_renderer   = Mockery::mock( 'AWPCP_TemplateRenderer' );
        $request             = Mockery::mock( 'AWPCP_Request' );
        $wordpress           = Mockery::mock( 'AWPCP_WordPress' );
        $table_actions       = Mockery::mock( 'AWPCP_ListTableActionsHandler' );

        $template_renderer->shouldReceive( 'render_template' )
            ->andReturn( 'rendered template' );

        $listing_renderer->shouldReceive( 'get_listing_title' )
            ->andReturn( 'Test Listing' );

        $request->shouldReceive( 'param' )
            ->andReturn( $post_id );

        $listings_collection->shouldReceive( 'get' )
            ->andReturn( $post );

        $content_renderer->shouldReceive( 'render_content_with_notices' )
            ->andReturn( 'rendered content' );

        $wordpress->shouldReceive( 'get_edit_post_link' )
            ->andReturn( 'a URL' );

        Functions\expect( 'remove_query_arg' )->once();

        $page = new AWPCP_QuickViewListingAdminPage(
            $content_renderer,
            $listing_renderer,
            $listings_collection,
            $template_renderer,
            $wordpress,
            $request,
            $table_actions
        );

        // Execution.
        $content = $page->dispatch();

        // Verification.
        $this->assertNotEmpty( $content );
    }
}
