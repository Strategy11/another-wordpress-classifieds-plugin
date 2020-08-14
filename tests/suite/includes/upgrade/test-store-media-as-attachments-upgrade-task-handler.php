<?php
/**
 * @package AWPCP\Tests\Upgrade
 */

use Brain\Monkey\Functions;

class AWPCP_Test_Store_Media_As_Attachments_Upgrade_Task_Handler extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

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

        Functions\when( 'get_post_meta' )->justReturn( '' );
        Functions\when( 'media_handle_upload' )->justReturn( null );
        Functions\when( 'get_temp_dir' )->justReturn( '/tmp/' );
        Functions\when( 'wp_unique_filename' )->returnArg( 2 );
        Functions\when( 'is_wp_error' )->justReturn( false );
        Functions\when( 'update_post_meta' )->justReturn( true );
        Functions\when( 'get_intermediate_image_sizes' )->justReturn( [] );

        Functions\when( 'awpcp_sanitize_file_name' )->returnArg();

        Functions\when( 'add_post_meta' )->justReturn( true );

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
