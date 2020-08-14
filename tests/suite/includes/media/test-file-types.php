<?php

class AWPCP_TestFileTypes extends AWPCP_UnitTestCase {

    public function test_get_file_extensions_in_group() {
        $expected_extensions = array( 'png', 'jpg', 'jpeg', 'pjpeg', 'gif' );

        $file_types = awpcp_file_types();

        $this->assertEquals( $expected_extensions, $file_types->get_file_extensions_in_group( 'image' ) );
    }

    public function test_get_allowed_file_mime_types_in_group() {
        $allowed_mime_types = array( 'image/jpg', 'image/jpeg', 'image/pjpeg' );

        $settings = Phake::mock( 'AWPCP_Settings_API' );
        Phake::when( $settings )->get_option( 'allowed-image-extensions', array() )->thenReturn( array( 'pjpeg' ) );

        $file_types = new AWPCP_FileTypes( $settings );

        $this->assertEquals( $allowed_mime_types, $file_types->get_allowed_file_mime_types_in_group( 'image' ) );
    }
}
