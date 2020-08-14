<?php

/**
 * @since 4.0.0
 */
function awpcp_tests_create_listing() {
    return awpcp_tests_get_empty_ad();
}

/**
 * @since 4.0.0
 */
function awpcp_tests_create_empty_listing() {
    return awpcp_tests_get_empty_ad();
}

function awpcp_tests_get_empty_ad() {
    $post_id = wp_insert_post( array(
        'post_title' => 'Test Listing',
        'post_type' => 'awpcp_listing',
        'post_date_gmt' => get_gmt_from_date( current_time( 'mysql' ) ),
    ), true );

    return is_wp_error( $post_id ) ? null : get_post( $post_id );
}

/**
 * @since 4.0.0
 */
function awpcp_tests_delete_all_listings() {
    $posts = get_posts( array(
        'post_type' => 'awpcp_listing',
        'post_status' => array( 'draft', 'publish', 'disabled', 'pending' ),
        'posts_per_page' => -1,
    ) );

    foreach ( $posts as $post ) {
        wp_delete_post( $post->ID, true /*bypass trash and force deletion*/ );
    }
}

function awpcp_tests_create_attachment( $args = array() ) {
    $args = wp_parse_args( $args, array( 'post_type' => 'attachment' ) );

    $factory = new WP_UnitTest_Factory();

    $attachment_id = $factory->post->create( $args );

    return get_post( $attachment_id );
}
