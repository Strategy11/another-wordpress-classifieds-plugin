<?php
/**
 * @package AWPCP\Tests\Upgrade
 */

class AWPCP_Test_Store_Media_As_Attachments_Upgrade_Task_Handler extends AWPCP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();

        $this->settings  = Phake::mock( 'AWPCP_Settings_API' );
        $this->wordpress = Phake::mock( 'AWPCP_WordPress' );
        $this->db        = Mockery::mock( 'wpdb' );

        $this->last_item_id = wp_rand() + 1;

        $this->item = (object) array(
            'id'         => wp_rand() + 1,
            'ad_id'      => wp_rand() + 1,
            'name'       => 'store-media-as-attachmments-test-item.jpg',
            'path'       => 'store-media-as-attachmments-test-item.jpg',
            'mime_type'  => 'image/jpeg',
            'enabled'    => true,
            'status'     => 'Approved',
            'is_primary' => 0,
            'metadata'   => '',
            'created'    => date( 'Y-m-d H:i:s' ),
        );
    }

    public function test_process_item() {
        $parent_listing = (object) array( 'ID' => wp_rand() + 1 );

        WP_Mock::userFunction( 'get_post_meta', [
            'return' => '',
        ] );
        WP_Mock::userFunction( 'media_handle_upload', [
            'return' => null,
        ] );
        WP_Mock::userFunction( 'get_temp_dir', [
            'return' => '/tmp/',
        ] );
        WP_Mock::userFunction( 'wp_unique_filename', [
            'return' => function( $dir, $filename ) {
                return $filename;
            },
        ] );
        WP_Mock::userFunction( 'is_wp_error', [
            'return' => false,
        ] );
        WP_Mock::userFunction( 'update_post_meta', [
            'return' => true,
        ] );
        WP_Mock::userFunction( 'get_intermediate_image_sizes', [
            'return' => [],
        ] );

        WP_Mock::userFunction( 'awpcp_sanitize_file_name', [
            'return' => function( $arg ) {
                return $arg;
            },
        ] );

        WP_Mock::userFunction( 'add_post_meta', [
            'return' => true,
        ] );

        Phake::when( $this->settings )->get_runtime_option->thenReturn( WP_TESTS_DATA_DIR . '/upgrade' );

        $this->redefine( 'AWPCP_Store_Media_As_Attachments_Upgrade_Task_Handler::get_id_of_associated_listing', Patchwork\always( $parent_listing->ID ) );

        $task_handler = new AWPCP_Store_Media_As_Attachments_Upgrade_Task_Handler(
            $this->settings,
            $this->wordpress,
            $this->db
        );

        $this->new_listing_id = $task_handler->process_item( $this->item, $this->last_item_id );

        Phake::verify( $this->wordpress )->handle_media_sideload(
            Phake::capture( $sideloaded_file ),
            Phake::capture( $parent_listing_id ),
            Phake::ignoreRemaining()
        );

        $this->assertEquals( '/tmp/' . $this->item->name, $sideloaded_file['tmp_name'] );
        $this->assertEquals( $parent_listing->ID, $parent_listing_id );
    }
}
