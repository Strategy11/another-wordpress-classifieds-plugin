<?php

function awpcp_store_media_as_attachments_upgrade_task_handler() {
    return new AWPCP_Upgrade_Task_Handler(
        new AWPCP_Store_Media_As_Attachments_Upgrade_Task_Handler(
            awpcp_settings_api(),
            awpcp_wordpress(),
            $GLOBALS['wpdb']
        )
    );
}

class AWPCP_Store_Media_As_Attachments_Upgrade_Task_Handler implements AWPCP_Upgrade_Task_Runner {

    private $settings;
    private $wordpress;
    private $db;

    public function __construct( $settings, $wordpress, $db ) {
        $this->settings = $settings;
        $this->wordpress = $wordpress;
        $this->db = $db;
    }

    public function get_last_item_id() {
        return $this->wordpress->get_option( 'awpcp-smaa-last-listing-id' );
    }

    public function update_last_item_id( $last_item_id  ) {
        $this->wordpress->update_option( 'awpcp-smaa-last-listing-id', $last_item_id );
    }

    public function count_pending_items( $last_item_id ) {
        $query = 'SELECT COUNT(id) FROM ' . AWPCP_TABLE_MEDIA . ' WHERE id > %d';
        return intval( $this->db->get_var( $this->db->prepare( $query, $last_item_id ) ) );
    }

    public function get_pending_items( $last_item_id ) {
        $query = 'SELECT * FROM ' . AWPCP_TABLE_MEDIA . ' WHERE id > %d LIMIT 0, 10';
        return $this->db->get_results( $this->db->prepare( $query, $last_item_id ) );
    }

    public function process_item( $item, $last_item_id ) {
        if ( ! function_exists( 'media_handle_upload' ) ) {
            require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
            require_once( ABSPATH . "wp-admin" . '/includes/file.php' );
            require_once( ABSPATH . "wp-admin" . '/includes/media.php' );
        }

        $file_path = trailingslashit( $this->settings->get_runtime_option( 'awpcp-uploads-dir' ) ) . $item->path;
        $file_name = awpcp_utf8_pathinfo( $file_path, PATHINFO_BASENAME );

        if ( ! file_exists( $file_path ) ) {
            debugf(
                sprintf( 'The file %s does not exists.', $file_path ),
                $item,
                $file_path,
                $file_name
            );

            return $item->id;
        }

        $new_name = wp_unique_filename( '/tmp', $file_name );
        $tmp_name = '/tmp/' . $new_name;

        $file_was_copied = @copy( $file_path, $tmp_name );

        if ( ! $file_was_copied ) {
            throw new AWPCP_Exception( sprintf( "The file %s couldn't be copied to the temporary location %s", $file_path, $tmp_name ) );
        }

        $file_array = array(
            'name' => awpcp_sanitize_file_name( $file_name ),
            'tmp_name' => $tmp_name,
        );

        $parent_listing_id = $this->get_id_of_associated_listing( $item );
        $description = '';

        // do the validation and storage stuff
        $attachment_id = $this->wordpress->handle_media_sideload( $file_array, $parent_listing_id, $description );

        // If error storing permanently, unlink
        if ( is_wp_error( $attachment_id ) ) {
            throw new AWPCP_Exception( sprintf( "An attachment couldn't be created for media item with id %d", $item->id ) );
        } else if ( file_exists( $tmp_name ) ) {
            @unlink( $tmp_name );
        }

        if ( $item->enabled ) {
            update_post_meta( $attachment_id, '_awpcp_enabled', true );
        }

        if ( $item->is_primary ) {
            update_post_meta( $attachment_id, '_awpcp_featured', true );
        }

        update_post_meta( $attachment_id, '_awpcp_allowed_status', $item->status );

        return $item->id;
    }

    private function get_id_of_associated_listing( $item ) {
        $listings = get_posts( array(
            'post_type' => 'awpcp_listing',
            'meta_query' => array(
                'key' => '_awpcp_old_id',
                'value' => $item->ad_id,
            ),
        ) );

        if ( ! is_array( $listings ) || empty( $listings ) ) {
            return 0;
        }

        return $listings[0]->ID;
    }
}
