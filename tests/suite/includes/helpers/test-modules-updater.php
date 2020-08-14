<?php

class AWPCP_Test_ModulesUpdater extends AWPCP_UnitTestCase {

    public function test_filter_plugins_version_information_handles_http_exception() {
        $edd = Phake::mock( 'AWPCP_EasyDigitalDownloads' );

        Phake::when( $edd )->get_version->thenThrow(
            new AWPCP_HTTP_Exception( 'Catch me if you can.' )
        );

        $module = (object) array(
            'slug'    => 'foo',
            'name'    => 'Foo',
            'version' => '1.0.0',
            'file'    => 'foo.php',
        );

        $license = 'fake-license';

        $information = (object) array(
            'response' => array(),
        );

        $modules_updater = new AWPCP_ModulesUpdater( $edd );
        $modules_updater->watch( $module, $license );

        /* Execution */
        $modules_updater->filter_plugins_version_information( $information );

        /* Verification */
        Phake::verify( $edd )->get_version( Phake::anyParameters() );
    }
}
