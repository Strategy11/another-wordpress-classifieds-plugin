<?php

/**
 * Returns the current name of the AWPCP main page.
 */
function get_currentpagename() {
    return get_awpcp_option('main-page-name');
}

/**
 * Return the number of pages with the given post_name.
 */
function checkforduplicate($cpagename_awpcp) {
    global $wpdb;

    $awpcppagename = sanitize_title( $cpagename_awpcp );

    $query = "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = %s";
    $query = $wpdb->prepare( $query, $awpcppagename, 'post' );

    $post_ids = $wpdb->get_col( $query );

    if ( $post_ids !== false ) {
        return count( $post_ids );
    } else {
        return '';
    }
}

/**
 * Check if the page identified by $refname exists.
 */
function awpcp_find_page($refname) {
    global $wpdb;

    $query = 'SELECT posts.ID, page FROM ' . $wpdb->posts . ' AS posts ';
    $query.= 'LEFT JOIN ' . AWPCP_TABLE_PAGES . ' AS pages ';
    $query.= 'ON (posts.ID = pages.id) WHERE pages.page = %s';

    $query = $wpdb->prepare($query, $refname);
    $pages = $wpdb->get_results($query);

    return $pages !== false && !empty($pages);
}

/**
 * Get the id of a page by its name.
 */
function awpcp_get_page_id( $name ) {
    global $wpdb;

    if ( ! empty( $name ) ) {
        $sql = "SELECT ID FROM {$wpdb->posts} WHERE post_name = '$name'";
        $id = $wpdb->get_var( $sql );
        return $id;
    }

    return 0;
}

/**
 * Returns the ID of WP Page associated to a page-name setting.
 *
 * @param $refname the name of the setting that holds the name of the page
 */
function awpcp_get_page_id_by_ref( $refname ) {
    $all_pages_ids = awppc_get_pages_ids();
    return isset( $all_pages_ids[ $refname ] ) ? $all_pages_ids[ $refname ] : false;
}

/**
 * Return the IDs of WP pages associated with AWPCP pages.
 *
 * @return array Array of Page IDs
 */
function awpcp_get_page_ids_by_ref( $refnames ) {
    $all_pages_ids = awppc_get_pages_ids();

    $pages_ids = array();
    foreach ( $refnames as $refname ) {
        if ( isset( $all_pages_ids[ $refname ] ) ) {
            $pages_ids[] = $all_pages_ids[ $refname ];
        }
    }

    return $pages_ids;
}

/**
 * @since next-release
 */
function awppc_get_pages_ids() {
    static $pages_ids;

    if ( is_null( $pages_ids ) ) {
        $pages_ids = awpcp_get_pages_ids_from_db();
    }

    return $pages_ids;
}

/**
 * @since next-release
 */
function awpcp_get_pages_ids_from_db() {
    global $wpdb;

    $query = 'SELECT page, id FROM ' . AWPCP_TABLE_PAGES;
    $results = $wpdb->get_results( $query );

    $pages_ids = array();
    foreach ( $results as $row ) {
        $pages_ids[ $row->page ] = $row->id;
    }

    return $pages_ids;
}

if ( ! function_exists( 'is_awpcp_page' ) ) {
    /**
     * Check if the current page is one of the AWPCP pages.
     *
     * @since next-release
     */
    function is_awpcp_page() {
        global $wpdb, $wp_the_query;

        $page_names = implode( "', '", array_keys( awpcp_pages() ) );
        $page_ids = $wpdb->get_col( sprintf( 'SELECT id FROM ' . AWPCP_TABLE_PAGES . " WHERE page IN ('%s')", $page_names ) );

        return $wp_the_query && in_array( $wp_the_query->get_queried_object_id(), $page_ids );
    }
}

function is_awpcp_browse_listings_page() {
    return awpcp_queried_object_is_page_that_has_shortcode( 'AWPCPBROWSEADS' );
}

function awpcp_queried_object_is_page_that_has_shortcode( $shortcode ) {
    global $wp_the_query;

    if ( ! $wp_the_query || ! $wp_the_query->is_page() ) {
        return false;
    }

    $page = $wp_the_query->get_queried_object();

    if ( ! $page || ! has_shortcode( $page->post_content, $shortcode ) ) {
        return false;
    }

    return true;
}

function is_awpcp_browse_categories_page() {
    return awpcp_queried_object_is_page_that_has_shortcode( 'AWPCPBROWSECATS' );
}

