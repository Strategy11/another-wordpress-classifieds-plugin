<?php

class AWPCP_Test_Listings_Table extends AWPCP_UnitTestCase {

    public function test_get_views_dont_show_awaiting_approval_views_to_regular_users() {
        $this->login_as_subscriber();

        $page = Phake::mock( 'AWPCP_Admin_Listings' );

        Phake::when( $page )->url->thenReturn( 'http://example.com' );

        $params = array(
            'hook_suffix' => 'whatever',
            'screen' => 'awpcp-panel',
        );

        $table = new AWPCP_Listings_Table( $page, $params );

        // Execution
        $table->get_views();

        Phake::verify( $page )->links( Phake::capture( $views ), Phake::capture( $params ) );

        // Verification
        $this->assertNotContains( 'awaiting-approval', array_keys( $views ) );
        $this->assertNotContains( 'images-awaiting-approval', array_keys( $views ) );
    }
}
