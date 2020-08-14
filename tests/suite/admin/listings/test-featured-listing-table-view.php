<?php
/**
 * @package AWPCP\Tests\Plugin\Admin\Listings
 */

/**
 * Unit tests for Featured view for Listings table.
 */
class AWPCP_FeaturedListingTableViewTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        $this->table_view_test_helper = new AWPCP_ListingTableViewTestHelper( $this );

        $this->listings_collection = Mockery::mock( 'AWPCP_ListingsCollection' );
    }

    /**
     * @since 4.0.0
     */
    public function test_common_features() {
        $this->table_view_test_helper->check_common_table_view_methods( $this->get_test_subject() );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_FeaturedListingTableView( $this->listings_collection );
    }

    /**
     * @since 4.0.0
     */
    public function test_get_count() {
        $count = rand() + 1;

        $this->listings_collection->shouldReceive( 'count_featured_listings' )
            ->andReturn( $count );

        $view = $this->get_test_subject();

        // Execution & Verification.
        $this->assertEquals( $count, $view->get_count() );
    }

    /**
     * @since 4.0.0
     */
    public function test_pre_get_posts() {
        $query = (object) [
            'query_vars' => [],
        ];

        $view = $this->get_test_subject();

        // Execution.
        $view->pre_get_posts( $query );

        // Verification.
        $this->assertEquals( true, $query->query_vars['classifieds_query']['is_featured'] );
    }
}
