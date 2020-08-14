<?php
/**
 * @package AWPCP\Tests\Plugin\Admin\Listings
 */

/**
 * Unit tests for Listings Table Columns Handler.
 */
class AWPCP_ListingsTableColumnsHandlerTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        parent::setup();

        $this->listing_renderer    = Mockery::mock( 'AWPCP_ListingRenderer' );
        $this->listings_collection = Mockery::mock( 'AWPCP_ListingsCollection' );
    }

    /**
     * @since 4.0.0
     */
    public function test_manage_posts_columns() {
        $columns = $this->get_test_subject()->manage_posts_columns( [] );

        // Verification.
        $this->assertArrayHasKey( 'awpcp-dates', $columns );
        $this->assertArrayHasKey( 'awpcp-payment-term', $columns );
        $this->assertArrayHasKey( 'awpcp-status', $columns );
    }

    /**
     * @since 4.0.0
     */
    public function test_label_for_status_column() {
        $columns = $this->get_test_subject()->manage_posts_columns( [] );

        // Verification.
        $this->assertEquals( 'Status', $columns['awpcp-status'] );
    }

    /**
     * @since 4.0.0
     */
    public function get_test_subject() {
        return new AWPCP_ListingsTableColumnsHandler(
            'awpcp_listing',
            'awpcp_listing_category',
            $this->listing_renderer,
            $this->listings_collection
        );
    }

    /**
     * @dataProvider manage_date_columns_provider
     * @param string $column            The name of the column to test.
     * @param string $method_name       The name of the method to mock.
     * @param string $expected_output   The expected output of the method under test.
     * @since 4.0.0
     */
    public function test_manage_date_columns( $column, $method_name, $expected_output ) {
        $this->listing_renderer->shouldReceive( $method_name )->andReturn( $expected_output );

        $this->verify_column_output( $column, $expected_output );
    }

    /**
     * @param string $column            The name of the column to test.
     * @param string $expected_output   The expected output of the method under test.
     * @since 4.0.0
     */
    private function verify_column_output( $column, $expected_output ) {
        $post = (object) [
            'ID' => wp_rand() + 1,
        ];

        $this->listings_collection->shouldReceive( 'get' )->with( $post->ID )->andReturn( $post );

        // Verification.
        $this->expectOutputRegex( '/' . $expected_output . '/' );

        // Execution.
        $this->get_test_subject()->manage_posts_custom_column( $column, $post->ID );
    }

    /**
     * @since 4.0.0
     */
    public function manage_date_columns_provider() {
        return [
            [ 'awpcp-start-date', 'get_start_date', 'May 3, 2018' ],
            [ 'awpcp-end-date', 'get_end_date', 'May 3, 2018' ],
            [ 'awpcp-renewed-date', 'get_renewed_date_formatted', 'May 3, 2018' ],
        ];
    }

    /**
     * @since 4.0.0
     */
    public function test_manage_renewed_date_column_when_renewed_date_is_not_available() {
        $this->listing_renderer->shouldReceive( 'get_renewed_date_formatted' )->andReturn( '' );

        $this->verify_column_output( 'awpcp-renewed-date', '&mdash;' );
    }

    /**
     * @since 4.0.0
     */
    public function test_manage_payment_term_column() {
        $expected_output = 'Payment Term';

        $payment_term = (object) [
            'name' => $expected_output,
        ];

        $this->listing_renderer->shouldReceive(
            [
                'get_payment_term'   => $payment_term,
                'get_payment_status' => AWPCP_Payment_Transaction::PAYMENT_STATUS_COMPLETED,
            ]
        );

        $this->verify_column_output( 'awpcp-payment-term', $expected_output );
    }

    /**
     * @since 4.0.0
     */
    public function test_manage_status_column_for_pending_approval_listings() {
        $this->listing_renderer->shouldReceive(
            [
                'is_public'           => false,
                'is_pending_approval' => true,
            ]
        );

        $this->verify_column_output( 'awpcp-status', 'Pending Approval' );
    }

    /**
     * @since 4.0.0
     */
    public function test_manage_status_column_for_listings_pending_payment() {
        $this->listing_renderer->shouldReceive(
            [
                'is_public'           => false,
                'is_pending_approval' => false,
                'is_expired'          => false,
                'is_disabled'         => false,
                'has_payment'         => false,
            ]
        );

        $this->verify_column_output( 'awpcp-status', 'Pending Payment' );
    }

    /**
     * @since 4.0.0
     */
    public function test_manage_status_column_for_unverified_listings() {
        $this->listing_renderer->shouldReceive(
            [
                'is_public'           => false,
                'is_pending_approval' => false,
                'is_expired'          => false,
                'is_disabled'         => false,
                'has_payment'         => true,
                'is_verified'         => false,
            ]
        );
        $this->verify_column_output( 'awpcp-status', 'Pending Verification' );
    }
}
