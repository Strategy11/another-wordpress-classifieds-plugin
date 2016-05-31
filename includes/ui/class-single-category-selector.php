<?php

/**
 * @since 3.3
 */
function awpcp_single_category_selector() {
    return new AWPCP_Single_Category_Selector(
        awpcp_categories_selector_helper(),
        awpcp_categories_collection(),
        awpcp_template_renderer()
    );
}

class AWPCP_Single_Category_Selector {

    private $helper;
    private $categories;
    private $template_renderer;

    public function __construct( $helper, $categories, $template_renderer ) {
        $this->helper = $helper;
        $this->categories = $categories;
        $this->template_renderer = $template_renderer;
    }

    public function render($params) {
        $params = $this->helper->get_params( $params );
        $placeholders = $this->get_placeholders( $params );

        $categories = $this->categories->get_all();
        $categories_hierarchy = $this->helper->build_categories_hierarchy(
            $categories,
            $params['hide_empty']
        );
        $chain = $this->get_selection_chain( $params['selected'], $categories );

        // export categories list to JavaScript, but don't replace an existing categories list
        awpcp()->js->set( 'categories', $categories_hierarchy, false );

        $use_multiple_dropdowns = get_awpcp_option( 'use-multiple-category-dropdowns' );

        $template_params = array(
            'name' => $params['name'],
            'label' => $params['label'],
            'required' => $params['required'],
            'use_multiple_dropdowns' => $use_multiple_dropdowns,
            'placeholders' => $placeholders,
            'chain' => $chain,
            'selected' => $params['selected'],
            'categories_hierarchy' => $categories_hierarchy,
        );

        $template = AWPCP_DIR . '/templates/components/single-category-selector.tpl.php';

        return $this->template_renderer->render_template( $template, $template_params );
    }

    private function get_placeholders( $params ) {
        $placeholders = array();

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

    private function get_selection_chain( $categories, &$all_categories ) {
        if ( empty( $categories ) ) {
            return array();
        }

        $categories_parents = $this->helper->get_categories_parents(
            (array) $categories,
            $all_categories
        );

        return array_shift( $categories_parents );
    }
}