function url_showad($ad_id) {
    try {
        $ad = awpcp_listings_collection()->get( $ad_id );
    } catch( AWPCP_Exception $e ) {
        return false;
    }

    $seoFriendlyUrls = get_awpcp_option('seofriendlyurls');
    $permastruc = get_option('permalink_structure');

    $awpcp_showad_pageid = awpcp_get_page_id_by_ref('show-ads-page-name');
    $base_url = get_permalink($awpcp_showad_pageid);
    $url = false;

    $params = array('id' => $ad_id);

    if($seoFriendlyUrls && isset($permastruc) && !empty($permastruc)) {
        $url = sprintf( '%s/%s', trim( $base_url, '/' ), $ad_id );

        $region = $ad->get_first_region();

        $parts = array();

        if ( get_awpcp_option( 'include-title-in-listing-url' ) ) {
            $parts[] = sanitize_title( $ad->get_title() );
        }

        if( get_awpcp_option( 'include-city-in-listing-url' ) && $region ) {
            $parts[] = sanitize_title( awpcp_array_data( 'city', '', $region ) );
        }
        if( get_awpcp_option( 'include-state-in-listing-url' ) && $region ) {
            $parts[] = sanitize_title( awpcp_array_data( 'state', '', $region ) );
        }
        if( get_awpcp_option( 'include-country-in-listing-url' ) && $region ) {
            $parts[] = sanitize_title( awpcp_array_data( 'country', '', $region ) );
        }
        if( get_awpcp_option( 'include-county-in-listing-url' ) && $region ) {
            $parts[] = sanitize_title( awpcp_array_data( 'county', '', $region ) );
        }
        if( get_awpcp_option( 'include-category-in-listing-url' ) ) {
            $awpcp_ad_category_id = $ad->ad_category_id;
            $parts[] = sanitize_title(get_adcatname($awpcp_ad_category_id));
        }

        // always append a slash (RSS module issue)
        $url = sprintf( "%s%s", trailingslashit( $url ), join( '/', array_filter( $parts ) ) );
        $url = user_trailingslashit($url);
    } else {
        $base_url = user_trailingslashit($base_url);
        $url = add_query_arg($params, $base_url);
    }

    return apply_filters( 'awpcp-listing-url', $url, $ad );
}

/**
 * @since next-release
 */
function awpcp_get_browse_category_url_from_id( $category_id ) {
    try {
        $category = awpcp_listings_collection()->get( $category_id );
        $category_url = url_browsecategory( $category );
    } catch ( AWPCP_Exception $ex ) {
        $category_url = '';
    }

    return $category_url;
}

function url_browsecategory( $category ) {
    $permalinks = get_option('permalink_structure');
    $base_url = awpcp_get_page_url('browse-categories-page-name');

    $cat_id = $category->id;
    $cat_slug = sanitize_title( $category->name );

    if (get_awpcp_option('seofriendlyurls')) {
        if (!empty($permalinks)) {
            $url_browsecats = sprintf('%s/%s/%s', trim($base_url, '/'), $cat_id, $cat_slug);
        } else {
            $params = array('a' => 'browsecat', 'category_id' => $cat_id);
            $url_browsecats = add_query_arg($params, $base_url);
        }
    } else {
        if (!empty($permalinks)) {
            $params = array('category_id' => "$cat_id/$cat_slug");
        } else {
            $params = array('a' => 'browsecat', 'category_id' => $cat_id);
        }
        $url_browsecats = add_query_arg($params, $base_url);
    }

    return user_trailingslashit($url_browsecats);
}

function url_placead() {
    return user_trailingslashit(awpcp_get_page_url('place-ad-page-name'));
}

/**
 * @deprecated deprecated since 2.0.6.
 */
function url_classifiedspage() {
    return awpcp_get_main_page_url();
}

function url_searchads() {
    return user_trailingslashit(awpcp_get_page_url('search-ads-page-name'));
}

function url_editad() {
    return user_trailingslashit(awpcp_get_page_url('edit-ad-page-name'));
}

/**
 * Return name of current AWPCP page.
 *
 * This is part of an effor to put all AWPCP functions under
 * the same namespace.
 */
function awpcp_get_main_page_name() {
    return get_awpcp_option('main-page-name');
}

/**
 * Always return the full URL, even if AWPCP main page
 * is also the home page.
 */
