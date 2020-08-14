<?php

class AWPCP_Test_MashShare_Plugin_Integration  extends AWPCP_UnitTestCase {

    public function test_load() {
        global $wp_filter;

        unset( $wp_filter['template_redirect'] );

        $integration = new AWPCP_MashShare_Plugin_Integration( null );
        $integration->load();

        $handlers = array_values( $wp_filter['template_redirect'][10] );

        $this->assertEquals( $integration, $handlers[0]['function'][0] );
        $this->assertEquals( 'maybe_remove_mashshare_opengraph_tags', $handlers[0]['function'][1] );
    }

    public function test_maybe_remove_mashshare_opengraph_tags_removes_hook_in_listing_pages() {
        global $wp_filter;

        unset( $wp_filter['wp_head'] );
        add_action( 'wp_head', 'mashsb_meta_tags_init', 1 );

        $query = Phake::mock( 'AWPCP_Query' );

        Phake::when( $query )->is_single_listing_page->thenReturn( true );

        $integration = new AWPCP_MashShare_Plugin_Integration( $query );
        $integration->maybe_remove_mashshare_opengraph_tags();

        $this->assertTrue( ! isset( $wp_filter['wp_head'] ) || empty( $wp_filter['wp_head'] ) );
    }

    public function test_maybe_remove_mashshare_opengraph_tags_does_not_removes_hook_in_other_pages() {
        global $wp_filter;

        unset( $wp_filter['wp_head'] );
        add_action( 'wp_head', 'mashsb_meta_tags_init', 1 );

        $query = Phake::mock( 'AWPCP_Query' );

        Phake::when( $query )->is_single_listing_page->thenReturn( false );

        $integration = new AWPCP_MashShare_Plugin_Integration( $query );
        $integration->maybe_remove_mashshare_opengraph_tags();

        if ( is_array( $wp_filter['wp_head'] ) ) {
            $this->assertEquals( 1, count( $wp_filter['wp_head'] ) );
        } else {
            $this->assertEquals( 1, count( $wp_filter['wp_head']->callbacks ) );
        }
    }
}
