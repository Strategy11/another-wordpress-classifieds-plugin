<?php

/**
 * @group core
 */
class AWPCP_TestPathFunctions extends AWPCP_UnitTestCase {

    public function test_utf8_pathinfo() {
        $expected_info = array(
            'dirname' => '/wp-content/uploads/כרטיס עסק מסעדה',
            'basename' => 'עיצוב ובניית אתרים.png',
            'filename' => 'עיצוב ובניית אתרים',
            'extension' => 'png',
        );

        // somehow the following line yield different results if put in a file
        // accesed throw the webserver. In that case pathinfo returns the
        // incorrect basename and filename, because it cannot handle utf8
        // characters. However, if you execute the same line of code or
        // the same file, through the command line, the output is correct.
        $path_info = awpcp_utf8_pathinfo( '/wp-content/uploads/כרטיס עסק מסעדה/עיצוב ובניית אתרים.png' );

        $this->assertEquals( $expected_info, $path_info );
    }

    public function test_utf8_basename() {
        $this->assertTrue( true );
    }

    public function test_get_file_extension() {
        $this->assertEquals( 'png', awpcp_get_file_extension( 'עיצוב ובניית אתרים.png' ) );
    }

    /**
     * Related issue: https://github.com/drodenbaugh/awpcp/issues/1214
     */
    public function test_unique_filename_removes_whitespace_from_filenames() {
        $file_name = 'test       image.png';
        $unique_file_name = awpcp_unique_filename( '/tmp', $file_name, array() );

        $this->assertStringStartsWith( 'test-image', $unique_file_name );
        $this->assertStringEndsWith( '.png', $unique_file_name );
    }
}