function awpcp_get_main_page_url() {
    $id = awpcp_get_page_id_by_ref('main-page-name');

    if (get_option('permalink_structure')) {
        $url = home_url(get_page_uri($id));
    } else {
        $url = add_query_arg('page_id', $id, home_url());
    }

    return user_trailingslashit($url);
}

/**
 * Returns a link to an AWPCP page identified by $pagename.
 *
 * Always return the full URL, even if the page is set as
 * the homepage.
 *
 * The returned URL has no trailing slash. However, if the
 * $trailinghslashit parameter is set to true, the returned URL
 * will be passed through user_trailingslashit() function.
 *
 * If permalinks are disabled, the home url will have
 * a trailing slash.
 *
 * @since 2.0.7
 */
function awpcp_get_page_url($pagename, $trailingslashit=false) {
    global $wp_rewrite;

    $id = awpcp_get_page_id_by_ref($pagename);

    if (get_option('permalink_structure')) {
        $permalink = $wp_rewrite->get_page_permastruct();
        $permalink = str_replace( '%pagename%', get_page_uri( $id ), $permalink );

        $url = home_url( $permalink );
        $url = $trailingslashit ? user_trailingslashit( $url ) : rtrim($url, '/');
    } else {
        $url = add_query_arg( 'page_id', $id, home_url('/') );
    }

    return $url;
}

/**
 * @since 3.0.2
 */
function awpcp_get_view_categories_url() {
    $permalinks = get_option('permalink_structure');
    $main_page_id = awpcp_get_page_id_by_ref('main-page-name');
    $page_name = get_awpcp_option('view-categories-page-name');
    $slug = sanitize_title($page_name);

    if ( !empty( $permalinks ) ) {
        $url = sprintf( '%s/%s', trim( home_url( get_page_uri( $main_page_id ) ), '/' ), $slug );
        $url = user_trailingslashit( $url );
    } else {
        $url = add_query_arg( array( 'page_id' => $main_page_id, 'layout' => 2 ), home_url('/') );
    }

    return $url;
}

/**
 * @since next-release
 */
function awpcp_get_edit_listing_url( $listing ) {
    if ( awpcp()->settings->get_option( 'requireuserregistration' ) ) {
        $url = awpcp_get_edit_listing_direct_url( $listing );
    } else {
        $url = awpcp_get_edit_listing_generic_url();
    }

    return apply_filters( 'awpcp-edit-listing-url', $url, $listing );
}

/**
 * @since next-release
 */
function awpcp_get_edit_listing_direct_url( $listing ) {
    if ( awpcp()->settings->get_option( 'enable-user-panel' ) ) {
        return add_query_arg( array( 'action' => 'edit', 'id' => $listing->ad_id ), awpcp_get_user_panel_url() );
    } else {
        return awpcp_get_edit_listing_page_url_with_listing_id( $listing );
    }
}

/**
 * @since next-release
 */
function awpcp_get_edit_listing_page_url_with_listing_id( $listing ) {
    $permalinks = get_option( 'permalink_structure' );

    if ( ! empty( $permalinks ) && get_awpcp_option( 'seofriendlyurls' ) ) {
        $url = sprintf( '%s/%d', trim( awpcp_get_page_url( 'edit-ad-page-name' ) ), $listing->ad_id );
        $url = user_trailingslashit( $url );
    } else {
        $url = add_query_arg( 'id', $listing->ad_id, awpcp_get_page_url( 'edit-ad-page-name' ) );
    }

    return $url;
}

/**
 * @since next-release
 */
function awpcp_get_edit_listing_generic_url() {
    if ( awpcp()->settings->get_option( 'enable-user-panel' ) ) {
        return awpcp_get_user_panel_url();
    } else {
        return awpcp_get_page_url( 'edit-ad-page-name' );
    }
}

/**
 * Returns a link that can be used to initiate the Ad Renewal process.
 *
 * @since 2.0.7
 */
function awpcp_get_renew_ad_url($ad_id) {
    $hash = awpcp_get_renew_ad_hash( $ad_id );
    if ( get_awpcp_option( 'enable-user-panel' ) == 1 ) {
        $url = awpcp_get_user_panel_url();
        $url = add_query_arg( array( 'id' => $ad_id, 'action' => 'renew', 'awpcprah' => $hash ), $url );
    } else {
        $url = awpcp_get_page_url('renew-ad-page-name');
        $url = add_query_arg( array( 'ad_id' => $ad_id, 'awpcprah' => $hash ), $url );
    }

    return $url;
}

