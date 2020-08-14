<?php
/**
 * @package AWPCP\Tests\Compatibility
 */

use Brain\Monkey\Functions;

/**
 * @since 4.0.4
 */
class AWPCP_IndeedMemebershipProPluginIntegrationTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.4
     */
    public function setup() {
        parent::setup();

        $this->query = Mockery::mock( 'AWPCP_Query' );
    }

    /**
     * @since 4.0.4
     */
    public function test_setup() {
        $integration = $this->get_test_subject();

        // Execution.
        $integration->setup();

        // Verification.
        $this->assertTrue( has_action( 'wp_enqueue_scripts', [ $integration, 'maybe_dequeue_select2' ], 9999 ) );
    }

    /**
     * @since 4.0.4
     */
    private function get_test_subject() {
        return new AWPCP_IndeedMembershipProPluginIntegration( $this->query );
    }

    /**
     * @since 4.0.4
     *
     * @dataProvider maybe_dequeue_select2_data_provider
     */
    public function test_maybe_dequeue_select2( $times, $expectations ) {
        $expectations = array_merge(
            [
                'is_post_listings_page'   => false,
                'is_edit_listing_page'    => false,
                'is_browse_listings_page' => false,
                'is_search_listings_page' => false,
            ],
            $expectations
        );

        $this->query->shouldReceive( $expectations );

        Functions\expect( 'wp_dequeue_style' )->times( $times )->with( 'ihc_select2_style' );
        Functions\expect( 'wp_dequeue_script' )->times( $times )->with( 'ihc-select2' );

        // Execution & Verification.
        $this->get_test_subject()->maybe_dequeue_select2();
    }

    /**
     * @since 4.0.4
     */
    public function maybe_dequeue_select2_data_provider() {
        return [
            'not-an-awpcp-page'    => [ 0, [] ],
            'place-listing-page'   => [ 1, [ 'is_post_listings_page' => true ] ],
            'edit-listing-page'    => [ 1, [ 'is_edit_listing_page' => true ] ],
            'browse-listings-page' => [ 1, [ 'is_browse_listings_page' => true ] ],
            'search-listings-page' => [ 1, [ 'is_search_listings_page' => true ] ],
        ];
    }
}
