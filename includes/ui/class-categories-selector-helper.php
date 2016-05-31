<?php

function awpcp_categories_selector_helper() {
    return new AWPCP_Categories_Selector_Helper();
}

class AWPCP_Categories_Selector_Helper {

    public function get_params( $params = array() ) {
        $hide_empty_categories = awpcp_get_option( 'hide-empty-categories-dropdown' );

        return wp_parse_args( $params, array(
            'context' => 'default',
            'name' => 'category',
            'label' => __( 'Ad Category', 'another-wordpress-classifieds-plugin' ),
            'required' => true,
            'selected' => null,
            'hide_empty' => awpcp_parse_bool( $hide_empty_categories ),
        ) );
    }

    public function build_categories_hierarchy( $categories, $hide_empty ) {
        return awpcp_build_categories_hierarchy( $categories, $hide_empty );
    }

    public function get_categories_parents( $categories, &$all_categories ) {
        $categories_parents = array();
        $all_categories_parents = array();

        foreach ( $all_categories as $item ) {
            $all_categories_parents[ $item->term_id ] = $item->parent;
        }

        foreach ( $categories as $category_id ) {
            $categories_parents[] = $this->get_category_parents(
                $category_id, $all_categories_parents
            );
        }

        return $categories_parents;
    }

    private function get_category_parents( $category_id, &$all_categories_parents ) {
        $category_parents = array();

        $parent_id = $category_id;
        while ( $parent_id != 0 && isset( $all_categories_parents[ $parent_id ] ) ) {
            $category_parents[] = $parent_id;
            $parent_id = $all_categories_parents[ $parent_id ];
        }

        return array_reverse( $category_parents );
    }
}
