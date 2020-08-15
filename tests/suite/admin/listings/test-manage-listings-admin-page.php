<?php

class AWPCP_Test_Manage_Listings_Admin_Page extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->markTestSkipped( 'TODO: Where is the AWPCP_Admin_Listings class?' );

        $this->attachments = Phake::mock( 'AWPCP_Attachments_Collection' );
        $this->listings_logic = Phake::mock( 'AWPCP_ListingsAPI' );
        $this->listing_renderer = Phake::mock( 'AWPCP_ListingRenderer' );
        $this->listings = Phake::mock( 'AWPCP_ListingsCollection' );
        $this->settings = Phake::mock( 'AWPCP_Settings_API' );
        $this->template_renderer = Phake::mock( 'AWPCP_Template_Renderer' );
    }

    public function test_constructor() {
        $page = awpcp_manage_listings_admin_page();
        $this->assertInstanceOf( 'AWPCP_Admin_Listings', $page );
    }

    public function test_actions() {
        $listing = awpcp_tests_create_listing();

        $page = new AWPCP_Admin_Listings( null, null, $this->attachments, null, $this->listing_renderer, null, null, null );

        $actions = $page->actions( $listing );

        foreach ( $actions as $action ) {
            $this->assertContains( "id={$listing->ID}", $action[1] );
        }
    }

    public function test_actions_generates_the_enable_listing_action() {
        $this->login_as_administrator();

        $listing = awpcp_tests_create_listing();

        Phake::when( $this->listing_renderer )->is_disabled->thenReturn( true );

        $page = new AWPCP_Admin_Listings( null, null, $this->attachments, null, $this->listing_renderer, null, null, null );
        $actions = $page->actions( $listing );

        $this->assertContains( 'enable', array_keys( $actions ) );
    }

    public function test_view_ad_for_administrators() {
        $this->login_as_administrator();

        $listing = awpcp_tests_create_listing();
        $category_name = 'Test Category';

        Phake::when( $this->listing_renderer )->get_category_name->thenReturn( $category_name );

        $page = new AWPCP_Admin_Listings( null, null, $this->attachments, null, $this->listing_renderer, null, null, null );
        $content = $page->view_ad( $listing );

        $this->assertContains( $category_name, $content );
    }

    public function test_enable_ad_action() {
        $listing = awpcp_tests_create_listing();

        Phake::when( $this->listings_logic )->enable_listing( $listing )->thenReturn( true );
        Phake::when( $this->settings )->get_option( 'send-ad-enabled-email' )->thenReturn( true );

        $page = new AWPCP_Admin_Listings( null, null, null, $this->listings_logic, null, null, $this->settings, null );
        $result = $page->enable_ad_action( $listing );

        $this->assertTrue( $result );
    }

    public function test_send_access_key() {
        // to avoid undefined index error while calling home_url() in email-send-ad-access-key.tpl.php
        $_SERVER['QUERY_STRING'] = 'whatever';

        $listing = awpcp_tests_create_listing();

        Phake::when( $this->listings )->get->thenReturn( $listing );

        $page = new AWPCP_Admin_Listings( null, null, null, null, $this->listing_renderer, $this->listings, null, null );
        $page->id = $listing->ID;

        $content = $page->send_access_key();

        $this->assertNotEmpty( $content );
    }

    public function test_manage_images() {
        $listing = awpcp_tests_create_listing();

        Phake::when( $this->attachments )->find_attachments->thenReturn( array() );

        $page = new AWPCP_Admin_Listings( null, null, $this->attachments, null, null, null, null, $this->template_renderer );
        $page->id = $listing->ID;

        $content = $page->manage_images( $listing );

        Phake::verify( $this->attachments )->find_attachments( Phake::capture( $query ) );
        Phake::verify( $this->template_renderer )->render_template( Phake::capture( $template ), Phake::capture( $params ) );

        $this->assertEquals( $listing->ID, $query['post_parent'] );
        $this->assertEquals( $listing->ID, $params['media_uploader_configuration']['listing_id'] );
        $this->assertEquals(
            wp_create_nonce( 'awpcp-manage-listing-media-' . $listing->ID ),
            $params['media_manager_configuration']['nonce']
        );
        $this->assertEquals(
            wp_create_nonce( 'awpcp-upload-media-for-listing-' . $listing->ID ),
            $params['media_uploader_configuration']['nonce']
        );
        $this->assertContains( (string) $listing->ID, $params['urls']['view-listing'] );
    }
}
