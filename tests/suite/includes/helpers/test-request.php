<?php
/**
 * Unit tests for Request class.
 *
 * @package AWPCP\Tests\Helpers
 */

use Brain\Monkey\Functions;

/**
 * @group core
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class AWPCP_TestRequest extends AWPCP_UnitTestCase {

    public function test_method() {
        $_SERVER['REQUEST_METHOD'] = 'FOO';

        $request = new AWPCP_Request();
        $this->assertEquals( 'FOO', $request->method() );
    }

    /**
     * @dataProvider test_domain_data_provider
     */
    public function test_domain( $http_host, $server_name, $include_www, $replace_prefix, $result ) {
        $_SERVER['SERVER_NAME'] = $server_name;

        $this->redefine_filter_input(
            [
                'HTTP_HOST'   => $http_host,
                'SERVER_NAME' => $server_name,
            ]
        );

        $request = new AWPCP_Request();

        $this->assertEquals( $result, $request->domain( $include_www, $replace_prefix ) );
    }

    /**
     * Use Patchwork to redefine AWPCP_Request::filter_input().
     *
     * @uses AWPCP_UnitTestCase::redefine()
     */
    private function redefine_filter_input( $variables ) {
        $this->redefine(
            'AWPCP_Request::filter_input',
            function( ...$args ) use ( $variables ) {
                if ( isset( $variables[ $args[0] ] ) ) {
                    return $variables[ $args[0] ];
                }

                return null;
            }
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_domain_data_provider() {
        return [
            [ 'bar.com', 'foo.com', true, null, 'bar.com' ],
            [ '', 'www.google.com', true, null, 'www.google.com' ],
            [ '', 'www.google.com', false, null, 'google.com' ],
            [ '', 'www.google.com', false, '.', '.google.com' ],
            [ 'www.example.com', '', true, null, 'www.example.com' ],
            [ 'www.example.com', '', false, null, 'example.com' ],
            [ 'www.example.com', '', false, '.', '.example.com' ],
            [ 'other.example.com', '', true, null, 'other.example.com' ],
            [ 'other.example.com', '', false, null, 'other.example.com' ],
            [ 'other.example.com', '', false, '.', 'other.example.com' ],
            [ 'example.com:8080', 'example.com', true, null, 'example.com' ],
        ];
    }

    public function test_domain_if_http_host_is_not_available() {
        $_SERVER['SERVER_NAME'] = 'foo.com';
        unset( $_SERVER['HTTP_HOST'] );

        $request = new AWPCP_Request();

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
        $this->assertEquals( $_SERVER['SERVER_NAME'], $request->domain() );
    }

    public function test_domain_replacing_www_prefix() {
        $this->redefine_filter_input(
            [
                'HTTP_HOST' => 'www.example.com',
            ]
        );

        $request = new AWPCP_Request();

        $this->assertEquals( 'foo.example.com', $request->domain( false, 'foo.' ) );
    }

    public function test_param() {
        $_REQUEST['foo'] = 'bar';

        $request = new AWPCP_Request();
        $this->assertEquals( 'bar', $request->param( 'foo' ) );
    }

    public function test_get() {
        $_GET['foo'] = 'bar';

        $request = new AWPCP_Request();
        $this->assertEquals( 'bar', $request->get( 'foo' ) );
    }

    public function test_post() {
        $_POST['foo'] = 'bar';

        $request = new AWPCP_Request();
        $this->assertEquals( 'bar', $request->post( 'foo' ) );
    }

    /**
     * @since 4.0.2
     *
     * @dataProvider get_query_var_data_provider
     */
    public function test_get_query_var( $name, $real_value, $expected_value, $default = '' ) {
        Functions\expect( 'get_query_var' )->with( $name )->andReturn( $real_value );

        $returned_value = $this->get_test_subject()->get_query_var( $name, $default );

        // Verification.
        $this->assertSame( $expected_value, $returned_value );
    }

    /**
     * @since 4.0.2
     */
    public function get_query_var_data_provider() {
        $random_default = wp_rand();

        return [
            [
                'name'           => 'post_type',
                'real_value'     => 'awpcp_listing',
                'expected_value' => 'awpcp_listing',
            ],
            [
                'name'           => 'post_type',
                'real_value'     => '',
                'expected_value' => $random_default,
                'default'        => $random_default,
            ],

            /*
             * Make sure it can handle an array of post types.
             *
             * See https://github.com/drodenbaugh/awpcp/issues/2531.
             */
            [
                'name'           => 'post_type',
                'real_value'     => [ 'post', 'tribe_events' ],
                'expected_value' => [ 'post', 'tribe_events' ],
            ],
            [
                'name'           => 'post_type',
                'real_value'     => [],
                'expected_value' => [],
            ],
        ];
    }

    /**
     * @since 4.0.2
     */
    private function get_test_subject() {
        return new AWPCP_Request();
    }

    public function test_get_category_id_from_request() {
        $_REQUEST['category_id'] = '13/general/';

        $request = new AWPCP_Request();

        $this->assertEquals( 13, $request->get_category_id() );
    }

    public function test_get_category_id_from_query_var() {
        Functions\expect( 'get_query_var' )->with( 'cid' )->andReturn( 13 );

        $request = new AWPCP_Request();

        $this->assertEquals( 13, $request->get_category_id() );
    }

    public function test_get_current_listing_from_request() {
        Functions\when( 'get_query_var' )->justReturn( false );

        $request = new AWPCP_Request();

        // from adid parameter.
        $_REQUEST = array( 'adid' => '13' );

        $this->assertTrue( is_int( $request->get_current_listing_id() ) );
        $this->assertEquals( 13, $request->get_current_listing_id() );

        // from id parameter.
        $_REQUEST = array( 'id' => '21' );

        $this->assertTrue( is_int( $request->get_current_listing_id() ) );
        $this->assertEquals( 21, $request->get_current_listing_id() );
    }

    /**
     * @since 4.0.2 Modified to use a Data Provider.
     *
     * @dataProvider get_current_listing_from_query_var_data_provider
     */
    public function test_get_current_listing_from_query_var( $listing_id, $query_vars ) {
        foreach ( $query_vars as $name => $value ) {
            Functions\expect( 'get_query_var' )->with( $name )->andReturn( $value );
        }

        $_REQUEST = [];

        $request = new AWPCP_Request();

        $this->assertSame( $listing_id, $request->get_current_listing_id() );
    }

    /**
     * @since 4.0.2
     */
    public function get_current_listing_from_query_var_data_provider() {
        return [
            [
                'listing_id' => 15,
                'query_vars' => [
                    'id' => 15,
                ],
            ],
            [
                'listing_id' => 15,
                'query_vars' => [
                    'post_type' => 'awpcp_listing',
                    'p'         => 15,
                ],
            ],
            // It uses the p query var when the query retrieves listings only.
            [
                'listing_id' => 0,
                'query_vars' => [
                    'post_type' => [ 'post', 'awpcp_listing' ],
                    'p'         => 15,
                ],
            ],
        ];
    }

    public function test_get_current() {
        $this->login_as_administrator();

        $request      = new AWPCP_Request();
        $current_user = $request->get_current_user();

        $this->assertObjectHasAttribute( 'ID', $current_user );
        $this->assertGreaterThan( 0, $current_user->ID );
    }

    public function test_get_current_user_if_there_is_no_user() {
        $this->logout();

        $request      = new AWPCP_Request();
        $current_user = $request->get_current_user();

        $this->assertObjectHasAttribute( 'ID', $current_user );
        $this->assertEquals( 0, $current_user->ID );
    }
}
