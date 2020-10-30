<?php
/**
 * @package AWPCP\Tests\Listings
 */

use Brain\Monkey\Functions;
use Brain\Monkey\Filters;

/**
 * @since 4.0.0
 */
class AWPCP_ListingsContentRendererTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        parent::setup();

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

        Functions\when( 'awpcp' )->justReturn( $awpcp );
        Functions\when( 'awpcp_maybe_add_thickbox' )->justReturn( null );
        Functions\when( 'awpcp_maybe_enqueue_font_awesome_style' )->justReturn( null );
        Functions\when( 'wp_create_nonce' )->justReturn( '' );
        Functions\when( 'wp_enqueue_script' )->justReturn( null );
        Functions\when( 'awpcp_get_listing_single_view_layout' )->justReturn( '' );
        Functions\when( 'awpcp_do_placeholders' )->justReturn( '' );

        Filters\expectApplied( 'awpcp-content-before-listing-page' )
            ->once()
            ->andReturn( $content_with_percentage_character );

        $awpcp->js->shouldReceive( 'set' );
        $awpcp->js->shouldReceive( 'localize' );

		$this->markTestSkipped( 'Failing. Needs work' );

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
