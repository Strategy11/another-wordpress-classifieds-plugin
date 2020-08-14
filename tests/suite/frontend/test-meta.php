<?php

/**
 * @group core
 */
class AWPCP_TestMeta extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->listing = awpcp_tests_create_empty_listing();

        wp_update_post( array(
            'ID' => $this->listing->ID,
            'post_title' => 'Test Ad',
        ) );

        $this->listings_collection = Phake::mock( 'AWPCP_ListingsCollection' );
        $this->title_builder = Phake::mock( 'AWPCP_PageTitleBuilder' );
        $this->meta_tags_generator = Phake::mock( 'AWPCP_MetaTagsGenerator' );
        $this->query = Phake::mock( 'AWPCP_Query' );
        $this->request = Phake::mock( 'AWPCP_Request' );

        Phake::when( $this->query )->is_single_listing_page->thenReturn( true );
        Phake::when( $this->request )->get_current_listing_id()->thenReturn( $this->listing->ID );
        Phake::when( $this->request )->get_category_id()->thenReturn( 1 );
        Phake::when( $this->listings_collection )->get( $this->listing->ID )->thenReturn( $this->listing );
    }

    public function test_opengraph_action_is_added_in_() {
        Phake::when( $this->request )->get_category_id()->thenReturn( null );

        $meta = new AWPCP_Meta(
            $this->listings_collection,
            $this->title_builder,
            $this->meta_tags_generator,
            $this->query,
            $this->request
        );
        $meta->configure();

        $this->assertTrue( $this->listing->ID > 0 );
        $this->assertEquals( 10, has_action( 'wp_head', array( $meta, 'opengraph' ) ) );
    }

    public function test_title_action_is_added_in_single_listing_page() {
        Phake::when( $this->request )->get_category_id()->thenReturn( null );

        $meta = new AWPCP_Meta( $this->listings_collection, $this->title_builder, $this->meta_tags_generator, $this->query, $this->request );
        $meta->configure();

        $this->assertEquals( 10, has_action( 'wp_title', array( $this->title_builder, 'build_title' ) ) );
    }

    public function test_title_action_is_added_in_browse_categories_page() {
        Phake::when( $this->request )->get_ad_id()->thenReturn( null );

        $meta = new AWPCP_Meta( $this->listings_collection, $this->title_builder, $this->meta_tags_generator, $this->query, $this->request );
        $meta->configure();

        $this->assertEquals( 10, has_action( 'wp_title', array( $this->title_builder, 'build_title' ) ) );
    }

    public function test_get_listing_metadata() {
        $meta = new AWPCP_Meta( $this->listings_collection, $this->title_builder, $this->meta_tags_generator, $this->query, $this->request );
        $meta->configure();

        $this->assertArrayHasKey( 'http://ogp.me/ns#type', $meta->get_listing_metadata() );
    }
}
