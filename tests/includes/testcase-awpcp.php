<?php
/**
 * @package AWPCP\Tests
 */

// phpcs:disable Squiz.Commenting.InlineComment
// phpcs:disable Squiz.PHP.CommentedOutCode.Found
// phpcs:disable WordPress.NamingConventions.ValidVariableName.MemberNotSnakeCase
// phpcs:disable WordPress.VIP.DirectDatabaseQuery.DirectQuery
// phpcs:disable WordPress.VIP.DirectDatabaseQuery.NoCaching

use Brain\Monkey;
use Brain\Monkey\Functions;

use function Patchwork\redefine;

/**
 * Base class for all plugin tests.
 */
abstract class AWPCP_UnitTestCase extends PHPUnit\Framework\TestCase {

    /**
     * @var array [Patchwork\CallRouting\Handle]
     */
    private $redefined_functions = [];

    // /**
    //  * @var string Placeholder for the save the SQL mode at the start of the test.
    //  */
    // private $sql_mode;

    // /**
    //  * @var int ID of the current test user.
    //  */
    // private $current_user_id;

    /**
     * @var array List of filters that have been turned off.
     */
    private $paused_filters = array();

    /**
     * Code executed at the begining of every test.
     */
    public function setup() {
        parent::setup();

        Monkey\setup();

        // $this->save_current_user();
    }

    // /**
    //  * TODO: We probably won't need this if we stop using WordPress testing framework.
    //  */
    // private function save_current_user() {
    //     $current_user = wp_get_current_user();

    //     if ( ! is_null( $current_user ) ) {
    //         $this->current_user_id = $current_user->ID;
    //     } else {
    //         $this->current_user_id = 0;
    //     }
    // }

    // /**
    //  * TODO: We probably won't need this if we stop using WordPress testing framework.
    //  */
    // private function restore_current_user() {
    //     if ( $this->current_user_id > 0 ) {
    //         wp_set_current_user( $this->current_user_id );
    //     } else {
    //         global $current_user;
    //         $current_user = null;
    //     }
    // }

    /**
     * TODO: We probably won't need this if we stop using WordPress testing framework.
     */
    public function start_transaction() {
        $this->activate_mysql_strict_mode();
        parent::start_transaction();
    }

    // /**
    //  * TODO: We probably won't need this if we stop using WordPress testing framework.
    //  */
    // private function activate_mysql_strict_mode() {
    //     global $wpdb;
    //     $this->sql_mode = $wpdb->get_var( 'SELECT @@SESSION.sql_mode' );
    //     $wpdb->query( "SET @@SESSION.sql_mode = 'TRADITIONAL';" );
    // }

    /**
     * Code executed at the end of every test.
     */
    public function teardown() {
        array_map( 'Patchwork\restore', $this->redefined_functions );
        // $this->restore_mysql_mode();
        // $this->restore_current_user();
        // $this->resume_all_filters();
        Monkey\teardown();

        parent::teardown();
    }

    // /**
    //  * TODO: We probably won't need this if we stop using WordPress testing framework.
    //  */
    // private function restore_mysql_mode() {
    //     global $wpdb;
    //     $wpdb->query( $wpdb->prepare( 'SET @@SESSION.sql_mode = %s', $this->sql_mode ) );
    // }

    /**
     * @since 4.0.0
     */
    protected function logout() {
        $user = (object) [ 'ID' => 0 ];

        Functions\when( 'is_user_logged_in' )->justReturn( false );
        Functions\when( 'wp_get_current_user' )->justReturn( $user );
        Functions\when( 'get_current_user_id' )->justReturn( $user->ID );
        Functions\when( 'awpcp_current_user_is_admin' )->justReturn( false );
    }

    /**
     * @since 4.0.0
     */
    protected function login_as_subscriber() {
        $user = Mockery::mock( 'WP_User' );

        $user->ID = wp_rand();

        Functions\when( 'is_user_logged_in' )->justReturn( true );
        Functions\when( 'wp_get_current_user' )->justReturn( $user );
        Functions\when( 'get_current_user_id' )->justReturn( $user->ID );
        Functions\when( 'awpcp_current_user_is_admin' )->justReturn( false );
    }

    /**
     * @since 4.0.0
     */
    protected function login_as_administrator() {
        $this->login_as_subscriber();

        Functions\when( 'awpcp_current_user_is_admin' )->justReturn( true );
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

        $this->paused_filters[ $name ] = array_merge( $this->paused_filters[ $name ], $wp_filter[ $name ] );

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
     * To set expectations or control the behaviour of other methods/functions
     * use Brain\Monkey\Functions API.
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
}
