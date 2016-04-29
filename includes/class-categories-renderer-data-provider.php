<?php

function awpcp_categories_renderer_data_provider() {
    return new AWPCP_Categories_Renderer_Data_Provider(
        awpcp_categories_collection()
    );
}

class AWPCP_Categories_Renderer_Data_Provider {

    private $categories;

    public function __construct( $categories ) {
        $this->categories = $categories;
    }

    public function get_categories( $params ) {
        $selected_categories = array();

        if ( ! is_null( $params['category_id'] ) && $params['show_children_categories'] ) {
            $categories_found = $this->categories->get_categories_hierarchy( $params['category_id'] );
        } else if ( ! is_null( $params['category_id'] ) ) {
            $categories_found = $this->categories->find( array( 'id' => $params['category_id'] ) );
        } else if ( is_null( $params['parent_category_id'] ) && $params['show_children_categories'] ) {
            $categories_found = $this->categories->get_all();
        } else if ( is_null( $params['parent_category_id'] ) ) {
            $categories_found = $this->categories->find_by_parent_id( 0 );
        } else {
            $categories_found = $this->categories->find_by_parent_id( $params['parent_category_id'] );
        }

        foreach ( $categories_found as $category ) {
            $category->listings_count = total_ads_in_cat( $category->id );
            if ( $params['show_empty_categories'] || $category->listings_count > 0 ) {
                $selected_categories[] = $category;
            }
        }

        return $selected_categories;
    }
}
