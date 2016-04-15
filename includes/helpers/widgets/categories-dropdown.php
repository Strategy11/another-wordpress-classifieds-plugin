<?php

/**
 * @since 3.3
 */
function awpcp_categories_dropdown() {
    return new AWPCP_CategoriesDropdown(
        awpcp_categories_collection(),
        awpcp_template_renderer()
    );
}

class AWPCP_CategoriesDropdown {

    private $categories;
    private $template_renderer;

    public function __construct( $categories, $template_renderer ) {
        $this->categories = $categories;
        $this->template_renderer = $template_renderer;
    }

    public function render($params) {
        extract( $params = wp_parse_args( $params, array(
            'context' => 'default',
            'name' => 'category',
            'label' => __( 'Ad Category', 'another-wordpress-classifieds-plugin' ),
            'required' => true,
            'selected' => null,
            'placeholders' => array(),
        ) ) );

        if ( $context == 'search' ) {
            $placeholders = array_merge( array(
                'default-option-first-level' => __( 'All Categories', 'another-wordpress-classifieds-plugin' ),
                'default-option-second-level' => __( 'All Sub-categories', 'another-wordpress-classifieds-plugin' ),
            ), $placeholders );
        } else {
            if ( get_awpcp_option( 'noadsinparentcat' ) ) {
                $second_level_option_placeholder = __( 'Select a Sub-category', 'another-wordpress-classifieds-plugin' );
            } else {
                $second_level_option_placeholder = __( 'Select a Sub-category (optional)', 'another-wordpress-classifieds-plugin' );
            }

            $placeholders = array_merge( array(
                'default-option-first-level' => __( 'Select a Category', 'another-wordpress-classifieds-plugin' ),
                'default-option-second-level' => $second_level_option_placeholder
            ), $placeholders );
        }

        $categories = $this->categories->get_all();
        $categories_hierarchy = awpcp_build_categories_hierarchy( $categories );
        $chain = $this->get_category_parents( $selected, $categories );

        // TODO: test the dropdown with multiple dropdowns enabled
        $use_multiple_dropdowns = get_awpcp_option( 'use-multiple-category-dropdowns' );

        // export categories list to JavaScript, but don't replace an existing categories list
        awpcp()->js->set( 'categories', $categories_hierarchy, false );

        $template = AWPCP_DIR . '/frontend/templates/html-widget-category-dropdown.tpl.php';

        $params = compact(
            'label',
            'required',
            'use_multiple_dropdowns',
            'placeholders',
            'chain',
            'name',
            'selected',
            'categories_hierarchy'
        );

        return $this->template_renderer->render_template( $template, $params );
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

        do {
            $category_ancestors[] = $parent_id;
            $parent_id = $categories_parents[ $parent_id ];
        } while ( $parent_id != 0 );

        return array_reverse( $category_ancestors );
    }
}

function awpcp_render_category_selector( $params = array() ) {
    return awpcp_categories_selector_component()->render( $params );
}
