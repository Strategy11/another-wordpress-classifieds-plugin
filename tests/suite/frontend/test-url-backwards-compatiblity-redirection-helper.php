<?php
/**
 * @package AWPCP\Test\Plugin\Listings
 */

use Brain\Monkey\Functions;

/**
 * Tests for URL Backwards Compatiblity Redirection Helper class.
 *
 * @backupGlobals disabled
 */
class AWPCP_URLBackwardsCompatiblityRedirectionHelperTest extends AWPCP_UnitTestCase {

    public function test_url_redirection_when_rewrite_rule_includes_pagename() {
        $single_listing_page_uri = 'parent-page/child-page';

        $query = (object) array(
            'query_vars' => array(
                'id' => rand() + 1,
                'pagename' => $single_listing_page_uri,
            ),
        );

        $listing = (object) array(
            'ID' => rand() + 1,
        );

        $listing_permalink = 'https://example.com/' . $single_listing_page_uri . '/' . $listing->ID . '/';

        $listings = Mockery::mock( 'AWPCP_ListingsCollection' );
        $settings = Mockery::mock( 'AWPCP_Settings_API' );

        $listings->shouldReceive( 'get_listing_with_old_id' )
            ->once()
            ->with( $query->query_vars['id'] )
            ->andReturn( $listing );

        $settings->shouldReceive( 'get_option' )
            ->once()
            ->with( 'show-listing-page' )
            ->andReturn( rand() + 1 );

        Functions\when( 'get_page_uri' )->justReturn( $single_listing_page_uri );

        Functions\expect( 'get_permalink' )
            ->once()
            ->with( $listing )
            ->andReturn( $listing_permalink );

        $helper = new AWPCP_URL_Backwards_Compatibility_Redirection_Helper( null, null, null, $listings, null, $settings, null );

        Functions\expect( 'wp_redirect' )
            ->once()
            ->with( $listing_permalink, 301 )
            ->andReturn( false ); // To prevent exit() from being called.

        // Execution.
        $helper->maybe_redirect_from_old_listing_url( $query );

        // Verification.

        // XXX: Mark test as passed if all Mockery expectations are met.
        $this->assertTrue( true );
    }
}
