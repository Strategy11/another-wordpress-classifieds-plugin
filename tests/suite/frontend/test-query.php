<?php
/**
 * @package AWPCP\Tests\Frontend
 */

use Brain\Monkey\Functions;

class AWPCP_Test_Query extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.4
     */
    public function setup() {
        parent::setup();

        $this->listing_post_type = 'awpcp_listing';
    }

    public function test_is_browse_listings_page() {
        $this->markTestIncomplete();

        /* preparation */
        awpcp_create_pages( 'another-wordpress-classifieds-plugin' );

        wp_update_post( array(
            'ID' => awpcp_get_page_id_by_ref( 'browse-ads-page-name' ),
            'post_name' => 'custom-slug',
        ) );

        $this->go_to( awpcp_get_page_url( 'browse-ads-page-name' ) );

        $query = new AWPCP_Query();

        $this->assertTrue( $query->is_browse_listings_page() );
    }

    public function test_is_page_that_has_shortcode() {
        $this->markTestIncomplete();

        $shortcode = 'my_shortcode';

        add_shortcode( $shortcode, '__return_false' );

        $post_id = wp_insert_post( array(
            'post_title' => 'Test Page',
            'post_content' => "[$shortcode]",
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_date' => current_time( 'mysql' ),
            'post_date_gmt' => current_time( 'mysql', true ),
        ) );

        $this->assertNotFalse( $post_id );
        $this->assertNotInstanceOf( 'WP_Error', $post_id );
        $this->assertTrue( is_numeric( $post_id ) );

        $this->go_to( get_permalink( $post_id ) );

        $query = new AWPCP_Query();
        $has_shortcode = $query->is_page_that_has_shortcode( $shortcode );

        $this->assertTrue( $has_shortcode );
    }

    public function test_place_listing_page_is_a_page_that_accepts_payments() {
        $this->markTestIncomplete();

        $this->verify_page_with_content_is_page_that_accepts_payments( '[AWPCPPLACEAD]' );
    }

    private function verify_page_with_content_is_page_that_accepts_payments( $content ) {
        $page_id = $this->factory->post->create( array( 'post_content' => $content, 'post_type' => 'page' ) );

        $this->go_to( get_permalink( $page_id ) );

        $query = new AWPCP_Query();

        $this->assertTrue( $query->is_page_that_accepts_payments() );
    }

    public function test_renew_listing_page_is_a_page_that_accepts_payments() {
        $this->markTestIncomplete();

        $this->verify_page_with_content_is_page_that_accepts_payments( '[AWPCP-RENEW-AD]' );
    }

    public function test_buy_subscription_page_is_a_page_that_accepts_payments() {
        $this->markTestIncomplete();

        add_shortcode( 'AWPCP-BUY-SUBSCRIPTION', '__return_false' );
        $this->verify_page_with_content_is_page_that_accepts_payments( '[AWPCP-BUY-SUBSCRIPTION]' );
    }

    public function test_buy_credits_page_is_a_page_that_accepts_payments() {
        $this->markTestIncomplete();

        $this->verify_page_with_content_is_page_that_accepts_payments( '[AWPCPBUYCREDITS]' );
    }

    /**
     * @since 4.0.4
     */
    public function test_is_search_listings_page() {
        global $wp_the_query;

        $page = (object) [ 'post_content' => 'something' ];

        $wp_the_query = Mockery::mock( 'WP_Query' );

        $wp_the_query->shouldReceive( 'is_page' )->andReturn( true );
        $wp_the_query->shouldReceive( 'get_queried_object' )->andReturn( $page );

        Functions\expect( 'has_shortcode' )
            ->with( $page->post_content, 'AWPCPSEARCHADS' )
            ->andReturn( true );

        // Verification.
        $this->assertTrue( $this->get_test_subject()->is_search_listings_page() );
    }

    /**
     * @since 4.0.4
     */
    private function get_test_subject() {
        return new AWPCP_Query( $this->listing_post_type );
    }
}
