<?php
/**
 * @package AWPCP\Tests
 */

// phpcs:disable

class AWPCP_Test_Categories_Dropdown extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->categories = Phake::mock( 'AWPCP_Categories_Collection' );
        $this->template_renderer = Phake::mock( 'AWPCP_Template_Renderer' );
    }

    public function test_constructor() {
        $dropdown = awpcp_categories_dropdown();
        $this->assertInstanceOf( 'AWPCP_CategoriesDropdown', $dropdown );
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function test_render() {
        $all_categories = array(
            (object) array(
                'name'    => 'Parent Category',
                'term_id' => 1,
                'parent'  => 0,
            ),
            (object) array(
                'name'    => 'Child Category',
                'term_id' => 2,
                'parent'  => 1,
            ),
        );

        $params = array(
            'selected' => 2,
        );

        Phake::when( $this->categories )->get_all->thenReturn( $all_categories );

        $dropdown = new AWPCP_CategoriesDropdown(
            $this->categories, $this->template_renderer
        );

        $dropdown->render( $params );

        Phake::verify( $this->template_renderer )->render_template(
            Phake::capture( $template ), Phake::capture( $template_params )
        );

        $this->assertContains( 'label', array_keys( $template_params ) );
        $this->assertContains( 'required', array_keys( $template_params ) );
        $this->assertContains( 'placeholders', array_keys( $template_params ) );
        $this->assertContains( 'categories_hierarchy', array_keys( $template_params ) );
        $this->assertContains( 'chain', array_keys( $template_params ) );
        $this->assertContains( 'name', array_keys( $template_params ) );
        $this->assertContains( 'selected', array_keys( $template_params ) );
    }
}
