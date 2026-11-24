<?php
/**
 * @package AWPCP\Tests\Frontend
 */

use Brain\Monkey\Functions;

/**
 * Tests for awpcp_replace_placeholders() and contact placeholder escaping.
 */
class AWPCP_ReplacePlaceholdersTest extends AWPCP_UnitTestCase {

    private $renderer;

    public function setUp(): void {
        parent::setUp();

        require_once AWPCP_DIR . '/frontend/placeholders.php';

        Functions\stubEscapeFunctions();
        Functions\stubTranslationFunctions();
        Functions\when( 'get_awpcp_option' )->justReturn( 0 );
        Functions\when( 'is_user_logged_in' )->justReturn( false );

        $this->renderer = Mockery::mock( 'AWPCP_ListingRenderer' );
        $this->renderer->shouldReceive( 'get_view_listing_url' )->andReturn( 'https://example.org/listing/' );

        Functions\when( 'awpcp_listing_renderer' )->justReturn( $this->renderer );
    }

    public function test_placeholders_inside_listing_values_are_not_replaced() {
        $listing = (object) [ 'ID' => 1 ];

        $this->renderer->shouldReceive( 'get_listing_title' )->andReturn( 'Bike $contact_phone' );
        $this->renderer->shouldReceive( 'get_contact_phone' )->andReturn( '555-1234' );

        $output = awpcp_do_placeholders( $listing, '$title | $contact_phone', 'single' );

        $this->assertEquals( 'Bike $contact_phone | 555-1234', $output );
    }

    public function test_contact_phone_is_escaped() {
        $listing = (object) [ 'ID' => 1 ];

        $this->renderer->shouldReceive( 'get_contact_phone' )->andReturn( '555<img src=x onerror=alert(1)>' );

        $output = awpcp_do_placeholders( $listing, '$contact_phone', 'single' );

        $this->assertEquals( '555&lt;img src=x onerror=alert(1)&gt;', $output );
    }
}
