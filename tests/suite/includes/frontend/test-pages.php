<?php
/**
 * @package AWPCP\Tests\Frontend
 */

use Brain\Monkey\Functions;

/**
 * @since 4.0.0
 */
class AWPCP_PagesTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function test_constructor() {
        $container = [
            'Meta'            => null,
            'ShowListingPage' => null,
        ];

        // Necessary because AWPCP_Pages creates an instance of AWPCP_BrowseAdsPage
        // using awpcp_browse_listings_page() instead of loading one from the
        // container or receiving it as a parameter.
        //
        // Also because if we use Brain\Monkey\Functions a 'Cannot redeclare'
        // error is thrown when page-browse-ads.php is required in shortcode.php.
        $this->redefine( 'awpcp_browse_listings_page', Patchwork\always( null ) );

        $this->assertNotNull( new AWPCP_Pages( $container ) );
    }
}
