<?php
/**
 * @package AWPCP\Tests\Listings
 */

use Brain\Monkey\Functions;

/**
 * Unit tests for implementation of Listings Collection for AWPCP 4.0
 */
class AWPCP_ListingsCollectionTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        $this->query = (object) array(
            'posts'       => array(),
            'found_posts' => 0,
        );

        $this->wordpress = Mockery::mock( 'AWPCP_WordPress' );
        $this->roles     = Mockery::mock( 'AWPCP_RolesAndCapabilities' );

        Functions\when( 'apply_filters' )->returnArg( 2 );
        Functions\when( 'is_admin' )->justReturn( false );
    }

    /**
     * @expectedException AWPCP_Exception
     * @since 4.0.0
     */
    public function test_get_listing_with_old_id() {
        $previous_id = wp_rand() + 1;

        $query_vars = array(
            'classifieds_query' => array(
                'previous_id' => $previous_id,
            ),
        );

        $collection = $this->get_collection_for_query( $query_vars );

        // Execution.
        $collection->get_listing_with_old_id( $previous_id );
    }

    /**
     * @param array $query_vars     The full list of query vars.
     * @since 4.0.0
     */
    private function get_collection_for_query( $query_vars ) {
        $this->wordpress->shouldReceive( 'create_posts_query' )
            ->with(
                Mockery::on(
                    function( $processed_query_vars ) use ( $query_vars ) {
                        $this->assertArraySubset( $query_vars, $processed_query_vars );

                        return true;
                    }
                )
            )
            ->andReturn( $this->query );

        return $this->get_test_subject();
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_ListingsCollection(
            'awpcp_listing',
            null,
            $this->wordpress,
            null,
            $this->roles
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_find_all_by_id() {
        $identifiers = array( wp_rand() + 1 );

        $query_vars = array(
            'post__in'          => $identifiers,
            'classifieds_query' => array(),
        );

        $collection = $this->get_collection_for_query( $query_vars );

        // Execution.
        $collection->find_all_by_id( $identifiers );
    }

    /**
     * @since 4.0.0
     */
    public function test_count_valid_listings() {
        $this->test_method_takes_query_vars( array(), 'count_valid_listings' );

        $query_vars = array(
            'classifieds_query' => array(
                'is_valid' => true,
            ),
        );

        $collection = $this->get_collection_for_query( $query_vars );

        // Execution.
        $collection->count_valid_listings();
    }

    /**
     * @dataProvider find_methods_provider
     * @param array  $query_vars     The full list of query vars.
     * @param string $method_name    Name of the mothod that will be tested.
     * @param array  $method_args    An array of arguments for the tested method.
     * @since 4.0.0
     */
    public function test_find_methods( $query_vars, $method_name, $method_args = array() ) {
        if ( ! isset( $query_vars['classifieds_query'] ) ) {
            $query_vars = array( 'classifieds_query' => $query_vars );
        }

        $collection = $this->get_collection_for_query( $query_vars );
        $this->roles->shouldReceive( 'current_user_is_moderator' )->andReturn( true );

        // Execution.
        call_user_func_array( array( $collection, $method_name ), $method_args );
    }

    /**
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function find_methods_provider() {
        $user_id     = wp_rand() + 1;
        $category_id = wp_rand() + 1;

        return [
            'valid listings'                 => [
                [
                    'is_valid' => true,
                ],
                'find_valid_listings',
            ],
            'new listings'                   => [
                [
                    'is_new' => true,
                ],
                'count_new_listings',
            ],
            'expired listings'               => [
                [
                    'is_expired' => true,
                ],
                'find_expired_listings',
            ],
            'listings about to expire'       => [
                [
                    'is_about_to_expire' => true,
                ],
                'find_listings_about_to_expire',
            ],
            'enabled listings'               => [
                [
                    'is_enabled' => true,
                ],
                'find_enabled_listings',
            ],
            'disabled listings'              => [
                [
                    'is_disabled' => true,
                ],
                'find_disabled_listings',
            ],
            'listings awaiting approval'     => [
                [
                    'is_awaiting_approval' => true,
                ],
                'find_listings_awaiting_approval',
            ],
            'images awaiting approval'       => [
                [
                    'has_images_awaiting_approval' => true,
                ],
                'count_listings_with_images_awaiting_approval',
            ],
            'successfully paid listings'     => [
                [
                    'is_successfully_paid' => true,
                ],
                'find_successfully_paid_listings',
            ],
            'listings awaiting verification' => [
                [
                    'is_awaiting_verification' => true,
                ],
                'find_listings_awaiting_verification',
            ],
            'featured listings'              => [
                [
                    'is_featured' => true,
                ],
                'count_featured_listings',
            ],
            'flagged listings'               => [
                [
                    'is_flagged' => true,
                ],
                'count_flagged_listings',
            ],
            'incomplete listings'            => [
                [
                    'is_incomplete' => true,
                ],
                'count_incomplete_listings',
            ],
            'user listings'                  => [
                [
                    'author'            => $user_id,
                    'classifieds_query' => [
                        'is_valid' => true,
                    ],
                ],
                'find_user_listings',
                [ $user_id ],
            ],
            'user enabled listings'          => [
                [
                    'author'            => $user_id,
                    'classifieds_query' => [
                        'is_enabled' => true,
                    ],
                ],
                'find_user_enabled_listings',
                [ $user_id ],
            ],
            'user disabled listings'         => [
                [
                    'author'            => $user_id,
                    'classifieds_query' => [
                        'is_disabled' => true,
                    ],
                ],
                'find_user_disabled_listings',
                [ $user_id ],
            ],
            'enabled listings in category'   => [
                [
                    'category'   => $category_id,
                    'is_enabled' => true,
                ],
                'count_enabled_listings_in_category',
                [ $category_id ],
            ],
        ];
    }

    /**
     * @dataProvider find_methods_provider
     * @dataProvider count_methods_provider
     * @param array  $query_vars     The full list of query vars.
     * @param string $method_name    Name of the mothod that will be tested.
     * @param array  $method_args    An array of arguments for the tested method.
     * @since 4.0.0
     */
    public function test_method_takes_query_vars( $query_vars, $method_name, $method_args = array() ) {
        $query_vars = array( 'foo' => 'bar' );

        $this->wordpress->shouldReceive( 'create_posts_query' )
            ->with(
                Mockery::on(
                    function( $query_vars ) {
                        return isset( $query_vars['foo'] );
                    }
                )
            )
            ->andReturn( $this->query );

        $this->roles->shouldReceive( 'current_user_is_moderator' )->andReturn( true );

        Functions\when( 'apply_filters' )->returnArg( 2 );

        $collection = $this->get_test_subject();

        // Execution.
        array_push( $method_args, $query_vars );
        call_user_func_array( array( $collection, $method_name ), $method_args );
    }

    /**
     * @dataProvider count_methods_provider
     * @param array  $query_vars     The full list of query vars.
     * @param string $method_name    Name of the mothod that will be tested.
     * @param array  $method_args    An array of arguments for the tested method.
     * @since 4.0.0
     */
    public function test_count_methods( $query_vars, $method_name, $method_args = array() ) {
        if ( ! isset( $query_vars['classifieds_query'] ) ) {
            $query_vars = array( 'classifieds_query' => $query_vars );
        }

        $this->query->found_posts = wp_rand() + 1;

        $collection = $this->get_collection_for_query( $query_vars );
        $this->roles->shouldReceive( 'current_user_is_moderator' )->andReturn( true );

        // Execution.
        $count = call_user_func_array( array( $collection, $method_name ), $method_args );

        // Verification.
        $this->assertEquals( $this->query->found_posts, $count );
    }

    /**
     * @since 4.0.0
     */
    public function count_methods_provider() {
        $data = $this->find_methods_provider();

        // Remove data sets that have no count_ method defined.
        unset( $data['listings about to expire'] );

        // The data sets are the same for count_ and find_ methods, except for the
        // name of the method.
        foreach ( $data as $name => $data_set ) {
            $data[ $name ][1] = str_replace( 'find_', 'count_', $data_set[1] );
        }

        return $data;
    }
}
