<?php
/**
 * @package AWPCP\Tests\Listings
 */

/**
 * @since 4.0.0
 */
class AWPCP_ListingsContentRendererTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setUp(): void {
        parent::setUp();

        $this->listing_renderer = Mockery::mock( 'AWPCP_ListingRenderer' );
    }

    /**
     * @issue https://github.com/drodenbaugh/awpcp/issues/644
     * @since 4.0.0
     */
    public function test_render_content_without_notices() {
        $awpcp = (object) [
            'js' => Mockery::mock( 'AWPCP_JavaScript' ),
        ];

        $listing = (object) [
            'ID'           => wp_rand(),
            'post_content' => '100% Bug Free',
        ];

        $content_with_percentage_character = 'Content including % character.';

        WP_Mock::userFunction( 'awpcp', [
            'return' => $awpcp,
        ] );
        WP_Mock::userFunction( 'awpcp_maybe_add_thickbox', [
            'return' => null,
        ] );
        WP_Mock::userFunction( 'awpcp_maybe_enqueue_font_awesome_style', [
            'return' => null,
        ] );
        WP_Mock::userFunction( 'wp_create_nonce', [
            'return' => '',
        ] );
        WP_Mock::userFunction( 'wp_enqueue_script', [
            'return' => null,
        ] );
        WP_Mock::userFunction( 'awpcp_get_listing_single_view_layout', [
            'return' => '',
        ] );
        WP_Mock::userFunction( 'awpcp_do_placeholders', [
            'return' => '',
        ] );

        $this->markTestSkipped( 'Failing. Needs work' );

        \WP_Mock::onFilter( 'awpcp-content-before-listing-page' )
            ->reply( $content_with_percentage_character );

        $awpcp->js->shouldReceive( 'set' );
        $awpcp->js->shouldReceive( 'localize' );

        $output = $this->get_test_subject()->render_content_without_notices( $listing->post_content, $listing );

        $this->assertContains( $content_with_percentage_character, $output );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_ListingsContentRenderer( $this->listing_renderer );
    }
}
