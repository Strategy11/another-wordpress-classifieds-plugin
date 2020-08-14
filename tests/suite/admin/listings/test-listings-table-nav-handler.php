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
    public function setup() {
        parent::setup();

        $this->html_renderer = Mockery::mock( 'AWPCP_HTMLRenderer' );
        $this->request       = Mockery::mock( 'AWPCP_Request' );
    }

    /**
     * @since 4.0.0
     */
    public function test_pre_get_posts_with_category_filter() {
        $query = Mockery::mock( 'WP_Query' );

        $query->query_vars = [];

        $query->shouldReceive( 'is_main_query' )->andReturn( true );

        $this->request->shouldReceive( 'param' )->with( 'awpcp_category_id' )->andReturn( '2' );
        $this->request->shouldReceive( 'param' )->with( 'awpcp_date_filter' )->andReturn( '' );

        Functions\when( 'sanitize_key' )->returnArg();

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
            $this->html_renderer,
            $this->request
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
