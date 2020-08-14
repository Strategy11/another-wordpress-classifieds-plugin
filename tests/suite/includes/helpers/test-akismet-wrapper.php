<?php

class AWPCP_TestAkismetWrapper extends AWPCP_UnitTestCase {

    public function test_get_reporter_data() {
        $request = Phake::mock( 'AWPCP_Request' );
        Phake::when( $request )->get_current_user()->thenReturn( Phake::mock( 'WP_User' ) );

        $akismet_wrapper = new AWPCP_AkismetWrapper( $request );
        $reporter_data = $akismet_wrapper->get_reporter_data();

        $this->assertArrayHasKey( 'site_domain', $reporter_data );
        $this->assertArrayHasKey( 'reporter', $reporter_data );
        $this->assertArrayHasKey( 'user_role', $reporter_data );
    }
}
