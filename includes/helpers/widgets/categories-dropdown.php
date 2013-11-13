<?php

class AWPCP_CategoriesDropdown {

    private function get_categories($parent_id=0) {
        global $wpdb;

        $categories = AWPCP_Category::query( array(
            'where' => $wpdb->prepare( "category_parent_id = %d AND category_name <> ''", $parent_id ),
            'orderby' => 'category_order ASC, category_name',
            'order' => 'ASC',
        ) );

        return $categories;
    }

    private function get_all_categories() {
        $categories['root'] = $this->get_categories();

        foreach ( $categories['root'] as $category ) {
            $categories[ $category->id ] = $this->get_categories( $category->id );
        }

        return $categories;
    }

    private function get_category_parents( $category ) {
        if ( empty($category) ) return array();

        $categories = AWPCP_Category::query();
        $hierarchy = array();

        foreach ( $categories as $item ) {
            $hierarchy[ $item->id ] = $item->parent;
        }

        $parent = $category;
        $chain = array();

        do {
            $chain[] = $parent;
            $parent = $hierarchy[ $parent ];
        } while ( $parent != 0 );

        return array_reverse( $chain );
    }

    public function render($selected=null, $name='category', $label=null, $required=true) {
        $label = is_null( $label ) ? __( 'Ad Category', 'AWPCP' ) : $label;
        $use_multiple_dropdowns = get_awpcp_option( 'use-multiple-category-dropdowns' );

        $categories = $this->get_all_categories();
        $chain = $this->get_category_parents( $selected );

        // export categories list to JavaScript, but don't replace
        // an existing categories list
        awpcp()->js->set( 'categories', $categories, false );

        if ( get_awpcp_option( 'noadsinparentcat' ) ) {
            $message = __( 'Select a sub category', 'AWPCP' );
        } else {
            $message = __( 'Select a sub category (optional)', 'AWPCP' );
        }

        awpcp()->js->localize( 'categories-dropdown', 'no-category', $message );

        ob_start();
        include( AWPCP_DIR . '/frontend/templates/html-widget-category-dropdown.tpl.php' );
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }
}
