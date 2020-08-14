<?php
/**
 * @package AWPCP\Tests\Plugin\Admin
 */

/**
 * Unit tests for List Table Views Handler class.
 */
class AWPCP_ListTableViewsHandlerTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        $this->views   = array();
        $this->request = Mockery::mock( 'AWPCP_Request' );
    }

    /**
     * @since 4.0.0
     */
    public function test_pre_get_posts() {
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_ListTableViewsHandler(
            $this->views,
            $this->request
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_pre_get_posts_does_nothing_if_query_is_not_main_query() {
        $query = Mockery::mock( 'WP_Query' );

        $query->shouldReceive( 'is_main_query' )->andReturn( false );

        $this->request->shouldReceive( 'param' )->never();

        $handler = $this->get_test_subject();

        // Execution.
        $handler->pre_get_posts( $query );
    }

    /**
     * @since 4.0.0
     */
    public function test_views_register_given_views() {
        $view_handler = Mockery::mock( 'AWPCP_ListTableView' );

        $this->views = array(
            'custom_view' => $view_handler,
        );

        $this->request->shouldReceive( 'param' )
            ->with( 'awpcp_filter', false )
            ->andReturn( 'custom_view' );

        $this->request->shouldReceive( 'param' )
            ->with( 'post_type' )
            ->andReturn( 'post-type' );

        $view_handler->shouldReceive( [
            'get_label' => 'Custom View',
            'get_url'   => 'the-url',
            'get_count' => 8,
        ] );

        $handler = $this->get_test_subject();

        // Execution.
        $filtered_views = $handler->views( array() );

        // Verification.
        $this->assertArrayHasKey( 'custom_view', $filtered_views );
        $this->assertContains( 'current', $filtered_views['custom_view'] );
        $this->assertContains( 'Custom View <', $filtered_views['custom_view'] );
        $this->assertContains( 'the-url', $filtered_views['custom_view'] );
        $this->assertContains( 'count', $filtered_views['custom_view'] );
        $this->assertContains( '(8)', $filtered_views['custom_view'] );
    }

    /**
     * @since 4.0.0
     */
    public function test_views_with_no_listings_are_not_registered() {
        $view_handler = Mockery::mock( 'AWPCP_ListTableView' );

        $this->views = [
            'custom-view' => $view_handler,
        ];

        $this->request->shouldReceive( 'param' );

        $view_handler->shouldReceive( 'get_count' )->andReturn( 0 );

        // Execution and Verification.
        $this->assertEmpty( $this->get_test_subject()->views( [] ) );
    }
}
