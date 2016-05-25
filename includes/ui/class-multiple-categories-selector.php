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
        $chain = $this->get_selected_categories_parents(
            (array) $params['selected'],
            $categories
        );

        $unique_id = uniqid();
        $use_multiple_dropdowns = get_awpcp_option( 'use-multiple-category-dropdowns' );

        awpcp()->js->set( 'CategoriesSelector-' . $unique_id, array(
            'name' => $params['name'],
            'categories' => $categories_hierarchy,
            'selectionMatrix' => $this->generate_selection_matrix( $params ),
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

    private function get_selected_categories_parents( $categories, &$all_categories ) {
        $selected_categories_parents = array();
        $all_categories_parents = array();

        foreach ( $all_categories as $item ) {
            $all_categories_parents[ $item->term_id ] = $item->parent;
        }

        foreach ( $categories as $category_id ) {
            $selected_categories_parents[] = $this->get_category_parents(
                $category_id, $all_categories_parents
            );
        }

        return $selected_categories_parents;
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

    private function generate_selection_matrix( $params ) {
        $can_be_selected_together = array();

        foreach ( $params['payment_terms'] as $payment_term_type => $payment_terms ) {
            foreach ( $payment_terms as $payment_term ) {
                if ( empty( $payment_term->categories ) ) {
                    // no need to build a selection matrix, every combination of categories
                    // can be used with at least one of the payment terms.
                    return null;
                }

                foreach ( $payment_term->categories as $current_category ) {
                    foreach ( $payment_term->categories as $sibling_category ) {
                        if ( $current_category == $sibling_category ) {
                            continue;
                        }

                        if ( ! isset( $can_be_selected_together[ $current_category ] ) ) {
                            $can_be_selected_together[ $current_category ] = array();
                        }

                        if ( ! in_array( $sibling_category, $can_be_selected_together[ $current_category ] ) ) {
                            $can_be_selected_together[ $current_category ][] = $sibling_category;
                        }
                    }
                }
            }
        }

        return $can_be_selected_together;
    }
}
