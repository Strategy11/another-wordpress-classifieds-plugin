<?php

class AWPCP_Test_CSV_Import_Session extends AWPCP_UnitTestCase {

    public function test_params_setters_and_getters() {
        $params = array( 'foo' => 'bar' );

        $import_session = new AWPCP_CSV_Import_Session( array(), null );
        $import_session->set_params( $params );

        $this->assertEquals( $params, $import_session->get_params() );
    }
}
