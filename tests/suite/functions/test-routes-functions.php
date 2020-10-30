<?php

use Brain\Monkey\Functions;

class AWPCP_TestRouteFunctions extends AWPCP_UnitTestCase {

    public function test_get_email_verification_url() {
        update_option( 'permalink_structure', '' );

        $listing_id = rand() + 1;
        $url = awpcp_get_email_verification_url( $listing_id );

        $this->assertContains( build_query( array( 'awpcpx' => 1 ) ), $url );
        $this->assertContains( build_query( array( 'awpcp-module' => 'listings' ) ), $url );
        $this->assertContains( build_query( array( 'awpcp-action' => 'verify' ) ), $url );
        $this->assertContains( build_query( array( 'awpcp-ad' => $listing_id ) ), $url );
    }

    public function test_get_view_categories_url_when_permalink_structure_includes_index_dot_php() {
        global $wp_rewrite;

        $wp_rewrite->set_permalink_structure( "/index.php/%year%/%monthnum%/%day%/%postname%/" );

        $main_page = array(
            'post_title' => 'AWPCP',
            'post_name' => 'awpcp',
            'post_content' => '',
            'post_type' => 'page',
            'post_date_gmt' => current_time( 'mysql' ),
            'post_status' => 'publish',
        );

        $main_page_id = wp_insert_post( $main_page );
        awpcp_update_plugin_page_id( 'main-page-name', $main_page_id );

        $url = awpcp_get_view_categories_url();

        $this->assertContains( "/index.php/{$main_page['post_name']}/view-categories", $url );
    }

    // public function test_get_edit_listing_url_b() {
    //     $listing = awpcp_tests_create_listing();

    //     awpcp()->settings->set_or_update_option( 'requireuserregistration', true );
    //     $url = awpcp_get_edit_listing_url( $listing );
    //     $this->assertContains( (string) $listing->ID, $url );

    //     Functions::expect( 'awpcp_get_edit_listing_url' )->once()->with( $listing );

    //     awpcp()->settings->set_or_update_option( 'requireuserregistration', true );
    //     awpcp()->settings->set_or_update_option( 'seofriendlyurls', true );
    //     $GLOBALS['wp_rewrite']->set_permalink_structure( '/%postname%/' );
    //     $url = awpcp_get_edit_listing_url( $listing );

    //     $this->assertTrue( true );
    // }

    public function test_get_edit_listing_url_for_non_privileged_users_when_registrstion_is_on() {
        $listing = awpcp_tests_create_listing();

        awpcp()->settings->set_or_update_option( 'requireuserregistration', true );

        Functions::expect( 'awpcp_get_edit_listing_direct_url' )->once()->with( $listing );

        /* Execution */
        awpcp_get_edit_listing_url( $listing );

        /* Verification */
        $this->assertTrue( true );
    }

    public function test_get_edit_listing_url_for_non_privileged_users_when_registration_is_off() {
        $listing = awpcp_tests_create_listing();

        awpcp()->settings->set_or_update_option( 'requireuserregistration', false );

        Functions::expect( 'awpcp_get_edit_listing_generic_url' )->once()->withNoArgs();

        /* Execution */
        awpcp_get_edit_listing_url( $listing );

        /* Verification */
        $this->assertTrue( true );
    }

    public function test_get_edit_listing_direct_url_when_user_panel_is_off() {
        $listing = awpcp_tests_create_listing();

        Functions::expect( 'awpcp_get_edit_listing_page_url_with_listing_id' )->once()->with( $listing );

        /* Execution */
        $url = awpcp_get_edit_listing_direct_url( $listing );

        /* Verification */
        $this->assertTrue( true );
    }

    public function test_get_edit_listing_page_url_with_listing_id_when_firendly_urls_are_enabled() {
        $listing = awpcp_tests_create_listing();

        Functions::when( 'awpcp_get_page_id_by_ref' )->justReturn( rand() + 1 );
        Functions::when( 'get_permalink' )->justReturn( 'http://example.org/%pagename%/' );
        Functions::when( 'get_page_uri' )->justReturn( 'plugin-page' );

        Functions::expect( 'get_awpcp_option' )->once()->with( 'seofriendlyurls' )->andReturn( true );
        Functions::expect( 'get_option' )->once()->with( 'permalink_structure' )->andReturn( '/%postname%/' );

        /* Execution */
        $url = awpcp_get_edit_listing_page_url_with_listing_id( $listing );

        /* Verification */
        $this->assertEquals( "http://example.org/plugin-page/{$listing->ID}/", $url );
    }

