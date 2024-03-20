<?php
/**
 * @package AWPCP\Tests\Frontend
 */

/**
 * @group core
 */
class AWPCP_ShowListingPageTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setUp(): void {
        parent::setUp();

        $this->listings_content_renderer = Mockery::mock( 'AWPCP_ListingsContentRenderer' );
        $this->listings_logic            = Mockery::mock( 'AWPCP_ListingsAPI' );
        $this->listings_collection       = Mockery::mock( 'AWPCP_ListingsCollection' );
        $this->request                   = Mockery::mock( 'AWPCP_Request' );
    }

    /**
     * @since 4.0.0
     */
    public function test_dispatch() {
        $listing = (object) [
            'ID'           => wp_rand(),
            'post_title'   => '100% Bug Free',
            'post_content' => '100% Original Content',
        ];

        $this->request->shouldReceive( 'get_current_listing_id' )
            ->once()
            ->andReturn( $listing->ID );

        $this->listings_collection->shouldReceive( 'get' )
            ->once()
            ->andReturn( $listing );

        $this->listings_content_renderer->shouldReceive( 'render' )
            ->once()
            ->andReturn( 'some-content' );

        WP_Mock::userFunction( 'get_awpcp_option', [
            'return' => true,
        ] );

        $content = $this->get_test_subject()->dispatch();

        $this->assertNotEmpty( $content );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_Show_Ad_Page(
            $this->listings_content_renderer,
            $this->listings_logic,
            $this->listings_collection,
            $this->request
        );
    }
}
