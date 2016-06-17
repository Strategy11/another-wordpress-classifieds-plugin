<?php

function awpcp_multiple_categories_selector() {
    return new AWPCP_Multiple_Categories_Selector(
        awpcp_categories_selector_helper(),
        awpcp_categories_collection(),
        awpcp_template_renderer()
    );
}

class AWPCP_Multiple_Categories_Selector {

    private $helper;
    private $categories;
    private $template_renderer;

    public function __construct( $helper, $categories, $template_renderer ) {
        $this->helper = $helper;
        $this->categories = $categories;
        $this->template_renderer = $template_renderer;
    }

    public function render( $params ) {
        $params = $this->helper->get_params( $params );

        $categories = $this->categories->get_all();
        $categories_hierarchy = $this->helper->build_categories_hierarchy(
            $categories,
            $params['hide_empty']
        );

        $unique_id = uniqid();
        $use_multiple_dropdowns = get_awpcp_option( 'use-multiple-category-dropdowns' );

        awpcp()->js->set( 'MultipleCategoriesSelector-' . $unique_id, array(
            'fieldName' => $params['name'],
            'selectedCategoriesIds' => $params['selected'],
            'categoriesHierarchy' => $categories_hierarchy,
            'paymentTerms' => $this->get_payment_terms( $params ),
            'multistep' => (bool) $use_multiple_dropdowns,
        ) );

        $template_params = array(
            'unique_id' => $unique_id,
            'label' => $params['label'],
            'required' => $params['required'],
        );

        $template = AWPCP_DIR . '/templates/components/multiple-categories-selector.tpl.php';

        return $this->template_renderer->render_template( $template, $template_params );
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

    private function get_payment_terms( $params ) {
        $all_payment_terms = array();

        foreach ( $params['payment_terms'] as $payment_term_type => $payment_terms ) {
            foreach ( $payment_terms as $payment_term ) {
                if ( isset( $payment_term->number_of_categories_allowed ) ) {
                    $number_of_categories_allowed = $payment_term->number_of_categories_allowed;
                } else {
                    $number_of_categories_allowed = 1;
                }

                $all_payment_terms[ "{$payment_term_type}-{$payment_term->id}" ] = (object) array(
                    'numberOfCategoriesAllowed' => $number_of_categories_allowed,
                    'categories' => $payment_term->categories,
                );
            }
        }

        return $all_payment_terms;
    }
}
