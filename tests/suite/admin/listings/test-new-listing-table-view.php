<?php
/**
 * @package AWPCP\Plugin\Admin\Listings
 */

/**
 * Unit tests for New Listing's table view.
 */
class AWPCP_NewListingTableViewTest extends AWPCP_UnitTestCase {

    /**
     * @var mixed
     */
    public $table_view_test_helper;

    /**
     * @var mixed
     */
    public $listings_collection;

    /**
     * @since 4.0.0
     */
    public function setUp(): void {
        parent::setUp();
        $this->table_view_test_helper = new AWPCP_ListingTableViewTestHelper( $this );

        $this->listings_collection = Mockery::mock( 'AWPCP_ListingsCollection' );
    }

    /**
     * @since 4.0.0
     */
    public function test_common_features() {
        $this->expectAddQueryArg();
        $this->table_view_test_helper->check_common_table_view_methods( $this->get_test_subject() );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_NewListingTableView(
            $this->listings_collection
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_get_count() {
        $count = rand() + 1;

        $this->listings_collection->shouldReceive( 'count_new_listings' )
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
        $this->assertEquals( true, $query->query_vars['classifieds_query']['is_new'] );
    }
}