/**
 * @since 3.0.2
 */
function awpcp_get_email_verification_url( $ad_id ) {
    $hash = awpcp_get_email_verification_hash( $ad_id );

    if ( get_option( 'permalink_structure' ) ) {
        return home_url( "/awpcpx/listings/verify/{$ad_id}/$hash" );
    } else {
        $params = array(
            'awpcpx' => true,
            'awpcp-module' => 'listings',
            'awpcp-action' => 'verify',
            'awpcp-ad' => $ad_id,
            'awpcp-hash' => $hash,
        );

        return add_query_arg( $params, home_url( 'index.php' ) );
    }

    return user_trailingslashit( $url );
}

/**
 * Returns a link to the page where visitors can contact the Ad's owner
 *
 * @since  3.0.0
 */
function awpcp_get_reply_to_ad_url($ad_id, $ad_title=null) {
    $base_url = awpcp_get_page_url('reply-to-ad-page-name');
    $permalinks = get_option('permalink_structure');
    $url = false;

    if (!is_null($ad_title)) {
        $title = sanitize_title($ad_title);
    } else {
        $title = sanitize_title(AWPCP_Ad::find_by_id($ad_id)->ad_title);
    }

    if (get_awpcp_option('seofriendlyurls')) {
        if (get_option('permalink_structure')) {
            $url = sprintf("%s/%s/%s", $base_url, $ad_id, $title);
            $url = user_trailingslashit($url);
        }
    }

    if ($url === false) {
        $base_url = user_trailingslashit($base_url);
        $url = add_query_arg(array('i' => $ad_id), $base_url);
    }

    return $url;
}

/**
 * @since  3.0
 */
function awpcp_get_admin_panel_url() {
    return add_query_arg( 'page', 'awpcp.php', admin_url('admin.php'));
}

/**
 * @since 3.0.2
 */
function awpcp_get_admin_settings_url( $group = false ) {
    return add_query_arg( array( 'page' => 'awpcp-admin-settings', 'g' => $group ), admin_url( 'admin.php' ) );
}

/**
 * @since 3.2.1
 */
function awpcp_get_admin_credit_plans_url() {
    return add_query_arg( 'page', 'awpcp-admin-credit-plans', admin_url( 'admin.php' ) );
}

/**
 * @since 3.2.1
 */
function awpcp_get_admin_fees_url() {
    return add_query_arg( 'page', 'awpcp-admin-fees', admin_url( 'admin.php' ) );
}

/**
 * @since 3.0.2
 */
function awpcp_get_admin_categories_url() {
    return add_query_arg( 'page', 'awpcp-admin-categories', admin_url( 'admin.php' ) );
}

/**
 * @since  3.0
 */
function awpcp_get_admin_upgrade_url() {
    return add_query_arg( 'page', 'awpcp-admin-upgrade', admin_url('admin.php'));
}

/**
 * Returns a link to Manage Listings
 *
 * @since 2.1.4
 */
function awpcp_get_admin_listings_url() {
    return admin_url('admin.php?page=awpcp-listings');
}

/**
 * Returns a link to Ad Management (a.k.a User Panel).
 *
 * @since 2.0.7
 */
function awpcp_get_user_panel_url( $params=array() ) {
    return add_query_arg( $params, admin_url( 'admin.php?page=awpcp-panel' ) );
}


function awpcp_current_url() {
    return (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Returns the domain used in the current request, optionally stripping
 * the www part of the domain.
 *
 * @since 2.0.6
 * @param $www  boolean     true to include the 'www' part,
 *                          false to attempt to strip it.
 */
function awpcp_get_current_domain($www=true, $prefix='') {
    _deprecated_function( __FUNCTION__, '3.2.3', 'awpcp_request()->domain( $include_www, $www_prefix_replacement )' );
    return awpcp_request()->domain( $www, $prefix );
}

/**
 * Bulds WordPress ajax URL using the same domain used in the current request.
 *
 * @since 2.0.6
 */
function awpcp_ajaxurl($overwrite=false) {
    static $ajaxurl = false;

    if ($overwrite || $ajaxurl === false) {
        $url = admin_url('admin-ajax.php');
        $parts = parse_url($url);
        $ajaxurl = str_replace($parts['host'], awpcp_request()->domain(), $url);
    }

    return $ajaxurl;
}
