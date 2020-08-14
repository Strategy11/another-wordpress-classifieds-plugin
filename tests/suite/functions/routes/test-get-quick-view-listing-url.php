<?php
/**
 * @package AWPCP\Tests\Functions
 */

use Brain\Monkey\Functions;

/**
 * @since 4.0.4
 */
class AWPCP_GetQuickViewListingURLTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.4
     *
     * @dataProvider get_quick_view_listing_url_data_provider
     */
    public function test_get_quick_view_listing_url( $current_url ) {
        $listing = (object) [ 'ID' => wp_rand() ];

        $admin_listings_url = 'https://wp.org/fake/awpcp/admin/listings/url';
        $modified_url       = 'https://wp.org/modified/url';

        Functions\when( 'awpcp_get_admin_listings_url' )->justReturn( $admin_listings_url );

        Functions\expect( 'add_query_arg' )
            ->with(
                $this->capture( $params ),
                $current_url ?: $admin_listings_url
            )
            ->andReturn( $modified_url );

        $url = awpcp_get_quick_view_listing_url( $listing, $current_url );

        // Verification.
        $this->assertSame( $modified_url, $url );
        $this->assertSame( 'awpcp-admin-quick-view-listing', $params['page'] );
        $this->assertSame( $listing->ID, $params['post'] );
    }

    /**
     * @since 4.0.4
     */
    public function get_quick_view_listing_url_data_provider() {
        return [
            [ false ],
            [ 'http://wp.org/custom/url' ],
        ];
    }
}
