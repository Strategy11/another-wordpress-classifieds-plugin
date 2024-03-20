<?php

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

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
    /**
     * Strips all of the HTML in the content.
     *
     * @param string $data Content to strip all HTML from.
     * @return string Content without any HTML.
     */
    function wp_strip_all_tags( $data ) {
        return strip_tags( $data );
    }
}

if ( ! function_exists( 'has_action' ) ) {
    /**
     * Check if any action has been registered for a hook.
     *
     * @param string $tag The name of the action hook.
     * @param callable|bool $function_to_check Optional. The callback to check for. Default false.
     * @return int|bool If $function_to_check is omitted, returns boolean for whether the hook has anything registered.
     *   When checking a specific function, the priority of that hook is returned, or false if the function is not attached.
     */
    function has_action( $tag, $function_to_check = false ) {
        return has_filter( $tag, $function_to_check );
    }
}

if ( ! function_exists( 'has_filter' ) ) {
    /**
     * Check if any filter has been registered for a hook.
     *
     * @param string $tag The name of the filter hook.
     * @param callable|bool $function_to_check Optional. The callback to check for. Default false.
     * @return int|bool If $function_to_check is omitted, returns boolean for whether the hook has anything registered.
     *   When checking a specific function, the priority of that hook is returned, or false if the function is not attached.
     */
    function has_filter( $tag, $function_to_check = false ) {
        global $wp_filter;

        if ( ! isset( $wp_filter[ $tag ] ) ) {
            return false;
        }

        return $wp_filter[ $tag ]->has_filter( $tag, $function_to_check );
    }
}
