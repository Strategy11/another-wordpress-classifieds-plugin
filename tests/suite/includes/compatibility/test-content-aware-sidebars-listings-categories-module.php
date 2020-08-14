<?php

// the class under test won't be defined unless CASModule class (from CAS plugin) exists.
class CASModule {

    protected $id;
    protected $name;

    public function __construct( $id, $name ) {
        $this->id = $id;
        $this->name = $name;
    }

    public function get_content( $args = array() ) {
        return $this->_get_content( $args );
    }
}

// necessary auxiliar classes won't be defined unless CAS_Walker_Checklist class (from CAS plugin)
// exists.
class CAS_Walker_Checklist {

    public function __construct( $tree_type, $fields ) {
    }
}

class AWPCP_TestContentAwareSidebarsListingsCategoriesModule extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        require_once( AWPCP_DIR . '/includes/compatibility/class-content-aware-sidebars-listings-categories-module.php' );
        require_once( AWPCP_DIR . '/includes/compatibility/class-content-aware-sidebars-categories-walker.php' );

        $this->ad_id = rand() + 1;
        $this->category_id = rand() + 1;

        $this->request = Phake::mock( 'AWPCP_Request' );
    }

    public function test_get_content() {
        $category_a = (object) array(
            'term_id' => rand() + 1,
            'name' => 'Category A',
        );

        $category_b = (object) array(
            'term_id' => rand() + 1,
            'name' => 'Category B',
        );

        $control_items = array(
            $category_a->term_id => $category_a->name,
            $category_b->term_id => $category_b->name
        );

        $categories = Phake::mock( 'AWPCP_Categories_Collection' );
        Phake::when( $categories )->get_all()->thenReturn( array( $category_a, $category_b ) );

        $module = new AWPCP_ContentAwareSidebarsListingsCategoriesModule( null, $categories, null, null );

        $this->assertEquals( $control_items, $module->get_content() );
    }

    public function test_get_content_with_parameters() {
        $args = array( 'include' => array( rand() + 1 ) );

        $categories = Phake::mock( 'AWPCP_Categories_Collection' );
        Phake::when( $categories )->find_categories( Phake::anyParameters() )->thenReturn( array() );

        $module = new AWPCP_ContentAwareSidebarsListingsCategoriesModule( null, $categories, null, null );
        $module->get_content( $args );

        Phake::verify( $categories )->find_categories( Phake::capture( $passed_args ) );

        $this->assertEquals( $args['include'], $passed_args['include'] );
    }

    public function test_in_context_if_showing_listings_from_a_specific_category() {
        Phake::when( $this->request )->get_category_id()->thenReturn( $this->category_id );

        $module = new AWPCP_ContentAwareSidebarsListingsCategoriesModule( null, null, null, null, $this->request );

        $this->assertTrue( $module->in_context() );
    }

    public function test_in_context_if_showing_a_single_listing() {
        Phake::when( $this->request )->get_ad_id()->thenReturn( $this->ad_id );

        $module = new AWPCP_ContentAwareSidebarsListingsCategoriesModule( null, null, null, null, $this->request );

        $this->assertTrue( $module->in_context() );
    }

    public function test_get_context_data_if_showing_listings_from_a_specific_category() {
        Phake::when( $this->request )->get_category_id()->thenReturn( $this->category_id );

        $module = new AWPCP_ContentAwareSidebarsListingsCategoriesModule( null, null, null, null, $this->request );

        $this->assertEquals( array( $this->category_id ), $module->get_context_data() );
    }

    public function test_get_context_data_if_showing_a_single_listing() {
        $listing = (object) array();

        $listing_renderer = Phake::mock( 'AWPCP_ListingRenderer' );
        $listings = Phake::mock( 'AWPCP_ListingsCollection' );

        Phake::when( $this->request )->get_ad_id()->thenReturn( $this->ad_id );
        Phake::when( $listing_renderer )->get_category_id->thenReturn( $this->category_id );
        Phake::when( $listings )->find_by_id( $this->ad_id )->thenReturn( $listing );

        $module = new AWPCP_ContentAwareSidebarsListingsCategoriesModule(
            $listing_renderer, null, $listings, null, $this->request
        );

        $this->assertEquals( array( $this->category_id ), $module->get_context_data() );
    }
}
