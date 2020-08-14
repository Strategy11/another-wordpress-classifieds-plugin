<?php

class AWPCP_TestLatestAdsWidget extends AWPCP_UnitTestCase {
    public function test_widget_shows_message_if_there_are_no_ads() {
        $instance = array( 'title' => 'Test Widget' );

        $args = array(
            'before_widget' => '<div>',
            'before_title' => '<h1>',
            'after_title' => '</h1>',
            'after_widget' => '<div>',
        );

        $widget = Phake::mock( 'AWPCP_LatestAdsWidget' );

        Phake::when( $widget )->defaults()->thenCallParent();
        Phake::when( $widget )->widget( $args, $instance )->thenCallParent();
        Phake::when( $widget )->render( Phake::anyParameters() )->thenCallParent();

        Phake::when( $widget )->query( Phake::anyParameters() )->thenReturn( array( 'conditions' => array( '1 != 1' ), 'args' => array() ) );

        ob_start();
            $widget->widget( $args, $instance );
            $output = ob_get_contents();
        ob_end_clean();

        $this->assertContains( 'There are currently no Ads to show.', $output );
        $this->assertContains( 'awpcp-empty-widget', $output );
    }
}