    public function test_get_edit_listing_page_url_with_listing_id_when_firendly_urls_are_disabled() {
        $listing = awpcp_tests_create_listing();

        Functions::when( 'awpcp_get_page_url' )->justReturn( 'http://example.org/plugin-page/' );

        Functions::expect( 'get_awpcp_option' )->once()->with( 'seofriendlyurls' )->andReturn( false );

        /* Execution */
        $url = awpcp_get_edit_listing_page_url_with_listing_id( $listing );

        /* Verification */
        $this->assertEquals( "http://example.org/plugin-page/?id={$listing->ID}", $url );
    }

    public function test_url_browsecategory_with_permalinks() {
        global $wp_rewrite;

        $parent_page = wp_insert_post( array(
            'post_type' => 'page',
            'post_title' => 'Main Page',
            'post_name' => 'main-page',
            'post_date' => '2017-03-01 00:00:00',
            'post_date_gmt' => '2017-03-01 00:00:00'
        ) );

        $sub_page = wp_insert_post( array(
            'post_type' => 'page',
            'post_title' => 'Sub Page',
            'post_name' => 'sub-page',
            'post_parent' => $parent_page,
            'post_date' => '2017-03-01 00:00:00',
            'post_date_gmt' => '2017-03-01 00:00:00',
        ) );

        awpcp_update_plugin_pages_info( array( 'browse-ads-page-name' => array( 'page_id' => $sub_page ) ) );
        awpcp()->settings->set_or_update_option( 'seofriendlyurls', true );

        $wp_rewrite->set_permalink_structure( '/%year%/%monthnum%/%postname%/' );

        delete_option( 'show_on_front' );
        delete_option( 'page_on_front' );

        $category = (object) array( 'term_id' => rand() + 1, 'name' => 'Foo' );

        $url = url_browsecategory( $category );

        $this->assertEquals( "http://example.org/main-page/sub-page/{$category->term_id}/foo/", $url );
    }

    public function test_url_browsecategory_without_permalinks() {
        global $wp_rewrite;

        $parent_page = wp_insert_post( array(
            'post_type' => 'page',
            'post_title' => 'Main Page',
            'post_name' => 'main-page',
            'post_date' => '2017-03-01 00:00:00',
            'post_date_gmt' => '2017-03-01 00:00:00'
        ) );

        $sub_page = wp_insert_post( array(
            'post_type' => 'page',
            'post_title' => 'Sub Page',
            'post_name' => 'sub-page',
            'post_parent' => $parent_page,
            'post_date' => '2017-03-01 00:00:00',
            'post_date_gmt' => '2017-03-01 00:00:00',
        ) );

        awpcp_update_plugin_pages_info( array( 'browse-ads-page-name' => array( 'page_id' => $sub_page ) ) );
        awpcp()->settings->set_or_update_option( 'seofriendlyurls', false );

        $wp_rewrite->set_permalink_structure( '' );

        delete_option( 'show_on_front' );
        delete_option( 'page_on_front' );

        $category = (object) array( 'term_id' => rand() + 1, 'name' => 'Foo' );

        $url = url_browsecategory( $category );

        $this->assertEquals( "http://example.org/?page_id={$sub_page}&category_id={$category->term_id}", $url );
    }

    public function test_url_browsecategory_when_browse_listings_page_is_the_home_page() {
        global $wp_rewrite;

        $parent_page = wp_insert_post( array(
            'post_type' => 'page',
            'post_title' => 'Main Page',
            'post_name' => 'main-page',
            'post_date' => '2017-03-01 00:00:00',
            'post_date_gmt' => '2017-03-01 00:00:00'
        ) );

        $sub_page = wp_insert_post( array(
            'post_type' => 'page',
            'post_title' => 'Sub Page',
            'post_name' => 'sub-page',
            'post_parent' => $parent_page,
            'post_date' => '2017-03-01 00:00:00',
            'post_date_gmt' => '2017-03-01 00:00:00',
        ) );

        awpcp_update_plugin_pages_info( array( 'browse-ads-page-name' => array( 'page_id' => $sub_page ) ) );
        awpcp()->settings->set_or_update_option( 'seofriendlyurls', true );

        $wp_rewrite->set_permalink_structure( '/%year%/%monthnum%/%postname%/' );

        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $sub_page );

        $category = (object) array( 'term_id' => rand() + 1, 'name' => 'Foo' );

        $url = url_browsecategory( $category );

        $this->assertEquals( "http://example.org/main-page/sub-page/{$category->term_id}/foo/", $url );
    }
}
