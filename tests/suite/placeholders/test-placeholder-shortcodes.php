<?php
/**
 * @package AWPCP\Tests\Frontend
 */

use Brain\Monkey\Functions;

/**
 * Listing values printed by placeholders must not be parsed as shortcodes.
 */
class AWPCP_PlaceholderShortcodesTest extends AWPCP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();

        require_once AWPCP_DIR . '/frontend/placeholders.php';

        Functions\stubEscapeFunctions();
        Functions\stubTranslationFunctions();
        Functions\when( 'get_awpcp_option' )->justReturn( 0 );
        Functions\when( 'is_user_logged_in' )->justReturn( false );
    }

    /**
     * @dataProvider placeholder_provider
     */
    public function test_placeholder_prints_brackets_as_entities( $placeholder ) {
        $listing  = (object) [
            'ID'         => 1,
            'post_title' => 'Bike [gallery] for sale',
        ];
        $renderer = Mockery::mock( 'AWPCP_ListingRenderer' );

        $renderer->shouldReceive( 'get_listing_title' )->andReturn( $listing->post_title );
        $renderer->shouldReceive( 'get_view_listing_url' )->andReturn( 'https://example.org/listing/' );
        $renderer->shouldReceive( 'get_contact_name' )->andReturn( 'Jane [gallery]' );
        $renderer->shouldReceive( 'get_contact_phone' )->andReturn( '555 [gallery]' );
        $renderer->shouldReceive( 'get_contact_email' )->andReturn( '"[gallery]"@example.org' );
        $renderer->shouldReceive( 'get_website_url' )->andReturn( 'https://example.org/[gallery]' );

        Functions\when( 'awpcp_listing_renderer' )->justReturn( $renderer );

        $output = awpcp_do_placeholders( $listing, '$' . $placeholder, 'single' );

        $this->assertStringContainsString( '&#91;gallery&#93;', $output );
        $this->assertStringNotContainsString( '[', $output );
    }

    public function placeholder_provider() {
        return [
            [ 'title' ],
            [ 'title_link' ],
            [ 'contact_name' ],
            [ 'contact_phone' ],
            [ 'ad_contact_email' ],
            [ 'website_url' ],
            [ 'website_link' ],
        ];
    }
}
