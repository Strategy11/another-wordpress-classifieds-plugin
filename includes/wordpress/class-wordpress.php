<?php
/**
 * @package AWPCP\WordPress
 */

// phpcs:disable Squiz.Commenting
// phpcs:disable WordPress.VIP.RestrictedFunctions.get_posts_get_posts

function awpcp_wordpress() {
    return new AWPCP_WordPress();
}

/**
 * @SuppressWarnings(BooleanArgumentFlag)
 * @SuppressWarnings(TooManyPublicMethods)
 */
class AWPCP_WordPress {

    /* Users */

    public function get_user_by( $field, $value ) {
        return get_user_by( $field, $value );
    }

    /* Options */

    public function get_option( $option, $key = false ) {
        return get_option( $option, $key );
    }

    public function update_option( $option, $new_value, $autoload = null ) {
        return update_option( $option, $new_value, $autoload );
    }

    public function delete_option( $option ) {
        return delete_option( $option );
    }

    /* Custom Post Types */

    public function insert_post( $post, $return_wp_error_on_failure = false ) {
        return wp_insert_post( $post, $return_wp_error_on_failure );
    }

    public function update_post( $post, $return_wp_error_on_failure = false ) {
        return wp_update_post( $post, $return_wp_error_on_failure );
    }

    public function delete_post( $post_id, $force_delete = false ) {
        return wp_delete_post( $post_id, $force_delete );
    }

    public function get_post( $post = null, $output = OBJECT, $filter = 'raw' ) {
        return get_post( $post, $output, $filter );
    }

    public function get_posts( $args = array() ) {
        return get_posts( $args );
    }

    public function add_post_meta( $post_id, $meta_key, $meta_value, $unique = false ) {
        return add_post_meta( $post_id, $meta_key, $meta_value, $unique );
    }

    public function update_post_meta( $post_id, $meta_key, $meta_value, $prev_value = '' ) {
        return update_post_meta( $post_id, $meta_key, $meta_value, $prev_value );
    }

    public function get_post_meta( $post_id, $meta_key = '', $single = false ) {
        return get_post_meta( $post_id, $meta_key, $single );
    }

    public function delete_post_meta( $post_id, $meta_key, $meta_value = '' ) {
        return delete_post_meta( $post_id, $meta_key, $meta_value );
    }

    public function get_edit_post_link( $post, $context = 'display' ) {
        return get_edit_post_link( $post, $context );
    }

    /* Taxonomies */

    public function insert_term( $term, $taxonomy, $args = array() ) {
        return wp_insert_term( $term, $taxonomy, $args );
    }

    /**
     * Inserts a new term using wp_insert_term_data to specify the ID of the
     * term, bypassing the AUTO_INCREMENT counter.
     *
     * @since 4.0.0
     */
    public function insert_term_with_id( $term_id, $term, $taxonomy, $args ) {
        $force_term_id = function( $data ) use ( $term_id ) {
            $data['term_id'] = $term_id;

            return $data;
        };

        add_filter( 'wp_insert_term_data', $force_term_id );

        $result = $this->insert_term( $term, $taxonomy, $args );

        remove_filter( 'wp_insert_term_data', $force_term_id );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        // wp_insert_term() likely returned the term_id (and term_taxonomy_id) from
        // an existing term as it found out that the term we attempted to create was
        // a duplicate.
        //
        // See https://github.com/WordPress/WordPress/blob/da7a80d67fea29c2badfc538bfc01c8a585f0cbe/wp-includes/taxonomy.php#L2326
        //
        // This is very unlikely to happen, but I only want to mess with the term_taxonomy
        // table below if a new record was added to the database. When the returned ID
        // is not what we asked it to be, we know for sure that information
        // from a different record was returned.
        //
        // Additionally, plugins will be able to control the returned IDs on 5.0.1
        // and above, but that also seems very unlikely to happen considering we plan
        // to force the ID for new terms during the 4.0.0 upgrade only. At that
        // time no other plugins should be aware of our taxonomy.
        //
        // See https://github.com/WordPress/WordPress/commit/8142df82bcfa9201f0bb48499f89e5d2e957697
        if ( $term_id !== $result['term_id'] ) {
            return $result;
        }

        // We want both IDs to be equal, as it should be on most installations that
        // started using WordPress after the split shared terms update (WP 4.2).
        //
        // If term_id is less than term_taxonomy_id then the IDs were already different
        // before we started adding our own terms and we won't change that.
        //
        // If term_id is greater than term_taxonomy_id then we are likely to be
        // the reason those IDs are different and we want to fix it, even some
        // IDs are lost in the process.
        if ( $result['term_id'] <= $result['term_taxonomy_id'] ) {
            return $result;
        }

        $term_object = get_term( $result['term_id'], $taxonomy );

        $this->db = $GLOBALS['wpdb'];

        $term_taxonomy_deleted = $this->db->delete(
            $this->db->term_taxonomy,
            [
                'term_taxonomy_id' => $result['term_taxonomy_id'],
            ]
        );

        // The row wasn't deleted. Let's use the IDs that we received from wp_insert_term().
        if ( false === $term_taxonomy_deleted ) {
            return $result;
        }

        $term_taxonomy_inserted = $this->db->insert(
            $this->db->term_taxonomy,
            [
                'term_id'          => $term_object->term_id,
                'term_taxonomy_id' => $term_object->term_id,
                'taxonomy'         => $term_object->taxonomy,
                'description'      => $term_object->description,
                'parent'           => $term_object->parent,
                'count'            => $term_object->count,
            ]
        );

        // This is bad. Now the new term is not connected with a record on term_taxonomy.
        if ( false === $term_taxonomy_inserted ) {
            $message = 'There was an error trying to create a record on term_taxonomy table using a custom ID: {error_message}';
            $message = str_replace( '{error_message}', $this->db->last_error, $message );

            throw new AWPCP_Exception( $message );
        }

        return [
            'term_id'          => $result['term_id'],
            'term_taxonomy_id' => intval( $this->db->insert_id ),
        ];
    }

