<?php
/**
 * @package AWPCP\Tests\Functions
 */

/**
 * Tests for awpcp_strip_shortcodes().
 */
class AWPCP_StripShortcodesFunctionsTest extends AWPCP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();

        require_once AWPCP_DIR . '/frontend/placeholders.php';

        awpcp_tests_stub_shortcodes( [ 'gallery', 'embed' ] );
    }

    public function tearDown(): void {
        unset( $GLOBALS['shortcode_tags'] );

        parent::tearDown();
    }

    /**
     * @dataProvider escaped_shortcodes_provider
     */
    public function test_strip_shortcodes_removes_escaped_shortcodes( $content, $expected ) {
        $this->assertSame( $expected, awpcp_strip_shortcodes( $content ) );
    }

    public function escaped_shortcodes_provider() {
        return [
            'plain shortcode'          => [ 'Bike [gallery] for sale', 'Bike  for sale' ],
            'escaped once'             => [ '[[gallery]]', '' ],
            'escaped twice'            => [ '[[[gallery]]]', '' ],
            'mixed escaped shortcodes' => [ 'a [[gallery]] b [[[embed]]] c', 'a  b  c' ],
            'with attributes'          => [ '[[gallery ids="1,2"]]', '' ],
        ];
    }

    public function test_strip_shortcodes_keeps_ordinary_brackets() {
        $content = 'plain [brackets] and [[not-a-tag]] text';

        $this->assertSame( $content, awpcp_strip_shortcodes( $content ) );
    }

    public function test_strip_shortcodes_neutralizes_deeply_nested_shortcodes_quickly() {
        $depth   = 40000;
        $content = str_repeat( '[', $depth ) . 'gallery' . str_repeat( ']', $depth );

        $start  = microtime( true );
        $result = awpcp_strip_shortcodes( $content );

        $this->assertLessThan( 1, microtime( true ) - $start );
        $this->assertStringContainsString( '&#91;gallery&#93;', $result );
        $this->assertSame( 0, preg_match( '/' . get_shortcode_regex() . '/', $result ) );
    }
}
