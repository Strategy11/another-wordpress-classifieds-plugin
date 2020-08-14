<?php
/**
 * @package AWPCP\Tests\Plugin\UI
 */

use Brain\Monkey\Functions;

/**
 * Unit tests for Categories Selector component.
 */
class AWPCP_CategoriesSelectorTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        $this->categories_selector_helper = Mockery::mock( 'AWPCP_Categories_Selector_Helper' );
        $this->categories_collection      = Mockery::mock( 'AWPCP_Categories_Collection' );
        $this->template_renderer          = Mockery::mock( 'AWPCP_Template_Renderer' );
    }

    /**
     * @since 4.0.0
     */
    public function test_placeholder_parameter() {
        $placeholder = 'Placeholder';

        $template_params = [
            'name'                   => null,
            'label'                  => null,
            'placeholder'            => $placeholder,
            'selected'               => null,
            'required'               => null,
            'multiple'               => null,
            'auto'                   => null,
            'hash'                   => null,
            'categories_hierarchy'   => null,
            'javascript'             => null,
            'use_multiple_dropdowns' => null,
        ];

        Functions\when( 'awpcp_render_categories_dropdown_options' )->justReturn( '' );

        $this->get_test_subject();
        $template_renderer = new AWPCP_Template_Renderer();

        $output = $template_renderer->render_template(
            AWPCP_DIR . '/templates/components/category-selector.tpl.php',
            $template_params
        );

        $this->assertContains( $placeholder, $output );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_Category_Selector(
            $this->categories_selector_helper,
            $this->categories_collection,
            $this->template_renderer
        );
    }
}
