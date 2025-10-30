<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



if ( ! function_exists( 'is_post_type_viewable' ) ) {
    /**
     * Determines whether a post type is considered "viewable".
     *
     * For built-in post types such as posts and pages, the 'public' value will be evaluated.
     * For all others, the 'publicly_queryable' value will be used.
     *
     * @since 4.4.0
     *
     * @param object $post_type_object Post type object.
     * @return bool Whether the post type should be considered viewable.
     */
    function is_post_type_viewable( $post_type_object ) {
        return $post_type_object->publicly_queryable || ( $post_type_object->_builtin && $post_type_object->public );
    }
}

if ( ! function_exists( 'wp_kses_post' ) ) {
    function wp_kses_post( $data ) {
        return $data;
    }
}
