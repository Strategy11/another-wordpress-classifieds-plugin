<?php
/**
 * @package AWPCP\Upgrade
 */

/**
 * Upgrade routine to store media records as attachments.
 */
class AWPCP_Store_Media_As_Attachments_Upgrade_Task_Handler implements AWPCP_Upgrade_Task_Runner {

    /**
     * @var object
     */
    private $settings;

    /**
     * @var object
     */
    private $wordpress;

    /**
     * @var object
     */
    private $db;

    /**
     * @param object $settings  An instance of SettingsAPI.
     * @param object $wordpress An instance of WordPress.
     * @param object $db        An instance of wpdb.
     */
    public function __construct( $settings, $wordpress, $db ) {
        $this->settings  = $settings;
        $this->wordpress = $wordpress;
        $this->db        = $db;
    }

    /**
     * @since 4.0.0
     */
    public function get_last_item_id() {
        // Not used.
    }

    /**
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function update_last_item_id( $last_item_id ) {
        // Not used.
    }

    /**
     * Count number items that need to be processed.
     *
     * @param int $last_item_id     The ID of the last item processed by the routine.
     */
    public function count_pending_items( $last_item_id ) {
        $query = 'SELECT COUNT(id) FROM ' . AWPCP_TABLE_MEDIA . ' WHERE id > %d';
        return intval( $this->db->get_var( $this->db->prepare( $query, $last_item_id ) ) );
    }

    /**
     * Get items that need to be processed.
     *
     * @param int $last_item_id     The ID of the last item processed by the routine.
     */
    public function get_pending_items( $last_item_id ) {
        $query = 'SELECT * FROM ' . AWPCP_TABLE_MEDIA . ' WHERE id > %d LIMIT 0, 20';
        return $this->db->get_results( $this->db->prepare( $query, $last_item_id ) );
    }

    /**
     * @param object $item          An item to process.
     * @param int    $last_item_id  The ID of the last item processed by the routine.
     * @throws AWPCP_Exception  If the associated file cannot be copied or stored.
     * @SuppressWarnings(UnusedFormalParameter)
     */
    public function process_item( $item, $last_item_id ) {
        if ( ! function_exists( 'media_handle_upload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
        }

        $parent_listing_id = $this->get_id_of_associated_listing( $item );

        if ( 0 === $parent_listing_id ) {
            debugf(
                sprintf( 'The file %s has no associated listing.', $file_path ),
                $item,
                $file_path,
                $file_name
            );

            return $item->id;
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

        // phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
        // phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged
        $file_was_copied = @copy( $file_path, $tmp_name );

        if ( ! $file_was_copied ) {
            throw new AWPCP_Exception( sprintf( "The file %s couldn't be copied to the temporary location %s", $file_path, $tmp_name ) );
        }

        $file_array  = array(
            'name'     => awpcp_sanitize_file_name( $file_name ),
            'tmp_name' => $tmp_name,
        );
        $description = '';

        // Do the validation and storage stuff.
        $attachment_id = $this->wordpress->handle_media_sideload( $file_array, $parent_listing_id, $description );

        // If error storing permanently, unlink.
        if ( is_wp_error( $attachment_id ) ) {
            throw new AWPCP_Exception( sprintf( "An attachment couldn't be created for media item with id %d", $item->id ) );
        } elseif ( file_exists( $tmp_name ) ) {
            // phpcs:disable WordPress.VIP.FileSystemWritesDisallow.file_ops_unlink
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

    /**
     * phpcs:disable WordPress.VIP.SlowDBQuery.slow_db_query_meta_key
     * phpcs:disable WordPress.VIP.SlowDBQuery.slow_db_query_meta_value
     *
     * @param object $item  An item that is being processed.
     */
    private function get_id_of_associated_listing( $item ) {
        // TODO: Use a new WP_Query instead.
        $listings = $this->wordpress->get_posts(
            [
                'post_type'  => 'awpcp_listing',
                'meta_key'   => '_awpcp_old_id',
                'meta_value' => $item->ad_id,
            ]
        );

        if ( ! is_array( $listings ) || empty( $listings ) ) {
            return 0;
        }

        return $listings[0]->ID;
    }
}
