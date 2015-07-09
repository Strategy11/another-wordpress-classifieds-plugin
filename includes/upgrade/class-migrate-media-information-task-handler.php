<?php

function awpcp_migrate_media_information_task_handler() {
    return new AWPCP_Migrate_Media_Information_Task_Handler();
}

class AWPCP_Migrate_Media_Information_Task_Handler {

    /**
     * TODO: do this in the next version upgrade
     * $wpdb->query( 'DROP TABLE ' . AWPCP_TABLE_ADPHOTOS );
     */
    public function run_task() {
        global $wpdb;

        $mime_types = awpcp_mime_types();

        if ( ! awpcp_table_exists( AWPCP_TABLE_ADPHOTOS ) ) {
            return array( 0, 0 );
        }

        $cursor = get_option( 'awpcp-migrate-media-information-cursor', 0 );
        $total = $this->count_pending_images( $cursor );

        $sql = 'SELECT * FROM ' . AWPCP_TABLE_ADPHOTOS . ' ';
        $sql.= 'WHERE ad_id > %d ORDER BY key_id LIMIT 0, 100';

        $results = $wpdb->get_results( $wpdb->prepare( $sql, $cursor ) );

        $uploads = awpcp_setup_uploads_dir();
        $uploads = array_shift( $uploads );

        foreach ( $results as $image ) {
            $cursor = $image->ad_id;

            $filename = awpcp_get_image_url( $image->image_name );

            if ( empty( $filename ) ) continue;

            $path = str_replace( AWPCPUPLOADURL, $uploads, $filename );
            $mime_type = $mime_types->get_file_mime_type( $path );

            $entry = array(
                'ad_id' => $image->ad_id,
                'path' => $image->image_name,
                'name' => $image->image_name,
                'mime_type' => strtolower( $mime_type ),
                'enabled' => ! $image->disabled,
                'is_primary' => $image->is_primary,
                'created' => awpcp_datetime(),
            );

            $wpdb->insert( AWPCP_TABLE_MEDIA, $entry );
        }

        update_option( 'awpcp-migrate-media-information-cursor', $cursor );
        $remaining = $this->count_pending_images( $cursor );

        return array( $total, $remaining );
    }

    private function count_pending_images($cursor) {
        global $wpdb;

        $sql = 'SELECT count(key_id) FROM ' . AWPCP_TABLE_ADPHOTOS . '  ';
        $sql.= 'WHERE ad_id > %d ORDER BY key_id LIMIT 0, 100';

        return intval( $wpdb->get_var( $wpdb->prepare( $sql, $cursor ) ) );
    }
}
