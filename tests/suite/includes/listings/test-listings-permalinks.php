<?php
/**
 * @package \AWPCP\Tests\Plugin\Listings
 */

use Brain\Monkey\Functions;

/**
 * Test cases for Listings Permalinks class.
 *
 * @backupGlobals disabled
 */
class AWPCP_ListingPermalinksTest extends AWPCP_UnitTestCase {

    public function test_filter_post_type_link_returns_proper_urls_when_friendly_urls_are_disabled() {
        $post_type = 'awpcp_listing';
        $post = (object) array(
            'ID' => rand() + 1,
            'post_type' => $post_type,
        );

        $settings = Mockery::mock( 'AWPCP_Settings_API' );

        $settings->shouldReceive( 'get_option' )
            ->once()
            ->with( 'seofriendlyurls' )
            ->andReturn( false );

        Functions\expect( 'get_option' )
            ->once()
            ->with( 'permalink_structure' )
            ->andReturn( 'something' );

        $listings_permalinks = new AWPCP_ListingsPermalinks( $post_type, null, null, $settings );

        // Execution.
        $post_link = $listings_permalinks->filter_post_type_link(
            'http://next.awpcp.test/sample-page/veryoldpage/%awpcp_optional_listing_id%/',
            $post,
            null,
            null
        );

        // Verification.
        $this->assertNotContains( '%awpcp_optional_listing_id%', $post_link );
        $this->assertNotContains( '//', str_replace( '://', ':!!', $post_link ) );

        parse_str( parse_url( $post_link, PHP_URL_QUERY ), $query_varaibles );

        $this->assertEquals( $post->ID, $query_varaibles['id'] );
    }

    public function test_get_post_type_permastruct_includes_location_placeholder() {
        Functions\expect( 'get_option' )
            ->zeroOrMoreTimes()
            ->with( 'permalink_structure' )
            ->andReturn( 'something' );

        // Execution & Verification
        $this->check_placeholder_is_inclued_when_setting_is_enabled( 'include-country-in-listing-url' );
        $this->check_placeholder_is_inclued_when_setting_is_enabled( 'include-state-in-listing-url' );
        $this->check_placeholder_is_inclued_when_setting_is_enabled( 'include-city-in-listing-url' );
        $this->check_placeholder_is_inclued_when_setting_is_enabled( 'include-county-in-listing-url' );
    }

    private function check_placeholder_is_inclued_when_setting_is_enabled( $setting_name ) {
        $post_type_object = (object) array(
            'rewrite' => array(
                'slug' => 'slug',
            ),
        );

        $this->settings = Mockery::mock( 'AWPCP_Settings_API' );

        $settings_names = array(
            'seofriendlyurls' => true,
            'include-title-in-listing-url' => null,
            'include-category-in-listing-url' => null,
            'include-country-in-listing-url' => null,
            'include-state-in-listing-url' => null,
            'include-city-in-listing-url' => null,
            'include-county-in-listing-url' => null,
            $setting_name => true, // This will overwrite the value for one of the settings above.
        );

        foreach ( $settings_names as $name => $value ) {
            $this->settings->shouldReceive( 'get_option' )->zeroOrMoreTimes()->with( $name )->andReturn( $value );
        }

        $this->listings_permalinks = new AWPCP_ListingsPermalinks( null, null, null, $this->settings );

        $permastruct = $this->listings_permalinks->get_post_type_permastruct( $post_type_object );

        // Verification.
        $this->assertContains( '%awpcp_location%', $permastruct, "Placeholder not included when $setting_name is enabled." );
    }
}
