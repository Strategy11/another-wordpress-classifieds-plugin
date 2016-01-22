<?php

function awpcp_wordpress() {
    return new AWPCP_WordPress();
}

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

    /* Custom Post Types */

    public function insert_post( $post, $return_wp_error_on_failure = false ) {
        return wp_insert_post( $post, $return_wp_error_on_failure );
    }

    public function update_post( $post, $return_wp_error_on_failure = false ) {
        return wp_update_post( $post, $return_wp_error_on_failure );
    }

    public function get_post( $post = null, $output = OBJECT, $filter = 'raw' ) {
        return get_post( $post, $output, $filter );
    }

    public function add_post_meta( $post_id, $meta_key, $meta_value, $unique = false ) {
        return add_post_meta( $post_id, $meta_key, $meta_value, $unique );
    }

    public function get_post_meta( $post_id, $key = '', $single = false ) {
        return get_post_meta( $post_id, $key, $single );
    }

    /* Taxonomies */

    public function insert_term( $term, $taxonomy, $args = array() ) {
        return wp_insert_term( $term, $taxonomy, $args );
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

    public function get_attachment_image_url( $attachment_id, $size = 'thumbnail', $icon = false ) {
        return wp_get_attachment_image_url( $attachment_id, $size, $icon );
    }
}
