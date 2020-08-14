<?php

class AWPCP_Test_Category_Shortcode extends AWPCP_UnitTestCase {

    public function test_the_id_paramater_accepts_multiple_categories() {
        $this->pause_filter( 'awpcp_menu_items' );

        $categories_renderer_factory = Phake::mock( 'AWPCP_Categories_Renderer_Factory' );
        $categories_list_renderer = Phake::mock( 'AWPCP_CategoriesRenderer' );
        $categories = Phake::mock( 'AWPCP_Categories_Collection' );
        $category = (object) array( 'term_id' => rand() + 1 );
        $db = Phake::mock( 'wpdb' );
        $request = Phake::mock( 'AWPCP_Request' );

        Phake::when( $categories_renderer_factory )->create_list_renderer->thenReturn( $categories_list_renderer );
        Phake::when( $categories )->get->thenReturn( $category );

        $categories_ids = array( 1, 2 );
        $shortcode_params = array(
            'id' => implode( ',', $categories_ids ),
            'show_categories_list' => true,
        );

        $shortcode_handler = new AWPCP_CategoryShortcode(
            $categories_renderer_factory,
            $categories,
            $db,
            $request
        );
        $shortcode_handler->render( $shortcode_params );

        Phake::verify( $categories_list_renderer )->render( Phake::capture( $categories_list_params ) );

        $this->assertEquals( $categories_ids, $categories_list_params['category_id'] );
    }
}
