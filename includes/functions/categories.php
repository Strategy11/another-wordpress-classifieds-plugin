<?php

/**
 * @since 3.4
 */
function awpcp_build_categories_hierarchy( &$categories, $hide_empty ) {
    $hierarchy = array( 'root' => array() );

    foreach ( $categories as $category ) {
        $listings_count = total_ads_in_cat( $category->term_id );

        if ( !$hide_empty || $listings_count > 0 ) {
            if ( $category->parent == 0 ) {
                $hierarchy['root'][] = $category;
            } else {
                $hierarchy[ $category->parent ][] = $category;
            }
        }

    }

    return $hierarchy;
}

/**
 * @since 3.4
 */
function awpcp_organize_categories_by_id( &$categories ) {
    $organized = array();

    foreach ( $categories as $category ) {
        $organized[ $category->id ] = $category;
    }

    return $organized;
}

/**
 * @param $categories   Array of categories index by Category ID.
 * @since 3.4
 */
function awpcp_get_category_hierarchy( $category_id, &$categories ) {
    $category_parents = array();

    while ( $category_id > 0 && isset( $categories[ $category_id ] ) ) {
        $category_parents[] = $categories[ $category_id ];
        $category_id = $categories[ $category_id ]->parent;
    }

    return $category_parents;
}

/**
 * @since 3.4
 */
function awpcp_render_categories_dropdown_options( &$categories, &$hierarchy, $selected_category ) {
    $output = '';

    foreach ( $categories as $category ) {
        $category_name = stripslashes( stripslashes( $category->name ) );

        if( $category->term_id == $selected_category ) {
            $item = '<option class="dropdownparentcategory" selected="selected" value="' . $category->term_id . '">' . $category_name . '</option>';
            $item = '<option selected="selected" value="' . $category->term_id . '">- ' . $category_name . '</option>';
        } else {
            $item = '<option class="dropdownparentcategory" value="' . $category->term_id . '">' . $category_name . '</option>';
            $item = '<option value="' . $category->term_id . '">-' . $category_name . '</option>';
        }

        $output .= awpcp_render_categories_dropdown_option( $category, $selected_category );

        if ( isset( $hierarchy[ $category->term_id ] ) ) {
            $output .= awpcp_render_categories_dropdown_options( $hierarchy[ $category->term_id ], $hierarchy, $selected_category );
        }
    }

    return $output;
}

/**
 * @since 3.4
 */
function awpcp_render_categories_dropdown_option( $category, $selected_category ) {
    if ( $selected_category == $category->term_id ) {
        $selected_attribute = 'selected="selected"';
    } else {
        $selected_attribute = '';
    }

    if ( $category->parent == 0 ) {
        $class_attribute = 'class="dropdownparentcategory"';
        $category_name = esc_html( $category->name );
    } else {
        $class_attribute = '';
        $category_name = sprintf('- %s', esc_html( $category->name ) );
    }

    return sprintf(
        '<option %s %s value="%d">%s</option>',
        $class_attribute,
        $selected_attribute,
        esc_attr( $category->term_id ),
        $category_name
    );
}

/**
 * @since 3.4
 */
function awpcp_get_count_of_listings_in_categories() {
    static $listings_count;

    if ( is_null( $listings_count ) ) {
        $listings_count = awpcp_count_listings_in_categories();
    }

    return $listings_count;
}

/**
 * @since 3.4
 * @since feature/1112  Modified to work with custom post type and custom taxonomies.
 */
function awpcp_count_listings_in_categories() {
    $listings_count = array();

    foreach ( awpcp_categories_collection()->get_all() as $category ) {
        $listings_count[ $category->term_id ] = awpcp_count_listings_in_category( $category->term_id );
    }

    return $listings_count;
}

/**
 * TODO: Make sure other moduels (Like regions) are able to filter the query
 *       and their own parameters.
 *
 *       See the old implementation of awpcp_count_listings_in_categories
 *       (up to, at least, version 3.6.3.1).
 *
 * @since feature/1112
 */
function awpcp_count_listings_in_category( $category_id ) {
    $cache_entry_key = 'term-padded-count-' . $category_id;
    $cache_entry_found = false;

    // $listings_count = intval( wp_cache_get( $cache_entry_key , 'awpcp', false, $cache_entry_found ) );

    if ( $cache_entry_found ) {
        return $listings_count;
    }

    $children_categories = get_term_children( $category_id , 'awpcp_listing_category' );

    $listings_count = awpcp_listings_collection()->count_enabled_listings( array(
        'tax_query' => array(
            array(
                'taxonomy' => 'awpcp_listing_category',
                'field' => 'term_id',
                'terms' => array_merge( array( $category_id ), $children_categories ),
                'operator' => 'IN',
            )
        )
    ) );

    return $listings_count;
}

function total_ads_in_cat( $category_id ) {
    $listings_count = awpcp_get_count_of_listings_in_categories();
    return isset( $listings_count[ $category_id ] ) ? $listings_count[ $category_id ] : 0;
}

/**
 * @since feature/1112
 */
function awpcp_categories_selector() {
    $constructor_function = apply_filters(
        'awpcp-categories-selector-constructor-function',
        'awpcp_single_category_selector'
    );

    return call_user_func( $constructor_function );
}
