<?php
/**
 * @package AWPCP\Tests
 */

use WP_Mock\Tools\TestCase;

use function Patchwork\redefine;

/**
 * Base class for all plugin tests.
 */
abstract class AWPCP_UnitTestCase extends TestCase {
    protected static $mockCommonWpFunctionsInSetUp = false;
    /**
     * @var array [Patchwork\CallRouting\Handle]
     */
    private $redefined_functions = [];

    /**
     * @var array List of filters that have been turned off.
     */
    private $paused_filters = array();

    /**
     * Code executed at the begining of every test.
     */
    public function setUp(): void {
        parent::setUp();
        $this->mockCommonWpFunctions();
    }

    /**
     * Code executed at the end of every test.
     */
    public function tearDown(): void {
        array_map( 'Patchwork\restore', $this->redefined_functions );
        parent::tearDown();
    }

    /**
     * @since 4.0.0
     */
    protected function logout() {
        $user = (object) [ 'ID' => 0 ];

        WP_Mock::userFunction( 'is_user_logged_in', [
            'return' => false,
        ] );
        WP_Mock::userFunction( 'wp_get_current_user', [
            'return' => $user,
        ] );
        WP_Mock::userFunction( 'get_current_user_id', [
            'return' => $user->ID,
        ] );
        WP_Mock::userFunction( 'awpcp_current_user_is_admin', [
            'return' => false,
        ] );
    }

    /**
     * @since 4.0.0
     */
    protected function login_as_subscriber() {
        $user = Mockery::mock( 'WP_User' );

        $user->ID = wp_rand();

        WP_Mock::userFunction( 'is_user_logged_in', [
            'return' => true,
        ] );
        WP_Mock::userFunction( 'wp_get_current_user', [
            'return' => $user,
        ] );
        WP_Mock::userFunction( 'get_current_user_id', [
            'return' => $user->ID,
        ] );
        WP_Mock::userFunction( 'awpcp_current_user_is_admin', [
            'return' => false,
        ] );
    }

    /**
     * @since 4.0.0
     */
    protected function login_as_administrator() {
        $this->login_as_subscriber();

        WP_Mock::userFunction( 'awpcp_current_user_is_admin', [
            'return' => true,
        ] );
    }

    /**
     * TODO: We probably won't need this if we stop using WordPress testing framework.
     *
     * @param string $name  The name of the filter to turn off.
     */
    protected function pause_filter( $name ) {
        global $wp_filter;

        if ( ! isset( $wp_filter[ $name ] ) ) {
            return false;
        }

        if ( ! isset( $this->paused_filters[ $name ] ) ) {
            $this->paused_filters[ $name ] = array();
        }

        $this->paused_filters[ $name ] = array_merge( $this->paused_filters[ $name ], (array) $wp_filter[ $name ] );

        unset( $wp_filter[ $name ] );

        return true;
    }

    /**
     * TODO: We probably won't need this if we stop using WordPress testing framework.
     */
    protected function resume_all_filters() {
        global $wp_filter;
        $wp_filter = array_merge( $wp_filter, $this->paused_filters );
    }

    /**
     * TODO: We probably won't need this if we stop using WordPress testing framework.
     */
    protected function enable_permalinks() {
        global $wp_rewrite;

        update_option( 'permalink_structure', '/%year%/%monthnum%/%postname%/' );
        $wp_rewrite->init();
    }

    /**
     * Use it to redefine methods of the object under the test or static methods.
     *
     * The same can be achieved creating a partial mock of the object under test,
     * but I find the following easier to write:
     *
     * `$this->redefine( 'ObjectUnderTest::some_method', function() { ... } );
     */
    protected function redefine( $callable, $callback ) {
        $this->redefined_functions[] = redefine( $callable, $callback );
    }

    /**
     * Return a matcher that capture the argument passed in that position.
     *
     * The closure returns true so that the returned matcher will match and
     * capture any argument in its position.
     *
     * Inspired by Phake's parameter capturing:
     * https://phake.readthedocs.io/en/2.1/method-parameter-matchers.html#parameter-capturing
     *
     * @since 4.0.4
     */
    protected function capture( &$param ) {
        return Mockery::on(
            function( $arg ) use ( &$param ) {
                $param = $arg;

                return true;
            }
        );
    }
    protected function mockCommonWpFunctions() {
        $functions = [
                '__',
                'esc_attr__',
                'esc_html__',
                '_x',
                'esc_attr_x',
                'esc_html_x',
                '_n',
                '_nx',
                'esc_attr',
                'esc_html',
                'esc_textarea',
                'esc_url',
                'sanitize_text_field',
                'sanitize_textarea_field',
                'wp_parse_args'        => static function ( $settings, $defaults ) {
                    return \array_merge( $defaults, $settings );
                },
                'wp_slash'             => null,
                'wp_unslash'           => static function( $value ) {
                    return \is_string( $value ) ? \stripslashes( $value ) : $value;
                },
                'wp_rand'           => static function() {
                    return  rand();
                },
                'esc_url_raw',
                'wp_json_encode' => static function( $value ) {
                    return \json_encode( $value );
                },
        ];

        foreach ( $functions as $function => $callback ) {
            if ( \is_string( $function ) ) {
                WP_Mock::userFunction( $function, [ 'return' => $callback ] );
            } else {
                // Return the first argument.
                WP_Mock::userFunction( $callback,  [ 'return' => function( $arg ) {
                    return $arg;
                } ] );
            }
        }

        $functions = [
            '_e',
            'esc_attr_e',
            'esc_html_e',
            '_ex',
        ];

        foreach ( $functions as $function ) {
            WP_Mock::userFunction( $function, [
                'return' => function( $arg ) {
                    echo $arg;
                }
            ] );
        }
    }

    protected function expectAddQueryArg( $key = null, $val = null, $url = null ) {
        WP_Mock::userFunction( 'add_query_arg', [
            'return' => function () use ( $key, $val, $url ) {
                if ( is_array( $key ) ) {
                    return 'https://example.org' . '?' . key( $key ) . '=' . $key[ key( $key ) ];
                } else {
                    return $url . '?' . $key . '=' . $val;
                }
            }
        ] );
    }
}
