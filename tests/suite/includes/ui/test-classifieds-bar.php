<?php

use \Brain\Monkey\Functions;

class AWPCP_Test_Classifieds_Bar extends AWPCP_UnitTestCase {

    public function test_constructor_function() {
        $classifieds_bar = awpcp_classifieds_bar();

        $this->assertInstanceOf( 'AWPCP_Classifieds_Bar', $classifieds_bar );
    }

    public function test_render() {
        $classifieds_search_bar = Phake::mock( 'AWPCP_Classifieds_Search_Bar_Component' );
        $classifieds_menu = Phake::mock( 'AWPCP_Classifieds_Menu_Component' );
        $settings = Phake::mock( 'AWPCP_Settings_API' );

        Phake::when( $settings )->get_option( 'show-classifieds-bar' )->thenReturn( true );
        Phake::when( $settings )->get_option( 'show-classifieds-search-bar' )->thenReturn( true );

        $classifieds_bar = new AWPCP_Classifieds_Bar(
            $classifieds_search_bar,
            $classifieds_menu,
            $settings
        );

        /* Execution */
        $classifieds_bar->render();

        /* Verification */
        Phake::verify( $classifieds_search_bar )->render();
        Phake::verify( $classifieds_menu )->render();
    }

    public function test_render_returns_nothing_if_classifieds_bar_is_disabled() {
        $classifieds_search_bar = Phake::mock( 'AWPCP_Classifieds_Search_Bar_Component' );
        $classifieds_menu = Phake::mock( 'AWPCP_Classifieds_Menu_Component' );
        $settings = Phake::mock( 'AWPCP_Settings_API' );

        $classifieds_bar = new AWPCP_Classifieds_Bar(
            $classifieds_search_bar,
            $classifieds_menu,
            $settings
        );

        /* Execution */
        $output = $classifieds_bar->render();

        /* Verification */
        $this->assertEmpty( $output );
    }

    public function test_render_does_not_render_disabled_components() {
        $classifieds_search_bar = Phake::mock( 'AWPCP_Classifieds_Search_Bar_Component' );
        $classifieds_menu = Phake::mock( 'AWPCP_Classifieds_Menu_Component' );
        $settings = Phake::mock( 'AWPCP_Settings_API' );

        Phake::when( $settings )->get_option( 'show-classifieds-bar' )->thenReturn( true );

        $classifieds_bar = new AWPCP_Classifieds_Bar(
            $classifieds_search_bar,
            $classifieds_menu,
            $settings
        );

        /* Execution */
        $output = $classifieds_bar->render( array( 'search_bar' => false ) );

        /* Verification */
        Phake::verify( $classifieds_search_bar, Phake::times( 0 ) )->render();
        Phake::verify( $classifieds_menu )->render();
    }
}
