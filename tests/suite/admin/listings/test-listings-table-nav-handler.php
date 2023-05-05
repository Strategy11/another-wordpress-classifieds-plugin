<?php
/**
 * @package AWPCP\Tests\Plugin\Admin\Listings
 */

use Brain\Monkey\Functions;

/**
 * Unit tests for Listings Table Nav Handler.
 */
class AWPCP_ListingsTableNavHandlerTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setUp(): void {
        parent::setUp();
        $this->html_renderer = Mockery::mock( 'AWPCP_HTMLRenderer' );
        $this->request       = Mockery::mock( 'AWPCP_Request' );
    }

    /**
     * @since 4.0.0
     */
    public function test_pre_get_posts_with_category_filter() {
        $query = Mockery::mock( 'WP_Query' );
        $html_renderer = Mockery::mock( 'AWPCP_HTMLRenderer' );

        $query->query_vars = [];

        $query->shouldReceive( 'is_main_query' )->andReturn( true );


        Functions\expect( 'awpcp_get_var' )->with(  array( 'param' => 'awpcp_category_id', 'sanitize' => 'absint' ) )
                                           ->andReturn( '2' );

        Functions\when( 'sanitize_key' )->returnArg();

        $html_renderer->shouldReceive( 'get_selected_category' )->andReturn( 2 );
        // Execution.
        $this->get_test_subject()->pre_get_posts( $query );

        // Verification.
        $this->assertEquals( 2, $query->query_vars['classifieds_query']['category'] );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_ListingsTableNavHandler(
            $this->html_renderer
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_pre_get_posts_affects_main_query_only() {
        $query = Mockery::mock( 'WP_Query' );

        $query->query_vars = [];

        $query->shouldReceive( 'is_main_query' )->andReturn( false );

        // Execution.
        $this->get_test_subject()->pre_get_posts( $query );

        // Verification.
        $this->assertFalse( isset( $query->query_vars['classifieds_query']['category'] ) );
    }
}
