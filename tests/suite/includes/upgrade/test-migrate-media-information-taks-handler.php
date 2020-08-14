<?php
/**
 * @package AWPCP\Tests\Plugin\Upgrade
 */

/**
 * Unit tests for migration rotuine for media information.
 */
class AWPCP_Test_Migrate_Media_Information_Task_Handler extends AWPCP_UnitTestCase {

    /**
     * Test run task method.
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function test_run_task() {
        $settings = Phake::mock( 'AWPCP_Settings' );
        $db       = Phake::mock( 'wpdb' );

        $filename    = 'test-image.jpg';
        $uploads_dir = '/tmp';

        $photos = array(
            (object) array(
                'ad_id'      => rand() + 1,
                'image_name' => $filename,
                'disabled'   => false,
                'is_primary' => false,
            ),
        );

        Phake::when( $settings )->get_runtime_option( 'awpcp-uploads-dir' )->thenReturn( $uploads_dir );

        Phake::when( $db )->get_results->thenReturn( $photos );
        Phake::when( $db )->get_var->thenReturn( 1 )->thenReturn( 0 );

        // phpcs:disable WordPress.VIP.FileSystemWritesDisallow.file_ops_touch
        touch( $uploads_dir . $filename );
        // phpcs:enable
        $handler = Phake::partialMock( 'AWPCP_Migrate_Media_Information_Task_Handler', $settings, $db );
        Phake::when( $handler )->photos_table_exists->thenReturn( true );
        $result = $handler->run_task();
        // phpcs:disable WordPress.VIP.FileSystemWritesDisallow.file_ops_unlink
        unlink( $uploads_dir . $filename );
        // phpcs:enable

        Phake::verify( $db )->insert( Phake::capture( $table ), Phake::capture( $entry ) );

        $this->assertEquals( array( 1, 0 ), $result );
        $this->assertEquals( $filename, $entry['path'] );
    }
}
