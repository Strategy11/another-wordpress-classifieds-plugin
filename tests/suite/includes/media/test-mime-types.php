<?php

class AWPCP_TestMimeTypes extends AWPCP_UnitTestCase {

    public function test_get_file_mime_type_with_non_existent_files() {
        $mime_types = awpcp_mime_types();

        $files = array(
            'video.asf' => 'video/x-ms-asf',
            'video.mov' => 'video/quicktime',
            'video.AVI' => 'video/avi',
            'video.mp4' => 'video/mp4',
            'video.3gp' => 'video/3gpp',
            'video.flv' => 'video/x-flv',
            'video.webm' => 'video/webm',
            'video.ogv' => 'video/ogg',
        );

        foreach ( $files as $filename => $mime_type ) {
            $this->assertEquals( $mime_type, $mime_types->get_file_mime_type( $filename ) );
        }
    }
}
