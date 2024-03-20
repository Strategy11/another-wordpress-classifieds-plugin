<?php
/**
 * @package AWPCP\Tests\Plugin\Admin\Listings
 */

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


        WP_Mock::userFunction( 'awpcp_get_var', [
            'args'   => [ [ 'param' => 'awpcp_category_id', 'sanitize' => 'absint' ] ],
            'return' => '2',
        ] );

        WP_Mock::userFunction( 'sanitize_key', [
            'return' => function( $arg ) {
                return $arg;
            },
        ] );

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
