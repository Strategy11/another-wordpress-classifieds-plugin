<?php
/**
 * @package AWPCP\Tests\Admin\Listings
 */

/**
 * Unit tests for Complete Listing Table View.
 */
use Brain\Monkey\Functions;

class AWPCP_CompleteListingTableViewTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setUp(): void {
        parent::setUp();

        $this->test_helper = new AWPCP_ListingTableViewTestHelper( $this );

        $this->listings_collection = Mockery::mock( 'AWPCP_ListingsCollection' );
    }

    /**
     * @since 4.0.0
     */
    public function test_common_features() {
        Functions\expect( 'add_query_arg' )->andReturnUsing( function ( $key , $val=null , $url =null ) {
            if(is_array($key)){
                return 'https://example.org' . '?' . key($key) . '=' . $key[key($key)];
            }else{
                return $url . '?' . $key . '=' . $val;
            }
        } );

        $this->test_helper->check_common_table_view_methods( $this->get_test_subject() );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_CompleteListingTableView( $this->listings_collection );
    }

    /**
     * @since 4.0.0
     */
    public function test_get_count() {
        $count = rand() + 1;

        $this->listings_collection->shouldReceive( 'count_valid_listings' )
            ->andReturn( $count );

        // Execution and Verification.
        $this->assertEquals( $count, $this->get_test_subject()->get_count() );
    }

    /**
     * @since 4.0.0
     */
    public function test_pre_get_posts() {
        $query = (object) [
            'query_vars' => [],
        ];

        // Execution.
        $this->get_test_subject()->pre_get_posts( $query );

        // Verification.
        $this->assertTrue( $query->query_vars['classifieds_query']['is_valid'] );
    }
}
