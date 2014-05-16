<?php

define( 'AWPCP_CAS_LISTINGS_CATEGORIES_MODULE', 'awpcp_listings_categories' );

function awpcp_register_content_aware_sidebars_listings_categories_module( $modules ) {
    if ( class_exists( 'AWPCP_ContentAwareSidebarsListingsCategoriesModule' ) ) {
        $modules[ AWPCP_CAS_LISTINGS_CATEGORIES_MODULE ] = 'AWPCP_ContentAwareSidebarsListingsCategoriesModule';
    }

    return $modules;
}

if ( class_exists( 'CASModule' ) ) {

class AWPCP_ContentAwareSidebarsListingsCategoriesModule extends CASModule {

    private $categories;
    private $listings;
    private $request;

    public function __construct( $categories = null, $listings = null, $request = null ) {
        parent::__construct( AWPCP_CAS_LISTINGS_CATEGORIES_MODULE, __( 'Categories (AWPCP)', 'AWPCP' ) );

        if ( is_null( $categories ) ) {
            $this->categories = awpcp_categories_collection();
        } else {
            $this->categories = $categories;
        }

        if ( is_null( $listings ) ) {
            $this->listings = awpcp_listings_collection();
        } else {
            $this->listings = $listings;
        }

        if ( is_null( $request ) ) {
            $this->request = awpcp_request();
        } else {
            $this->request = $request;
        }
    }

    protected function _get_content( $args = array() ) {
        $all_categories = $this->categories->get_all();

        $control_items = array();
        foreach ( $all_categories as $category ) {
            $control_items[ $category->id ] = $category->name;
        }

        return $control_items;
    }

    public function in_context() {
        $category_id = $this->request->get_category_id();
        $ad_id = $this->request->get_ad_id();

        return $category_id > 0 || $ad_id > 0;
    }

    public function get_context_data() {
        $category_id = $this->request->get_category_id();

        if ( $category_id > 0 ) {
            return array( $category_id );
        }

        $ad_id = $this->request->get_ad_id();

        if ( $ad_id > 0 ) {
            $ad = $this->listings->find_by_id( $ad_id );
            return is_null( $ad ) ? array() : array( $ad->ad_category_id );
        }

        return array();
    }
}

}
