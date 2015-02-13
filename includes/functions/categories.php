<?php

function awpcp_build_categories_hierarchy( &$categories ) {
    $hierarchy = array();

    foreach ( $categories as $category ) {
        if ( $category->parent == 0 ) {
            $hierarchy['root'][] = $category;
        } else {
            $hierarchy[ $category->parent ][] = $category;
        }
    }

    return $hierarchy;
}

function awpcp_render_categories_dropdown_options( &$categories, &$hierarchy, $selected_category ) {
    $output = '';

    foreach ( $categories as $category ) {
        $category_name = stripslashes( stripslashes( $category->name ) );

        if( $category->id == $selected_category ) {
            $item = '<option class="dropdownparentcategory" selected="selected" value="' . $category->id . '">' . $category_name . '</option>';
            $item = '<option selected="selected" value="' . $category->id . '">- ' . $category_name . '</option>';
        } else {
            $item = '<option class="dropdownparentcategory" value="' . $category->id . '">' . $category_name . '</option>';
            $item = '<option value="' . $category->id . '">-' . $category_name . '</option>';
        }

        $output .= awpcp_render_categories_dropdown_option( $category, $selected_category );

        if ( isset( $hierarchy[ $category->id ] ) ) {
            $output .= awpcp_render_categories_dropdown_options( $hierarchy[ $category->id ], $hierarchy, $selected_category );
        }
    }

    return $output;
}

function awpcp_render_categories_dropdown_option( $category, $selected_category ) {
    if ( $selected_category == $category->id ) {
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
        esc_attr( $category->id ),
        $category_name
    );
}
