<?php
/**
 * @package AWPCP\Tests\Functions
 */

use Brain\Monkey\Functions;

class AWPCP_Test_Listings_Functions extends AWPCP_UnitTestCase {

    public function test_display_listings() {
        $this->markTestSkipped();

        $content = awpcp_display_listings( array(), null, array() );

        $this->assertContains( 'There were no listings found.', $content );
    }

    /**
     * @dataProvider render_classifieds_bar_data_provider
     * @since 4.0.0
     */
    public function test_render_classifieds_bar( $is_admin, $expected_output ) {
        $classifieds_bar = Mockery::mock( 'AWPCP_Classifieds_Bar' );

        $classifieds_bar->shouldReceive( 'render' )->andReturn( 'classifieds-bar' );

        Functions\when( 'awpcp_classifieds_bar' )->justReturn( $classifieds_bar );
        Functions\when( 'is_admin' )->justReturn( $is_admin );

        // Execution.
        $output = awpcp_render_classifieds_bar();

        // Verification.
        $this->assertEquals( $expected_output, $output );
    }

    /**
     * @since 4.0.0
     */
    public function render_classifieds_bar_data_provider() {
        return [
            [ true, '' ],
            [ false, 'classifieds-bar' ],
        ];
    }

    /**
     * @since 4.0.0
     *
     * @dataProvider get_results_per_page_data_provider
     */
    public function test_get_results_per_page( $results_per_page, $query_vars, $query_parameter = null, $default = 10 ) {
        if ( is_null( $query_parameter ) ) {
            $query_parameter = $default;
        }

        Functions\when( 'awpcp_request_param' )->justReturn( $query_parameter );
        Functions\when( 'get_awpcp_option' )->justReturn( $default );

        $this->assertEquals( $results_per_page, awpcp_get_results_per_page( $query_vars ) );
    }

    public function get_results_per_page_data_provider() {
        $default = wp_rand();

        return [
            // It should ignore zero or null values.
            [
                $default,
                [
                    'results' => 0,
                ],
                null,
                $default,
            ],
            [
                $default,
                [
                    'limit' => 0,
                ],
                null,
                $default,
            ],
            [
                $default,
                [
                    'posts_per_page' => 0,
                ],
                null,
                $default,
            ],
        ];
    }
}
