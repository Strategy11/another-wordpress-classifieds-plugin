<?php
/**
 * @package AWPCP\Tests\Frontend
 */

use Brain\Monkey\Filters;

/**
 * Tests for awpcp_neutralize_listing_content_shortcodes().
 */
class AWPCP_NeutralizeListingContentShortcodesTest extends AWPCP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();

        require_once AWPCP_DIR . '/frontend/placeholders.php';

        awpcp_tests_stub_shortcodes( [ 'gallery' ] );
    }

    public function tearDown(): void {
        unset( $GLOBALS['shortcode_tags'] );

        parent::tearDown();
    }

    public function test_registered_shortcode_is_removed() {
        $this->assertSame( 'Bike  for sale', awpcp_neutralize_listing_content_shortcodes( 'Bike [gallery] for sale' ) );
    }

    public function test_escaped_shortcode_leftover_gets_entity_brackets() {
        $this->assertSame( 'a &#91;gallery&#93; b', awpcp_neutralize_listing_content_shortcodes( 'a [[gallery]] b' ) );
    }

    public function test_inline_script_brackets_are_kept() {
        $content = '<script>var list = [1, 2]; list[0] = 3;</script>';

        $this->assertSame( $content, awpcp_neutralize_listing_content_shortcodes( $content ) );
    }

    public function test_content_is_left_alone_when_listing_shortcodes_are_enabled() {
        Filters\expectApplied( 'awpcp_disable_listing_shortcode_stripping' )->andReturn( true );

        $this->assertSame( 'a [gallery] b', awpcp_neutralize_listing_content_shortcodes( 'a [gallery] b' ) );
    }
}
