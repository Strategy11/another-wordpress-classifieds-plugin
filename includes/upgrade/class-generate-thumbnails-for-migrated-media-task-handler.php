<?php
/**
 * @package AWPCP\Upgrade
 */

/**
 * Upgrade routine to genrate thumbnails for migrated media.
 */
class AWPCP_GenerateThumbnailsForMigratedMediaTaskHandler implements AWPCP_Upgrade_Task_Runner {

    /**
     * @var WordPress
     */
    private $wordpress;

    public function __construct( $wordpress ) {
        $this->wordpress = $wordpress;
    }

    public function get_last_item_id() {
        // Not used.
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function update_last_item_id( $last_item_id ) {
        // Not used.
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function count_pending_items( $last_item_id ) {
        $query = $this->wordpress->create_posts_query(
            [
                'post_type'   => 'attachment',
                'post_status' => 'inherit',
                'meta_query'  => [
                    [
                        'key'     => '_awpcp_generate_intermediate_image_sizes',
                        'compare' => 'EXISTS',
                    ],
                ],
            ]
        );

        return $query->found_posts;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_pending_items( $last_item_id ) {
        $query = $this->wordpress->create_posts_query(
            [
                'posts_per_page' => 1,
                'orderby'        => 'ID',
                'order'          => 'ASC',
                'post_type'      => 'attachment',
                'post_status'    => 'inherit',
                'meta_query'     => [
                    [
                        'key'     => '_awpcp_generate_intermediate_image_sizes',
                        'compare' => 'EXISTS',
                    ],
                ],
            ]
        );

        return $query->posts;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process_item( $item, $last_item_id ) {
        if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
            require_once ABSPATH . 'wp-admin/includes/admin.php';
        }

        $pending_sizes = $this->wordpress->get_post_meta( $item->ID, '_awpcp_generate_intermediate_image_sizes', true );

        // There is no information about what image sizes to generate. Skip this attachment.
        if ( ! is_array( $pending_sizes ) || 0 === count( $pending_sizes ) ) {
            $this->wordpress->delete_post_meta( $item->ID, '_awpcp_generate_intermediate_image_sizes' );

            return $item->ID;
        }

        $old_metadata = wp_get_attachment_metadata( $item->ID );
        $new_metadata = $this->generate_intermediate_image_sizes( $item, [ $pending_sizes[0] ] );

        $new_metadata['sizes'] = array_merge( $old_metadata['sizes'], $new_metadata['sizes'] );

        wp_update_attachment_metadata( 2597, $new_metadata );

        $this->update_or_delete_pending_image_sizes( $item, array_slice( $pending_sizes, 1 ) );

        return $item->ID;
    }

    private function generate_intermediate_image_sizes( $item, $wanted_sizes ) {
        /**
         * Handler for the intermediate_image_sizes_advanced filter used to
         * force WordPress to generate the intermediate image sizes we want only.
         */
        $callback = function( $sizes ) use ( $wanted_sizes ) {
            $new_sizes = [];

            foreach ( $wanted_sizes as $size ) {
                if ( isset( $sizes[ $size ] ) ) {
                    $new_sizes[ $size ] = $sizes[ $size ];
                }
            }

            return $new_sizes;
        };

        add_filter( 'intermediate_image_sizes_advanced', $callback, 10, 2 );
        $new_metadata = wp_generate_attachment_metadata( $item->ID, get_attached_file( $item->ID ) );
        remove_filter( 'intermediate_image_sizes_advanced', $callback, 10, 2 );

        return $new_metadata;
    }

    private function update_or_delete_pending_image_sizes( $item, $pending_sizes ) {
        if ( $pending_sizes ) {
            return $this->wordpress->update_post_meta( $item->ID, '_awpcp_generate_intermediate_image_sizes', $pending_sizes );
        }

        return $this->wordpress->delete_post_meta( $item->ID, '_awpcp_generate_intermediate_image_sizes' );
    }
}
