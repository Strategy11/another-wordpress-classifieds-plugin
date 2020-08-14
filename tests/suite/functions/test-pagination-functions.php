<?php
/**
 * @package AWPCP\Test\Functions
 */

use Brain\Monkey\Functions;

/**
 * Unit tests for functions involved in generating pagination controls.
 */
class AWPCP_Test_Pagination_Functions extends AWPCP_UnitTestCase {

    /**
     * @phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification
     * @phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
     * @phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated
     * @phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
     */
    public function test_pagination_forms_keep_page_id_url_parameter() {
        $_GET['page_id'] = wp_rand();

        Functions\when( 'is_admin' )->justReturn( false );
        Functions\when( 'get_awpcp_option' )->justReturn( 0 );
        Functions\when( 'awpcp_pagination_options' )->justReturn( [ 10, 20 ] );

        $pagination_form = awpcp_pagination( array(), '' );

        $this->assertContains( 'name="page_id" value="' . $_GET['page_id'] . '"', $pagination_form );
    }

    /**
     * @since 4.0.0
     */
    public function test_get_results_per_page() {
        $query_vars = [
            'posts_per_page' => 1,
            'limit'          => 2,
            'results'        => 3,
        ];

        Functions\when( 'awpcp_request_param' )->justReturn( 4 );
        Functions\when( 'get_awpcp_option' )->justReturn( 5 );

        $this->assertEquals( 1, awpcp_get_results_per_page( $query_vars ) );

        unset( $query_vars['posts_per_page'] );

        $this->assertEquals( 2, awpcp_get_results_per_page( $query_vars ) );

        unset( $query_vars['limit'] );

        $this->assertEquals( 3, awpcp_get_results_per_page( $query_vars ) );

        unset( $query_vars['results'] );

        $this->assertEquals( 4, awpcp_get_results_per_page( $query_vars ) );

        Functions\when( 'awpcp_request_param' )->returnArg( 2 );

        $this->assertEquals( 5, awpcp_get_results_per_page( $query_vars ) );

        Functions\when( 'get_awpcp_option' )->returnArg( 2 );

        $this->assertEquals( 10, awpcp_get_results_per_page( $query_vars ) );
    }

    /**
     * @since 4.0.0
     */
    public function test_get_results_offset() {
        $results_per_page = 5;

        $query_vars = [
            'paged'  => 2,
            'offset' => 1,
        ];

        Functions\expect( 'awpcp_request_param' )
            ->with( 'offset', 15 )
            ->andReturn( 15 );

        Functions\expect( 'awpcp_request_param' )
            ->with( 'offset', 20 )
            ->andReturn( 20 );

        Functions\expect( 'awpcp_request_param' )
            ->with( 'offset', Mockery::any() )
            ->andReturn( 3 );

        Functions\expect( 'get_query_var' )
            ->with( 'page' )
            ->andReturn( 5 );

        Functions\expect( 'get_query_var' )
            ->with( 'paged' )
            ->andReturnValues( [ wp_rand(), wp_rand(), wp_rand(), 4, 0 ] );

        $this->assertEquals( 1, awpcp_get_results_offset( $results_per_page, $query_vars ) );

        unset( $query_vars['offset'] );

        // It should use the value from $query_vars['paged']. If we are showing the
        // second page of 5 results, then the offset must be 5.
        $this->assertEquals( 5, awpcp_get_results_offset( $results_per_page, $query_vars ) );

        unset( $query_vars['paged'] );

        // It should use the value from 'offset' request parameter.
        $this->assertEquals( 3, awpcp_get_results_offset( $results_per_page, $query_vars ) );

        // It should use the value from paged query var. If we are showing the
        // fourth page of 5 results, then the offset must be 15.
        $this->assertEquals( 15, awpcp_get_results_offset( $results_per_page, $query_vars ) );

        // It should use the value from page query var. If we are showing the
        // fifth page of 5 results, then the offset must be 20.
        $this->assertEquals( 20, awpcp_get_results_offset( $results_per_page, $query_vars ) );
    }
}
