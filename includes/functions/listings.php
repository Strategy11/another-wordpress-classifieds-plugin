<?php

/**
 * @since next-release
 */
function awpcp_display_listings( $query, $context, $options ) {
    $options = wp_parse_args( $options, array(
        'show_intro_message' => false,
        'show_menu_items' => false,
        'show_category_selector' => false,
        'show_pagination' => false,
        'before_content' => '',
        'before_pagination' => '',
        'before_list' => '',
        'after_pagination' => '',
        'after_content' => '',
    ) );

    // DONE?: see awpcp_browse_ads_template_action is being used and use the output of that function
    //      as the return value for this function.
    if ( has_action( 'awpcp_browse_ads_template_action' ) || has_filter( 'awpcp_browse_ads_template_filter' ) ) {
        do_action( 'awpcp_browse_ads_template_action' );
        return apply_filters( 'awpcp_browse_ads_template_filter' );
    }

    $results_per_page = absint( awpcp_request_param( 'results', get_awpcp_option( 'adresultsperpage', 10 ) ) );
    $results_offset = absint( awpcp_request_param( 'offset', 0 ) );

    if ( $results_per_page ) {
        $query['limit'] = $results_per_page;
        $query['offset'] = $results_offset;
    }

    $listings_collection = awpcp_listings_collection();

    $listings = $listings_collection->find_enabled_listings_with_query( $query );
    $listings_count = $listings_collection->count_enabled_listings_with_query( $query );

    // debugp( $listings, $listings_count );

    // DONE?: take a show_intro_message option.
    // DONE?: take a show_menu_items option.
    // DONE?: take a show_category_selector option.

    // DONE?: take a before_content option and insert it into the output
    // DONE?: use awpcp-listings-before-content or similar hook to show region selector depending on context:
    //      - show in standard plugin pages, except Search Ads
    //      - do not show in shortcodes and others?

    // awpcp-content-before-listings-page => before the page's content, classiwrapper first children
    // awpcp-listings-before-content => before listings pagination
    // awpcp-display-ads-before-list => after pagination, before list of listings
    // awpcp-listings-after-content => after listings pagination
    // awpcp-content-after-listings-page => right at the end of the page, before clasiwrapper closing tag

    // DONE?: apply filters for content before listings page and show that content.
    $before_content = apply_filters( 'awpcp-content-before-listings-page', $options['before_content'], $context );
    $before_pagination = array(
        10 => array(
            'category-selector' => awpcp_render_category_selector(),
            'user-content' => $options['before_pagination'],
        ),
    );
    $before_pagination = apply_filters( 'awpcp-listings-before-content', $before_pagination, $context );
    ksort( $before_pagination );
    $before_pagination = awpcp_flatten_array( $before_pagination );

    $before_list = apply_filters( 'awpcp-display-ads-before-list', $options['before_list'], $context );

    if ( $listings_count > 0 ) {
        // DONE?: show pagination at the top
        $pagination_options = array(
            'results' => $results_per_page,
            'offset' => $results_offset,
            'total' => $listings_count,
        );
        $pagination = $options['show_pagination'] ? awpcp_pagination( $pagination_options, awpcp_current_url() ) : '';

        $items = awpcp_render_listings_items( $listings, $context );
    } else {
        $pagination = '';
        $items = array();
    }
    // DONE?: show items list
    // DONE?: show pagination at the bottom

    // DONE?: apply filters for content after listings page and show that content.
    $after_pagination = array( 'user-content' => $options['after_pagination'] );
    $after_pagination = apply_filters( 'awpcp-listings-after-content', $after_pagination, $context );

    $after_content = apply_filters( 'awpcp-content-after-listings-page', $options['after_content'], $context );

    ob_start();
    include( AWPCP_DIR . '/templates/frontend/listings.tpl.php' );
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}

/**
 * @since next-release
 */
function awpcp_display_listings_in_page( $query, $context, $options = array() ) {
    $options = wp_parse_args( $options, array(
        'show_intro_message' => true,
        'show_menu_items' => true,
        'show_category_selector' => true,
        'show_pagination' => true,
    ) );

    return awpcp_display_listings( $query, $context, $options );
}