    public function update_term( $temr_id, $taxonomy, $args = array() ) {
        return wp_update_term( $temr_id, $taxonomy, $args );
    }

    public function delete_term( $term_id, $taxonomy, $args = array() ) {
        return wp_delete_term( $term_id, $taxonomy, $args );
    }

    public function get_term_by( $field = 'id', $value, $taxonomy, $output = OBJECT, $filter = 'raw' ) {
        return get_term_by( $field, $value, $taxonomy, $output, $filter );
    }

    public function get_terms( $taxonomies, $args = array() ) {
        return get_terms( $taxonomies, $args );
    }

    public function get_term_hierarchy( $taxonomy ) {
        return _get_term_hierarchy( $taxonomy );
    }

    public function set_object_terms( $object_id, $terms, $taxonomy, $append = false ) {
        return wp_set_object_terms( $object_id, $terms, $taxonomy, $append );
    }

    public function add_object_terms( $object_id, $terms, $taxonomy ) {
        return wp_add_object_terms( $object_id, $terms, $taxonomy );
    }

    public function get_object_terms( $objects_ids, $taxonomies, $args = array() ) {
        return wp_get_object_terms( $objects_ids, $taxonomies, $args );
    }

    /* Attachments */

    public function handle_media_sideload( $file_array, $parent_post_id, $description ) {
        return media_handle_sideload( $file_array, $parent_post_id, $description );
    }

    /**
     * @since 4.0.0
     */
    public function get_attachment_url( $attachment_id ) {
        return wp_get_attachment_url( $attachment_id );
    }

    public function get_attachment_image_url( $attachment_id, $size = 'thumbnail', $icon = false ) {
        return wp_get_attachment_image_url( $attachment_id, $size, $icon );
    }

    public function get_attachment_image( $attachment_id, $size = 'thumbnail', $icon = false, $attr = array() ) {
        return wp_get_attachment_image( $attachment_id, $size, $icon, $attr );
    }

    public function get_attachment_image_src( $attachment_id, $size = 'thumbnail', $icon = false ) {
        return wp_get_attachment_image_src( $attachment_id, $size, $icon );
    }

    public function delete_attachment( $attachment_id, $force_delete = false ) {
        return wp_delete_attachment( $attachment_id, $force_delete );
    }

    /**
     * @since 4.0.0
     */
    public function set_post_thumbnail( $post_id, $attachment_id ) {
        return set_post_thumbnail( $post_id, $attachment_id );
    }

    /* Others */

    public function schedule_single_event( $timestamp, $hook, $args ) {
        return wp_schedule_single_event( $timestamp, $hook, $args );
    }

    public function current_time( $time, $gmt = 0 ) {
        return current_time( $time, $gmt );
    }

    /* Misc */

    public function create_posts_query( $query = array() ) {
        return new WP_Query( $query );
    }
}
