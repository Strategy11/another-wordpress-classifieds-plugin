<?php
/**
 * Unit tests for Categories Selector Component.
 *
 * @package AWPCP\Tests\Frontend\Components
 */

// phpcs:disable

/**
 * TestCase for Categories Switcher class.
 */
class AWPCP_Test_Categories_Switcher extends AWPCP_UnitTestCase {

    /**
     * Prepares common resources for this test case.
     */
    public function setup() {
        parent::setup();

        wp_insert_term( 'Test Category', 'awpcp_listing_category' );

        $this->query = Phake::mock( 'AWPCP_Query' );
        $this->settings = Phake::mock( 'AWPCP_Settings_API' );
        $this->request = Phake::mock( 'AWPCP_Request' );
    }

    /**
     * Test the render method when used outside of the Browse Listings and
     * Brwose Categories page.
     */
    public function test_render_outside_of_browse_listings_or_browse_categories_pages() {
        Phake::when( $this->query )->is_browse_listings_page->thenReturn( false );
        Phake::when( $this->query )->is_browse_categories_page->thenReturn( false );

        $browse_listings_page = array(
            'post_title' => 'Browse Listings',
            'post_name' => 'awpcp',
            'post_content' => '',
            'post_type' => 'page',
            'post_date_gmt' => current_time( 'mysql' ),
        );

        $browse_listings_page_id = wp_insert_post( $browse_listings_page );
        awpcp_update_plugin_page_id( 'browse-ads-page-name', $browse_listings_page_id );

        $selector = new AWPCP_Categories_Switcher( $this->query, $this->settings, $this->request );
        $content = $selector->render( array() );

        $this->assertContains( awpcp_get_browse_categories_page_url(), $content );
    }

    /**
     * Test the render method when used in the Browse Listings or Browse Categories
     * page.
     */
    public function test_render_in_browse_listings_or_browse_categories_pages() {
        Phake::when( $this->query )->is_browse_listings_page->thenReturn( true );

        $selector = new AWPCP_Categories_Switcher( $this->query, $this->settings, $this->request );
        $content = $selector->render( array() );

        $this->assertContains( awpcp_current_url(), $content );
    }

    /**
     * @see https://github.com/drodenbaugh/awpcp/issues/1368
     */
    public function test_category_selector_resets_page_number() {
        $results = rand() + 1;

        Phake::when( $this->request )->param( 'results' )->thenReturn( $results );
        Phake::when( $this->request )->post( 'results' )->thenReturn( $results );
        Phake::when( $this->request )->get( 'results' )->thenReturn( $results );

        $selector = new AWPCP_Categories_Switcher( $this->query, $this->settings, $this->request );
        $content = $selector->render( array() );

        $this->assertNotContains( sprintf( 'name="offset" value="%d"', $results ), $content );
        $this->assertContains( sprintf( 'name="offset" value="0"', $results ), $content );
    }

    /**
     * Test for modifications added in https://github.com/drodenbaugh/awpcp/issues/1934
     */
    public function test_rendered_html_includes_single_dropdown_css_class() {
        // Execution
        $selector = new AWPCP_Categories_Selector_Component( $this->query, $this->settings, $this->request );
        $output = $selector->render( array() );

        // Verification
        $this->assertContains( 'awpcp-single-dropdown-category-selector-container', $output );
    }
}
