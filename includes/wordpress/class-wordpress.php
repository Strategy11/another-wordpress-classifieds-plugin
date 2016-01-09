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

    public function add_post_meta( $post_id, $meta_key, $meta_value, $unique = false ) {
        return add_post_meta( $post_id, $meta_key, $meta_value, $unique );
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

    public function add_object_terms( $object_id, $terms, $taxonomy ) {
        return wp_add_object_terms( $object_id, $terms, $taxonomy );
    }

    /* Attachments */

    public function handle_media_sideload( $file_array, $parent_post_id, $description ) {
        return media_handle_sideload( $file_array, $parent_post_id, $description );
    }
}
