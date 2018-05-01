<?php
/**
 * @package AWPCP\UI
 */

/**
 * @since 3.3
 */
function awpcp_categories_selector() {
    return new AWPCP_Category_Selector(
        awpcp_categories_selector_helper(),
        awpcp_categories_collection(),
        awpcp_template_renderer()
    );
}

/**
 * A class to render and prepare settings for the Category Selector component.
 */
class AWPCP_Category_Selector {

    /**
     * @var object
     */
    private $helper;

    /**
     * @var object
     */
    private $categories;

    /**
     * @var object
     */
    private $template_renderer;

    /**
     * @param object $helper                An instance of Categories Selector Helper.
     * @param object $categories            An instance of Categories Collection.
     * @param object $template_renderer     An instance of Template Renderer.
     */
    public function __construct( $helper, $categories, $template_renderer ) {
        $this->helper            = $helper;
        $this->categories        = $categories;
        $this->template_renderer = $template_renderer;
    }

    /**
     * @param array $params     An array of parameters for the Category Selector component.
     */
    public function render( $params ) {
        $categories = $this->categories->get_all();

        $params               = $this->helper->get_params( $params );
        $categories_hierarchy = $this->helper->build_categories_hierarchy(
            $categories,
            $params['hide_empty']
        );

        $placeholder = $this->get_placeholder( $params );

        // Export categories list to JavaScript, but don't replace an existing categories list.
        awpcp()->js->set( 'categories', $categories_hierarchy, false );

        $template_params = array(
            'name'                 => $params['name'],
            'label'                => $params['label'],
            'required'             => $params['required'],
            'placeholder'          => $placeholder,
            'selected'             => $params['selected'],
            'auto'                 => $params['auto'],
            'categories_hierarchy' => $categories_hierarchy,
            'hash'                 => uniqid(),
            'multiple'             => $params['multiple'],
            'javascript'           => $this->get_javascript_options( $params, $placeholder, $categories_hierarchy ),
        );

        return $this->template_renderer->render_template(
            AWPCP_DIR . '/templates/components/category-selector.tpl.php',
            $template_params
        );
    }

    /**
     * @param array $params     An array of parameters for the Category Selector.
     */
    private function get_placeholder( $params ) {
        if ( 'search' === $params['context'] ) {
            return __( 'All Categories', 'another-wordpress-classifieds-plugin' );
        }

        return __( 'Select a Category', 'another-wordpress-classifieds-plugin' );
    }

    /**
     * @param array $available_payment_terms    An array of payment terms.
     */
    private function prepare_payment_terms( $available_payment_terms ) {
        $all_payment_terms = array();

        foreach ( $available_payment_terms as $payment_term_type => $payment_terms ) {
            foreach ( $payment_terms as $payment_term ) {
                $number_of_categories_allowed = 1;

                if ( isset( $payment_term->number_of_categories_allowed ) ) {
                    $number_of_categories_allowed = $payment_term->number_of_categories_allowed;
                }

                $all_payment_terms[ "{$payment_term_type}-{$payment_term->id}" ] = (object) array(
                    'numberOfCategoriesAllowed' => $number_of_categories_allowed,
                    'categories'                => $payment_term->categories,
                );
            }
        }

        return $all_payment_terms;
    }

    /**
     * @param array  $params                An array of parameters for the Category Selector component.
     * @param string $placeholder           The placeholder for the dropdown.
     * @param array  $categories_hierarchy  An array that holds the entire hierarchy of categories.
     * @since 4.0.0
     */
    private function get_javascript_options( $params, $placeholder, $categories_hierarchy ) {
        $options = array(
            'mode'    => $params['mode'],
            'select2' => array(
                'debug'                  => true,
                'allowClear'             => true,
                'placeholder'            => $placeholder,
                'maximumSelectionLength' => 100,
            ),
        );

        if ( 'advanced' === $params['mode'] ) {
            $options['selectedCategoriesIds'] = $params['selected'];
            $options['categoriesHierarchy']   = $categories_hierarchy;
            $options['paymentTerms']          = $this->prepare_payment_terms( $params['payment_terms'] );
        }

        return $options;
    }
}
