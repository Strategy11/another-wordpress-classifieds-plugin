<?php

/**
 * @group core
 * @group broken
 */
class AWPCP_TestMenuItemsFunctions extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->pause_filter( 'awpcp_menu_items' );

        awpcp()->settings->update_option( 'onlyadmincanplaceads', false );
    }

    public function test_get_menu_items() {
        $this->enable_basic_menu_items();

        $params = array(
            'show-create-listing-button' => true,
            'show-edit-listing-button' => true,
            'show-browse-listings-button' => true,
            'show-search-listings-button' => true,
        );

        $items = awpcp_get_menu_items( $params );

        $this->assertTrue( isset( $items['post-listing'] ) );
        $this->assertTrue( isset( $items['edit-listing'] ), 'Edit Listing menu item is present.' );
        $this->assertTrue( isset( $items['browse-listings'] ) );
        $this->assertTrue( isset( $items['search-listings'] ) );
    }

    private function enable_basic_menu_items() {
        awpcp()->settings->update_option( 'show-menu-item-place-ad', true );
        awpcp()->settings->update_option( 'show-menu-item-edit-ad', true );
        awpcp()->settings->update_option( 'show-menu-item-browse-ads', true );
        awpcp()->settings->update_option( 'show-menu-item-search-ads', true );

        // Edit Listing menu item is not shown unless a listing is being
        // displayed or Require Registration is Off.
        awpcp()->settings->update_option( 'requireuserregistration', false );
    }

    public function test_edit_listing_menu_item_is_not_shown_when_require_registration_is_on_and_no_listing_is_being_displayed() {
        awpcp()->settings->update_option( 'show-menu-item-edit-ad', true );
        awpcp()->settings->update_option( 'requireuserregistration', true );

        $_REQUEST['adid'] = null;
        $_REQUEST['id'] = null;
        set_query_var( 'id', null );

        $params = array(
            'show-create-listing-button' => null,
            'show-edit-listing-button' => true,
            'show-browse-listings-button' => null,
            'show-search-listings-button' => null,
        );

        $items = awpcp_get_menu_items( $params );

        $this->assertTrue( empty( $items['edit-listing'] ), 'Edit Listing menu item is not present if Require Registration is On and no listing is being displayed.' );
    }

    public function test_get_menu_items_when_only_admin_can_place_ads() {
        $this->enable_basic_menu_items();

        awpcp()->settings->update_option( 'onlyadmincanplaceads', true );

        $this->login_as_administrator();

        $params = array(
            'show-create-listing-button' => true,
            'show-edit-listing-button' => true,
            'show-browse-listings-button' => true,
            'show-search-listings-button' => true,
        );

        $items = awpcp_get_menu_items( $params );

        $this->assertTrue( isset( $items['post-listing'] ) );
        $this->assertTrue( isset( $items['edit-listing'] ) );
        $this->assertTrue( isset( $items['browse-listings'] ) );
        $this->assertTrue( isset( $items['search-listings'] ) );

        $this->login_as_subscriber();

        $items = awpcp_get_menu_items( $params );

        $this->assertFalse( isset( $items['post-listing'] ) );
        $this->assertFalse( isset( $items['edit-listing'] ) );
        $this->assertTrue( isset( $items['browse-listings'] ) );
        $this->assertTrue( isset( $items['search-listings'] ) );
    }

    public function test_view_categories_menu_item_is_generated_when_viewing_browse_listings_page() {
        /* preparation */
        awpcp_create_pages( 'another-wordpress-classifieds-plugin' );

        wp_update_post( array(
            'ID' => awpcp_get_page_id_by_ref( 'browse-ads-page-name' ),
            'post_name' => 'custom-slug',
        ) );

        $this->enable_basic_menu_items();
        $this->go_to( awpcp_get_page_url( 'browse-ads-page-name' ) );

        /* execution */
        $params = array(
            'show-create-listing-button' => true,
            'show-edit-listing-button' => true,
            'show-browse-listings-button' => true,
            'show-search-listings-button' => true,
        );

        $items = awpcp_get_menu_items( $params );

        /* verification */
        $this->assertTrue( isset( $items['browse-listings'] ) );
        $this->assertEquals( get_awpcp_option( 'view-categories-page-name' ), $items['browse-listings']['title'] );
    }
}
