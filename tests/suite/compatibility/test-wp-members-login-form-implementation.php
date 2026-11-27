<?php
/**
 * @package AWPCP\Tests\Compatibility
 */

use Brain\Monkey\Functions;

/**
 * Tests for the WP-Members login form implementation.
 */
class AWPCP_WP_Members_Login_Form_ImplementationTest extends AWPCP_UnitTestCase {

    /**
     * @since x.x
     */
    public function test_render_does_not_let_the_redirect_url_inject_shortcodes() {
        $shortcode = null;

        Functions\expect( 'do_shortcode' )->once()->with( $this->capture( $shortcode ) )->andReturn( '' );

        $implementation = new AWPCP_WP_Members_Login_Form_Implementation();
        $implementation->render( 'https://example.org/reply-to-ad/?x=][injected][' );

        $this->assertSame( 1, substr_count( $shortcode, '[' ) );
        $this->assertSame( 1, substr_count( $shortcode, ']' ) );
        $this->assertStringContainsString( 'redirect_to="https://example.org/reply-to-ad/?x=%5D%5Binjected%5D%5B"', $shortcode );
    }
}
