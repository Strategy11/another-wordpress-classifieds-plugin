<?php

/**
 * @group core
 */
class AWPCP_TestPageTitleBuilder extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->listing = (object) array( 'ID' => rand() + 1, 'post_title' => 'Test Ad' );
        $this->listing_renderer = Phake::mock( 'AWPCP_ListingRenderer' );
        $this->categories_collection = Phake::mock( 'AWPCP_Categories_Collection' );

        Phake::when( $this->listing_renderer )->get_listing_title->thenReturn( $this->listing->post_title );
        Phake::when( $this->listing_renderer )->get_regions->thenReturn( array() );
    }

    public function test_title_in_category_page() {
        global $wpdb;

        $category_name = 'Default Category';
        $category = wp_insert_term( $category_name, 'awpcp_listing_category' );

        $query = $this->createMock( 'WP_Query' );
        $query->expects( $this->any( ) )
              ->method( 'is_page' )
              ->will( $this->onConsecutiveCalls( false, true, false, true ) );

        $GLOBALS[ 'wp_the_query' ] = $query;

        Phake::when( $this->categories_collection )->get->thenReturn( get_term( $category['term_id'], 'awpcp_listing_category' ) );

        $title_builder = new AWPCP_PageTitleBuilder( null, $this->categories_collection );
        $title_builder->set_current_category_id( $category['term_id'] );

        $this->assertEquals( "Test - $category_name", $title_builder->build_title( 'Test', '-', 'left' ) );
        $this->assertEquals( "$category_name - Test", $title_builder->build_title( 'Test', '-', 'right' ) );
    }

    public function test_title_always_returns_the_same_title() {
        $page_id = awpcp_get_page_id_by_ref( 'show-ads-page-name' );

        $query = Phake::mock( 'WP_Query' );
        Phake::when( $query )->is_page( $page_id )->thenReturn( true );
        $GLOBALS['wp_the_query'] = $query;

        $title_builder = new AWPCP_PageTitleBuilder( $this->listing_renderer, null );
        $title_builder->set_current_listing( $this->listing );

        $first_title = $title_builder->build_title( '' );
        $texturized_title = wptexturize( $first_title );
        $second_title = $title_builder->build_title( $texturized_title );
        $third_title = $title_builder->build_title( $first_title );
        $fourth_title = $title_builder->build_title( '' );

        $this->assertContains( $this->listing->post_title, $first_title );

        $this->assertEquals( $texturized_title, $second_title );
        $this->assertEquals( $first_title, $third_title );
        $this->assertEquals( $third_title, $fourth_title );
    }

    public function test_title_does_not_contain_page_title_twice() {
        $page_id = awpcp_get_page_id_by_ref( 'show-ads-page-name' );

        $query = Phake::mock( 'WP_Query' );
        Phake::when( $query )->is_page( $page_id )->thenReturn( true );
        $GLOBALS['wp_the_query'] = $query;

        $title_builder = new AWPCP_PageTitleBuilder( $this->listing_renderer, null );
        $title_builder->set_current_listing( $this->listing );

        $first_title = $title_builder->build_title( '' );
        $texturized_title = wptexturize( $first_title );
        $second_title = $title_builder->build_title( $texturized_title );

        str_replace( $texturized_title, '', $second_title, $replacements );
        $this->assertEquals( 1, $replacements );
    }

    /**
     * @since 3.6
     */
    public function test_build_single_post_title_does_not_trigger_empty_needle_warning() {
        $title_builder = $this->prepare_page_title_builder_for_empty_needle_warning_test();
        $title_builder->build_single_post_title( '' );

        $this->assertTrue( true );
    }

    /**
     * @since 3.6
     */
    private function prepare_page_title_builder_for_empty_needle_warning_test() {
        $non_existent_category_id = PHP_INT_MAX;

        Phake::when( $this->categories_collection )->get( $non_existent_category_id )->thenThrow( new AWPCP_Exception() );

        $title_builder = new AWPCP_PageTitleBuilder( null, $this->categories_collection );
        $title_builder->set_current_category_id( $non_existent_category_id );

        return $title_builder;
    }

    /**
     * @since 3.6
     */
    public function test_build_title_does_not_trigger_empty_needle_warning() {
        $title_builder = $this->prepare_page_title_builder_for_empty_needle_warning_test();
        $title_builder->build_title( '' );

        $this->assertTrue( true );
    }
}
