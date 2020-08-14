<?php
/**
 * @package AWPCP\Tests\Admin\Listings
 */

/**
 * Unit tests for Awaiting Approval listings table view.
 */
class AWPCP_AwaitingApprovalListingTableViewTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        $this->test_helper = new AWPCP_ListingTableViewTestHelper( $this );

        $this->listings_collection = Mockery::mock( 'AWPCP_ListingsCollection' );
    }

    /**
     * @since 4.0.0
     */
    public function test_common_features() {
        $this->test_helper->check_common_table_view_methods( $this->get_test_subject() );
    }

    /**
     * @since 4.0.0
     */
    public function get_test_subject() {
        return new AWPCP_AwaitingApprovalListingTableView(
            $this->listings_collection
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_get_count() {
        $count = rand() + 1;

        $this->listings_collection->shouldReceive( 'count_listings_awaiting_approval' )
            ->andReturn( $count );

        // Execution & Verification.
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
        $this->assertTrue( $query->query_vars['classifieds_query']['is_awaiting_approval'] );
    }
}
