<?php

function awpcp_multiple_categories_selector() {
    return new AWPCP_Multiple_Categories_Selector( awpcp_categories_collection() );
}

class AWPCP_Multiple_Categories_Selector {

    private $categories;

    public function __construct( $categories ) {
        $this->categories = $categories;
    }

    public function render( $params ) {
        $params = $this->parse_args( $params );

        $categories = $this->categories->get_all();
        $categories_hierarchy = awpcp_build_categories_hierarchy( $categories, $params['hide_empty'] );
        $chain = $this->get_category_parents( $params['selected'], $categories );

        $unique_id = uniqid();
        $use_multiple_dropdowns = get_awpcp_option( 'use-multiple-category-dropdowns' );

        awpcp()->js->set( 'CategoriesSelector-' . $unique_id, array(
            'name' => $params['name'],
            'categories' => $categories_hierarchy,
            'selected' => $chain,
            'multistep' => (bool) $use_multiple_dropdowns,
        ) );

        return '<div class="awpcp-categories-selector" data-multiple-value-selector-id="' . $unique_id . '"></div>';
    }

    private function parse_args( $params ) {
        $hide_empty_categories = awpcp_get_option( 'hide-empty-categories-dropdown' );

        $params = wp_parse_args( $params, array(
            'context' => 'default',
            'name' => 'category',
            'label' => __( 'Ad Category', 'another-wordpress-classifieds-plugin' ),
            'required' => true,
            'selected' => null,
            'placeholders' => array(),
            'hide_empty' => awpcp_parse_bool( $hide_empty_categories ),
        ) );

        $params['placeholders'] = $this->get_placeholders( $params );

        return $params;
    }

    private function get_placeholders( $params ) {
        $placeholders = $params['placeholders'];

        if ( $params['context'] == 'search' ) {
            return array_merge( array(
                'default-option-first-level' => __( 'All Categories', 'another-wordpress-classifieds-plugin' ),
                'default-option-second-level' => __( 'All Sub-categories', 'another-wordpress-classifieds-plugin' ),
            ), $placeholders );
        } else {
            if ( get_awpcp_option( 'noadsinparentcat' ) ) {
                $second_level_option_placeholder = __( 'Select a Sub-category', 'another-wordpress-classifieds-plugin' );
            } else {
                $second_level_option_placeholder = __( 'Select a Sub-category (optional)', 'another-wordpress-classifieds-plugin' );
            }

            return array_merge( array(
                'default-option-first-level' => __( 'Select a Category', 'another-wordpress-classifieds-plugin' ),
                'default-option-second-level' => $second_level_option_placeholder
            ), $placeholders );
        }
    }

    private function get_category_parents( $category_id, &$categories ) {
        if ( empty( $category_id ) ) {
            return array();
        }

        $categories_parents = array();

        foreach ( $categories as $item ) {
            $categories_parents[ $item->term_id ] = $item->parent;
        }

        $category_ancestors = array();
        $parent_id = $category_id;

        while ( $parent_id != 0 && isset( $categories_parents[ $parent_id ] ) ) {
            $category_ancestors[] = $parent_id;
            $parent_id = $categories_parents[ $parent_id ];
        }

        return array_reverse( $category_ancestors );
    }
}
